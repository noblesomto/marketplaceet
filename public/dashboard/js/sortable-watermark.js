document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("imageUpload");
    const preview = document.getElementById("preview");
    const errorBox = document.getElementById("image-error");
    const form = input.closest("form");
    const uploadLabel = document.querySelector('label[for="imageUpload"]');
    let fileList = [];
    let sortableInstance = null;
    let watermarkFlags = [];

    // FIX 1: Add explicit click handler for Android PWA
    if (uploadLabel) {
        uploadLabel.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            input.click();
        });

        // Also handle touch events for Android
        uploadLabel.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            input.click();
        });
    }

    // FIX 2: Make the input itself clickable as fallback
    const cameraIcon = uploadLabel?.querySelector('svg')?.parentElement;
    if (cameraIcon) {
        cameraIcon.style.pointerEvents = 'auto';
        cameraIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            input.click();
        });
    }

    // File input change
    input.addEventListener("change", async (e) => {
        // FIX 3: Ensure event is properly captured
        e.stopPropagation();

        const newFiles = Array.from(input.files);

        if (newFiles.length === 0) {
            console.log('No files selected');
            return;
        }

        console.log(`Selected ${newFiles.length} files`);

        // Validate file types
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/gif'];
        const invalidFiles = newFiles.filter(file => !allowedTypes.includes(file.type));

        if (invalidFiles.length > 0) {
            errorBox.textContent = `Invalid file type(s). Only PNG, JPEG, JPG, WebP, and GIF are allowed.`;
            errorBox.classList.remove("hidden");
            return;
        }

        if (fileList.length + newFiles.length > 20) {
            errorBox.textContent = "Maximum 20 images allowed. Please select fewer images.";
            errorBox.classList.remove("hidden");
            return;
        }

        fileList = [...fileList, ...newFiles];

        const newWatermarkFlags = Array(newFiles.length).fill(false);
        watermarkFlags = [...watermarkFlags, ...newWatermarkFlags];

        updateFileInput();
        await renderPreview();
        errorBox.classList.add("hidden");
    });

    // Initialize Sortable
    function initializeSortable() {
        if (sortableInstance) {
            sortableInstance.destroy();
        }

        sortableInstance = new Sortable(preview, {
            animation: 150,
            handle: '.preview-item',
            ghostClass: 'sortable-ghost',
            onEnd: (evt) => {
                const newOrder = [];
                const newWatermarkFlags = [];

                preview.querySelectorAll(".preview-item").forEach(div => {
                    const index = parseInt(div.dataset.originalIndex);
                    newOrder.push(fileList[index]);
                    newWatermarkFlags.push(watermarkFlags[index]);
                });

                fileList = newOrder;
                watermarkFlags = newWatermarkFlags;
                updateFileInput();
                updateDatasetIndices();
            }
        });
    }

    // Validate on submit
    form?.addEventListener("submit", function (e) {
        const validImages = fileList.filter((_, index) => !watermarkFlags[index]);

        // ✅ Check both new uploads AND temp images from previous submission
        const tempImageInputs = document.querySelectorAll('input[name="temp_image_paths[]"]');
        const tempImageCount = tempImageInputs.length;
        const totalValidImageCount = validImages.length + tempImageCount;

        if (!totalValidImageCount && typeof selectedCategoryId !== 'undefined' && selectedCategoryId != 3) {
            e.preventDefault();
            errorBox.textContent = "Please select at least one image without watermarks before submitting.";
            errorBox.classList.remove("hidden");
            errorBox.scrollIntoView({ behavior: "smooth" });
            return;
        }

        const watermarkedCount = watermarkFlags.filter(flag => flag).length;
        if (watermarkedCount > 0) {
            const confirmed = confirm(`${watermarkedCount} image(s) with watermarks will be excluded. Continue?`);
            if (!confirmed) {
                e.preventDefault();
            }
        }
    });

    // Update dataset indices
    function updateDatasetIndices() {
        preview.querySelectorAll(".preview-item").forEach((div, index) => {
            div.dataset.originalIndex = index;
            const deleteBtn = div.querySelector('.delete-btn');
            const overrideBtn = div.querySelector('.override-btn');

            if (deleteBtn) deleteBtn.dataset.index = index;
            if (overrideBtn) overrideBtn.dataset.index = index;
        });
    }

    // Update file input
    function updateFileInput() {
        const dt = new DataTransfer();

        fileList.forEach((file, index) => {
            if (!watermarkFlags[index]) {
                dt.items.add(file);
            }
        });

        input.files = dt.files;

        const orderInput = document.getElementById("image_order");
        if (orderInput) {
            const orderArray = fileList
                .map((file, index) => !watermarkFlags[index] ? index : -1)
                .filter(idx => idx !== -1);
            orderInput.value = orderArray.join(",");
        }
    }

    // Enhanced watermark detection with multiple methods
    async function detectWatermark(file, imgElement) {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Resize for faster processing while maintaining detection quality
            const maxSize = 600;
            let width = imgElement.width;
            let height = imgElement.height;

            if (width > maxSize || height > maxSize) {
                if (width > height) {
                    height = Math.round((height / width) * maxSize);
                    width = maxSize;
                } else {
                    width = Math.round((width / height) * maxSize);
                    height = maxSize;
                }
            }

            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(imgElement, 0, 0, width, height);

            const imageData = ctx.getImageData(0, 0, width, height);

            // Use multiple detection methods with weighted scoring
            const methods = [
                { method: detectCornerWatermarks, weight: 0.4 },
                { method: detectEdgeDensityWatermarks, weight: 0.3 },
                { method: detectUniformRegions, weight: 0.2 },
                { method: detectLogoPatterns, weight: 0.1 }
            ];

            let totalScore = 0;
            let methodResults = [];

            for (const { method, weight } of methods) {
                const result = await method(imageData, width, height);
                methodResults.push({ method: method.name, result, weight });
                totalScore += result ? weight : 0;
            }

            console.log('Watermark detection results:', {
                file: file.name,
                methods: methodResults,
                totalScore: totalScore.toFixed(2)
            });

            return totalScore >= 0.5;

        } catch (error) {
            console.error('Error detecting watermark:', error);
            return false;
        }
    }

    // Method 1: Detect watermarks in corners
    function detectCornerWatermarks(imageData, width, height) {
        const { data } = imageData;
        const cornerSize = Math.min(width, height) * 0.2;

        const corners = [
            { x: 0, y: 0, width: cornerSize, height: cornerSize },
            { x: width - cornerSize, y: 0, width: cornerSize, height: cornerSize },
            { x: 0, y: height - cornerSize, width: cornerSize, height: cornerSize },
            { x: width - cornerSize, y: height - cornerSize, width: cornerSize, height: cornerSize }
        ];

        let watermarkCorners = 0;

        for (const corner of corners) {
            if (analyzeRegionForWatermark(data, corner.x, corner.y, corner.width, corner.height, width)) {
                watermarkCorners++;
            }
        }

        return watermarkCorners >= 2;
    }

    // Method 2: Detect high edge density regions
    function detectEdgeDensityWatermarks(imageData, width, height) {
        const { data } = imageData;

        const regions = [
            { x: 0, y: 0, width: width, height: height * 0.15 },
            { x: 0, y: height * 0.85, width: width, height: height * 0.15 },
            { x: 0, y: height * 0.4, width: width, height: height * 0.2 }
        ];

        let highDensityRegions = 0;

        for (const region of regions) {
            if (analyzeRegionForWatermark(data, region.x, region.y, region.width, region.height, width)) {
                highDensityRegions++;
            }
        }

        return highDensityRegions >= 1;
    }

    // Method 3: Detect uniform color regions
    function detectUniformRegions(imageData, width, height) {
        const { data } = imageData;
        const cornerSize = Math.min(width, height) * 0.15;

        const corners = [
            { x: 5, y: 5, width: cornerSize, height: cornerSize / 3 },
            { x: width - cornerSize - 5, y: 5, width: cornerSize, height: cornerSize / 3 }
        ];

        for (const corner of corners) {
            if (isUniformRegion(data, corner.x, corner.y, corner.width, corner.height, width)) {
                return true;
            }
        }

        return false;
    }

    // Method 4: Detect logo-like patterns
    function detectLogoPatterns(imageData, width, height) {
        const { data } = imageData;

        const logoSize = Math.min(width, height) * 0.1;
        const regions = [
            { x: 10, y: 10, width: logoSize, height: logoSize },
            { x: width - logoSize - 10, y: 10, width: logoSize, height: logoSize },
            { x: 10, y: height - logoSize - 10, width: logoSize, height: logoSize },
            { x: width - logoSize - 10, y: height - logoSize - 10, width: logoSize, height: logoSize }
        ];

        for (const region of regions) {
            if (isLogoLikeRegion(data, region.x, region.y, region.width, region.height, width)) {
                return true;
            }
        }

        return false;
    }

    // Helper functions
    function analyzeRegionForWatermark(data, startX, startY, regionWidth, regionHeight, imageWidth) {
        let edgeCount = 0;
        let contrastSum = 0;
        let pixelCount = 0;

        const endX = Math.min(startX + regionWidth, imageWidth);
        const endY = Math.min(startY + regionHeight, Math.floor(data.length / (imageWidth * 4)));

        for (let y = Math.floor(startY); y < endY; y += 2) {
            for (let x = Math.floor(startX); x < endX; x += 2) {
                const i = (y * imageWidth + x) * 4;

                if (i + 7 >= data.length) continue;

                const brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;

                if (x + 2 < endX) {
                    const rightBrightness = (data[i + 4] + data[i + 5] + data[i + 6]) / 3;
                    const contrast = Math.abs(brightness - rightBrightness);
                    contrastSum += contrast;
                    if (contrast > 80) {
                        edgeCount++;
                    }
                }

                if (y + 2 < endY) {
                    const bottomIndex = i + imageWidth * 4;
                    const bottomBrightness = (data[bottomIndex] + data[bottomIndex + 1] + data[bottomIndex + 2]) / 3;
                    const contrast = Math.abs(brightness - bottomBrightness);
                    contrastSum += contrast;
                    if (contrast > 80) {
                        edgeCount++;
                    }
                }

                pixelCount += 2;
            }
        }

        if (pixelCount === 0) return false;

        const edgeDensity = edgeCount / pixelCount;
        const averageContrast = contrastSum / pixelCount;

        return edgeDensity > 0.15 || averageContrast > 60;
    }

    function isUniformRegion(data, startX, startY, regionWidth, regionHeight, imageWidth) {
        let colorVariance = 0;
        let sampleCount = 0;
        let previousColor = null;

        for (let y = startY; y < startY + regionHeight; y += 2) {
            for (let x = startX; x < startX + regionWidth; x += 2) {
                const i = (y * imageWidth + x) * 4;
                const color = (data[i] << 16) | (data[i + 1] << 8) | data[i + 2];

                if (previousColor !== null) {
                    colorVariance += Math.abs(color - previousColor);
                }
                previousColor = color;
                sampleCount++;
            }
        }

        return sampleCount > 0 && (colorVariance / sampleCount) < 1000;
    }

    function isLogoLikeRegion(data, startX, startY, regionWidth, regionHeight, imageWidth) {
        let highContrastPixels = 0;
        let totalPixels = 0;

        for (let y = startY; y < startY + regionHeight; y++) {
            for (let x = startX; x < startX + regionWidth; x++) {
                const i = (y * imageWidth + x) * 4;

                if (i + 7 >= data.length) continue;

                const brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;

                for (let dy = -1; dy <= 1; dy += 2) {
                    for (let dx = -1; dx <= 1; dx += 2) {
                        const nx = x + dx;
                        const ny = y + dy;

                        if (nx >= startX && nx < startX + regionWidth &&
                            ny >= startY && ny < startY + regionHeight) {
                            const ni = (ny * imageWidth + nx) * 4;
                            const neighborBrightness = (data[ni] + data[ni + 1] + data[ni + 2]) / 3;

                            if (Math.abs(brightness - neighborBrightness) > 100) {
                                highContrastPixels++;
                            }
                        }
                    }
                }

                totalPixels++;
            }
        }

        return totalPixels > 0 && (highContrastPixels / totalPixels) > 0.3;
    }

    // Create preview item
    function createPreviewItem(file, index) {
        return new Promise(async (resolve) => {
            const reader = new FileReader();

            reader.onload = async function (e) {
                const img = new Image();

                img.onload = async function () {
                    try {
                        const hasWatermark = await detectWatermark(file, img);
                        watermarkFlags[index] = hasWatermark;

                        const wrapper = document.createElement("div");
                        wrapper.className = `relative preview-item group ${hasWatermark ? 'watermarked' : ''}`;
                        wrapper.dataset.originalIndex = index;

                        const watermarkBadge = hasWatermark ? `
                            <div class="absolute top-0 left-0 right-0 bg-red-500 text-white text-xs px-2 py-1 flex items-center justify-between z-20">
                                <span>⚠ Potential Watermark Detected</span>
                                <button type="button"
                                        class="override-btn underline hover:text-red-100"
                                        data-index="${index}"
                                        title="Keep this image">
                                    Keep
                                </button>
                            </div>
                            <div class="absolute inset-0 bg-red-500 bg-opacity-30 pointer-events-none"></div>
                        ` : '';

                        wrapper.innerHTML = `
                            ${watermarkBadge}
                            <img src="${e.target.result}"
                                 class="w-full h-auto object-cover rounded shadow cursor-move ${hasWatermark ? 'opacity-60' : ''}"
                                 draggable="false">
                            <button type="button"
                                    class="absolute ${hasWatermark ? 'top-8' : 'top-1'} right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition delete-btn z-20"
                                    title="Remove"
                                    data-index="${index}">✖</button>
                        `;

                        const deleteBtn = wrapper.querySelector('.delete-btn');
                        deleteBtn.addEventListener("click", function (e) {
                            e.stopPropagation();
                            const index = parseInt(this.dataset.index);
                            deleteFile(index);
                        });

                        if (hasWatermark) {
                            const overrideBtn = wrapper.querySelector('.override-btn');
                            overrideBtn.addEventListener("click", function (e) {
                                e.stopPropagation();
                                const index = parseInt(this.dataset.index);
                                overrideWatermarkFlag(index);
                            });
                        }

                        resolve(wrapper);
                    } catch (error) {
                        console.error('Error creating preview item:', error);
                        watermarkFlags[index] = false;
                        const wrapper = document.createElement("div");
                        wrapper.className = "relative preview-item group";
                        wrapper.dataset.originalIndex = index;

                        wrapper.innerHTML = `
                            <img src="${e.target.result}"
                                 class="w-full h-auto object-cover rounded shadow cursor-move"
                                 draggable="false">
                            <button type="button"
                                    class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition delete-btn z-20"
                                    title="Remove"
                                    data-index="${index}">✖</button>
                        `;

                        const deleteBtn = wrapper.querySelector('.delete-btn');
                        deleteBtn.addEventListener("click", function (e) {
                            e.stopPropagation();
                            const index = parseInt(this.dataset.index);
                            deleteFile(index);
                        });

                        resolve(wrapper);
                    }
                };

                img.onerror = function() {
                    console.error('Error loading image:', file.name);
                    const errorWrapper = document.createElement("div");
                    errorWrapper.className = "relative preview-item group border border-red-300 bg-red-50";
                    errorWrapper.innerHTML = `
                        <div class="w-full h-24 flex items-center justify-center text-red-500 text-xs">
                            Error loading image
                        </div>
                        <button type="button"
                                class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center delete-btn"
                                title="Remove"
                                data-index="${index}">✖</button>
                    `;

                    const deleteBtn = errorWrapper.querySelector('.delete-btn');
                    deleteBtn.addEventListener("click", function (e) {
                        e.stopPropagation();
                        const index = parseInt(this.dataset.index);
                        deleteFile(index);
                    });

                    resolve(errorWrapper);
                };

                img.src = e.target.result;
            };

            reader.onerror = function() {
                console.error('Error reading file:', file.name);
                resolve(null);
            };

            reader.readAsDataURL(file);
        });
    }

    function overrideWatermarkFlag(index) {
        watermarkFlags[index] = false;
        updateFileInput();
        renderPreview();
    }

    function deleteFile(index) {
        fileList.splice(index, 1);
        watermarkFlags.splice(index, 1);
        updateFileInput();
        renderPreview();
    }

    async function renderPreview() {
        preview.innerHTML = "";

        if (fileList.length === 0) {
            return;
        }

        try {
            const previewItems = [];
            for (let i = 0; i < fileList.length; i++) {
                const item = await createPreviewItem(fileList[i], i);
                if (item) {
                    previewItems.push(item);
                }
            }

            previewItems.forEach(item => {
                if (item) {
                    preview.appendChild(item);
                }
            });

            if (previewItems.length > 0) {
                initializeSortable();
            }

            const watermarkedCount = watermarkFlags.filter(flag => flag).length;
            if (watermarkedCount > 0) {
                errorBox.textContent = `${watermarkedCount} image(s) with potential watermarks detected. These will be excluded unless you click "Keep".`;
                errorBox.classList.remove("hidden");
                errorBox.classList.remove("bg-red-100", "text-red-800");
                errorBox.classList.add("bg-yellow-100", "text-yellow-800");
            } else {
                errorBox.classList.add("hidden");
            }
        } catch (error) {
            console.error('Error rendering preview:', error);
            errorBox.textContent = 'Error loading image previews. Please try again.';
            errorBox.classList.remove("hidden");
            errorBox.classList.remove("bg-yellow-100", "text-yellow-800");
            errorBox.classList.add("bg-red-100", "text-red-800");
        }
    }
});

const style = document.createElement('style');
style.textContent = `
    .sortable-ghost {
        opacity: 0.5;
        background: #f0f0f0;
    }

    .preview-item {
        cursor: move;
        transition: all 0.2s ease;
        position: relative;
        min-height: 80px;
    }

    .preview-item:hover {
        transform: scale(1.02);
    }

    .preview-item.watermarked {
        border: 2px solid #ef4444;
        border-radius: 0.375rem;
    }

    .delete-btn, .override-btn {
        cursor: pointer;
    }

    .delete-btn:hover {
        background-color: #dc2626 !important;
        transform: scale(1.1);
    }

    .override-btn:hover {
        font-weight: 600;
    }

    #preview {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 0.5rem;
        width: 100%;
        padding: 0.5rem;
    }

    #preview img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 0.25rem;
    }

    /* Android PWA fixes */
    label[for="imageUpload"] {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }
`;
document.head.appendChild(style);
