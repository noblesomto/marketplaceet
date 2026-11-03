/**
 * Photo picker for the post/edit ad forms (user/partials/ad-photos.blade.php).
 *
 * One ordered list holds every photo — saved (edit page), retained from a
 * failed submit ("temp"), and newly picked files. The grid renders that list;
 * on every change the form inputs are rewritten from it:
 *   images[]            new files, in display order
 *   photo_order         every photo in display order: s:<media id>, t:<n>, n:<n>
 *   temp_image_paths[]  retained uploads (post page)
 *   existing_images[] / existing_image_order / deleted_images[]  (edit page)
 *
 * Quality checks use ImageQualityValidator, but only real problems are shown:
 * a photo that will be rejected gets a red outline, a small one gets an amber
 * "!" with a one-line tip, and everything else shows nothing.
 */
(function () {
    const JOBS_CATEGORIES = [3, 18]; // no photos required
    const SMALL_PIXELS = 640000;     // below ~925×690 may look soft on a phone

    // Copy each picked file into memory right away: Android (Google Photos,
    // camera apps) can move the original, failing the upload with
    // ERR_UPLOAD_FILE_CHANGED.
    function readIntoMemory(file) {
        return new Promise(resolve => {
            const reader = new FileReader();
            reader.onload = e => resolve(new File([e.target.result], file.name, { type: file.type, lastModified: file.lastModified }));
            reader.onerror = () => resolve(file);
            reader.readAsArrayBuffer(file);
        });
    }

    function describe(result) {
        if (!result.valid) {
            const error = result.errors[0] || '';
            if (/file type/i.test(error)) return { status: 'error', message: 'Use a JPG, PNG or WebP photo.' };
            if (/too large/i.test(error)) return { status: 'error', message: 'Over 20 MB. Use a smaller photo.' };
            if (/too small \(\d+×\d+px\)/i.test(error)) return { status: 'error', message: 'Too small. Replace it.' };
            if (/too small/i.test(error)) return { status: 'error', message: 'Too low quality. Replace it.' };
            return { status: 'error', message: "Can't read this photo." };
        }
        const { width = 0, height = 0 } = result.details || {};
        if (width * height < SMALL_PIXELS) {
            return { status: 'warn', message: 'This photo is a bit small, so it may look blurry to buyers.' };
        }
        return { status: 'ok' };
    }

    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('adPhotos');
        if (!root) return;

        const form = root.closest('form');
        const grid = root.querySelector('#photoGrid');
        const addTile = grid.querySelector('[data-photo-add]');
        const fileInput = root.querySelector('#imageUpload');
        const inputs = root.querySelector('[data-photo-inputs]');
        const countLabel = root.querySelector('[data-photo-count]');
        const errorBox = root.querySelector('#image-error');
        const tileTpl = root.querySelector('template[data-tpl="tile"]');
        const loadingTpl = root.querySelector('template[data-tpl="loading"]');
        const min = parseInt(root.dataset.min, 10) || 0;
        const max = parseInt(root.dataset.max, 10) || 10;
        // A top-level `class` isn't a window property, hence typeof
        const validator = typeof ImageQualityValidator === 'function' ? new ImageQualityValidator() : null;
        const initial = JSON.parse(root.querySelector('[data-photo-initial]').textContent || '{}');

        let nextKey = 1;
        let photos = [
            ...(initial.saved || []).map(p => ({ key: nextKey++, kind: 'saved', id: p.id, url: p.url, status: 'ok' })),
            ...(initial.temp || []).map(p => ({ key: nextKey++, kind: 'temp', path: p.path, url: p.url, status: 'ok' })),
        ];
        const deletedIds = [];
        let pending = 0;
        let openMenuKey = null;

        const isJobs = () => JOBS_CATEGORIES.includes(parseInt(document.getElementById('category')?.value || 0, 10));
        const required = () => (isJobs() ? 0 : min);

        // ---- messages --------------------------------------------------------
        function showError(message) {
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }
        function clearError() {
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }
        if (errorBox.textContent.trim()) errorBox.classList.remove('hidden');

        // ---- rendering -------------------------------------------------------
        function render() {
            grid.querySelectorAll('.photo-tile, .photo-loading').forEach(el => el.remove());

            photos.forEach((photo, index) => {
                const tile = tileTpl.content.firstElementChild.cloneNode(true);
                tile.dataset.key = photo.key;
                tile.querySelector('[data-img]').src = photo.url;
                tile.querySelector('[data-img]').alt = `Photo ${index + 1}`;
                tile.querySelector('[data-cover]').classList.toggle('hidden', index !== 0);
                tile.querySelector('[data-make-cover]').classList.toggle('hidden', index === 0);

                if (photo.status === 'error') {
                    tile.classList.add('has-problem');
                    const problem = tile.querySelector('[data-problem]');
                    problem.textContent = photo.message;
                    problem.classList.remove('hidden');
                } else if (photo.status === 'warn') {
                    tile.querySelector('[data-warn]').classList.remove('hidden');
                    tile.querySelector('[data-note]').textContent = photo.message;
                }

                if (openMenuKey === photo.key) tile.querySelector('[data-menu]').classList.remove('hidden');
                grid.insertBefore(tile, addTile);
            });

            for (let i = 0; i < pending; i++) {
                grid.insertBefore(loadingTpl.content.firstElementChild.cloneNode(true), addTile);
            }

            const total = photos.length + pending;
            addTile.classList.toggle('hidden', total >= max);
            const need = required();
            countLabel.textContent = need
                ? `${photos.length} of ${max} photos · at least ${need} required`
                : `${photos.length} of ${max} photos`;
            countLabel.classList.toggle('text-red-600', photos.length < need);
            countLabel.classList.toggle('text-gray-500', photos.length >= need);

            syncInputs();
        }

        function hidden(name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            inputs.appendChild(input);
        }

        function syncInputs() {
            inputs.innerHTML = '';
            const files = new DataTransfer();
            const order = [];
            let t = 0;

            photos.forEach(photo => {
                if (photo.kind === 'saved') {
                    order.push(`s:${photo.id}`);
                    hidden('existing_images[]', photo.id);
                } else if (photo.kind === 'temp') {
                    order.push(`t:${t++}`);
                    hidden('temp_image_paths[]', photo.path);
                } else {
                    order.push(`n:${files.items.length}`);
                    files.items.add(photo.file);
                }
            });

            fileInput.files = files.files;
            hidden('photo_order', order.join(','));
            hidden('image_order', Array.from({ length: files.items.length }, (_, i) => i).join(','));
            if (initial.saved && initial.saved.length) {
                hidden('existing_image_order', photos.filter(p => p.kind === 'saved').map(p => p.id).join(','));
            }
            deletedIds.forEach(id => hidden('deleted_images[]', id));
        }

        // ---- adding ----------------------------------------------------------
        fileInput.addEventListener('change', async () => {
            const picked = Array.from(fileInput.files);
            fileInput.value = '';
            if (!picked.length) return;

            clearError();
            const room = max - photos.length - pending;
            const accepted = picked.slice(0, Math.max(0, room));
            if (picked.length > accepted.length) {
                showError(`You can add up to ${max} photos. ${picked.length - accepted.length} photo${picked.length - accepted.length === 1 ? ' was' : 's were'} not added.`);
            }
            if (!accepted.length) { render(); return; }

            pending += accepted.length;
            render();

            for (const original of accepted) {
                const file = await readIntoMemory(original);
                const quality = validator ? describe(await validator.validateImage(file)) : { status: 'ok' };
                pending--;
                photos.push({ key: nextKey++, kind: 'new', file, url: URL.createObjectURL(file), ...quality });
                render();
            }
        });

        // ---- tile actions ----------------------------------------------------
        function remove(key) {
            const photo = photos.find(p => p.key === key);
            if (!photo) return;

            // Server rule (edit): what's left after deletions, counting new
            // uploads, must still meet the minimum.
            if (photo.kind === 'saved' && photos.length - 1 < required()) {
                showError(`Your ad needs at least ${required()} photos. Add another photo before removing this one.`);
                return;
            }

            if (photo.kind === 'saved') deletedIds.push(photo.id);
            if (photo.kind === 'new') URL.revokeObjectURL(photo.url);
            photos = photos.filter(p => p.key !== key);
            openMenuKey = null;
            clearError();
            render();
        }

        function makeCover(key) {
            const photo = photos.find(p => p.key === key);
            if (!photo) return;
            photos = [photo, ...photos.filter(p => p.key !== key)];
            openMenuKey = null;
            render();
        }

        grid.addEventListener('click', e => {
            const tile = e.target.closest('.photo-tile');
            if (!tile) return;
            const key = parseInt(tile.dataset.key, 10);

            if (e.target.closest('[data-remove], [data-menu-remove]')) return remove(key);
            if (e.target.closest('[data-make-cover]')) return makeCover(key);
            if (e.target.closest('[data-warn]')) {
                tile.querySelector('[data-note]').classList.toggle('hidden');
                return;
            }
            openMenuKey = openMenuKey === key ? null : key;
            render();
        });

        document.addEventListener('click', e => {
            // composedPath, not closest(): the tapped tile may already have
            // been replaced by render() when this runs
            if (openMenuKey !== null && !e.composedPath().includes(grid)) {
                openMenuKey = null;
                render();
            }
        });

        // ---- reordering (mouse + touch) --------------------------------------
        if (window.Sortable) {
            new Sortable(grid, {
                animation: 150,
                draggable: '.photo-tile',
                filter: '[data-remove], [data-menu], [data-warn]',
                preventOnFilter: false,
                delay: 180,              // touch: hold briefly to drag, so swiping still scrolls
                delayOnTouchOnly: true,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onStart: () => { openMenuKey = null; grid.querySelectorAll('[data-menu]').forEach(m => m.classList.add('hidden')); },
                onEnd: () => {
                    const keys = Array.from(grid.querySelectorAll('.photo-tile')).map(el => parseInt(el.dataset.key, 10));
                    photos = keys.map(k => photos.find(p => p.key === k)).filter(Boolean);
                    render();
                },
            });
        }

        // ---- submit ----------------------------------------------------------
        // Capture phase, so this runs before the form's other submit handlers
        // (which show a "Processing..." state).
        form?.addEventListener('submit', e => {
            let message = null;
            if (pending) message = 'Please wait, your photos are still loading.';
            else if (photos.some(p => p.status === 'error')) message = 'Some photos can’t be used (marked in red). Remove or replace them.';
            else if (photos.length < required()) message = `Add at least ${required()} photos. You have ${photos.length}.`;
            else if (photos.length > max) message = `You can have up to ${max} photos.`;

            if (message) {
                e.preventDefault();
                e.stopImmediatePropagation();
                showError(message);
                root.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, true);

        document.getElementById('category')?.addEventListener('change', render);
        render();
    });
})();
