// registration.js - Registration Page Functionality

// Form steps functionality
let currentStep = 1;
const totalSteps = 3;

// Debug logging
console.log('Register.js loaded successfully');

// Initialize form steps
function initRegistrationForm() {
    console.log('Initializing registration form...');
    
    // Show only first step
    const formSteps = document.querySelectorAll('.form-step');
    console.log('Form steps found:', formSteps.length);
    
    formSteps.forEach(step => {
        step.classList.remove('active');
    });
    
    const firstStep = document.querySelector('[data-step="1"]');
    if (firstStep) {
        firstStep.classList.add('active');
        console.log('Step 1 activated');
    }
    
    updateProgress();
    
    // Initialize password strength check
    checkPasswordStrength('');
    
    // Add event listeners
    setupEventListeners();
}

function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    // Next step buttons
    const nextButtons = document.querySelectorAll('.btn-next');
    console.log('Next buttons:', nextButtons.length);
    
    nextButtons.forEach(button => {
        button.addEventListener('click', nextStep);
    });
    
    // Previous step buttons
    const prevButtons = document.querySelectorAll('.btn-prev');
    console.log('Previous buttons:', prevButtons.length);
    
    prevButtons.forEach(button => {
        button.addEventListener('click', prevStep);
    });
    
    // Password strength checker
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function(e) {
            checkPasswordStrength(e.target.value);
        });
        console.log('Password input listener added');
    }
    
    // Password match checker
    const confirmPasswordInput = document.getElementById('confirmPassword');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        console.log('Confirm password listener added');
    }
    
    // Password visibility toggles
    const toggleButtons = document.querySelectorAll('.toggle-password');
    console.log('Toggle buttons:', toggleButtons.length);
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const fieldId = this.getAttribute('data-target');
            console.log('Toggling password for:', fieldId);
            togglePassword(fieldId);
        });
    });
    
    // Form submission
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        registrationForm.addEventListener('submit', handleFormSubmit);
        console.log('Form submission listener added');
    }
    
    // Account type selection
    const accountTypeRadios = document.querySelectorAll('.account-type-card input');
    console.log('Account type radios:', accountTypeRadios.length);
    
    accountTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            updateAccountTypeSelection(this.value);
        });
    });
    
    console.log('All event listeners set up successfully');
}

