// Photos are handled by ad-photos.js (user/partials/ad-photos.blade.php).

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

