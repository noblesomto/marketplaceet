/**
 * Device model field — Mobile Phones and Laptops & Computers only show the
 * Model dropdown when the chosen brand has models (e.g. Apple → iPhone 12 Pro
 * Max, HP → EliteBook). It is then required, with an "Other / not listed"
 * escape so a missing model never blocks posting (the server stores it as no
 * model). Brands without models keep the dropdown hidden. Shared by post-ad,
 * edit-ad and the admin editor. Keep SUBCATEGORIES in sync with
 * App\Support\DeviceModels::SUBCATEGORIES.
 */
window.DeviceModelField = {
    SUBCATEGORIES: ['6', '27'],
    OTHER: 'other',

    isDeviceSubcategory(subcategoryId) {
        return this.SUBCATEGORIES.includes(String(subcategoryId));
    },

    /**
     * Call after the model <select> has been filled with the brand's models.
     * Safe to call again on the same <select>.
     */
    apply(subcategoryId, models, { select, container = null, selected = null, required = true } = {}) {
        if (!select || !this.isDeviceSubcategory(subcategoryId)) return;

        const div = container || document.getElementById('divModel');
        select.querySelector(`option[value="${this.OTHER}"]`)?.remove();

        if (models && models.length) {
            if (select.options[0] && select.options[0].value === '') {
                select.options[0].text = 'Select Model';
            }

            const other = document.createElement('option');
            other.value = this.OTHER;
            other.text = 'Other / not listed';
            select.appendChild(other);

            if (selected) select.value = String(selected);

            div?.classList.remove('hidden');
            if (required) select.setAttribute('required', 'required');
        } else {
            div?.classList.add('hidden');
            select.removeAttribute('required');
            select.value = '';
        }
    },

    /**
     * Edit pages: the server already rendered the brand's models into the
     * <select>, but the category UI rules hide the Model field for phones on
     * load — re-apply using those rendered options.
     */
    applyToRenderedOptions(subcategoryId, options = {}) {
        const select = options.select;
        if (!select) return;

        const models = [...select.options].filter(o => o.value && o.value !== this.OTHER);
        this.apply(subcategoryId, models, { ...options, selected: options.selected || select.value || null });
    },

    /** Laptops & Computers: the optional Computer Specs card (#divComputer). */
    COMPUTERS_SUBCATEGORY: '27',

    toggleComputerSpecs(subcategoryId) {
        document.getElementById('divComputer')
            ?.classList.toggle('hidden', String(subcategoryId) !== this.COMPUTERS_SUBCATEGORY);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const subcategory = document.getElementById('subcategory');
    if (!subcategory || !document.getElementById('divComputer')) return;

    subcategory.addEventListener('change', () => window.DeviceModelField.toggleComputerSpecs(subcategory.value));
});