function nextStep() {
    console.log('Next step called, current step:', currentStep);
    
    if (currentStep < totalSteps) {
        // Validate current step
        if (validateStep(currentStep)) {
            console.log('Step validation passed, moving to step:', currentStep + 1);
            
            // Hide current step
            const currentStepElement = document.querySelector(`[data-step="${currentStep}"]`);
            if (currentStepElement) {
                currentStepElement.classList.remove('active');
            }
            
            // Show next step
            currentStep++;
            const nextStepElement = document.querySelector(`[data-step="${currentStep}"]`);
            if (nextStepElement) {
                nextStepElement.classList.add('active');
            }
            
            updateProgress();
            
            // Smooth scroll to top of form
            const form = document.querySelector('.registration-form');
            if (form) {
                form.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
        } else {
            console.log('Step validation failed');
        }
    } else {
        console.log('Already at last step');
    }
}

function prevStep() {
    console.log('Previous step called, current step:', currentStep);
    
    if (currentStep > 1) {
        const currentStepElement = document.querySelector(`[data-step="${currentStep}"]`);
        if (currentStepElement) {
            currentStepElement.classList.remove('active');
        }
        
        currentStep--;
        const prevStepElement = document.querySelector(`[data-step="${currentStep}"]`);
        if (prevStepElement) {
            prevStepElement.classList.add('active');
        }
        
        updateProgress();
        
        // Smooth scroll to top of form
        const form = document.querySelector('.registration-form');
        if (form) {
            form.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }
}

function updateProgress() {
    console.log('Updating progress, current step:', currentStep);
    
    const progressFill = document.querySelector('.progress-fill');
    const percentage = (currentStep / totalSteps) * 100;
    
    if (progressFill) {
        progressFill.style.width = `${percentage}%`;
        console.log('Progress updated to:', percentage + '%');
    }
    
    // Update step indicators
    document.querySelectorAll('.step').forEach(step => {
        const stepNumber = parseInt(step.dataset.step);
        if (stepNumber <= currentStep) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
}

function validateStep(step) {
    console.log('Validating step:', step);
    let isValid = true;
    
    // Clear all previous errors first
    document.querySelectorAll('.form-group.error').forEach(group => {
        group.classList.remove('error');
    });
    document.querySelectorAll('.error-message').forEach(error => {
        error.style.display = 'none';
    });
    
    switch(step) {
        case 1:
            // Step 1: Personal Information
            const fields = [
                { id: 'firstName', message: 'First name is required' },
                { id: 'lastName', message: 'Last name is required' },
                { id: 'email', message: 'Valid email is required' },
                { id: 'phone', message: 'Phone number is required' },
                { id: 'location', message: 'Please select your location' }
            ];
            
            fields.forEach(field => {
                const element = document.getElementById(field.id);
                if (!element || !element.value.trim()) {
                    showError(element, field.message);
                    isValid = false;
                    console.log('Validation failed for:', field.id);
                } else if (field.id === 'email' && !isValidEmail(element.value)) {
                    showError(element, 'Please enter a valid email address');
                    isValid = false;
                    console.log('Email validation failed');
                } else {
                    clearError(element);
                }
            });
            break;
            
        case 2:
            // Step 2: Account Security
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');
            
            if (!password || !validatePassword(password.value)) {
                showError(password, 'Password must meet all requirements');
                isValid = false;
                console.log('Password validation failed');
            } else {
                clearError(password);
            }
            
            if (!confirmPassword || password.value !== confirmPassword.value) {
                showError(confirmPassword, 'Passwords do not match');
                isValid = false;
                console.log('Password match validation failed');
            } else {
                clearError(confirmPassword);
            }
            break;
            
        case 3:
            // Step 3: Preferences
            const terms = document.getElementById('terms');
            if (!terms || !terms.checked) {
                showNotification('You must agree to the Terms of Service and Privacy Policy', 'error');
                isValid = false;
                console.log('Terms validation failed');
            } else {
                isValid = true;
            }
            break;
    }
    
    console.log('Step validation result:', isValid ? 'PASS' : 'FAIL');
    return isValid;
}

function showError(input, message) {
    if (!input) {
        console.log('Cannot show error: input element is null');
        return;
    }
    
    const formGroup = input.closest('.form-group');
    if (!formGroup) {
        console.log('Cannot show error: form group not found');
        return;
    }
    
    formGroup.classList.add('error');
    
    let errorElement = formGroup.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        formGroup.appendChild(errorElement);
    }
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    console.log('Error shown:', message);
    
    // Focus on the field with error
    input.focus();
    
    // Add animation to draw attention
    input.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        input.style.animation = '';
    }, 500);
}

function clearError(input) {
    if (!input) return;
    
    const formGroup = input.closest('.form-group');
    if (!formGroup) return;
    
    formGroup.classList.remove('error');
    
    const errorElement = formGroup.querySelector('.error-message');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

function checkPasswordStrength(password) {
    console.log('Checking password strength...');
    
    if (!password) password = '';
    
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
    
    // Update requirement icons
    Object.keys(requirements).forEach(req => {
        const element = document.querySelector(`[data-requirement="${req}"] i`);
        if (element) {
            if (requirements[req]) {
                element.classList.remove('fa-times');
                element.classList.add('fa-check');
                element.style.color = '#10b981';
            } else {
                element.classList.remove('fa-check');
                element.classList.add('fa-times');
                element.style.color = '#ef4444';
            }
        }
    });
    
    // Calculate strength
    let strength = 0;
    Object.values(requirements).forEach(met => {
        if (met) strength++;
    });
    
    // Update strength bar
    const strengthBar = document.querySelector('.strength-fill');
    const percentage = (strength / 5) * 100;
    if (strengthBar) {
        strengthBar.style.width = `${percentage}%`;
    }
    
    // Update strength text
    const strengthText = document.querySelector('.strength-text');
    let strengthLevel = 'Weak';
    let color = '#ef4444';
    
    if (strength >= 4) {
        strengthLevel = 'Strong';
        color = '#10b981';
    } else if (strength >= 3) {
        strengthLevel = 'Good';
        color = '#f59e0b';
    } else if (strength >= 2) {
        strengthLevel = 'Fair';
        color = '#f59e0b';
    }
    
    if (strengthText) {
        strengthText.textContent = `Password strength: ${strengthLevel}`;
    }
    
    if (strengthBar) {
        strengthBar.style.backgroundColor = color;
    }
    
    console.log('Password strength:', strengthLevel, '(', strength, '/5)');
}

function checkPasswordMatch() {
    const password = document.getElementById('password')?.value || '';
    const confirmPassword = this.value;
    const matchElement = document.querySelector('.password-match');
    
    if (!matchElement) return;
    
    if (confirmPassword) {
        if (password === confirmPassword) {
            matchElement.innerHTML = '<i class="fas fa-check"></i><span>Passwords match</span>';
            matchElement.style.color = '#10b981';
        } else {
            matchElement.innerHTML = '<i class="fas fa-times"></i><span>Passwords do not match</span>';
            matchElement.style.color = '#ef4444';
        }
        matchElement.style.display = 'flex';
    } else {
        matchElement.style.display = 'none';
    }
}

function togglePassword(fieldId) {
    console.log('Toggling password visibility for:', fieldId);
    
    const input = document.getElementById(fieldId);
    if (!input) {
        console.log('Input element not found:', fieldId);
        return;
    }
    
    const toggleIcon = input.parentElement.querySelector('.toggle-password i');
    if (!toggleIcon) {
        console.log('Toggle icon not found');
        return;
    }
    
    if (input.type === 'password') {
        input.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
        console.log('Password shown');
    } else {
        input.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
        console.log('Password hidden');
    }
}

function updateAccountTypeSelection(selectedType) {
    console.log('Account type selected:', selectedType);
    
    // Remove active class from all account type cards
    document.querySelectorAll('.account-type-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to selected card
    const selectedCard = document.querySelector(`.account-type-card input[value="${selectedType}"]`)?.closest('.account-type-card');
    if (selectedCard) {
        selectedCard.classList.add('active');
    }
}

// Email validation
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Password validation
function validatePassword(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
    
    return Object.values(requirements).every(met => met);
}

async function handleFormSubmit(e) {
    e.preventDefault();
    console.log('Form submission started');
    
    // Validate all steps before submission
    for (let i = 1; i <= totalSteps; i++) {
        if (!validateStep(i)) {
            console.log('Validation failed at step:', i);
            
            // Go to the step with error
            if (currentStep !== i) {
                document.querySelector(`[data-step="${currentStep}"]`)?.classList.remove('active');
                currentStep = i;
                document.querySelector(`[data-step="${i}"]`)?.classList.add('active');
                updateProgress();
            }
            showNotification('Please fix the errors in the form', 'error');
            return;
        }
    }
    
    // Check terms agreement
    const termsCheckbox = document.getElementById('terms');
    if (!termsCheckbox || !termsCheckbox.checked) {
        showNotification('You must agree to the Terms of Service and Privacy Policy', 'error');
        return;
    }
    
    // Show loading
    const submitBtn = document.querySelector('.btn-submit');
    const spinner = submitBtn?.querySelector('.loading-spinner');
    const buttonText = submitBtn?.querySelector('span');
    
    if (submitBtn) submitBtn.disabled = true;
    if (spinner) spinner.style.display = 'block';
    if (buttonText) buttonText.textContent