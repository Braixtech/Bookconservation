// Digital Library Functionality

document.addEventListener('DOMContentLoaded', function() {
    // Sample resource data
    const resources = [
        {
            id: 1,
            title: "History of Northern Nigeria",
            type: "book",
            author: "Prof. Abdullahi Smith",
            year: "2022",
            views: 1245,
            downloads: 345,
            description: "Comprehensive history of Northern Nigeria from pre-colonial times to present"
        },
        {
            id: 2,
            title: "Traditional Hausa Architecture",
            type: "manuscript",
            author: "Dr. Fatima Bello",
            year: "2021",
            views: 892,
            downloads: 210,
            description: "Detailed documentation of traditional Hausa building techniques"
        },
        {
            id: 3,
            title: "Arewa Folk Songs Collection",
            type: "audio",
            author: "Cultural Heritage Team",
            year: "2023",
            views: 1567,
            downloads: 432,
            description: "Audio recordings of traditional folk songs from Northern Nigeria"
        },
        {
            id: 4,
            title: "Sokoto Caliphate Documents",
            type: "manuscript",
            author: "Historical Archives",
            year: "2020",
            views: 2103,
            downloads: 567,
            description: "Digitized documents from the Sokoto Caliphate era"
        },
        {
            id: 5,
            title: "Wildlife Conservation in Chad Basin",
            type: "video",
            author: "Conservation Biology Dept.",
            year: "2023",
            views: 987,
            downloads: 321,
            description: "Documentary on wildlife conservation efforts in the Chad Basin"
        },
        {
            id: 6,
            title: "Traditional Textile Patterns",
            type: "images",
            author: "Arts & Culture Team",
            year: "2022",
            views: 1345,
            downloads: 456,
            description: "Collection of traditional textile patterns and designs"
        }
    ];

    // DOM Elements
    const resourceGrid = document.getElementById('resourceGrid');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const sortSelect = document.getElementById('sortSelect');
    const searchInput = document.getElementById('librarySearch');
    const searchBtn = document.getElementById('searchBtn');
    const uploadBtns = document.querySelectorAll('.upload-btn');
    const uploadModal = document.getElementById('uploadModal');
    const closeModal = document.querySelector('.close-modal');
    const uploadForm = document.getElementById('uploadForm');

    // Current state
    let currentFilter = 'all';
    let currentSort = 'newest';
    let currentPage = 1;
    const itemsPerPage = 6;
    let filteredResources = [...resources];

    // Initialize
    renderResources();
    setupEventListeners();

    function renderResources() {
        // Filter resources
        let displayResources = filteredResources.filter(resource => {
            if (currentFilter === 'all') return true;
            return resource.type === currentFilter;
        });

        // Sort resources
        displayResources.sort((a, b) => {
            switch (currentSort) {
                case 'newest':
                    return new Date(b.year) - new Date(a.year);
                case 'oldest':
                    return new Date(a.year) - new Date(b.year);
                case 'popular':
                    return b.views - a.views;
                case 'title':
                    return a.title.localeCompare(b.title);
                default:
                    return 0;
            }
        });

        // Paginate
        const totalPages = Math.ceil(displayResources.length / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedResources = displayResources.slice(startIndex, startIndex + itemsPerPage);

        // Update page info
        document.getElementById('currentPage').textContent = currentPage;
        document.getElementById('totalPages').textContent = totalPages;

        // Render resources
        resourceGrid.innerHTML = paginatedResources.map(resource => `
            <div class="resource-card" data-type="${resource.type}">
                <div class="resource-icon">
                    <i class="${getResourceIcon(resource.type)}"></i>
                </div>
                <div class="resource-info">
                    <span class="resource-type">${resource.type.toUpperCase()}</span>
                    <h3>${resource.title}</h3>
                    <p class="resource-author">By ${resource.author}</p>
                    <p class="resource-desc">${resource.description}</p>
                    <div class="resource-meta">
                        <span><i class="fas fa-calendar"></i> ${resource.year}</span>
                        <span><i class="fas fa-eye"></i> ${resource.views.toLocaleString()}</span>
                        <span><i class="fas fa-download"></i> ${resource.downloads.toLocaleString()}</span>
                    </div>
                </div>
                <div class="resource-actions">
                    <button class="btn-preview" data-id="${resource.id}">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button class="btn-download" data-id="${resource.id}">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
        `).join('');

        // Update pagination buttons
        updatePaginationButtons(totalPages);
    }

    function getResourceIcon(type) {
        const icons = {
            'book': 'fas fa-book',
            'manuscript': 'fas fa-scroll',
            'audio': 'fas fa-music',
            'video': 'fas fa-video',
            'images': 'fas fa-images',
            'journals': 'fas fa-newspaper'
        };
        return icons[type] || 'fas fa-file';
    }

    function setupEventListeners() {
        // Filter buttons
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                currentPage = 1;
                renderResources();
            });
        });

        // Sort select
        sortSelect.addEventListener('change', function() {
            currentSort = this.value;
            renderResources();
        });

        // Search functionality
        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') performSearch();
        });

        // Upload buttons
        uploadBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                openUploadModal(this.id.replace('upload', '').toLowerCase());
            });
        });

        // Modal close
        closeModal.addEventListener('click', closeUploadModal);
        window.addEventListener('click', function(e) {
            if (e.target === uploadModal) closeUploadModal();
        });

        // Upload form
        uploadForm.addEventListener('submit', handleUpload);

        // Pagination
        document.getElementById('prevPage').addEventListener('click', goToPrevPage);
        document.getElementById('nextPage').addEventListener('click', goToNextPage);

        // Resource actions
        resourceGrid.addEventListener('click', function(e) {
            const btn = e.target.closest('button');
            if (!btn) return;

            const resourceId = btn.dataset.id;
            if (btn.classList.contains('btn-preview')) {
                previewResource(resourceId);
            } else if (btn.classList.contains('btn-download')) {
                downloadResource(resourceId);
            }
        });
    }

    function performSearch() {
        const query = searchInput.value.toLowerCase().trim();
        if (query) {
            filteredResources = resources.filter(resource => 
                resource.title.toLowerCase().includes(query) ||
                resource.author.toLowerCase().includes(query) ||
                resource.description.toLowerCase().includes(query)
            );
        } else {
            filteredResources = [...resources];
        }
        currentPage = 1;
        renderResources();
    }

    function openUploadModal(type) {
        if (type) {
            document.getElementById('resourceType').value = type;
        }
        uploadModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeUploadModal() {
        uploadModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        uploadForm.reset();
    }

    function handleUpload(e) {
        e.preventDefault();
        
        const formData = {
            title: document.getElementById('resourceTitle').value,
            type: document.getElementById('resourceType').value,
            description: document.getElementById('resourceDescription').value,
            tags: document.getElementById('resourceTags').value.split(',').map(tag => tag.trim()),
            file: document.getElementById('resourceFile').files[0]
        };

        // Validate file
        if (!formData.file) {
            alert('Please select a file to upload');
            return;
        }

        // Validate file size (5MB limit)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (formData.file.size > maxSize) {
            alert('File size must be less than 5MB');
            return;
        }

        // Validate file type
        const allowedTypes = ['application/pdf', 'application/msword', 
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg', 'image/png', 'audio/mpeg', 'video/mp4'];
        if (!allowedTypes.includes(formData.file.type)) {
            alert('Invalid file type. Please upload PDF, DOC, JPG, PNG, MP3, or MP4 files.');
            return;
        }

        // Show loading state
        const submitBtn = uploadForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        submitBtn.disabled = true;

        // Simulate upload delay
        setTimeout(() => {
            // In a real application, you would send the data to a server here
            console.log('Uploading resource:', formData);
            
            // Show success message
            alert('Resource uploaded successfully! It will be reviewed before publication.');
            
            // Reset form and close modal
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            closeUploadModal();
            
            // Refresh resource list (in a real app, you would fetch from server)
            filteredResources.unshift({
                id: resources.length + 1,
                title: formData.title,
                type: formData.type,
                author: 'You',
                year: new Date().getFullYear().toString(),
                views: 0,
                downloads: 0,
                description: formData.description
            });
            
            renderResources();
        }, 2000);
    }

    function previewResource(id) {
        const resource = resources.find(r => r.id === parseInt(id));
        if (resource) {
            alert(`Previewing: ${resource.title}\n\nThis would open a preview window in a real application.`);
            // In a real application, open a preview modal or new page
        }
    }

    function downloadResource(id) {
        const resource = resources.find(r => r.id === parseInt(id));
        if (resource) {
            // Simulate download
            resource.downloads++;
            renderResources();
            
            // Show download started message
            alert(`Download started: ${resource.title}\n\nIn a real application, the file would download automatically.`);
        }
    }

    function goToPrevPage() {
        if (currentPage > 1) {
            currentPage--;
            renderResources();
            window.scrollTo({ top: resourceGrid.offsetTop - 100, behavior: 'smooth' });
        }
    }

    function goToNextPage() {
        const totalPages = Math.ceil(filteredResources.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderResources();
            window.scrollTo({ top: resourceGrid.offsetTop - 100, behavior: 'smooth' });
        }
    }

    function updatePaginationButtons(totalPages) {
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        
        prevBtn.style.opacity = prevBtn.disabled ? '0.5' : '1';
        nextBtn.style.opacity = nextBtn.disabled ? '0.5' : '1';
    }
});