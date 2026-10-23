
// ==================== Image Upload and Management ====================
const existingPreview = document.getElementById("preview");
const orderInput = document.getElementById("existing_image_order");
const fileInput = document.getElementById("imageUpload");
const form = document.getElementById("advertForm");

if (existingPreview) {
    new Sortable(existingPreview, {
        animation: 150,
        handle: '.image-container',
        onEnd: updateOrderInput
    });

    existingPreview.addEventListener("click", function (e) {
        if (e.target.classList.contains("delete-image")) {
            const imageId = e.target.dataset.id;
            const container = e.target.closest(".image-container");

            // Only saved images carry a data-id; only those count against the
            // minimum. This is advisory UX only — the server re-validates and
            // is the actual source of truth, so it's fine if this is imprecise.
            if (imageId) {
                const minImages = window.MIN_IMAGES || 0;
                const savedCount = existingPreview.querySelectorAll(".image-container[data-id]").length;
                if (savedCount - 1 < minImages) {
                    const errorDiv = document.getElementById('image-error');
                    const message = "Cannot delete this image. Adverts must have at least " + minImages + (minImages === 1 ? " image." : " images.");
                    if (errorDiv) {
                        errorDiv.textContent = message;
                        errorDiv.classList.remove("hidden", "d-none");
                        errorDiv.scrollIntoView({ behavior: "smooth", block: "center" });
                    } else {
                        alert(message);
                    }
                    return;
                }
            }

            if (imageId && form) {
                const deletedInput = document.createElement('input');
                deletedInput.type = 'hidden';
                deletedInput.name = 'deleted_images[]';
                deletedInput.value = imageId;
                form.appendChild(deletedInput);
            }

            container.remove();

            const hiddenInput = document.querySelector(`input[name="existing_images[]"][value="${imageId}"]`);
            if (hiddenInput) hiddenInput.remove();

            updateOrderInput();
        }
    });
}

// Read file into memory immediately so Android path changes (Google Photos,
// camera apps) don't cause ERR_UPLOAD_FILE_CHANGED on form submit.
function readFileIntoMemory(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const blob = new Blob([e.target.result], { type: file.type });
            resolve(new File([blob], file.name, { type: file.type, lastModified: file.lastModified }));
        };
        reader.onerror = () => resolve(file);
        reader.readAsArrayBuffer(file);
    });
}

// Accumulates in-memory copies across multiple selections
let editAccumulatedDT = new DataTransfer();

fileInput?.addEventListener("change", async function () {
    const maxImages = window.MAX_IMAGES || 8;
    const existingCount = existingPreview
        ? existingPreview.querySelectorAll(".image-container").length
        : 0;
    const incomingCount = fileInput.files.length;

    if (existingCount + editAccumulatedDT.files.length + incomingCount > maxImages) {
        const errorDiv = document.getElementById('image-error');
        if (errorDiv) {
            const currentNew = editAccumulatedDT.files.length;
            errorDiv.textContent = "Too many images. Maximum " + maxImages + " allowed. You currently have " + existingCount + " existing and " + currentNew + " new, and are adding " + incomingCount + " more.";
            errorDiv.classList.remove("hidden");
            errorDiv.scrollIntoView({ behavior: "smooth", block: "center" });
        }
        fileInput.value = '';
        return;
    }

    for (const file of fileInput.files) {
        const memFile = await readFileIntoMemory(file);
        editAccumulatedDT.items.add(memFile);

        // Generate preview using the in-memory file
        const previewReader = new FileReader();
        previewReader.onload = function (e) {
            const newImage = document.createElement("div");
            newImage.classList.add("relative", "group", "cursor-move", "image-container");
            newImage.innerHTML = `
                <img src="${e.target.result}" class="w-full h-auto rounded-lg shadow">
                <button type="button" class="absolute top-0 right-0 w-6 h-6 text-red-500 bg-white rounded-full hover:bg-red-100 delete-image flex items-center justify-center">&times;</button>
            `;
            existingPreview.appendChild(newImage);
        };
        previewReader.readAsDataURL(memFile);
    }

    // Keep fileInput.files in sync with accumulated in-memory files
    fileInput.files = editAccumulatedDT.files;
});

function updateOrderInput() {
    if (!orderInput) return;
    const ids = Array.from(existingPreview.querySelectorAll(".image-container[data-id]"))
        .map(el => el.dataset.id);
    orderInput.value = ids.join(',');
}

// Initial population
updateOrderInput();

// ==================== LGA Initialization ====================
    // Get advert data from global variable (passed from Blade)
    if (window.advertData && window.advertData.state) {
        const stateSelect = document.getElementById('state');
        if (stateSelect) {
            stateSelect.value = window.advertData.state;
            // toggleLGA is async (fetch-based) — await it before restoring the LGA value
            toggleLGA(stateSelect).then(() => {
                const lgaSelect = document.getElementById('lga');
                if (lgaSelect && window.advertData.lga) {
                    lgaSelect.value = window.advertData.lga;
                }
            });
        }
    }

