/**
 * Buy Direct field on the post/edit ad forms.
 *
 * - "Pickup Only" hides the Buy Direct section and turns it off: escrow needs
 *   the item to be handed over/delivered, which pickup-only listings opt out of.
 * - Buy Direct can't be enabled until the seller has bank details on file
 *   (#buyDirect[data-has-bank="0"]); choosing it opens the bank details popup
 *   (user/partials/bank-details-modal.blade.php) instead, and saving there
 *   enables it without leaving the page.
 *
 * The server enforces the same rule (AdvertValidationService::validateShipping).
 */
(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const section = document.getElementById('buyDirect');
        if (!section) return;

        const yes = section.querySelector('input[name="buy_direct"][value="Yes"]');
        const no = section.querySelector('input[name="buy_direct"][value="No"]');
        const shipmentRadios = document.querySelectorAll('input[name="shipment"]');
        const modal = document.getElementById('bankDetailsModal');
        const form = document.getElementById('bankDetailsForm');

        const hasBank = () => section.dataset.hasBank === '1';
        const isPickup = () => document.querySelector('input[name="shipment"]:checked')?.value === 'Pickup';

        // ---- Pickup Only hides Buy Direct ----------------------------------
        // Inline display, not the `hidden` class: CategoryUIManager owns that
        // class for categories where Buy Direct doesn't apply at all.
        // Also runs after the legacy inline toggleShipping(), which ticks
        // "Enable Buy Direct" whenever Shipping is chosen — undo that for
        // sellers without bank details.
        function syncWithShipment() {
            const pickup = isPickup();
            section.style.display = pickup ? 'none' : '';
            if ((pickup || !hasBank()) && yes?.checked && no) no.checked = true;
        }

        shipmentRadios.forEach(radio => radio.addEventListener('change', syncWithShipment));
        syncWithShipment();

        // ---- Bank details gate ---------------------------------------------
        if (!yes || !modal || !form) return;

        // A failed submit can bring back "Yes" for a seller without a bank
        if (!hasBank() && yes.checked && no) no.checked = true;

        function openModal() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => form.querySelector('select, input:not([type="hidden"])')?.focus(), 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        yes.addEventListener('click', e => {
            if (hasBank()) return;
            e.preventDefault(); // keeps "No" selected
            openModal();
        });

        section.querySelectorAll('[data-open-bank-modal]').forEach(el =>
            el.addEventListener('click', e => { e.preventDefault(); openModal(); })
        );

        modal.querySelectorAll('[data-bank-modal-close]').forEach(el => el.addEventListener('click', closeModal));
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });

        const bankSelect = form.querySelector('select[name="bank_name"]');
        const bankCode = form.querySelector('input[name="bank_code"]');
        bankSelect?.addEventListener('change', () => {
            bankCode.value = bankSelect.selectedOptions[0]?.dataset.bankCode || '';
        });

        const errorBox = form.querySelector('[data-bank-error]');
        const submit = form.querySelector('button[type="submit"]');

        function showError(message) {
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorBox.classList.add('hidden');

            const data = new FormData(form);
            const missing = ['bank_name', 'account_name', 'account_number'].some(k => !String(data.get(k) || '').trim());
            if (missing || !data.get('bank_code')) {
                showError('Please choose your bank and enter your account name and number.');
                return;
            }

            submit.disabled = true;
            const label = submit.textContent;
            submit.textContent = 'Saving...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: data,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const body = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const errors = body.errors ? Object.values(body.errors).flat() : [];
                    showError(errors[0] || body.message || 'Could not save your account details. Please try again.');
                    return;
                }

                section.dataset.hasBank = '1';
                section.querySelector('[data-bank-notice]')?.remove();
                yes.checked = true;
                yes.dispatchEvent(new Event('change', { bubbles: true }));
                closeModal();

                window.Swal?.fire({
                    toast: true, position: 'top-end', icon: 'success', timer: 3000, showConfirmButton: false,
                    title: 'Account details saved — Buy Direct enabled',
                });
            } catch (error) {
                showError('Network error. Check your connection and try again.');
            } finally {
                submit.disabled = false;
                submit.textContent = label;
            }
        });
    });
})();
