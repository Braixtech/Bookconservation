// Charts and Data Visualization for Arewa Conservation Platform

class ChartManager {
    constructor() {
        this.colors = {
            primary: '#4CAF50',
            secondary: '#2196F3',
            accent: '#FF9800',
            success: '#4CAF50',
            warning: '#FF9800',
            danger: '#f44336',
            info: '#2196F3'
        };
        
        this.charts = new Map();
    }
    
    // Initialize all charts on page
    initAllCharts() {
        this.initCollectionStatsChart();
        this.initGeographicDistributionChart();
        this.initTimelineChart();
        this.initVisitorStatsChart();
        this.initProjectFundingChart();
        this.initResearchPublicationChart();
        this.initVolunteerActivityChart();
    }
    
    // Collection Statistics Chart
    initCollectionStatsChart() {
        const canvas = document.getElementById('collectionStatsChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Artifacts', 'Documents', 'Artworks', 'Textiles', 'Photographs', 'Audio/Video'],
                datasets: [{
                    label: 'Number of Items',
                    data: [5230, 2850, 1240, 850, 2100, 450],
                    backgroundColor: [
                        this.colors.primary,
                        this.colors.secondary,
                        this.colors.accent,
                        '#9C27B0',
                        '#009688',
                        '#795548'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
        
        this.charts.set('collectionStats', chart);
    }
    
    // Geographic Distribution Chart (Map Visualization)
    initGeographicDistributionChart() {
        const canvas = document.getElementById('geographicChart');
        if (!canvas) return;
        
        const regions = [
            { name: 'Kano', value: 1250, color: this.colors.primary },
            { name: 'Sokoto', value: 1050, color: this.colors.secondary },
            { name: 'Kaduna', value: 890, color: this.colors.accent },
            { name: 'Borno', value: 750, color: '#9C27B0' },
            { name: 'Katsina', value: 680, color: '#009688' },
            { name: 'Zamfara', value: 520, color: '#795548' },
            { name: 'Other', value: 860, color: '#607D8B' }
        ];
        
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: regions.map(r => r.name),
                datasets: [{
                    data: regions.map(r => r.value),
                    backgroundColor: regions.map(r => r.color),
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} items (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        this.charts.set('geographicDistribution', chart);
    }
    
    // Timeline Chart
    initTimelineChart() {
        const canvas = document.getElementById('timelineChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['500 BCE', '900 CE', '1300', '1500', '1800', '1900', '1960', '2000', '2023'],
                datasets: [{
                    label: 'Number of Artifacts',
                    data: [45, 120, 280, 450, 850, 1470, 2450, 3800, 5230],
                    borderColor: this.colors.primary,
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: this.colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Items',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time Period',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        this.charts.set('timeline', chart);
    }
    
    // Visitor Statistics Chart
    initVisitorStatsChart() {
        const canvas = document.getElementById('visitorStatsChart');
        if (!canvas) return;
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Website Visitors',
                        data: [1250, 1380, 1560, 1420, 1680, 1950, 2200, 2380, 2150, 1980, 1870, 2100],
                        borderColor: this.colors.primary,
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Digital Library Access',
                        data: [850, 920, 1050, 980, 1120, 1350, 1580, 1680, 1520, 1420, 1280, 1450],
                        borderColor: this.colors.secondary,
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Users',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        this.charts.set('visitorStats', chart);
    }
    
    // Project Funding Chart
    initProjectFundingChart() {
        const canvas = document.getElementById('fundingChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['International Grants', 'Corporate Donations', 'Government Funding', 'Individual Donations', 'Partnerships'],
                datasets: [{
                    data: [45, 25, 15, 10, 5],
                    backgroundColor: [
                        this.colors.primary,
                        this.colors.secondary,
                        this.colors.accent,
                        '#9C27B0',
                        '#009688'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
        
        this.charts.set('funding', chart);
    }
    
    // Research Publication Chart
    initResearchPublicationChart() {
        const canvas = document.getElementById('researchChart');
        if (!canvas) return;
        
        const years = ['2018', '2019', '2020', '2021', '2022', '2023'];
        
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: years,
                datasets: [
                    {
                        label: 'Journal Articles',
                        data: [12, 18, 24, 32, 45, 58],
                        backgroundColor: this.colors.primary,
                        borderRadius: 6
                    },
                    {
                        label: 'Conference Papers',
                        data: [8, 12, 15, 20, 28, 35],
                        backgroundColor: this.colors.secondary,
                        borderRadius: 6
                    },
                    {
                        label: 'Research Reports',
                        data: [5, 8, 10, 15, 22, 30],
                        backgroundColor: this.colors.accent,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Publications',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });
        
        this.charts.set('researchPublications', chart);
    }
    
    // Volunteer Activity Chart
    initVolunteerActivityChart() {
        const canvas = document.getElementById('volunteerChart');
        if (!canvas) return;
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const activities = ['Field Work', 'Digital Archiving', 'Research', 'Education', 'Administration'];
        
        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: activities,
                datasets: [
                    {
                        label: 'Hours Contributed',
                        data: [45, 30, 25, 35, 20],
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
                        borderColor: this.colors.primary,
                        borderWidth: 2,
                        pointBackgroundColor: this.colors.primary,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: {
                            display: false
                        },
                        pointLabels: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
        
        this.charts.set('volunteerActivity', chart);
    }
    
    // Dynamic Data Update Methods
    updateCollectionStats(data) {
        const chart = this.charts.get('collectionStats');
        if (chart && data) {
            chart.data.datasets[0].data = data;
            chart.update();
        }
    }
    
    updateVisitorStats(months, visitors, libraryAccess) {
        const chart = this.charts.get('visitorStats');
        if (chart && months && visitors && libraryAccess) {
            chart.data.labels = months;
            chart.data.datasets[0].data = visitors;
            chart.data.datasets[1].data = libraryAccess;
            chart.update();
        }
    }
    
    // Utility Methods
    createSparklineChart(canvasId, data, color = this.colors.primary) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        
        const ctx = canvas.getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i + 1),
                datasets: [{
                    data: data,
                    borderColor: color,
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 0,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                }
            }
        });
    }
    
    createProgressChart(canvasId, value, max = 100, color = this.colors.primary) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        
        const percentage = (value / max) * 100;
        const ctx = canvas.getContext('2d');
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentage, 100 - percentage],
                    backgroundColor: [color, '#e0e0e0'],
                    borderWidth: 0,
                    circumference: 180,
                    rotation: 270
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
    }
    
    // Export chart as image
    exportChart(chartId, filename = 'chart') {
        const chart = this.charts.get(chartId);
        if (!chart) return;
        
        const link = document.createElement('a');
        link.download = `${filename}.png`;
        link.href = chart.toBase64Image();
        link.click();
    }
    
    // Responsive chart resizing
    handleResize() {
        this.charts.forEach(chart => {
            chart.resize();
        });
    }
    
    // Clean up charts
    destroy() {
        this.charts.forEach(chart => {
            chart.destroy();
        });
        this.charts.clear();
    }
}

// Initialize Chart.js and ChartManager
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded. Loading from CDN...');
        loadChartJS();
    } else {
        initCharts();
    }
    
    function loadChartJS() {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = initCharts;
        document.head.appendChild(script);
    }
    
    function initCharts() {
        window.chartManager = new ChartManager();
        chartManager.initAllCharts();
        
        // Handle window resize
        window.addEventListener('resize', debounce(() => {
            chartManager.handleResize();
        }, 250));
        
        // Example: Auto-update visitor stats every minute
        setInterval(() => {
            // In a real app, this would fetch new data from an API
            // chartManager.updateVisitorStats(newMonths, newVisitors, newLibraryAccess);
        }, 60000);
    }
});

// Dashboard-specific charts
function initDashboardCharts() {
    if (!window.chartManager) return;
    
    // Quick stats mini charts
    const quickStatsData = {
        'visitorsSparkline': [45, 52, 48, 60, 55, 58, 62, 65, 70, 68, 72, 75],
        'downloadsSparkline': [120, 135, 128, 145, 150, 142, 160, 165, 158, 170, 175, 180],
        'contributionsSparkline': [25, 30, 28, 35, 32, 38, 40, 42, 45, 48, 50, 52],
        'projectsSparkline': [8, 10, 9, 12, 11, 13, 14, 15, 16, 17, 18, 19]
    };
    
    Object.keys(quickStatsData).forEach(canvasId => {
        chartManager.createSparklineChart(canvasId, quickStatsData[canvasId]);
    });
    
    // Progress charts
    const progressData = {
        'projectProgressChart': { value: 65, max: 100, color: '#4CAF50' },
        'digitizationProgressChart': { value: 40, max: 100, color: '#2196F3' },
        'fundingProgressChart': { value: 75, max: 100, color: '#FF9800' },
        'conservationProgressChart': { value: 85, max: 100, color: '#9C27B0' }
    };
    
    Object.keys(progressData).forEach(canvasId => {
        const data = progressData[canvasId];
        chartManager.createProgressChart(canvasId, data.value, data.max, data.color);
    });
}

// Collections page specific charts
function initCollectionsCharts() {
    if (!window.chartManager) return;
    
    // Material type distribution
    const materialCanvas = document.getElementById('materialDistributionChart');
    if (materialCanvas) {
        const ctx = materialCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'polarArea',
            data: {
                labels: ['Textile', 'Ceramic', 'Metal', 'Wood', 'Stone', 'Other'],
                datasets: [{
                    data: [35, 25, 20, 10, 5, 5],
                    backgroundColor: [
                        '#4CAF50',
                        '#2196F3',
                        '#FF9800',
                        '#9C27B0',
                        '#009688',
                        '#795548'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }
    
    // Collection growth over time
    const growthCanvas = document.getElementById('collectionGrowthChart');
    if (growthCanvas) {
        const ctx = growthCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                datasets: [
                    {
                        label: 'New Acquisitions',
                        data: [45, 52, 48, 60],
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Digitized Items',
                        data: [30, 35, 32, 40],
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Research portal specific charts
function initResearchCharts() {
    if (!window.chartManager) return;
    
    // Citation impact chart
    const citationCanvas = document.getElementById('citationChart');
    if (citationCanvas) {
        const ctx = citationCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Publications',
                    data: [
                        { x: 2018, y: 12 },
                        { x: 2019, y: 18 },
                        { x: 2020, y: 25 },
                        { x: 2021, y: 35 },
                        { x: 2022, y: 48 },
                        { x: 2023, y: 65 }
                    ],
                    backgroundColor: '#4CAF50',
                    pointRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        type: 'linear',
                        position: 'bottom',
                        title: {
                            display: true,
                            text: 'Year'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Citations'
                        }
                    }
                }
            }
        });
    }
}

// Utility function for debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export for use in other modules
window.ChartManager = ChartManager;