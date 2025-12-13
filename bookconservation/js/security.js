// Security Center Functionality

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const toggle2FA = document.getElementById('toggle2FA');
    const toggleLoginNotifications = document.getElementById('toggleLoginNotifications');
    const viewSessionsBtn = document.getElementById('viewSessionsBtn');
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    const viewAllActivityBtn = document.getElementById('viewAllActivity');
    const reportSuspiciousBtn = document.getElementById('reportSuspicious');
    const exportDataBtn = document.getElementById('exportDataBtn');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const privacySettingsBtn = document.getElementById('privacySettingsBtn');
    const lockAccountBtn = document.getElementById('lockAccountBtn');
    const logoutAllBtn = document.getElementById('logoutAllBtn');
    const reportIncidentBtn = document.getElementById('reportIncidentBtn');
    const contactSupportBtn = document.getElementById('contactSupportBtn');

    // Modal Elements
    const sessionsModal = document.getElementById('sessionsModal');
    const passwordModal = document.getElementById('passwordModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');

    // Initialize
    setupEventListeners();
    update2FAStatus();
    checkPasswordStrength();

    function setupEventListeners() {
        // 2FA Toggle
        toggle2FA.addEventListener('change', toggleTwoFactorAuth);

        // Login Notifications Toggle
        toggleLoginNotifications.addEventListener('change', toggleLoginNotificationsFunc);

        // View Sessions
        viewSessionsBtn.addEventListener('click', openSessionsModal);

        // Change Password
        changePasswordBtn.addEventListener('click', openPasswordModal);

        // View All Activity
        viewAllActivityBtn.addEventListener('click', viewAllActivity);

        // Report Suspicious Activity
        reportSuspiciousBtn.addEventListener('click', reportSuspiciousActivity);

        // Data Export
        exportDataBtn.addEventListener('click', exportUserData);

        // Delete Account
        deleteAccountBtn.addEventListener('click', deleteAccount);

        // Privacy Settings
        privacySettingsBtn.addEventListener('click', openPrivacySettings);

        // Emergency Actions
        lockAccountBtn.addEventListener('click', lockAccount);
        logoutAllBtn.addEventListener('click', logoutAllDevices);
        reportIncidentBtn.addEventListener('click', reportSecurityIncident);
        contactSupportBtn.addEventListener('click', contactSecuritySupport);

        // Modal Close
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Password form submission
        const changePasswordForm = document.getElementById('changePasswordForm');
        if (changePasswordForm) {
            changePasswordForm.addEventListener('submit', handlePasswordChange);
            
            // Real-time password validation
            const newPasswordInput = document.getElementById('newPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            
            newPasswordInput.addEventListener('input', validatePassword);
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        }

        // Logout all sessions
        const logoutAllSessionsBtn = document.getElementById('logoutAllSessions');
        if (logoutAllSessionsBtn) {
            logoutAllSessionsBtn.addEventListener('click', logoutAllSessions);
        }

        // Individual session logout
        sessionsModal.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-logout-session')) {
                logoutSession(e.target);
            }
        });
    }

    function toggleTwoFactorAuth() {
        const isEnabled = toggle2FA.checked;
        const statusElement = document.getElementById('2faStatus');
        
        // Show confirmation for enabling
        if (isEnabled) {
            if (!confirm('Enable Two-Factor Authentication?\n\nYou will need to set up an authenticator app.')) {
                toggle2FA.checked = false;
                return;
            }
        }
        
        // Simulate API call
        showLoading(toggle2FA);
        
        setTimeout(() => {
            statusElement.textContent = isEnabled ? 'Enabled' : 'Disabled';
            statusElement.className = `setting-status ${isEnabled ? 'enabled' : 'disabled'}`;
            
            if (isEnabled) {
                // In a real app, this would redirect to 2FA setup
                alert('Two-Factor Authentication Enabled!\n\nPlease set up your authenticator app with the QR code that will be shown.');
                // Redirect to 2FA setup page
                // window.location.href = 'setup-2fa.html';
            } else {
                alert('Two-Factor Authentication Disabled');
            }
            
            hideLoading(toggle2FA);
        }, 1000);
    }

    function toggleLoginNotificationsFunc() {
        const isEnabled = toggleLoginNotifications.checked;
        const statusElement = document.getElementById('loginNotificationStatus');
        
        showLoading(toggleLoginNotifications);
        
        setTimeout(() => {
            statusElement.textContent = isEnabled ? 'Enabled' : 'Disabled';
            statusElement.className = `setting-status ${isEnabled ? 'enabled' : 'disabled'}`;
            
            // Simulate API call
            console.log(`Login notifications ${isEnabled ? 'enabled' : 'disabled'}`);
            
            hideLoading(toggleLoginNotifications);
        }, 500);
    }

    function openSessionsModal() {
        sessionsModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Load sessions data
        loadSessions();
    }

    function loadSessions() {
        // This would typically come from an API
        const sessions = [
            {
                id: 1,
                device: 'Chrome Browser',
                location: 'Kaduna, Nigeria',
                ip: '192.168.1.1',
                lastActive: 'Just now',
                current: true
            },
            {
                id: 2,
                device: 'Android App',
                location: 'Lagos, Nigeria',
                ip: '192.168.1.2',
                lastActive: '2 hours ago',
                current: false
            }
        ];
        
        // Update UI with sessions data
        // This would be implemented based on your modal structure
    }

    function openPasswordModal() {
        passwordModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Clear form
        const form = document.getElementById('changePasswordForm');
        if (form) form.reset();
        
        // Reset validation
        resetPasswordValidation();
    }

    function handlePasswordChange(e) {
        e.preventDefault();
        
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validate current password (in a real app, this would check against server)
        if (!currentPassword) {
            alert('Please enter your current password');
            return;
        }
        
        // Validate new password
        if (!validatePasswordStrength(newPassword)) {
            alert('New password does not meet requirements');
            return;
        }
        
        // Confirm password match
        if (newPassword !== confirmPassword) {
            alert('New passwords do not match');
            return;
        }
        
        // Show loading
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // In a real app, this would make an API call to change password
            console.log('Password changed successfully');
            
            // Show success
            alert('Password changed successfully!');
            
            // Close modal
            passwordModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Update UI
            updatePasswordChangeTime();
            
            // Reset button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 1500);
    }

    function validatePassword() {
        const password = document.getElementById('newPassword').value;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };
        
        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(req + 'Req');
            if (element) {
                if (requirements[req]) {
                    element.classList.add('valid');
                    element.innerHTML = '✓ ' + element.textContent.substring(2);
                } else {
                    element.classList.remove('valid');
                    element.innerHTML = '✗ ' + element.textContent.substring(2);
                }
            }
        });
        
        // Update strength meter
        const strength = calculatePasswordStrength(password);
        updateStrengthMeter(strength);
        
        return Object.values(requirements).every(req => req);
    }

    function validatePasswordMatch() {
        const password = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmPassword').value;
        const confirmInput = document.getElementById('confirmPassword');
        
        if (confirm && password !== confirm) {
            confirmInput.style.borderColor = '#f44336';
            return false;
        } else {
            confirmInput.style.borderColor = '';
            return true;
        }
    }

    function calculatePasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Character variety checks
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return Math.min(strength, 5); // Max strength of 5
    }

    function updateStrengthMeter(strength) {
        const meter = document.querySelector('.strength-level');
        const label = document.querySelector('.strength-label');
        
        let width, text, color, className;
        
        switch (true) {
            case strength <= 2:
                width = '33%';
                text = 'Weak';
                color = '#f44336';
                className = 'weak';
                break;
            case strength <= 4:
                width = '66%';
                text = 'Medium';
                color = '#ff9800';
                className = 'medium';
                break;
            default:
                width = '100%';
                text = 'Strong';
                color = '#4CAF50';
                className = 'strong';
        }
        
        if (meter) {
            meter.style.width = width;
            meter.style.backgroundColor = color;
            meter.className = `strength-level ${className}`;
        }
        
        if (label) {
            label.textContent = text;
            label.className = `strength-label ${className}`;
        }
    }

    function resetPasswordValidation() {
        // Reset requirement indicators
        const reqElements = document.querySelectorAll('.password-requirements li');
        reqElements.forEach(el => {
            el.classList.remove('valid');
            el.innerHTML = '✗ ' + el.textContent.substring(2);
        });
        
        // Reset strength meter
        updateStrengthMeter(0);
    }

    function checkPasswordStrength() {
        // This would typically check when the page loads
        // For now, we'll set it to strong as an example
        updateStrengthMeter(5);
    }

    function updatePasswordChangeTime() {
        // Update the "Last changed" time in the UI
        const timeElement = document.querySelector('.setting-info p');
        if (timeElement) {
            timeElement.textContent = 'Last changed: Just now';
        }
    }

    function viewAllActivity() {
        alert('This would show all security activity in a new page or expanded view.');
        // In a real app: window.location.href = 'security-activity.html';
    }

    function reportSuspiciousActivity() {
        const description = prompt('Please describe the suspicious activity:');
        if (description) {
            alert('Thank you for reporting. Our security team will investigate.');
            // In a real app, this would submit to an API
            console.log('Suspicious activity reported:', description);
        }
    }

    function exportUserData() {
        if (confirm('Export all your personal data?\n\nThis may take a few minutes.')) {
            // Show loading
            exportDataBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing Export...';
            exportDataBtn.disabled = true;
            
            // Simulate data preparation
            setTimeout(() => {
                // In a real app, this would generate and download a file
                alert('Your data export is ready. Download will start automatically.');
                
                // Reset button
                exportDataBtn.innerHTML = 'Export Data';
                exportDataBtn.disabled = false;
                
                // Simulate download
                const data = JSON.stringify({
                    user: 'John Doe',
                    email: 'john@example.com',
                    activities: [],
                    // ... other data
                }, null, 2);
                
                const blob = new Blob([data], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'arewa-conservation-data-export.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 2000);
        }
    }

    function deleteAccount() {
        if (confirm('WARNING: This will permanently delete your account and all associated data.\n\nThis action cannot be undone.\n\nAre you absolutely sure?')) {
            const confirmation = prompt('Type "DELETE" to confirm:');
            if (confirmation === 'DELETE') {
                // Show final warning
                if (confirm('Final Warning: This will delete everything. Continue?')) {
                    alert('Account deletion scheduled. You will receive a confirmation email.');
                    // In a real app, this would call an API
                }
            } else {
                alert('Account deletion cancelled.');
            }
        }
    }

    function openPrivacySettings() {
        alert('This would open privacy settings page.');
        // In a real app: window.location.href = 'privacy-settings.html';
    }

    function lockAccount() {
        if (confirm('Lock your account?\n\nYou will not be able to log in until you unlock it.')) {
            // Simulate API call
            showLoading(lockAccountBtn);
            
            setTimeout(() => {
                alert('Account locked successfully. Contact support to unlock.');
                hideLoading(lockAccountBtn);
            }, 1000);
        }
    }

    function logoutAllDevices() {
        if (confirm('Log out from all devices?\n\nYou will need to log in again on all devices.')) {
            // Simulate API call
            showLoading(logoutAllBtn);
            
            setTimeout(() => {
                alert('Logged out from all devices successfully.');
                hideLoading(logoutAllBtn);
                
                // In a real app, this would redirect to login
                // window.location.href = 'login.html?logout=all';
            }, 1000);
        }
    }

    function logoutAllSessions() {
        if (confirm('Log out from all active sessions?')) {
            // Close modal first
            sessionsModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Then perform logout
            logoutAllDevices();
        }
    }

    function logoutSession(button) {
        const sessionId = button.dataset.sessionId;
        if (confirm('Log out from this session?')) {
            // Remove session from UI
            const sessionItem = button.closest('.session-item');
            if (sessionItem) {
                sessionItem.style.opacity = '0.5';
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
                
                setTimeout(() => {
                    sessionItem.remove();
                    alert('Session logged out successfully.');
                }, 1000);
            }
        }
    }

    function reportSecurityIncident() {
        window.location.href = 'contact.html?subject=security-incident';
    }

    function contactSecuritySupport() {
        window.location.href = 'contact.html?subject=security-support';
    }

    function update2FAStatus() {
        // Check if 2FA is enabled (would come from API in real app)
        const isEnabled = false; // Default to false
        toggle2FA.checked = isEnabled;
        document.getElementById('2faStatus').textContent = isEnabled ? 'Enabled' : 'Disabled';
    }

    function showLoading(element) {
        element.disabled = true;
        element.style.opacity = '0.7';
        if (element.tagName === 'BUTTON') {
            const originalText = element.innerHTML;
            element.dataset.originalText = originalText;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
    }

    function hideLoading(element) {
        element.disabled = false;
        element.style.opacity = '1';
        if (element.tagName === 'BUTTON' && element.dataset.originalText) {
            element.innerHTML = element.dataset.originalText;
            delete element.dataset.originalText;
        }
    }
});