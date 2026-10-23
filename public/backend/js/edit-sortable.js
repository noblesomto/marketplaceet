
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

fileInput?.addEventListener("change", function () {
    const maxImages = window.MAX_IMAGES || 8;
    const existingCount = existingPreview
        ? existingPreview.querySelectorAll(".image-container").length
        : 0;
    const incomingCount = fileInput.files.length;

    if (existingCount + incomingCount > maxImages) {
        const errorDiv = document.getElementById('image-error');
        if (errorDiv) {
            errorDiv.textContent = "Too many images. Maximum " + maxImages + " allowed. You currently have " + existingCount + " and are adding " + incomingCount + " more.";
            errorDiv.classList.remove("d-none", "hidden");
        }
        fileInput.value = '';
        return;
    }

    Array.from(fileInput.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const newImage = document.createElement("div");
            newImage.classList.add("col-6", "col-md-3", "position-relative", "image-container");
            newImage.innerHTML = `
                <img src="${e.target.result}" class="img-fluid rounded shadow-sm">
                <button type="button" class="btn btn-sm btn-light text-danger border-0 position-absolute top-0 end-0 translate-middle rounded-circle delete-image d-flex align-items-center justify-content-center" style="width:24px;height:24px;">&times;</button>
            `;
            existingPreview.appendChild(newImage);
        };
        reader.readAsDataURL(file);
    });
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

