// Collections Page Functionality

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const searchInput = document.getElementById('collectionSearch');
    const searchBtn = document.getElementById('collectionSearchBtn');
    const filterModal = document.getElementById('filterModal');
    const filterBtn = document.getElementById('filterBtn');
    const closeModalBtn = document.querySelector('.close-modal');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const regionFilters = document.querySelectorAll('.map-region');
    const timelineItems = document.querySelectorAll('.timeline-item');

    // Sample collection data
    const collections = [
        {
            id: 1,
            title: "Hausa Traditional Textiles",
            category: "textile",
            region: "kano",
            period: "traditional",
            items: 250,
            views: 1245,
            description: "Traditional textiles including aso oke, kente, and adire fabrics",
            featured: true
        },
        {
            id: 2,
            title: "Traditional Musical Instruments",
            category: "performing-arts",
            region: "kaduna",
            period: "traditional",
            items: 85,
            views: 892,
            description: "Collection of drums, string instruments, and wind instruments",
            featured: false
        },
        {
            id: 3,
            title: "Royal Regalia",
            category: "crafts",
            region: "sokoto",
            period: "precolonial",
            items: 42,
            views: 1567,
            description: "Crowns, staffs, and ceremonial objects from Northern kingdoms",
            featured: false
        },
        {
            id: 4,
            title: "Historical Manuscripts",
            category: "islamic",
            region: "borno",
            period: "colonial",
            items: 320,
            views: 2103,
            description: "Pre-colonial and colonial era documents and writings",
            featured: false
        }
    ];

    // Current filters
    let currentFilters = {
        category: 'all',
        region: 'all',
        period: 'all',
        searchQuery: ''
    };

    // Initialize
    initEventListeners();
    initMapInteractions();
    initTimelineInteractions();
    loadCollectionStats();

    function initEventListeners() {
        // Search functionality
        if (searchBtn && searchInput) {
            searchBtn.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') performSearch();
            });
        }

        // Filter modal
        if (filterBtn) {
            filterBtn.addEventListener('click', openFilterModal);
        }

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeFilterModal);
        }

        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', applyFilters);
        }

        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', clearFilters);
        }

        // Category card clicks
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('.btn-view')) {
                    const category = this.querySelector('h3').textContent.toLowerCase();
                    filterByCategory(category);
                }
            });
        });

        // Collection card interactions
        const collectionCards = document.querySelectorAll('.collection-card');
        collectionCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            });
        });
    }

    function initMapInteractions() {
        regionFilters.forEach(region => {
            region.addEventListener('click', function() {
                const regionName = this.dataset.region;
                filterByRegion(regionName);
            });
            
            region.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.1)';
                this.style.zIndex = '10';
            });
            
            region.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.zIndex = '1';
            });
        });
    }

    function initTimelineInteractions() {
        timelineItems.forEach(item => {
            item.addEventListener('click', function() {
                const year = this.dataset.year;
                filterByPeriod(year);
            });
        });
    }

    function performSearch() {
        if (!searchInput) return;
        
        const query = searchInput.value.trim().toLowerCase();
        currentFilters.searchQuery = query;
        
        // Update URL for sharing
        updateURLParams();
        
        // Filter collections
        filterCollections();
        
        // Show search results
        showSearchResults(query);
    }

    function openFilterModal() {
        if (filterModal) {
            filterModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeFilterModal() {
        if (filterModal) {
            filterModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function applyFilters() {
        // Get filter values from modal
        const periodFilters = Array.from(document.querySelectorAll('input[name="period"]:checked'))
            .map(checkbox => checkbox.value);
        
        const regionFilters = Array.from(document.getElementById('regionFilter').selectedOptions)
            .map(option => option.value);
        
        const materialFilters = Array.from(document.querySelectorAll('input[value="textile"], input[value="ceramic"], input[value="metal"], input[value="wood"]:checked'))
            .map(checkbox => checkbox.value);
        
        // Update current filters
        currentFilters.period = periodFilters.length > 0 ? periodFilters : 'all';
        currentFilters.region = regionFilters.length > 0 ? regionFilters : 'all';
        currentFilters.material = materialFilters.length > 0 ? materialFilters : 'all';
        
        // Apply filters
        filterCollections();
        
        // Close modal
        closeFilterModal();
        
        // Show active filters
        showActiveFilters();
    }

    function clearFilters() {
        // Clear all filter inputs
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        document.getElementById('regionFilter').selectedIndex = -1;
        
        // Reset current filters
        currentFilters = {
            category: 'all',
            region: 'all',
            period: 'all',
            material: 'all',
            searchQuery: ''
        };
        
        // Reset URL
        resetURLParams();
        
        // Show all collections
        filterCollections();
        
        // Hide active filters
        hideActiveFilters();
    }

    function filterByCategory(category) {
        currentFilters.category = category;
        updateURLParams();
        filterCollections();
    }

    function filterByRegion(region) {
        currentFilters.region = region;
        updateURLParams();
        filterCollections();
    }

    function filterByPeriod(period) {
        currentFilters.period = period;
        updateURLParams();
        filterCollections();
    }

    function filterCollections() {
        // This function would filter and display collections based on currentFilters
        // In a real app, this would make an API call or filter client-side data
        
        const filtered = collections.filter(collection => {
            // Filter by search query
            if (currentFilters.searchQuery) {
                const searchFields = [
                    collection.title,
                    collection.description,
                    collection.category,
                    collection.region
                ].join(' ').toLowerCase();
                
                if (!searchFields.includes(currentFilters.searchQuery.toLowerCase())) {
                    return false;
                }
            }
            
            // Filter by category
            if (currentFilters.category !== 'all' && currentFilters.category !== collection.category) {
                return false;
            }
            
            // Filter by region
            if (currentFilters.region !== 'all' && currentFilters.region !== collection.region) {
                return false;
            }
            
            // Filter by period
            if (currentFilters.period !== 'all' && currentFilters.period !== collection.period) {
                return false;
            }
            
            return true;
        });
        
        displayFilteredCollections(filtered);
    }

    function displayFilteredCollections(filteredCollections) {
        // Update collection count
        const countElement = document.getElementById('filteredCount');
        if (countElement) {
            countElement.textContent = `${filteredCollections.length} collections found`;
        }
        
        // Update collection grid (simplified example)
        console.log('Displaying filtered collections:', filteredCollections);
        
        // In a real implementation, you would update the DOM with filtered results
    }

    function showSearchResults(query) {
        const resultsContainer = document.getElementById('searchResults');
        if (!resultsContainer) return;
        
        if (query) {
            resultsContainer.innerHTML = `
                <div class="search-results-header">
                    <h3>Search Results for "${query}"</h3>
                    <button class="btn-clear-search">Clear Search</button>
                </div>
                <div class="search-results-list">
                    <!-- Results would be populated here -->
                </div>
            `;
            
            // Add clear search functionality
            const clearBtn = resultsContainer.querySelector('.btn-clear-search');
            clearBtn.addEventListener('click', clearSearch);
            
            resultsContainer.style.display = 'block';
        } else {
            resultsContainer.style.display = 'none';
        }
    }

    function clearSearch() {
        if (searchInput) {
            searchInput.value = '';
        }
        currentFilters.searchQuery = '';
        filterCollections();
        
        const resultsContainer = document.getElementById('searchResults');
        if (resultsContainer) {
            resultsContainer.style.display = 'none';
        }
    }

    function showActiveFilters() {
        const activeFiltersContainer = document.getElementById('activeFilters');
        if (!activeFiltersContainer) return;
        
        const filters = [];
        
        if (currentFilters.category !== 'all') {
            filters.push(`Category: ${currentFilters.category}`);
        }
        
        if (currentFilters.region !== 'all') {
            filters.push(`Region: ${currentFilters.region}`);
        }
        
        if (currentFilters.period !== 'all') {
            filters.push(`Period: ${currentFilters.period}`);
        }
        
        if (currentFilters.searchQuery) {
            filters.push(`Search: "${currentFilters.searchQuery}"`);
        }
        
        if (filters.length > 0) {
            activeFiltersContainer.innerHTML = `
                <div class="active-filters-header">
                    <h4>Active Filters</h4>
                    <button class="btn-clear-all" id="clearAllFilters">Clear All</button>
                </div>
                <div class="active-filters-list">
                    ${filters.map(filter => `
                        <span class="active-filter-tag">
                            ${filter}
                            <button class="remove-filter" data-filter="${filter}">&times;</button>
                        </span>
                    `).join('')}
                </div>
            `;
            
            activeFiltersContainer.style.display = 'block';
            
            // Add event listeners
            document.getElementById('clearAllFilters').addEventListener('click', clearFilters);
            
            document.querySelectorAll('.remove-filter').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filterText = this.dataset.filter;
                    removeFilter(filterText);
                });
            });
        } else {
            activeFiltersContainer.style.display = 'none';
        }
    }

    function hideActiveFilters() {
        const activeFiltersContainer = document.getElementById('activeFilters');
        if (activeFiltersContainer) {
            activeFiltersContainer.style.display = 'none';
        }
    }

    function removeFilter(filterText) {
        // Parse filter text and remove from currentFilters
        if (filterText.startsWith('Category:')) {
            currentFilters.category = 'all';
        } else if (filterText.startsWith('Region:')) {
            currentFilters.region = 'all';
        } else if (filterText.startsWith('Period:')) {
            currentFilters.period = 'all';
        } else if (filterText.startsWith('Search:')) {
            currentFilters.searchQuery = '';
            if (searchInput) searchInput.value = '';
        }
        
        // Re-apply filters
        filterCollections();
        showActiveFilters();
    }

    function updateURLParams() {
        const params = new URLSearchParams();
        
        if (currentFilters.category !== 'all') {
            params.set('category', currentFilters.category);
        }
        
        if (currentFilters.region !== 'all') {
            params.set('region', currentFilters.region);
        }
        
        if (currentFilters.period !== 'all') {
            params.set('period', currentFilters.period);
        }
        
        if (currentFilters.searchQuery) {
            params.set('search', currentFilters.searchQuery);
        }
        
        const newURL = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({}, '', newURL);
    }

    function resetURLParams() {
        window.history.pushState({}, '', window.location.pathname);
    }

    function loadCollectionStats() {
        // This would load collection statistics from an API
        // For now, we'll use sample data
        
        const stats = {
            totalItems: 5230,
            digitizedItems: 2850,
            featuredCollections: 12,
            activeContributors: 45,
            monthlyVisitors: 12450,
            itemsAddedThisMonth: 85
        };
        
        // Update stats display if elements exist
        Object.keys(stats).forEach(stat => {
            const element = document.getElementById(`${stat}Stat`);
            if (element) {
                element.textContent = stats[stat].toLocaleString();
                
                // Add animation
                animateCount(element, 0, stats[stat], 2000);
            }
        });
    }

    function animateCount(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Initialize from URL parameters
    function initFromURLParams() {
        const params = new URLSearchParams(window.location.search);
        
        if (params.has('category')) {
            currentFilters.category = params.get('category');
        }
        
        if (params.has('region')) {
            currentFilters.region = params.get('region');
        }
        
        if (params.has('period')) {
            currentFilters.period = params.get('period');
        }
        
        if (params.has('search')) {
            currentFilters.searchQuery = params.get('search');
            if (searchInput) {
                searchInput.value = currentFilters.searchQuery;
            }
        }
        
        // Apply filters from URL
        if (Object.values(currentFilters).some(filter => filter !== 'all')) {
            filterCollections();
            showActiveFilters();
        }
    }

    // Initialize from URL on page load
    initFromURLParams();

    // Export data functionality
    const exportBtn = document.getElementById('exportDataBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportCollectionData);
    }

    function exportCollectionData() {
        const data = {
            filters: currentFilters,
            collections: collections,
            timestamp: new Date().toISOString(),
            totalItems: 5230
        };
        
        const dataStr = JSON.stringify(data, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(dataBlob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `arewa-collections-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        ArewaLib.NotificationManager.show('Collection data exported successfully!', 'success');
    }

    // Share functionality
    const shareBtn = document.getElementById('shareCollectionsBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', shareCollections);
    }

    function shareCollections() {
        if (navigator.share) {
            navigator.share({
                title: 'Arewa Conservation Collections',
                text: 'Explore Northern Nigeria\'s cultural heritage collections',
                url: window.location.href
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(window.location.href)
                .then(() => {
                    ArewaLib.NotificationManager.show('Link copied to clipboard!', 'success');
                })
                .catch(err => {
                    console.error('Failed to copy:', err);
                    ArewaLib.NotificationManager.show('Failed to copy link', 'error');
                });
        }
    }

    // Print functionality
    const printBtn = document.getElementById('printCollectionsBtn');
    if (printBtn) {
        printBtn.addEventListener('click', printCollections);
    }

    function printCollections() {
        window.print();
    }
});