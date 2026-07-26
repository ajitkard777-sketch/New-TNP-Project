/**
 * TPMS - Auth Pages JavaScript
 */
document.addEventListener('DOMContentLoaded', () => {
    // Role tab switching
    const roleTabs = document.querySelectorAll('.auth-role-tab');
    const roleInput = document.getElementById('role-input');
    
    roleTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            roleTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            if (roleInput) roleInput.value = tab.dataset.role;
        });
    });

    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Password strength checker
    const passwordInput = document.getElementById('password');
    const strengthFill = document.querySelector('.password-strength-fill');
    const strengthText = document.querySelector('.password-strength-text');

    if (passwordInput && strengthFill) {
        passwordInput.addEventListener('input', function() {
            const val = this.value;
            let score = 0;
            
            if (val.length >= 8) score++;
            if (/[a-z]/.test(val)) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = ['', 'weak', 'fair', 'good', 'good', 'strong'];
            const labels = ['', 'Weak', 'Fair', 'Good', 'Good', 'Strong'];
            const colors = ['', '#ef4444', '#f59e0b', '#06b6d4', '#06b6d4', '#10b981'];

            strengthFill.className = 'password-strength-fill ' + (levels[score] || '');
            if (strengthText) {
                strengthText.textContent = labels[score] || '';
                strengthText.style.color = colors[score] || '';
            }
        });
    }

    // Form AJAX submission
    const authForm = document.querySelector('.auth-form');
    if (authForm && authForm.dataset.ajax === 'true') {
        authForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Please wait...';
            btn.disabled = true;
            btn.classList.add('loading-btn');

            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.classList.remove('loading-btn');

                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            if (data.redirect) window.location.href = data.redirect;
                        });
                    } else {
                        if (typeof TPMS !== 'undefined') TPMS.showToast(data.message, 'success');
                        if (data.redirect) setTimeout(() => window.location.href = data.redirect, 1000);
                    }
                } else {
                    const msg = data.errors ? data.errors.join('<br>') : data.message;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', html: msg });
                    } else {
                        if (typeof TPMS !== 'undefined') TPMS.showToast(msg, 'danger');
                    }
                }
            })
            .catch(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.classList.remove('loading-btn');
                if (typeof TPMS !== 'undefined') TPMS.showToast('Network error. Please try again.', 'danger');
            });
        });
    }

    // OTP inputs auto-focus
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((input, index) => {
        input.addEventListener('keyup', function(e) {
            if (this.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
            if (e.key === 'Backspace' && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').split('');
            otpInputs.forEach((inp, i) => {
                if (digits[i]) inp.value = digits[i];
            });
            // Combine into hidden field
            const otpHidden = document.getElementById('otp');
            if (otpHidden) {
                otpHidden.value = digits.join('').substring(0, otpInputs.length);
            }
        });
    });

    // Combine OTP fields before submit
    const otpForm = document.getElementById('otp-form');
    if (otpForm) {
        otpForm.addEventListener('submit', function() {
            const otpHidden = document.getElementById('otp');
            if (otpHidden) {
                let otp = '';
                otpInputs.forEach(inp => otp += inp.value);
                otpHidden.value = otp;
            }
        });
    }

    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const val = this.value.trim();
            if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // Phone validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
        });
    }

    // CGPA validation
    const cgpaInput = document.getElementById('cgpa');
    if (cgpaInput) {
        cgpaInput.addEventListener('input', function() {
            let val = parseFloat(this.value);
            if (val > 10) this.value = '10.00';
            if (val < 0) this.value = '0';
        });
    }
});
