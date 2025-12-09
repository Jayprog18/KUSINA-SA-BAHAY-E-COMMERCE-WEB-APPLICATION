// Form Validation Functions

// Validate Email Format
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate Password Strength
function validatePassword(password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return password.length >= 8;
}

// Validate Username
function validateUsername(username) {
    // 3-20 characters, alphanumeric and underscore only
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    return usernameRegex.test(username);
}

// Show Error Message
function showError(inputId, message) {
    const errorElement = document.getElementById(inputId + 'Error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
    
    const inputElement = document.getElementById(inputId);
    if (inputElement) {
        inputElement.style.borderColor = '#e74c3c';
    }
}

// Hide Error Message
function hideError(inputId) {
    const errorElement = document.getElementById(inputId + 'Error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
    
    const inputElement = document.getElementById(inputId);
    if (inputElement) {
        inputElement.style.borderColor = '#ddd';
    }
}

// Registration Form Validation
function validateRegistrationForm() {
    let isValid = true;
    
    // Get form values
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const fullName = document.getElementById('fullName').value.trim();
    
    // Reset all errors
    hideError('username');
    hideError('email');
    hideError('password');
    hideError('confirmPassword');
    hideError('fullName');
    
    // Validate Full Name
    if (fullName === '') {
        showError('fullName', 'Full name is required');
        isValid = false;
    } else if (fullName.length < 2) {
        showError('fullName', 'Full name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate Username
    if (username === '') {
        showError('username', 'Username is required');
        isValid = false;
    } else if (!validateUsername(username)) {
        showError('username', 'Username must be 3-20 characters, letters, numbers, and underscore only');
        isValid = false;
    }
    
    // Validate Email
    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate Password
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    } else if (!validatePassword(password)) {
        showError('password', 'Password must be at least 8 characters');
        isValid = false;
    }
    
    // Validate Confirm Password
    if (confirmPassword === '') {
        showError('confirmPassword', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirmPassword', 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
}

// Login Form Validation
function validateLoginForm() {
    let isValid = true;
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    // Reset errors
    hideError('username');
    hideError('password');
    
    // Validate Username/Email
    if (username === '') {
        showError('username', 'Username or email is required');
        isValid = false;
    }
    
    // Validate Password
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    }
    
    return isValid;
}

// Profile Update Form Validation
function validateProfileForm() {
    let isValid = true;
    
    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    
    // Reset errors
    hideError('fullName');
    hideError('email');
    
    // Validate Full Name
    if (fullName === '') {
        showError('fullName', 'Full name is required');
        isValid = false;
    } else if (fullName.length < 2) {
        showError('fullName', 'Full name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate Email
    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    return isValid;
}

// Real-time validation on input
document.addEventListener('DOMContentLoaded', function() {
    // Add real-time validation listeners
    const inputs = document.querySelectorAll('input');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            const id = this.id;
            const value = this.value.trim();
            
            if (id === 'email' && value !== '') {
                if (!validateEmail(value)) {
                    showError(id, 'Please enter a valid email address');
                } else {
                    hideError(id);
                }
            }
            
            if (id === 'username' && value !== '') {
                if (!validateUsername(value)) {
                    showError(id, 'Username must be 3-20 characters, letters, numbers, and underscore only');
                } else {
                    hideError(id);
                }
            }
            
            if (id === 'password' && value !== '') {
                if (!validatePassword(value)) {
                    showError(id, 'Password must be at least 8 characters');
                } else {
                    hideError(id);
                }
            }
            
            if (id === 'confirmPassword' && value !== '') {
                const password = document.getElementById('password').value;
                if (value !== password) {
                    showError(id, 'Passwords do not match');
                } else {
                    hideError(id);
                }
            }
        });
        
        // Clear error on focus
        input.addEventListener('focus', function() {
            hideError(this.id);
        });
    });
});