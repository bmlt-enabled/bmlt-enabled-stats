/**
 * BMLT Enabled Stats - Frontend JavaScript
 * Handles Chart.js visualizations and animated counters
 */

(function() {
    'use strict';

    // Chart.js default configuration
    var chartColors = [
        '#0073aa', '#00a32a', '#dba617', '#72aee6',
        '#c2185b', '#7b1fa2', '#00838f', '#ef6c00',
        '#3949ab', '#2e7d32'
    ];

    /**
     * Initialize when DOM is ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initAnimatedCounters();
        initCharts();
    });

    /**
     * Animated number counters
     */
    function initAnimatedCounters() {
        var counters = document.querySelectorAll('.blst-stat-number[data-count]');

        if (!counters.length) return;

        // Use Intersection Observer to trigger animation when visible
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(function(counter) {
                observer.observe(counter);
            });
        } else {
            // Fallback for older browsers
            counters.forEach(function(counter) {
                animateCounter(counter);
            });
        }
    }

    /**
     * Animate a single counter
     */
    function animateCounter(element) {
        var target = parseInt(element.getAttribute('data-count'), 10);
        var suffix = element.getAttribute('data-suffix') || '';
        var duration = 2000; // 2 seconds
        var start = 0;
        var startTime = null;

        // Don't animate very small numbers
        if (target < 10) {
            element.textContent = formatNumber(target) + suffix;
            return;
        }

        element.classList.add('counting');

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);

            // Easing function (ease-out)
            var easeOut = 1 - Math.pow(1 - progress, 3);
            var current = Math.floor(easeOut * target);

            element.textContent = formatNumber(current) + suffix;

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                element.textContent = formatNumber(target) + suffix;
                element.classList.remove('counting');
            }
        }

        window.requestAnimationFrame(step);
    }

    /**
     * Format large numbers
     */
    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toLocaleString();
    }

    /**
     * Initialize Chart.js charts
     */
    function initCharts() {
        // Get chart data from page
        var dataElement = document.getElementById('blst-chart-data');
        if (!dataElement) return;

        var data;
        try {
            data = JSON.parse(dataElement.textContent);
        } catch (e) {
            console.error('BLST: Failed to parse chart data', e);
            return;
        }

        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.warn('BLST: Chart.js not loaded');
            return;
        }

        // Set default font
        Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

        // GitHub charts
        if (data.github && data.github.repos && data.github.repos.length) {
            createGitHubChart(data.github);
            if (data.github.forks && data.github.forks.length) {
                createGitHubForksChart(data.github);
            }
            if (data.github.downloads && data.github.downloads.length) {
                createGitHubDownloadsChart(data.github);
            }
        }

        // WordPress charts
        if (data.wordpress && data.wordpress.plugins && data.wordpress.plugins.length) {
            createWordPressInstallsChart(data.wordpress);
            createWordPressDownloadsBarChart(data.wordpress);
        }
    }

    /**
     * Create GitHub stars horizontal bar chart
     */
    function createGitHubChart(data) {
        var canvas = document.getElementById('blst-github-stars-chart');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.repos,
                datasets: [{
                    label: 'Stars',
                    data: data.stars,
                    backgroundColor: chartColors.slice(0, data.repos.length),
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x.toLocaleString() + ' stars';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    /**
     * Create GitHub forks horizontal bar chart
     */
    function createGitHubForksChart(data) {
        var canvas = document.getElementById('blst-github-forks-chart');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        var labels = data.forksRepos || data.repos;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Forks',
                    data: data.forks,
                    backgroundColor: chartColors.slice(0, labels.length),
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x.toLocaleString() + ' forks';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    /**
     * Create GitHub release downloads horizontal bar chart
     */
    function createGitHubDownloadsChart(data) {
        var canvas = document.getElementById('blst-github-downloads-chart');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        var labels = data.downloadsRepos || [];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Downloads',
                    data: data.downloads,
                    backgroundColor: chartColors.slice(0, labels.length),
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x.toLocaleString() + ' downloads';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    /**
     * Create WordPress active installs bar chart
     */
    function createWordPressInstallsChart(data) {
        var canvas = document.getElementById('blst-wp-installs-chart');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.plugins,
                datasets: [{
                    label: 'Active Installs',
                    data: data.installs,
                    backgroundColor: 'rgba(0, 115, 170, 0.8)',
                    borderRadius: 4,
                    borderSkipped: false
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
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toLocaleString() + '+ active installs';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) {
                                    return (value / 1000) + 'K';
                                }
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    /**
     * Create WordPress downloads horizontal bar chart
     */
    function createWordPressDownloadsBarChart(data) {
        var canvas = document.getElementById('blst-wp-downloads-bar-chart');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');

        // Sort by downloads descending
        var combined = data.plugins.map(function(plugin, i) {
            return { name: plugin, downloads: data.downloads[i] };
        });
        combined.sort(function(a, b) { return b.downloads - a.downloads; });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: combined.map(function(item) { return item.name; }),
                datasets: [{
                    label: 'Downloads',
                    data: combined.map(function(item) { return item.downloads; }),
                    backgroundColor: chartColors.slice(0, combined.length),
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x.toLocaleString() + ' downloads';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                }
                                if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + 'K';
                                }
                                return value;
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

})();
