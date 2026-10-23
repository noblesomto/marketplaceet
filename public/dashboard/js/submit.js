
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="/user/post-ad"]');
    const submitButton = form.querySelector('button[type="submit"]');

    if (!submitButton) return;

    // Store original button content
    const originalContent = submitButton.innerHTML;

    form.addEventListener('submit', function(e) {
        // ✅ DISABLE HIDDEN FIELDS TO PREVENT VALIDATION
        // Hidden fields (salary, expected_salary, etc.) shouldn't be validated
        const hiddenContainers = form.querySelectorAll('.hidden');
        hiddenContainers.forEach(container => {
            const inputs = container.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                // Mark as disabled so they won't be submitted
                if (!input.hasAttribute('data-always-submit')) {
                    input.setAttribute('data-was-disabled', 'true');
                    input.disabled = true;
                }
            });
        });

        // Check if form is valid before disabling
        if (!form.checkValidity()) {
            // Re-enable fields if validation fails
            const disabledInputs = form.querySelectorAll('[data-was-disabled]');
            disabledInputs.forEach(input => {
                input.disabled = false;
                input.removeAttribute('data-was-disabled');
            });
            return; // Let browser show validation messages
        }

        // Disable the button
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');

        // Change button text to show loading
        submitButton.innerHTML = `
            <span class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            </span>
        `;

        // Optional: Re-enable after 10 seconds as a fallback (in case redirect fails)
        setTimeout(function() {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            submitButton.innerHTML = originalContent;
        }, 10000);
    });
});
