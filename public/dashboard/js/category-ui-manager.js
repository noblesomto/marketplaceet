/**
 * ============================================================================
 * CATEGORY UI MANAGER - Database-driven configuration system
 * ============================================================================
 *
 * Version: 1.1
 * Date: 2026-02-03
 * Fixed: "not focusable" error and undefined defaults issue
 */

class CategoryUIManager {
    constructor() {
        this.config = null;
        this.elements = {};
        this.isReady = false;
        this.usingFallback = false;

        // Fallback configuration (mirrors current hardcoded logic)
        this.fallbackConfig = {
            categories: {
                "1": {
                    show: ["price"],
                    hide: ["services", "shipment", "itemCondition", "buyDirect", "quantity"],
                    labels: { brand: "Select Option:" }
                },
                "3": {
                    show: ["salary"],
                    hide: ["price", "shipment", "itemCondition", "shipping", "buyDirect", "expectedSalary", "quantity"],
                    labels: { brand: "Select Job Type:" }
                },
                "7": {
                    show: ["price"],
                    hide: ["services", "shipment", "itemCondition", "buyDirect", "quantity"],
                    labels: { brand: "Select Option:" }
                },
                "11": {
                    show: ["services", "price"],
                    hide: ["shipment", "itemCondition", "buyDirect", "quantity"],
                    labels: { brand: "Select Type:" }
                },
                "18": {
                    show: ["expectedSalary"],
                    hide: ["price", "salary", "shipment", "shipping", "itemCondition", "buyDirect", "quantity"],
                    labels: { brand: "Select Option:" }
                }
            },
            subcategories: {
                "2": {
                    show: ["divCar", "divModel"],
                    hide: ["shipment", "itemCondition", "buyDirect"],
                    labels: { brand: "Brand:" },
                    required: ["model"]
                },
                "6": {
                    show: ["divPhone", "shipment"],
                    hide: ["divModel", "itemCondition"],
                    labels: { brand: "Select Option:" },
                    required: []
                },
                "16": { show: ["shipment"], hide: ["itemCondition"] },
                "17": { show: ["shipment"], hide: ["itemCondition"] },
                "18": { show: ["shipment"], hide: ["itemCondition"] },
                "19": { show: ["shipment"], hide: ["itemCondition"] },
                "21": {
                    show: ["divCar", "divModel"],
                    hide: ["shipment", "itemCondition", "buyDirect"],
                    labels: { brand: "Brand:" },
                    required: ["model"]
                },
                "22": {
                    show: ["itemCondition"],
                    hide: ["shipment", "buyDirect", "divCar", "divModel"]
                },
                "23": {
                    show: ["divCar", "divModel"],
                    hide: ["shipment", "itemCondition", "buyDirect"],
                    labels: { brand: "Brand:" },
                    required: ["model"]
                },
                "24": {
                    show: ["itemCondition", "shipment", "buyDirect"],
                    hide: ["divCar", "divModel"]
                },
                "25": {
                    show: ["itemCondition"],
                    hide: ["shipment", "buyDirect", "divCar", "divModel"]
                }
            },
            defaults: {
                category: {
                    show: ["price", "quantity", "shipment", "itemCondition", "buyDirect"],
                    hide: ["services", "salary", "expectedSalary"],
                    labels: { brand: "Select Option:" }
                },
                subcategory: {
                    show: [],
                    hide: ["divCar", "divPhone", "divModel"],
                    labels: { brand: "Select Option:" },
                    required: []
                }
            }
        };
    }

