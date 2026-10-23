
document.getElementById('ad_title').addEventListener('input', function() {
    const charCount = this.value.length;
    const maxLength = 75;
    const charCountElement = document.getElementById('char-count');

    // Update character count
    charCountElement.textContent = charCount;

    // Style the input field
    if (charCount >= maxLength) {
        this.classList.remove('focus:ring-blue-500', 'border-gray-300');
        this.classList.add('border-red-500', 'focus:ring-red-500');

        // Make character counter red
        charCountElement.parentElement.classList.remove('text-gray-500');
        charCountElement.parentElement.classList.add('text-red-500', 'font-semibold');
    } else {
        this.classList.remove('border-red-500', 'focus:ring-red-500');
        this.classList.add('focus:ring-blue-500', 'border-gray-300');

        // Reset character counter to gray
        charCountElement.parentElement.classList.remove('text-red-500', 'font-semibold');
        charCountElement.parentElement.classList.add('text-gray-500');
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const trixEditor = document.querySelector('trix-editor');
    const hiddenInput = document.getElementById('content');
    const wordCountElement = document.getElementById('word-count');
    const maxLength = 3500;

    function updateCharacterCount() {
        // Check if editor is initialized
        if (!trixEditor.editor) {
            return;
        }

        // Get plain text for character counting (without HTML tags)
        const plainTextContent = trixEditor.editor.getDocument().toString();
        const charCount = plainTextContent.length;

        // Get HTML content for saving to database (preserves formatting)
        const htmlContent = trixEditor.innerHTML;

        // Update character count display
        wordCountElement.textContent = charCount;

        // Update hidden input with HTML content (preserves styling)
        hiddenInput.value = htmlContent;

        // Add red styling when at limit
        if (charCount >= maxLength) {
            trixEditor.style.border = '2px solid #ef4444';
            wordCountElement.parentElement.classList.remove('text-gray-500');
            wordCountElement.parentElement.classList.add('text-red-500', 'font-semibold');

            if (charCount > maxLength) {
                console.warn('Content exceeds maximum length');
            }
        } else {
            trixEditor.style.border = '1px solid #d1d5db';
            wordCountElement.parentElement.classList.remove('text-red-500', 'font-semibold');
            wordCountElement.parentElement.classList.add('text-gray-500');
        }
    }

    // Wait for Trix editor to initialize before setting up listeners
    trixEditor.addEventListener('trix-initialize', function() {
        // Initial count after initialization
        updateCharacterCount();
    });

    // Listen for text changes in Trix editor
    trixEditor.addEventListener('trix-change', updateCharacterCount);

    // Also listen for other Trix events that might change content
    trixEditor.addEventListener('trix-attachment-add', updateCharacterCount);
    trixEditor.addEventListener('trix-attachment-remove', updateCharacterCount);
});


const adTitleInput = document.getElementById('ad_title');
    // Regex to remove emojis and non-standard symbols
    const emojiRegex = /[^A-Za-z0-9\s\-\.,;:()'"!?[\]_]/g;

    adTitleInput.addEventListener('input', function() {
        // Remove emojis and non-standard symbols
        this.value = this.value.replace(emojiRegex, '');
       
    });