
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


document.addEventListener('DOMContentLoaded', function() {
    const trixEditor = document.querySelector('trix-editor');
    const hiddenInput = document.getElementById('content');
    let emojisRemoved = false;
    let linksRemoved = false;

    // Emoji detection regex
    const emojiRegex = /[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F1E0}-\u{1F1FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FA6F}\u{1FA70}-\u{1FAFF}]/gu;

    // Link detection regex
    const urlRegex = /(https?:\/\/[^\s]+|www\.[^\s]+)/gi;
    const anchorTagRegex = /<a[^>]*>(.*?)<\/a>/gi;

    trixEditor.addEventListener('trix-change', function(e) {
        let content = hiddenInput.value;

        // === REMOVE EMOJIS ===
        if (emojiRegex.test(content)) {
            let cleanContent = content.replace(emojiRegex, '');
            hiddenInput.value = cleanContent;
            trixEditor.editor.loadHTML(cleanContent);

            if (!emojisRemoved) {
                showNotification('Emojis are automatically removed from descriptions');
                emojisRemoved = true;
            }
            return; // prevent double rendering
        }

        // === REMOVE LINKS ===
        if (urlRegex.test(content) || anchorTagRegex.test(content)) {
            let cleanContent = content
                .replace(urlRegex, '')      // remove plain URLs
                .replace(anchorTagRegex, '$1'); // keep inner text of anchor tags

            hiddenInput.value = cleanContent;
            trixEditor.editor.loadHTML(cleanContent);

            if (!linksRemoved) {
                showNotification('Links are not allowed and have been removed');
                linksRemoved = true;
            }
            return;
        }

        // === UPDATE CHARACTER COUNT ===
        let text = trixEditor.editor.getDocument().toString();
        document.getElementById('word-count').textContent = text.length;
    });

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg text-sm';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }
});



    const displayInput = document.getElementById('price_display');
    const hiddenInput = document.getElementById('price_hidden');

    displayInput.addEventListener('input', function(e) {
        // Remove all non-digit characters
        let value = e.target.value.replace(/\D/g, '');

        // Update hidden input with raw value
        hiddenInput.value = value;

        // Format display value with commas
        if (value) {
            e.target.value = parseInt(value).toLocaleString('en-US');
        } else {
            e.target.value = '';
        }
    });
