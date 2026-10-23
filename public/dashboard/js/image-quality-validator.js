/**
 * Client-Side Image Quality Validator - TIER 1
 * Validates images before upload to provide instant feedback
 */

class ImageQualityValidator {
    constructor(options = {}) {
        this.config = {
            minWidth: options.minWidth || 800,
            minHeight: options.minHeight || 600,
            recommendedWidth: options.recommendedWidth || 1200,
            recommendedHeight: options.recommendedHeight || 900,
            minFileSize: options.minFileSize || 50 * 1024, // 50KB
            maxFileSize: options.maxFileSize || 20 * 1024 * 1024, // 20MB
            allowedTypes: options.allowedTypes || ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
        };
    }

    /**
     * Validate a single image file
     * @param {File} file - The image file to validate
     * @returns {Promise<Object>} Validation result
     */
    async validateImage(file) {
        const result = {
            valid: true,
            errors: [],
            warnings: [],
            score: 100,
            details: {}
        };

        // Check file type
        if (!this.config.allowedTypes.includes(file.type)) {
            result.valid = false;
            result.errors.push(`Invalid file type. Please upload JPEG, PNG, or WebP images only.`);
            return result;
        }

        // Check file size
        result.details.fileSize = file.size;
        result.details.fileSizeFormatted = this.formatFileSize(file.size);

        if (file.size < this.config.minFileSize) {
            result.valid = false;
            result.errors.push(`File too small (${this.formatFileSize(file.size)}). Minimum is ${this.formatFileSize(this.config.minFileSize)}.`);
            result.score -= 50;
        }

        if (file.size > this.config.maxFileSize) {
            result.valid = false;
            result.errors.push(`File too large (${this.formatFileSize(file.size)}). Maximum is ${this.formatFileSize(this.config.maxFileSize)}.`);
            result.score -= 50;
        }

        // Load image to check dimensions
        try {
            const dimensions = await this.getImageDimensions(file);
            result.details.width = dimensions.width;
            result.details.height = dimensions.height;

            // Check minimum dimensions
            if (dimensions.width < this.config.minWidth || dimensions.height < this.config.minHeight) {
                result.valid = false;
                result.errors.push(
                    `Image too small (${dimensions.width}×${dimensions.height}px). ` +
                    `Minimum required is ${this.config.minWidth}×${this.config.minHeight}px.`
                );
                result.score -= 50;
            }

            // Warn about recommended dimensions
            if (result.valid && (dimensions.width < this.config.recommendedWidth || dimensions.height < this.config.recommendedHeight)) {
                result.warnings.push(
                    `For best results, use images at least ${this.config.recommendedWidth}×${this.config.recommendedHeight}px. ` +
                    `Your image is ${dimensions.width}×${dimensions.height}px.`
                );
                result.score -= 15;
            }

            // Calculate quality score based on resolution
            const pixels = dimensions.width * dimensions.height;
            const recommendedPixels = this.config.recommendedWidth * this.config.recommendedHeight;

            if (pixels < recommendedPixels) {
                const ratio = pixels / recommendedPixels;
                result.score = Math.max(result.score * ratio, result.score - 30);
            }

            // Check compression (bytes per pixel)
            const bytesPerPixel = file.size / pixels;
            result.details.bytesPerPixel = bytesPerPixel.toFixed(2);

            if (bytesPerPixel < 0.5) {
                result.warnings.push('Image appears heavily compressed. Consider using a higher quality version.');
                result.score -= 15;
            }

        } catch (error) {
            result.valid = false;
            result.errors.push('Failed to read image. The file may be corrupted.');
            console.error('Image validation error:', error);
        }

        result.score = Math.max(0, Math.round(result.score));
        result.details.qualityRating = this.getQualityRating(result.score);

        return result;
    }

