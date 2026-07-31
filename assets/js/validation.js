/**
 * TPMS - Client-Side Validation Module
 * Mirrors the server-side Validator class in includes/helpers.php.
 * Provides real-time field validation with inline error messages.
 */

const TPMSValidation = {

    // ============================================================
    // DOM Helpers
    // ============================================================

    /**
     * Show an error on a field: red border + message below it.
     */
    showError(field, msg) {
        if (!field) return;
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        let fb = field.parentElement ? field.parentElement.querySelector('.invalid-feedback') : null;
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            field.insertAdjacentElement('afterend', fb);
        }
        fb.textContent = msg;
        fb.style.display = 'block';
    },

    /**
     * Clear the error state from a field.
     */
    clearError(field) {
        if (!field) return;
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        const fb = field.parentElement ? field.parentElement.querySelector('.invalid-feedback') : null;
        if (fb) { fb.textContent = ''; fb.style.display = 'none'; }
    },

    /**
     * Remove is-valid too (neutral state, no interaction yet).
     */
    resetField(field) {
        if (!field) return;
        field.classList.remove('is-invalid', 'is-valid');
        const fb = field.parentElement ? field.parentElement.querySelector('.invalid-feedback') : null;
        if (fb) { fb.textContent = ''; fb.style.display = 'none'; }
    },

    /**
     * Clear all errors in a form or container.
     */
    clearErrors(container) {
        container.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
            el.classList.add('is-valid');
        });
        container.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    },

    // ============================================================
    // Validation Rules (mirroring PHP Validator class)
    // ============================================================

    validatePhone(value) {
        value = value.trim();
        if (value === '') return 'Phone number is required.';
        if (!/^\d+$/.test(value)) return 'Phone number must contain digits only (no spaces, dashes, or symbols).';
        if (value.length !== 10) return 'Phone number must be exactly 10 digits.';
        return '';
    },

    validatePincode(value) {
        value = value.trim();
        if (value === '') return '';
        if (!/^\d+$/.test(value)) return 'PIN code must contain digits only.';
        if (value.length !== 6) return 'PIN code must be exactly 6 digits.';
        return '';
    },

    validateCity(value) {
        value = value.trim();
        if (value === '') return '';
        if (value.length < 2) return 'City name must be at least 2 characters.';
        if (value.length > 50) return 'City name must not exceed 50 characters.';
        if (!/^[\p{L}\s\-']+$/u.test(value)) return 'City can only contain letters, spaces, hyphens, and apostrophes (no numbers or special characters).';
        return '';
    },

    validateState(value) {
        value = value.trim();
        if (value === '') return '';
        if (value.length < 2) return 'State name must be at least 2 characters.';
        if (value.length > 50) return 'State name must not exceed 50 characters.';
        if (!/^[\p{L}\s\-']+$/u.test(value)) return 'State can only contain letters, spaces, hyphens, and apostrophes (no numbers or special characters).';
        return '';
    },

    validateCountry(value) {
        value = value.trim();
        if (value === '') return '';
        if (value.length < 2) return 'Country name must be at least 2 characters.';
        if (value.length > 50) return 'Country name must not exceed 50 characters.';
        if (!/^[\p{L}\s\-']+$/u.test(value)) return 'Country can only contain letters, spaces, hyphens, and apostrophes (no numbers or special characters).';
        return '';
    },

    validateAddress(value) {
        value = value.trim();
        if (value === '') return '';
        if (value.length < 10) return 'Address must be at least 10 characters.';
        if (value.length > 250) return 'Address must not exceed 250 characters.';
        return '';
    },

    validateProjectUrl(value) {
        value = value.trim();
        if (value === '') return 'Project URL is required (e.g. https://github.com/user/project).';
        if (!/^https?:\/\/.+/i.test(value)) return 'Project URL must start with http:// or https://.';
        try { new URL(value); } catch (e) { return 'Please enter a valid URL (e.g. https://github.com/user/project).'; }
        return '';
    },

    validateMeetingLink(value) {
        value = value.trim();
        if (value === '') return 'Meeting link is required (e.g. https://meet.google.com/abc-defg-hij).';
        if (!/^https?:\/\/.+/i.test(value)) return 'Meeting link must start with http:// or https://.';
        try { new URL(value); } catch (e) { return 'Please enter a valid meeting URL (e.g. https://meet.google.com/abc-defg-hij).'; }
        return '';
    },

    validateOptionalUrl(value, fieldLabel) {
        fieldLabel = fieldLabel || 'URL';
        value = value.trim();
        if (value === '') return '';
        if (!/^https?:\/\/.+/i.test(value)) return fieldLabel + ' must start with http:// or https://.';
        try { new URL(value); } catch (e) { return 'Please enter a valid ' + fieldLabel + '.'; }
        return '';
    },

    validateSkills(value) {
        value = value.trim();
        if (value === '') return { error: '', normalized: '' };
        if (value.length > 300) return { error: 'Skills must not exceed 300 characters.', normalized: value };
        const parts = value.split(',').map(s => s.trim()).filter(s => s !== '');
        const seen = new Set();
        const unique = [];
        for (const skill of parts) {
            const key = skill.toLowerCase();
            if (!seen.has(key)) { seen.add(key); unique.push(skill); }
        }
        return { error: '', normalized: unique.join(', ') };
    },

    validateBio(value) {
        value = value.trim();
        if (value === '') return '';
        if (value.length < 20) return 'Bio must be at least 20 characters.';
        if (value.length > 500) return 'Bio must not exceed 500 characters.';
        return '';
    },

    validateAchievement(value) {
        value = value.trim();
        if (value.length > 500) return 'Achievement description must not exceed 500 characters.';
        return '';
    },

    validateLanguageName(value) {
        value = value.trim();
        if (value === '') return 'Language name is required.';
        if (value.length < 2) return 'Language name must be at least 2 characters.';
        if (value.length > 50) return 'Language name must not exceed 50 characters.';
        if (!/^[\p{L}\s\-]+$/u.test(value)) return 'Language name can only contain letters, spaces, and hyphens.';
        return '';
    },

    validateText(value, label, min, max, required) {
        min = min || 1; max = max || 150;
        if (required === undefined) required = true;
        value = value.trim();
        if (value === '') {
            if (required) return (label || 'Field') + ' is required.';
            return '';
        }
        if (value.length < min) return (label || 'Field') + ' must be at least ' + min + ' characters.';
        if (value.length > max) return (label || 'Field') + ' must not exceed ' + max + ' characters.';
        return '';
    },

    validateEmail(value, label) {
        label = label || 'Email';
        value = value.trim();
        if (value === '') return label + ' is required.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Please enter a valid ' + label + ' address.';
        return '';
    },

    validateDate(value, label, mustBeFuture) {
        label = label || 'Date';
        if (mustBeFuture === undefined) mustBeFuture = true;
        value = value.trim();
        if (value === '') return label + ' is required.';
        const d = new Date(value);
        if (isNaN(d.getTime())) return 'Please enter a valid date for ' + label + '.';
        if (mustBeFuture) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (d < today) return label + ' cannot be in the past.';
        }
        return '';
    },

    validateNumeric(value, label, min, max) {
        label = label || 'Field';
        min = min !== undefined && min !== null ? min : 0;
        value = value !== null && value !== undefined ? String(value).trim() : '';
        if (value === '') return label + ' is required.';
        const num = parseFloat(value);
        if (isNaN(num)) return label + ' must be a valid number.';
        if (num < min) return label + ' cannot be less than ' + min + '.';
        if (max !== null && max !== undefined && num > max) return label + ' cannot exceed ' + max + '.';
        return '';
    },

    validateInteger(value, label, min) {
        label = label || 'Field';
        min = min !== undefined && min !== null ? min : 0;
        value = value !== null && value !== undefined ? String(value).trim() : '';
        if (value === '') return label + ' is required.';
        if (!/^\d+$/.test(value)) return label + ' must be a valid whole number.';
        const num = parseInt(value, 10);
        if (num < min) return label + ' must be at least ' + min + '.';
        return '';
    },

    validateSalaryMax(value, field) {
        const valRes = this.validateNumeric(value, 'Maximum salary', 0);
        if (valRes) return valRes;
        const maxVal = parseFloat(value);
        const form = field ? field.form : null;
        if (form) {
            const minField = form.querySelector('[name="salary_min"]');
            if (minField && minField.value !== '') {
                const minVal = parseFloat(minField.value);
                if (!isNaN(minVal) && maxVal < minVal) {
                    return 'Maximum salary cannot be less than minimum salary (' + minVal + ' LPA).';
                }
            }
        }
        return '';
    },

    // ============================================================
    // Real-time Field Binding
    // ============================================================

    bindField(field, rule) {
        if (!field) return;
        const self = this;
        field.addEventListener('blur', function() {
            const err = rule(this.value);
            if (err) self.showError(this, err);
            else self.clearError(this);
        });
        field.addEventListener('input', function() {
            const err = rule(this.value);
            if (!err && this.classList.contains('is-invalid')) {
                self.clearError(this);
            }
            self._updateCharCount(this);
        });
    },

    _updateCharCount(field) {
        const target = field.dataset.maxlengthTarget;
        if (!target) return;
        const counter = document.getElementById(target);
        if (counter) {
            const max = parseInt(field.dataset.maxlength || field.maxLength || '0');
            counter.textContent = field.value.length + '/' + max;
        }
    },

    // ============================================================
    // Form-level Initialization
    // ============================================================

    _getRuleFn(field) {
        const self = this;
        const rule       = field.dataset.validateRule;
        const ruleLabel  = field.dataset.validateLabel  || field.name || 'Field';
        const ruleLabel2 = field.dataset.validateLabel2 || ruleLabel;
        const ruleMin    = parseInt(field.dataset.validateMin  || '1');
        const ruleMax    = parseInt(field.dataset.validateMax  || '150');
        const ruleReq    = field.dataset.validateRequired !== 'false';

        switch (rule) {
            case 'phone':       return v => self.validatePhone(v);
            case 'pincode':     return v => self.validatePincode(v);
            case 'city':        return v => self.validateCity(v);
            case 'state':       return v => self.validateState(v);
            case 'country':     return v => self.validateCountry(v);
            case 'address':     return v => self.validateAddress(v);
            case 'projectUrl':  return v => self.validateProjectUrl(v);
            case 'meetingLink':  return v => self.validateMeetingLink(v);
            case 'optionalUrl': return v => self.validateOptionalUrl(v, ruleLabel2);
            case 'bio':         return v => self.validateBio(v);
            case 'achievement': return v => self.validateAchievement(v);
            case 'language':    return v => self.validateLanguageName(v);
            case 'text':        return v => self.validateText(v, ruleLabel, ruleMin, ruleMax, ruleReq);
            case 'email':       return v => self.validateEmail(v, ruleLabel);
            case 'date':        return v => self.validateDate(v, ruleLabel, field.dataset.validateFuture !== 'false');
            case 'numeric':     return v => self.validateNumeric(v, ruleLabel, parseFloat(field.dataset.validateMin || '0'), field.dataset.validateMax ? parseFloat(field.dataset.validateMax) : null);
            case 'integer':     return v => self.validateInteger(v, ruleLabel, parseInt(field.dataset.validateMin || '0'));
            case 'salaryMax':   return v => self.validateSalaryMax(v, field);
            default:            return () => '';
        }
    },

    initForm(formSelector) {
        const form = typeof formSelector === 'string'
            ? document.querySelector(formSelector)
            : formSelector;
        if (!form) return;

        const self = this;

        // Bind real-time rules
        form.querySelectorAll('[data-validate-rule]').forEach(function(field) {
            const rule = field.dataset.validateRule;
            if (rule === 'skills') {
                self.bindField(field, function(v) {
                    return self.validateSkills(v).error;
                });
            } else {
                self.bindField(field, self._getRuleFn(field));
            }
        });

        // Block form submit on errors
        form.addEventListener('submit', function(e) {
            let hasError = false;

            form.querySelectorAll('[data-validate-rule]').forEach(function(field) {
                const rule = field.dataset.validateRule;
                let err = '';

                if (rule === 'skills') {
                    const result = self.validateSkills(field.value);
                    if (result.error) {
                        self.showError(field, result.error);
                        hasError = true;
                    } else {
                        field.value = result.normalized;
                        self.clearError(field);
                    }
                    return;
                }

                err = self._getRuleFn(field)(field.value);
                if (err) {
                    self.showError(field, err);
                    hasError = true;
                } else {
                    self.clearError(field);
                }
            });

            if (hasError) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const firstErr = form.querySelector('.is-invalid');
                if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, true);
    },

    // ============================================================
    // Auto-initialize on DOM ready
    // ============================================================

    init() {
        const self = this;
        document.querySelectorAll('form[data-tpms-validate]').forEach(function(form) {
            self.initForm(form);
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { TPMSValidation.init(); });
} else {
    TPMSValidation.init();
}
