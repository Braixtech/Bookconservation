// User Profile Functionality

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const editProfileBtn = document.getElementById('editProfileBtn');
    const changeAvatarBtn = document.getElementById('changeAvatarBtn');
    const profileNavItems = document.querySelectorAll('.nav-item');
    const editProfileModal = document.getElementById('editProfileModal');
    const closeModal = document.querySelector('.close-modal');
    const editProfileForm = document.getElementById('editProfileForm');
    const cancelEditBtn = document.querySelector('.cancel-edit');
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    // Sample user data
    let userData = {
        name: 'John Doe',
        email: 'john.doe@example.com',
        phone: '+234 800 123 4567',
        location: 'Kaduna, Nigeria',
        bio: 'Passionate conservation volunteer with interest in preserving Northern Nigeria\'s cultural heritage. Special focus on traditional architecture and oral history documentation.',
        interests: ['Cultural Heritage', 'Traditional Architecture', 'Oral History', 'Community Engagement', 'Digital Preservation'],
        avatar: null
    };

    // Initialize
    loadUserData();
    setupEventListeners();

    function loadUserData() {
        // Load from localStorage or API
        const savedData = localStorage.getItem('arewaUserProfile');
        if (savedData) {
            try {
                userData = { ...userData, ...JSON.parse(savedData) };
            } catch (e) {
                console.error('Error loading user data:', e);
            }
        }
        
        updateProfileUI();
    }

    function updateProfileUI() {
        // Update profile header
        document.querySelector('.profile-details h1').textContent = userData.name;
        document.querySelector('.profile-title').textContent = 'Conservation Volunteer';
        
        // Update overview section
        document.getElementById('editName').value = userData.name;
        document.getElementById('editEmail').value = userData.email;
        document.getElementById('editPhone').value = userData.phone;
        document.getElementById('editLocation').value = userData.location;
        document.getElementById('editBio').value = userData.bio;
        document.getElementById('editInterests').value = userData.interests.join(', ');
        
        // Update info list in overview
        const infoList = document.querySelector('.info-list');
        if (infoList) {
            infoList.innerHTML = `
                <div class="info-item">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">${userData.name}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">${userData.email}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">${userData.phone}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Location:</span>
                    <span class="info-value">${userData.location}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member Since:</span>
                    <span class="info-value">January 15, 2022</span>
                </div>
            `;
        }
        
        // Update bio
        const bioText = document.querySelector('.bio-text');
        if (bioText) {
            bioText.textContent = userData.bio;
        }
        
        // Update interests
        const interestTags = document.querySelector('.interest-tags');
        if (interestTags) {
            interestTags.innerHTML = userData.interests.map(interest => 
                `<span class="interest-tag">${interest}</span>`
            ).join('');
        }
    }

    function setupEventListeners() {
        // Edit Profile Button
        editProfileBtn.addEventListener('click', openEditProfileModal);

        // Change Avatar Button
        changeAvatarBtn.addEventListener('click', changeAvatar);

        // Profile Navigation
        profileNavItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all items
                profileNavItems.forEach(i => i.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Show corresponding section
                const targetId = this.getAttribute('href').substring(1);
                showProfileSection(targetId);
            });
        });

        // Modal close
        closeModal.addEventListener('click', closeEditProfileModal);
        cancelEditBtn.addEventListener('click', closeEditProfileModal);
        window.addEventListener('click', function(e) {
            if (e.target === editProfileModal) closeEditProfileModal();
        });

        // Edit Profile Form
        editProfileForm.addEventListener('submit', saveProfileChanges);

        // User Menu
        userMenuBtn.addEventListener('click', toggleUserDropdown);
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    }

    function openEditProfileModal() {
        // Pre-fill form with current data
        document.getElementById('editName').value = userData.name;
        document.getElementById('editEmail').value = userData.email;
        document.getElementById('editPhone').value = userData.phone;
        document.getElementById('editLocation').value = userData.location;
        document.getElementById('editBio').value = userData.bio;
        document.getElementById('editInterests').value = userData.interests.join(', ');
        
        // Show modal
        editProfileModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeEditProfileModal() {
        editProfileModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        editProfileForm.reset();
    }

    function saveProfileChanges(e) {
        e.preventDefault();
        
        // Get form data
        const updatedData = {
            name: document.getElementById('editName').value.trim(),
            email: document.getElementById('editEmail').value.trim(),
            phone: document.getElementById('editPhone').value.trim(),
            location: document.getElementById('editLocation').value.trim(),
            bio: document.getElementById('editBio').value.trim(),
            interests: document.getElementById('editInterests').value
                .split(',')
                .map(item => item.trim())
                .filter(item => item)
        };
        
        // Validate
        if (!updatedData.name || !updatedData.email) {
            alert('Name and email are required.');
            return;
        }
        
        if (!isValidEmail(updatedData.email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Show loading
        const submitBtn = editProfileForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Update user data
            userData = { ...userData, ...updatedData };
            
            // Save to localStorage (in real app, send to server)
            localStorage.setItem('arewaUserProfile', JSON.stringify(userData));
            
            // Update UI
            updateProfileUI();
            
            // Update user menu
            updateUserMenu(updatedData.name);
            
            // Close modal
            closeEditProfileModal();
            
            // Show success message
            alert('Profile updated successfully!');
            
            // Reset button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            
        }, 1500);
    }

    function changeAvatar() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.onchange = function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }
            
            // Validate file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size must be less than 2MB.');
                return;
            }
            
            // Show loading
            changeAvatarBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            changeAvatarBtn.disabled = true;
            
            // Simulate upload
            setTimeout(() => {
                // In a real app, upload to server and get URL
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Save avatar data
                    userData.avatar = e.target.result;
                    localStorage.setItem('arewaUserProfile', JSON.stringify(userData));
                    
                    // Update avatar display
                    const avatarImage = document.querySelector('.avatar-image');
                    avatarImage.innerHTML = `<img src="${e.target.result}" alt="${userData.name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
                    
                    // Reset button
                    changeAvatarBtn.innerHTML = 'Change Photo';
                    changeAvatarBtn.disabled = false;
                    
                    alert('Profile photo updated successfully!');
                };
                reader.readAsDataURL(file);
            }, 1000);
        };
        
        input.click();
    }

    function showProfileSection(sectionId) {
        // Hide all sections
        const sections = document.querySelectorAll('.profile-section');
        sections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Show target section
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
            
            // Scroll to section
            targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function toggleUserDropdown() {
        userDropdown.classList.toggle('show');
    }

    function updateUserMenu(name) {
        const userNameSpan = userMenuBtn.querySelector('span');
        if (userNameSpan) {
            userNameSpan.textContent = name;
        }
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Load contributions and projects data
    loadContributions();
    loadProjects();
    
    function loadContributions() {
        // This would typically come from an API
        const contributions = [
            {
                type: 'upload',
                title: 'Historical Photo Collection',
                description: 'Collection of 25 historical photographs from 1950s Kano',
                date: 'Dec 1, 2023',
                views: 245,
                downloads: 89
            },
            {
                type: 'comment',
                title: 'Research Methodology Feedback',
                description: 'Provided detailed feedback on conservation research methodology',
                date: 'Nov 25, 2023',
                likes: 12,
                replies: 3
            }
        ];
        
        // Render contributions if on contributions tab
        const contributionsList = document.querySelector('.contributions-list');
        if (contributionsList) {
            contributionsList.innerHTML = contributions.map(cont => `
                <div class="contribution-item">
                    <div class="contribution-type">
                        <i class="fas fa-${cont.type === 'upload' ? 'upload' : 'comment'}"></i>
                        <span>${cont.type.charAt(0).toUpperCase() + cont.type.slice(1)}</span>
                    </div>
                    <div class="contribution-details">
                        <h4>${cont.title}</h4>
                        <p>${cont.description}</p>
                        <div class="contribution-meta">
                            <span><i class="fas fa-calendar"></i> ${cont.date}</span>
                            ${cont.views ? `<span><i class="fas fa-eye"></i> ${cont.views} views</span>` : ''}
                            ${cont.downloads ? `<span><i class="fas fa-download"></i> ${cont.downloads} downloads</span>` : ''}
                            ${cont.likes ? `<span><i class="fas fa-thumbs-up"></i> ${cont.likes} likes</span>` : ''}
                            ${cont.replies ? `<span><i class="fas fa-reply"></i> ${cont.replies} replies</span>` : ''}
                        </div>
                    </div>
                    <div class="contribution-actions">
                        <button class="btn-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-view"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            `).join('');
        }
    }
    
    function loadProjects() {
        // This would typically come from an API
        const projects = [
            {
                name: 'Kano City Walls Restoration',
                role: 'Field Volunteer',
                progress: 65,
                hours: 45,
                completedTasks: 12,
                totalTasks: 20
            },
            {
                name: 'Sokoto Manuscripts Digitization',
                role: 'Research Assistant',
                progress: 40,
                hours: 32,
                completedTasks: 8,
                totalTasks: 15
            }
        ];
        
        // Render projects if on projects tab
        const projectsGrid = document.querySelector('.projects-grid');
        if (projectsGrid) {
            projectsGrid.innerHTML = projects.map(project => `
                <div class="project-card-mini">
                    <div class="project-header">
                        <h4>${project.name}</h4>
                        <span class="project-role">${project.role}</span>
                    </div>
                    <div class="project-progress">
                        <div class="progress-bar">
                            <div class="progress" style="width: ${project.progress}%;"></div>
                        </div>
                        <span>${project.progress}% Complete</span>
                    </div>
                    <div class="project-stats">
                        <span><i class="fas fa-clock"></i> ${project.hours} hours</span>
                        <span><i class="fas fa-tasks"></i> ${project.completedTasks}/${project.totalTasks} tasks</span>
                    </div>
                </div>
            `).join('');
        }
    }
});