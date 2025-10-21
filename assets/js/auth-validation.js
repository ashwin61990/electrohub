// Real-time validation for authentication forms
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');

    // Validation rules
    const validationRules = {
        full_name: {
            required: true,
            minLength: 2,
            pattern: /^[a-zA-Z\s]+$/,
            message: 'Full name must contain only letters and spaces (min 2 characters)'
        },
        username: {
            required: true,
            minLength: 3,
            maxLength: 50,
            pattern: /^[a-zA-Z0-9_]+$/,
            message: 'Username must be 3-50 characters (letters, numbers, underscore only)'
        },
        email: {
            required: true,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        phone: {
            required: false,
            pattern: /^[\d\s\-\+\(\)]+$/,
            minLength: 10,
            message: 'Please enter a valid phone number'
        },
        password: {
            required: true,
            minLength: 8,
            pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
            message: 'Password must be 8+ characters with uppercase, lowercase, and number'
        },
        confirm_password: {
            required: true,
            matchField: 'password',
            message: 'Passwords do not match'
        }
    };

    // Validate single field
    function validateField(input) {
        const fieldName = input.name;
        const value = input.value.trim();
        const rules = validationRules[fieldName];
        const wrapper = input.closest('.form-group');
        const validationMessage = wrapper.querySelector('.validation-message');
        const inputWrapper = wrapper.querySelector('.input-wrapper');

        if (!rules) return true;

        // Clear previous validation
        inputWrapper.classList.remove('error', 'success');
        validationMessage.textContent = '';
        validationMessage.classList.remove('error', 'success');

        // Required check
        if (rules.required && !value) {
            showError(inputWrapper, validationMessage, 'This field is required');
            return false;
        }

        // Skip other validations if field is empty and not required
        if (!rules.required && !value) {
            return true;
        }

        // Min length check
        if (rules.minLength && value.length < rules.minLength) {
            showError(inputWrapper, validationMessage, `Minimum ${rules.minLength} characters required`);
            return false;
        }

        // Max length check
        if (rules.maxLength && value.length > rules.maxLength) {
            showError(inputWrapper, validationMessage, `Maximum ${rules.maxLength} characters allowed`);
            return false;
        }

        // Pattern check
        if (rules.pattern && !rules.pattern.test(value)) {
            showError(inputWrapper, validationMessage, rules.message);
            return false;
        }

        // Match field check (for confirm password)
        if (rules.matchField) {
            const matchInput = document.getElementById(rules.matchField);
            if (matchInput && value !== matchInput.value) {
                showError(inputWrapper, validationMessage, rules.message);
                return false;
            }
        }

        // Show success
        showSuccess(inputWrapper, validationMessage);
        return true;
    }

    function showError(inputWrapper, validationMessage, message) {
        inputWrapper.classList.add('error');
        inputWrapper.classList.remove('success');
        validationMessage.textContent = message;
        validationMessage.classList.add('error');
        validationMessage.classList.remove('success');
    }

    function showSuccess(inputWrapper, validationMessage) {
        inputWrapper.classList.add('success');
        inputWrapper.classList.remove('error');
        validationMessage.textContent = '✓ Looks good!';
        validationMessage.classList.add('success');
        validationMessage.classList.remove('error');
    }

    // Password strength indicator
    function updatePasswordStrength(password) {
        const strengthBar = document.querySelector('.strength-fill');
        const strengthText = document.querySelector('.strength-text');
        
        if (!strengthBar || !strengthText) return;

        let strength = 0;
        let text = 'Weak';
        let color = '#ef4444';

        if (password.length >= 8) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;

        if (strength >= 75) {
            text = 'Strong';
            color = '#10b981';
        } else if (strength >= 50) {
            text = 'Medium';
            color = '#f59e0b';
        }

        strengthBar.style.width = strength + '%';
        strengthBar.style.background = color;
        strengthText.textContent = text;
        strengthText.style.color = color;
    }

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Real-time validation on input
    if (registerForm) {
        const inputs = registerForm.querySelectorAll('input[name]');
        
        inputs.forEach(input => {
            // Validate on blur (when user leaves field)
            input.addEventListener('blur', function() {
                validateField(this);
            });

            // Validate on input (as user types)
            input.addEventListener('input', function() {
                const wrapper = this.closest('.form-group');
                const inputWrapper = wrapper.querySelector('.input-wrapper');
                
                // Only show validation if field has been touched
                if (inputWrapper.classList.contains('error') || inputWrapper.classList.contains('success')) {
                    validateField(this);
                }

                // Update password strength
                if (this.name === 'password') {
                    updatePasswordStrength(this.value);
                }

                // Validate confirm password when password changes
                if (this.name === 'password') {
                    const confirmPassword = document.getElementById('confirm_password');
                    if (confirmPassword && confirmPassword.value) {
                        validateField(confirmPassword);
                    }
                }
            });

            // Validate on focus (show hints)
            input.addEventListener('focus', function() {
                const wrapper = this.closest('.form-group');
                const inputWrapper = wrapper.querySelector('.input-wrapper');
                inputWrapper.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                const wrapper = this.closest('.form-group');
                const inputWrapper = wrapper.querySelector('.input-wrapper');
                inputWrapper.classList.remove('focused');
            });
        });

        // Form submission validation
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[name]');

            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            // Check terms checkbox
            const termsCheckbox = document.getElementById('terms');
            if (termsCheckbox && !termsCheckbox.checked) {
                const wrapper = termsCheckbox.closest('.form-group');
                const validationMessage = wrapper.querySelector('.validation-message');
                validationMessage.textContent = 'You must agree to the terms and conditions';
                validationMessage.classList.add('error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = this.querySelector('.input-wrapper.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    // Login form validation
    if (loginForm) {
        const inputs = loginForm.querySelectorAll('input[name]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                const wrapper = this.closest('.form-group');
                const validationMessage = wrapper.querySelector('.validation-message');
                const inputWrapper = wrapper.querySelector('.input-wrapper');

                if (!this.value.trim()) {
                    showError(inputWrapper, validationMessage, 'This field is required');
                } else {
                    inputWrapper.classList.remove('error');
                    validationMessage.textContent = '';
                }
            });

            input.addEventListener('focus', function() {
                const wrapper = this.closest('.form-group');
                const inputWrapper = wrapper.querySelector('.input-wrapper');
                inputWrapper.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                const wrapper = this.closest('.form-group');
                const inputWrapper = wrapper.querySelector('.input-wrapper');
                inputWrapper.classList.remove('focused');
            });
        });

        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required]');

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    const wrapper = input.closest('.form-group');
                    const validationMessage = wrapper.querySelector('.validation-message');
                    const inputWrapper = wrapper.querySelector('.input-wrapper');
                    showError(inputWrapper, validationMessage, 'This field is required');
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

});

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
});
