// Volunteer Portal Functionality

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const filterBtns = document.querySelectorAll('.filter-btn');
    const applyBtns = document.querySelectorAll('.btn-apply');
    const applicationModal = document.getElementById('applicationModal');
    const closeModal = document.querySelector('.close-modal');
    const applicationForm = document.getElementById('volunteerApplicationForm');
    const faqQuestions = document.querySelectorAll('.faq-question');

    // Sample opportunities data
    const opportunities = [
        {
            id: 1,
            title: 'Field Conservation Assistant',
            type: 'field',
            location: 'Kano State',
            description: 'Assist in field conservation activities including site documentation, artifact handling, and community engagement.',
            hours: '20 hrs/week',
            duration: '6 months',
            openings: 5,
            skills: ['Field Work', 'Documentation', 'Community Engagement']
        },
        {
            id: 2,
            title: 'Digital Archiving Volunteer',
            type: 'digital',
            location: 'Remote',
            description: 'Digitize historical documents, create metadata, and organize digital collections.',
            hours: 'Flexible',
            duration: '3 months minimum',
            openings: 10,
            skills: ['Digital Skills', 'Attention to Detail', 'Organization']
        }
    ];

    // Initialize
    setupEventListeners();
    setupFAQ();

    function setupEventListeners() {
        // Filter buttons
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterOpportunities(this.dataset.filter);
            });
        });

        // Apply buttons
        applyBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                openApplicationModal(this.dataset.role);
            });
        });

        // Modal close
        closeModal.addEventListener('click', closeApplicationModal);
        window.addEventListener('click', function(e) {
            if (e.target === applicationModal) closeApplicationModal();
        });

        // Application form
        applicationForm.addEventListener('submit', handleApplication);
    }

    function filterOpportunities(filter) {
        const opportunitiesGrid = document.querySelector('.opportunities-grid');
        const opportunityCards = opportunitiesGrid.querySelectorAll('.opportunity-card');
        
        opportunityCards.forEach(card => {
            if (filter === 'all' || card.dataset.category === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function openApplicationModal(role) {
        // Check if user is logged in
        const isLoggedIn = checkAuthStatus();
        
        if (!isLoggedIn) {
            if (confirm('You need to be logged in to apply. Would you like to login or register first?')) {
                window.location.href = 'login.html?redirect=volunteer&role=' + encodeURIComponent(role);
            }
            return;
        }
        
        // Set the applied role
        document.getElementById('appliedRole').value = role;
        
        // Pre-fill if user data exists
        const userData = getUserData();
        if (userData) {
            document.getElementById('applicantName').value = userData.name || '';
            document.getElementById('applicantEmail').value = userData.email || '';
            document.getElementById('applicantPhone').value = userData.phone || '';
            document.getElementById('applicantLocation').value = userData.location || '';
        }
        
        // Show modal
        applicationModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeApplicationModal() {
        applicationModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        applicationForm.reset();
    }

    function handleApplication(e) {
        e.preventDefault();
        
        const formData = {
            role: document.getElementById('appliedRole').value,
            name: document.getElementById('applicantName').value,
            email: document.getElementById('applicantEmail').value,
            phone: document.getElementById('applicantPhone').value,
            location: document.getElementById('applicantLocation').value,
            background: document.getElementById('applicantBackground').value,
            availability: document.getElementById('availability').value,
            cv: document.getElementById('applicantCV').files[0]
        };
        
        // Validate
        if (!formData.name || !formData.email || !formData.phone || !formData.location || !formData.background || !formData.availability) {
            alert('Please fill in all required fields.');
            return;
        }
        
        // Validate email
        if (!isValidEmail(formData.email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Show loading
        const submitBtn = applicationForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Success
            alert('Application submitted successfully! Our volunteer team will contact you within 3-5 business days.');
            
            // Close modal
            closeApplicationModal();
            
            // Reset button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            
            // Log application (in real app, send to server)
            console.log('Volunteer application submitted:', formData);
            
            // Update opportunities count (in a real app, this would come from server)
            updateOpportunityCount(formData.role);
            
        }, 2000);
    }

    function setupFAQ() {
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                // Toggle current answer
                answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
                
                // Rotate icon
                icon.style.transform = answer.style.display === 'block' ? 'rotate(180deg)' : 'rotate(0)';
                
                // Close other answers
                faqQuestions.forEach(otherQuestion => {
                    if (otherQuestion !== this) {
                        otherQuestion.nextElementSibling.style.display = 'none';
                        otherQuestion.querySelector('i').style.transform = 'rotate(0)';
                    }
                });
            });
        });
    }

    // Utility functions
    function checkAuthStatus() {
        // In a real app, this would check cookies/localStorage/tokens
        // For demo, check for user session
        return localStorage.getItem('userLoggedIn') === 'true' || 
               sessionStorage.getItem('userLoggedIn') === 'true';
    }

    function getUserData() {
        // In a real app, this would come from an API or localStorage
        try {
            return JSON.parse(localStorage.getItem('userData')) || 
                   JSON.parse(sessionStorage.getItem('userData'));
        } catch (e) {
            return null;
        }
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function updateOpportunityCount(role) {
        // In a real app, this would update the openings count via API
        // For demo, we'll just log it
        console.log('Updating openings for role:', role);
        
        // Find and update the corresponding opportunity card
        const opportunityCards = document.querySelectorAll('.opportunity-card');
        opportunityCards.forEach(card => {
            if (card.querySelector('.btn-apply').dataset.role === role) {
                const openingsSpan = card.querySelector('.opportunity-details span:nth-child(3)');
                if (openingsSpan) {
                    const currentOpenings = parseInt(openingsSpan.textContent.match(/\d+/)[0]);
                    if (currentOpenings > 1) {
                        openingsSpan.textContent = openingsSpan.textContent.replace(
                            currentOpenings.toString(),
                            (currentOpenings - 1).toString()
                        );
                    } else if (currentOpenings === 1) {
                        openingsSpan.textContent = openingsSpan.textContent.replace('1', '0');
                        const applyBtn = card.querySelector('.btn-apply');
                        applyBtn.textContent = 'Filled';
                        applyBtn.disabled = true;
                        applyBtn.style.opacity = '0.5';
                    }
                }
            }
        });
    }
});