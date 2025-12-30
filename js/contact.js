// Contact Page Functionality

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const contactForm = document.getElementById('contactForm');
    const newsletterForm = document.getElementById('newsletterForm');
    const faqQuestions = document.querySelectorAll('.faq-question');
    const successModal = document.getElementById('successModal');
    const closeSuccessBtn = document.querySelector('.close-success');

    // Initialize
    setupEventListeners();
    setupFAQ();
    checkURLParams();

    function setupEventListeners() {
        // Contact Form
        if (contactForm) {
            contactForm.addEventListener('submit', handleContactForm);
        }

        // Newsletter Form
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', handleNewsletterSignup);
        }

        // Success Modal Close
        if (closeSuccessBtn) {
            closeSuccessBtn.addEventListener('click', closeSuccessModal);
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === successModal) {
                closeSuccessModal();
            }
        });
    }

    function setupFAQ() {
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                // Toggle current answer
                const isExpanded = answer.style.display === 'block';
                answer.style.display = isExpanded ? 'none' : 'block';
                
                // Rotate icon
                icon.style.transform = isExpanded ? 'rotate(0)' : 'rotate(180deg)';
                
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

    function checkURLParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const subject = urlParams.get('subject');
        
        if (subject && document.getElementById('contactSubject')) {
            document.getElementById('contactSubject').value = subject;
            
            // Scroll to form
            document.getElementById('contact-form').scrollIntoView({ behavior: 'smooth' });
        }
    }

    function handleContactForm(e) {
        e.preventDefault();
        
        // Get form data
        const formData = {
            name: document.getElementById('contactName').value.trim(),
            email: document.getElementById('contactEmail').value.trim(),
            phone: document.getElementById('contactPhone').value.trim(),
            subject: document.getElementById('contactSubject').value,
            message: document.getElementById('contactMessage').value.trim(),
            attachment: document.getElementById('contactAttachment').files[0],
            newsletter: document.getElementById('contactNewsletter').checked
        };
        
        // Validate required fields
        if (!formData.name || !formData.email || !formData.subject || !formData.message) {
            alert('Please fill in all required fields.');
            return;
        }
        
        // Validate email
        if (!isValidEmail(formData.email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Validate phone if provided
        if (formData.phone && !isValidPhone(formData.phone)) {
            alert('Please enter a valid phone number.');
            return;
        }
        
        // Validate attachment if provided
        if (formData.attachment) {
            // Check file size (5MB limit)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (formData.attachment.size > maxSize) {
                alert('Attachment size must be less than 5MB.');
                return;
            }
            
            // Check file type
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png'
            ];
            
            if (!allowedTypes.includes(formData.attachment.type)) {
                alert('Please upload only PDF, DOC, JPG, or PNG files.');
                return;
            }
        }
        
        // Show loading
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Success
            showSuccessModal();
            
            // Reset form
            contactForm.reset();
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Log submission (in real app, send to server)
            console.log('Contact form submitted:', {
                ...formData,
                attachment: formData.attachment ? formData.attachment.name : 'None'
            });
            
            // Handle newsletter subscription if checked
            if (formData.newsletter) {
                subscribeToNewsletter(formData.email, formData.name);
            }
            
        }, 2000);
    }

    function handleNewsletterSignup(e) {
        e.preventDefault();
        
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const email = emailInput.value.trim();
        
        if (!email) {
            alert('Please enter your email address.');
            return;
        }
        
        if (!isValidEmail(email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Show loading
        const submitBtn = newsletterForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Success
            alert('Thank you for subscribing to our newsletter!');
            
            // Reset form
            newsletterForm.reset();
            
            // Reset button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            
            // Log subscription (in real app, send to server)
            console.log('Newsletter subscription:', email);
            
        }, 1500);
    }

    function showSuccessModal() {
        successModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            if (successModal.style.display === 'block') {
                closeSuccessModal();
            }
        }, 5000);
    }

    function closeSuccessModal() {
        successModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function subscribeToNewsletter(email, name) {
        // In a real app, this would make an API call
        console.log('Subscribing to newsletter:', { email, name });
        
        // You could add additional logic here, like:
        // - Add to mailing list
        // - Send welcome email
        // - Update user preferences
    }

    // Utility functions
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function isValidPhone(phone) {
        // Basic phone validation - adjust based on your needs
        const re = /^[\d\s\-\+\(\)]{10,}$/;
        return re.test(phone);
    }

    // Map initialization (placeholder - would be replaced with actual map API)
    function initMap() {
        // This would initialize a Google Map or similar
        // For now, we'll just log it
        console.log('Map would be initialized here');
        
        // Example with Google Maps:
        /*
        const map = new google.maps.Map(document.querySelector('.map-placeholder'), {
            center: { lat: 10.5264, lng: 7.4382 }, // Kaduna coordinates
            zoom: 15
        });
        
        new google.maps.Marker({
            position: { lat: 10.5264, lng: 7.4382 },
            map: map,
            title: 'Arewa Conservation Headquarters'
        });
        */
    }

    // Call map init if element exists
    if (document.querySelector('.map-placeholder')) {
        // In a real implementation, you would load the map API asynchronously
        // initMap();
    }

    // Department contact cards interactivity
    const departmentCards = document.querySelectorAll('.department-card');
    departmentCards.forEach(card => {
        card.addEventListener('click', function() {
            const email = this.querySelector('p:nth-child(3)').textContent.split(': ')[1];
            const subject = encodeURIComponent(`Inquiry: ${this.querySelector('h3').textContent}`);
            window.location.href = `mailto:${email}?subject=${subject}`;
        });
    });

    // Transport options hover effects
    const transportOptions = document.querySelectorAll('.transport-option');
    transportOptions.forEach(option => {
        option.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(10px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        option.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Contact cards animation
    const contactCards = document.querySelectorAll('.contact-card');
    contactCards.forEach((card, index) => {
        // Staggered animation
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-in');
    });
});