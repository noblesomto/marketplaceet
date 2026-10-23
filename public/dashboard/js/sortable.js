document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("imageUpload");
    const preview = document.getElementById("preview");
    const errorBox = document.getElementById("image-error");
    const form = input.closest("form");
    const MAX_IMAGES = window.MAX_IMAGES || 8;

    let fileList = [];
    let sortableInstance = null;

    // Handle file selection
    input.addEventListener("change", async (e) => {
        const newFiles = Array.from(input.files);

        if (newFiles.length === 0) return;

        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/gif'];
        const invalid = newFiles.filter(f => !allowedTypes.includes(f.type));

        if (invalid.length) {
            showError("Invalid file type(s). Only PNG, JPEG, JPG, WebP, GIF allowed.");
            return;
        }

        if (fileList.length + newFiles.length > MAX_IMAGES) {
            showError("Max " + MAX_IMAGES + " images allowed.");
            return;
        }

        hideError();

        fileList = [...fileList, ...newFiles];
        updateInputFiles();
        await renderPreview();
    });

    // Update native file input
    function updateInputFiles() {
        const dt = new DataTransfer();
        fileList.forEach(f => dt.items.add(f));
        input.files = dt.files;

        document.getElementById("image_order").value =
            fileList.map((_, i) => i).join(",");
    }

    // Error helpers
    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove("hidden");
    }
    function hideError() {
        errorBox.classList.add("hidden");
    }

    // Delete image
    function deleteFile(index) {
        fileList.splice(index, 1);
        updateInputFiles();
        renderPreview();
    }

    // Create preview item
    function createPreviewItem(file, index) {
        return new Promise(resolve => {
            const reader = new FileReader();

            reader.onload = function (e) {
                const wrapper = document.createElement("div");
                wrapper.className = "relative preview-item group cursor-move";
                wrapper.dataset.index = index;

                wrapper.innerHTML = `
                    <img src="${e.target.result}"
                         class="w-full h-24 object-cover rounded shadow">
                    <button type="button"
                        class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5
                               flex items-center justify-center opacity-0 group-hover:opacity-100 delete-btn"
                        title="Remove"
                        data-index="${index}">
                        ✖
                    </button>
                `;

                wrapper.querySelector(".delete-btn").addEventListener("click", (ev) => {
                    ev.preventDefault();
                    ev.stopPropagation();
                    deleteFile(index);
                });

                resolve(wrapper);
            };

            reader.readAsDataURL(file);
        });
    }

    // Render preview
    async function renderPreview() {
        preview.innerHTML = "";

        const items = await Promise.all(
            fileList.map((file, i) => createPreviewItem(file, i))
        );

        items.forEach(item => preview.appendChild(item));

        initializeSortable();
    }

    // SortableJS
    function initializeSortable() {
        if (sortableInstance) sortableInstance.destroy();

        sortableInstance = new Sortable(preview, {
            animation: 150,
            ghostClass: "sortable-ghost",
            onEnd: updateFileOrder
        });
    }

    function updateFileOrder() {
        const reordered = [];
        preview.querySelectorAll(".preview-item").forEach(div => {
            const idx = parseInt(div.dataset.index, 10);
            reordered.push(fileList[idx]);
        });
        fileList = reordered;
        updateInputFiles();
    }

    // Validate before submit
    form.addEventListener("submit", function (e) {
        // ✅ Check both new uploads AND temp images from previous submission
        const tempImageInputs = document.querySelectorAll('input[name="temp_image_paths[]"]');
        const tempImageCount = tempImageInputs.length;
        const totalImageCount = fileList.length + tempImageCount;

        if (totalImageCount === 0) {
            showError("Please select at least one image.");
            e.preventDefault();
        }
    });
});

// Extra styles
const style = document.createElement("style");
style.textContent = `
    .sortable-ghost {
        opacity: .5;
        background: #ddd;
    }
    .delete-btn:hover {
        transform: scale(1.1);
        background: #b91c1c;
    }
`;
document.head.appendChild(style);