    /**
     * Get image dimensions from File object
     * @param {File} file - The image file
     * @returns {Promise<Object>} {width, height}
     */
    getImageDimensions(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const url = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(url);
                resolve({
                    width: img.width,
                    height: img.height
                });
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('Failed to load image'));
            };

            img.src = url;
        });
    }

    /**
     * Format file size for display
     * @param {number} bytes - File size in bytes
     * @returns {string} Formatted size
     */
    formatFileSize(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        }
        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    /**
     * Get quality rating from score
     * @param {number} score - Quality score (0-100)
     * @returns {string} Rating text
     */
    getQualityRating(score) {
        if (score >= 80) return 'Excellent';
        if (score >= 60) return 'Good';
        if (score >= 40) return 'Acceptable';
        return 'Poor';
    }

    /**
     * Get quality badge class for styling
     * @param {number} score - Quality score (0-100)
     * @returns {string} CSS class name
     */
    getQualityBadgeClass(score) {
        if (score >= 80) return 'badge-success';
        if (score >= 60) return 'badge-info';
        if (score >= 40) return 'badge-warning';
        return 'badge-danger';
    }

    /**
     * Generate HTML for validation result display
     * @param {Object} result - Validation result
     * @param {string} fileName - Name of the file
     * @param {number} index - Image index for tracking
     * @returns {string} HTML string
     */
    generateResultHTML(result, fileName, index = 0) {
        const resultId = `validation-result-${index}`;

        let html = `
            <div id="${resultId}" class="image-validation-result ${result.valid ? 'valid' : 'invalid'}" data-image-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-sm">${this.truncateFileName(fileName, 25)}</strong>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge ${this.getQualityBadgeClass(result.score)}">
                            ${result.details.qualityRating || 'Unknown'} ${result.score}/100
                        </span>
                        <button type="button" onclick="removeValidationResult(${index})"
                                class="btn-close-validation" title="Dismiss">×</button>
                    </div>
                </div>
        `;

        // Show compact dimensions and size
        if (result.details.width && result.details.height) {
            html += `
                <div class="text-muted" style="font-size: 0.75rem; margin-bottom: 0.5rem;">
                    ${result.details.width}×${result.details.height}px | ${result.details.fileSizeFormatted}
                </div>
            `;
        }

        // Show errors (compact)
        if (result.errors.length > 0) {
            html += '<div class="alert alert-danger alert-sm mb-2 p-2" style="font-size: 0.75rem;">';
            result.errors.forEach(error => {
                html += `<div>❌ ${this.compactMessage(error)}</div>`;
            });
            html += '</div>';
        }

        // Show warnings (compact)
        if (result.warnings.length > 0) {
            html += '<div class="alert alert-warning alert-sm mb-2 p-2" style="font-size: 0.75rem;">';
            result.warnings.forEach(warning => {
                html += `<div>⚠️ ${this.compactMessage(warning)}</div>`;
            });
            html += '</div>';
        }

        // Show success message
        if (result.valid && result.errors.length === 0 && result.warnings.length === 0) {
            html += '<div class="alert alert-success alert-sm mb-0 p-2" style="font-size: 0.75rem;">';
            html += '✅ Good quality';
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    /**
     * Truncate long filenames for display
     */
    truncateFileName(fileName, maxLength) {
        if (fileName.length <= maxLength) return fileName;
        const ext = fileName.split('.').pop();
        const nameWithoutExt = fileName.substring(0, fileName.lastIndexOf('.'));
        const truncated = nameWithoutExt.substring(0, maxLength - ext.length - 4) + '...';
        return truncated + '.' + ext;
    }

    /**
     * Make messages more compact for mobile
     */
    compactMessage(message) {
        // Shorten common phrases
        return message
            .replace('For best results, use images at least', 'Recommended:')
            .replace('Your image is', 'Current:')
            .replace('Please use a higher resolution image.', '')
            .replace('Please use a clearer, focused photo.', '')
            .replace('Please use better lighting or a brighter photo.', '')
            .replace('Please use a less bright photo.', '')
            .replace('This may indicate a low-quality or corrupted image.', '')
            .replace('Please compress or resize your image.', '')
            .replace('Minimum required is', 'Need:')
            .replace('Minimum is', 'Min:')
            .replace('Maximum is', 'Max:')
            .trim();
    }

    /**
     * Validate that enough images have been selected
     * @param {FileList|Array} files - Selected image files
     * @param {number} minCount - Minimum required (default: 3)
     * @returns {Object} {valid: boolean, error: string|null}
     */
    validateMinimumCount(files, minCount = 3) {
        const count = files ? files.length : 0;
        if (count < minCount) {
            return {
                valid: false,
                error: `Please upload at least ${minCount} images. You have selected ${count}.`
            };
        }
        return { valid: true, error: null };
    }

    /**
     * Display quality tips
     * @returns {string} HTML with tips
     */
    getQualityTipsHTML() {
        return `
            <div class="image-quality-tips alert alert-info">
                <h6><i class="fas fa-lightbulb"></i> Tips for Best Quality:</h6>
                <ul class="mb-0 small">
                    <li>Use your phone camera at highest quality setting</li>
                    <li>Take photos in good lighting (natural daylight works best)</li>
                    <li>Hold steady and ensure subject is in focus</li>
                    <li>Recommended minimum: ${this.config.recommendedWidth}×${this.config.recommendedHeight}px</li>
                    <li>Avoid screenshots, downloaded images, or watermarked photos</li>
                </ul>
            </div>
        `;
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ImageQualityValidator;
}