    /**
     * Initialize: Fetch config from API with fallback to hardcoded
     */
    async initialize() {
        try {
            // Try to fetch from API
            const response = await fetch('/api/ui-config/all', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'default' // Use browser cache
            });

            if (!response.ok) {
                throw new Error(`API returned ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.data) {
                // Convert numeric keys to strings for consistent comparison
                this.config = {
                    categories: this.normalizeKeys(result.data.categories || {}),
                    subcategories: this.normalizeKeys(result.data.subcategories || {}),
                    // ✅ Always ensure defaults exist, use fallback if API doesn't provide them
                    defaults: result.data.defaults || this.fallbackConfig.defaults
                };

                this.usingFallback = false;
                console.log('✅ CategoryUIManager: Loaded config from database');
            } else {
                throw new Error('Invalid API response');
            }
        } catch (error) {
            console.warn('⚠️ CategoryUIManager: API failed, using fallback config', error);
            this.useFallbackConfig();
        }

        this.cacheElements();
        this.isReady = true;

        return this;
    }

    /**
     * Normalize object keys to strings for consistent comparison
     */
    normalizeKeys(obj) {
        if (!obj || typeof obj !== 'object') return obj;

        const normalized = {};
        for (const [key, value] of Object.entries(obj)) {
            normalized[String(key)] = value;
        }
        return normalized;
    }

    /**
     * Use fallback configuration
     */
    useFallbackConfig() {
        this.config = this.fallbackConfig;
        this.usingFallback = true;
    }

    /**
     * Cache all manageable elements
     */
    cacheElements() {
        const elementIds = [
            'divCar', 'divPhone', 'divModel', 'services', 'shipment',
            'itemCondition', 'buyDirect', 'price', 'shipping',
            'quantity', 'salary', 'expectedSalary'
        ];

        elementIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                this.elements[id] = element;
            }
        });
    }

    /**
     * Apply category-based UI rules
     */
    applyCategoryRules(categoryId) {
        if (!this.isReady) {
            console.warn('CategoryUIManager not ready yet');
            return;
        }

        // Convert to string for consistent comparison
        categoryId = String(categoryId);

        // Get config for this category (or default)
        const config = this.config.categories[categoryId] || this.config.defaults.category;

        // Reset all elements first
        this.resetAllElements();

        // Apply show/hide rules
        if (config.hide) {
            config.hide.forEach(elementId => this.hide(elementId));
        }

        if (config.show) {
            config.show.forEach(elementId => this.show(elementId));
        }

        // Apply label changes
        if (config.labels) {
            Object.entries(config.labels).forEach(([forAttr, labelText]) => {
                this.updateLabel(forAttr, labelText);
            });
        }

        // Clear required from model when divModel is hidden
        this.clearRequiredOnHiddenFields();

        console.log(`Applied category rules for ID ${categoryId}`, config);
    }

    /**
     * Apply subcategory-based UI rules
     */
    applySubcategoryRules(subcategoryId) {
        if (!this.isReady) {
            console.warn('CategoryUIManager not ready yet');
            return;
        }

        // Convert to string for consistent comparison
        subcategoryId = String(subcategoryId);

        // Get config for this subcategory (or default)
        const config = this.config.subcategories[subcategoryId] || this.config.defaults.subcategory;

        // Apply show/hide rules (additive to category rules)
        if (config.hide) {
            config.hide.forEach(elementId => this.hide(elementId));
        }

        if (config.show) {
            config.show.forEach(elementId => this.show(elementId));
        }

        // Apply label changes
        if (config.labels) {
            Object.entries(config.labels).forEach(([forAttr, labelText]) => {
                this.updateLabel(forAttr, labelText);
            });
        }

        // Always clear required on model first, then re-add only if explicitly listed
        const modelElement = document.getElementById('model');
        if (modelElement) {
            modelElement.removeAttribute('required');
        }

        if (config.required) {
            config.required.forEach(fieldName => {
                const element = document.getElementById(fieldName);
                if (element) {
                    element.setAttribute('required', 'required');
                }
            });
        }

        // Clear required from any fields in hidden containers
        this.clearRequiredOnHiddenFields();

        console.log(`Applied subcategory rules for ID ${subcategoryId}`, config);
    }

    /**
     * Clear required attribute from inputs in hidden containers
     */
    clearRequiredOnHiddenFields() {
        // List of container IDs that might contain required fields
        const containers = ['divModel', 'divCar', 'divPhone'];

        containers.forEach(containerId => {
            const container = this.elements[containerId];
            if (container && container.classList.contains('hidden')) {
                // Find all required inputs within this hidden container
                const requiredInputs = container.querySelectorAll('[required]');
                requiredInputs.forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    }

    /**
     * Show an element
     */
    show(elementId) {
        if (this.elements[elementId]) {
            this.elements[elementId].classList.remove('hidden');
        }
    }

    /**
     * Hide an element
     */
    hide(elementId) {
        if (this.elements[elementId]) {
            this.elements[elementId].classList.add('hidden');

            // Remove required from any inputs inside hidden containers
            const hiddenContainer = this.elements[elementId];
            const requiredInputs = hiddenContainer.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                input.removeAttribute('required');
            });
        }
    }

    /**
     * Reset all managed elements to hidden
     */
    resetAllElements() {
        Object.values(this.elements).forEach(element => {
            element.classList.add('hidden');
        });

        // Also remove required attributes
        const modelElement = document.getElementById('model');
        if (modelElement) {
            modelElement.removeAttribute('required');
        }
    }

    /**
     * Update label text
     */
    updateLabel(forAttribute, newText) {
        const label = document.querySelector(`label[for="${forAttribute}"]`);
        if (label) {
            label.textContent = newText;
        }
    }

    /**
     * Wait for manager to be ready
     */
    async waitForReady() {
        const startTime = Date.now();
        const timeout = 5000; // 5 second timeout

        while (!this.isReady) {
            if (Date.now() - startTime > timeout) {
                console.error('CategoryUIManager initialization timeout');
                this.useFallbackConfig();
                this.cacheElements();
                this.isReady = true;
                break;
            }
            await new Promise(resolve => setTimeout(resolve, 100));
        }

        return this;
    }

    /**
     * Get current configuration status
     */
    getStatus() {
        return {
            ready: this.isReady,
            usingFallback: this.usingFallback,
            categoriesConfigured: this.config ? Object.keys(this.config.categories).length : 0,
            subcategoriesConfigured: this.config ? Object.keys(this.config.subcategories).length : 0
        };
    }
}

// Export singleton instance
window.categoryUIManager = new CategoryUIManager();

// Expose for debugging in console
if (typeof window !== 'undefined') {
    window.debugCategoryUI = () => {
        console.log('CategoryUIManager Status:', window.categoryUIManager.getStatus());
        console.log('Current Config:', window.categoryUIManager.config);
    };
}
