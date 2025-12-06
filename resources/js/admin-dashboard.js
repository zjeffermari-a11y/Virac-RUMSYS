import Chart from "chart.js/auto";

class AdminDashboard {
    constructor(initialState = null) {
        this.elements = {};
        this.charts = {};
        this.initialStateLoaded = !!initialState;
        this.state = this.createState();

        // New property to hold all pre-fetched data
        this.allDataCache = null;

        // Set the refresh interval in milliseconds (e.g., 5 minutes = 300000)
        this.REFRESH_INTERVAL = 300000;

        this.filterData = initialState?.filterData || {
            years: [],
            sections: [],
        };

        if (this.initialStateLoaded) {
            this.state.dashboardData = initialState;
        }

        // State for infinite scroll on the "Needs Support" list
        this.supportListPage = this.initialStateLoaded ? 2 : 1;
        this.supportListHasMore = true;
        this.isFetchingSupportList = false;
    }

    /**
     * Creates a reactive state object.
     * When a property is changed, the render method is automatically called.
     */
    createState() {
        return new Proxy(
            {
                dashboardData: {
                    kpis: {},
                    vendorDistribution: [],
                    collectionTrends: [],
                    utilityConsumption: [],
                    vendorPulse: {
                        topPerformers: [],
                        needsSupport: [],
                    },
                },
            },
            {
                set: (target, property, value) => {
                    target[property] = value;
                    return true;
                },
            }
        );
    }

    /**
     * Initializes the dashboard, lazy loading, and polling.
     */
    async init() {
        this.cacheDOMElements();
        this.setupEventListeners();
        await this.loadDashboardData(); // This loads the initial view fast

        // After the initial view is ready, fetch all other data in the background.
        // Use setTimeout to ensure it runs after the main UI thread is free.
        setTimeout(() => this.fetchAllDataInBackground(), 1000);

        // Set up the timer to automatically refresh data periodically.
        setInterval(
            () => this.fetchAllDataInBackground(true),
            this.REFRESH_INTERVAL
        );
    }

    /**
     * Fetches all dashboard data in the background and stores it.
     * @param {boolean} isRefresh - If true, shows a toast notification on success.
     */
    async fetchAllDataInBackground(isRefresh = false) {
        try {
            // This new endpoint should return the comprehensive data structure
            const response = await fetch("/api/dashboard/all-data");
            if (!response.ok) throw new Error("Network response was not ok");

            this.allDataCache = await response.json();

            // When data is refreshed, also update the main view with the latest KPIs.
            this.state.dashboardData.kpis = this.allDataCache.kpis;
            this.renderKpis(); // Re-render just the KPIs.

            if (isRefresh) {
                this.showToast("Dashboard data has been refreshed.", "info");
            }
            console.log("Background data cache refreshed successfully.");
        } catch (error) {
            console.error("Failed to fetch background data:", error);
            if (isRefresh) {
                this.showToast("Could not refresh dashboard data.", "error");
            }
        }
    }

    /**
     * Caches all necessary DOM elements for the dashboard.
     */
    cacheDOMElements() {
        this.elements = {
            kpiContainer: document.getElementById("kpiContainer"),
            vendorDistributionChart: document.getElementById(
                "vendorDistributionChart"
            ),
            collectionTrendsChart: document.getElementById(
                "collectionTrendsChart"
            ),
            utilityConsumptionChart: document.getElementById(
                "utilityConsumptionChart"
            ),
            dashboardYearFilter: document.getElementById("dashboardYearFilter"),
            collectionTypeFilter: document.getElementById(
                "collectionTypeFilter"
            ),
            topPerformersContainer: document.getElementById(
                "topPerformersContainer"
            ),
            vendorsNeedingSupportContainer: document.getElementById(
                "vendorsNeedingSupportContainer"
            ),
            topVendorsSectionFilter: document.getElementById(
                "topVendorsSectionFilter"
            ),
            needsSupportSectionFilter: document.getElementById(
                "needsSupportSectionFilter"
            ),
            toastContainer: document.getElementById("toastContainer"),
        };
    }

    /**
     * Sets up event listeners for dashboard filters.
     */
    setupEventListeners() {
        // The infinite scroll logic has been simplified to work with the cache.
        // For a more robust implementation, you might paginate the cached data.

        this.elements.dashboardYearFilter?.addEventListener("change", () =>
            this.updateAllDashboardData()
        );
        this.elements.collectionTypeFilter?.addEventListener("change", () =>
            this.renderCollectionTrendsChart()
        );
        this.elements.topVendorsSectionFilter?.addEventListener("change", (e) =>
            this.updateVendorPulseData("topPerformers", e.target.value)
        );
        this.elements.needsSupportSectionFilter?.addEventListener(
            "change",
            (e) => this.updateVendorPulseData("needsSupport", e.target.value)
        );
    }

    /**
     * Loads the initial data for all dashboard components from the pre-loaded state.
     */
    async loadDashboardData() {
        this.populateDashboardYearFilter();
        this.populateVendorPulseFilters();

        if (this.initialStateLoaded) {
            this.render();
        } else {
            // Fallback just in case server-side pre-loading fails.
            this.elements.kpiContainer.innerHTML =
                "<p>Loading dashboard...</p>";
        }
    }

    /**
     * Renders all visual components based on the current state.
     */
    render() {
        this.renderKpis();
        this.renderVendorDistributionChart();
        this.renderCollectionTrendsChart();
        this.renderUtilityConsumptionChart();
        this.renderVendorPulse();
    }

    /**
     * Populates the year filter dropdown from the initial data.
     */
    populateDashboardYearFilter() {
        try {
            const years = this.filterData.years || [];
            if (this.elements.dashboardYearFilter) {
                if (years.length > 0) {
                    this.elements.dashboardYearFilter.innerHTML = years
                        .map(
                            (year) => `<option value="${year}">${year}</option>`
                        )
                        .join("");
                } else {
                    this.elements.dashboardYearFilter.innerHTML = `<option value="${new Date().getFullYear()}">${new Date().getFullYear()}</option>`;
                }
            }
        } catch (error) {
            console.error("Could not populate year filter:", error);
        }
    }

    /**
     * Populates the section filters from the initial data.
     */
    populateVendorPulseFilters() {
        try {
            const sections = this.filterData.sections || [];
            const filters = [
                this.elements.topVendorsSectionFilter,
                this.elements.needsSupportSectionFilter,
            ];
            filters.forEach((filter) => {
                if (filter) {
                    while (filter.options.length > 1) {
                        filter.remove(1);
                    }
                    sections.forEach((section) => {
                        filter.add(new Option(section.name, section.name));
                    });
                }
            });
        } catch (error) {
            console.error("Could not populate vendor pulse filters:", error);
        }
    }

    /**
     * ✅ MODIFIED: Updates charts instantly from the cache when the year changes.
     */
    updateAllDashboardData() {
        if (!this.allDataCache) {
            this.showToast(
                "Data is still loading, please wait a moment.",
                "info"
            );
            return;
        }

        const year = this.elements.dashboardYearFilter.value;

        // Update state from the cache instead of fetching
        this.state.dashboardData.collectionTrends =
            this.allDataCache.collectionTrends[year] || [];
        this.state.dashboardData.utilityConsumption =
            this.allDataCache.utilityConsumption[year] || [];
        this.state.dashboardData.vendorPulse.topPerformers =
            this.allDataCache.vendorPulse.topPerformers[year]["All"] || [];
        this.state.dashboardData.vendorPulse.needsSupport =
            this.allDataCache.vendorPulse.needsSupport[year]["All"]?.data || [];

        // Reset section filters to their default "All" state
        this.elements.topVendorsSectionFilter.value = "All";
        this.elements.needsSupportSectionFilter.value = "All";

        // Re-render all components
        this.render();
    }

    /**
     * ✅ MODIFIED: Updates vendor lists instantly from the cache.
     */
    updateVendorPulseData(type, section) {
        if (!this.allDataCache) {
            this.showToast(
                "Data is still loading, please wait a moment.",
                "info"
            );
            return;
        }

        const year = this.elements.dashboardYearFilter.value;

        if (type === "topPerformers") {
            const data =
                this.allDataCache.vendorPulse.topPerformers[year]?.[section] ||
                [];
            this.state.dashboardData.vendorPulse.topPerformers = data;
        } else {
            // Handles "needsSupport"
            const paginatedData =
                this.allDataCache.vendorPulse.needsSupport[year]?.[section];
            this.state.dashboardData.vendorPulse.needsSupport =
                paginatedData?.data || [];
        }

        this.renderVendorPulse();
    }

    // ===================================================================
    // UNCHANGED RENDER METHODS
    // The functions below are the same as your original file.
    // ===================================================================

    /**
     * Renders the key performance indicator cards.
     */
    renderKpis() {
        if (!this.elements.kpiContainer) return;
        const kpis = this.state.dashboardData.kpis;
        if (!kpis || Object.keys(kpis).length === 0) {
            this.elements.kpiContainer.innerHTML = "<p>Loading KPIs...</p>";
            return;
        }

        const kpiData = [
            {
                id: "totalVendors",
                icon: "fa-users",
                label: "Total Vendors",
                value: kpis.totalVendors,
                format: "number",
            },
            {
                id: "totalCollected",
                icon: "fa-hand-holding-dollar",
                label: "Collected This Month",
                value: kpis.totalCollected,
                format: "currency",
            },
            {
                id: "totalOverdue",
                icon: "fa-file-invoice-dollar",
                label: "Total Overdue",
                value: kpis.totalOverdue,
                format: "currency",
            },
            {
                id: "newSignups",
                icon: "fa-user-plus",
                label: "New Vendors This Month",
                value: kpis.newSignups,
                format: "number",
            },
        ];

        this.elements.kpiContainer.innerHTML = kpiData
            .map((kpi) => {
                const value =
                    kpi.value !== undefined
                        ? kpi.format === "currency"
                            ? `₱${parseFloat(kpi.value).toLocaleString(
                                  "en-US",
                                  {
                                      minimumFractionDigits: 2,
                                      maximumFractionDigits: 2,
                                  }
                              )}`
                            : parseInt(kpi.value).toLocaleString()
                        : "...";
                return `
                <div class="bg-white p-5 rounded-2xl shadow-soft flex items-center gap-5">
                    <div class="bg-indigo-100 text-indigo-600 rounded-full h-12 w-12 flex items-center justify-center">
                        <i class="fas ${kpi.icon} text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">${kpi.label}</p>
                        <p class="text-2xl font-bold text-gray-800">${value}</p>
                    </div>
                </div>`;
            })
            .join("");
    }

    /**
     * Renders the vendor distribution doughnut chart.
     */
    renderVendorDistributionChart() {
        const ctx = this.elements.vendorDistributionChart?.getContext("2d");
        if (!ctx) return;
        if (this.charts.vendorDistribution)
            this.charts.vendorDistribution.destroy();

        const data = this.state.dashboardData.vendorDistribution;
        const labels = Array.isArray(data) ? data.map((d) => d.name) : [];
        const values = Array.isArray(data)
            ? data.map((d) => d.vendor_count)
            : [];

        // Define colors for each section for easy distinction
        const sectionColors = {
            'Wet Section': '#7c3aed',      // Purple
            'Dry Section': '#06b6d4',      // Cyan/Teal (changed from purple for better distinction)
            'Semi-Wet': '#f59e0b',         // Orange
            'Semi-Wet Section': '#f59e0b', // Orange (alternative name)
        };

        // Map colors based on section names
        const backgroundColor = labels.map((label) => {
            return sectionColors[label] || '#6b7280'; // Default gray for unknown sections
        });

        this.charts.vendorDistribution = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Vendor Count",
                        data: values,
                        backgroundColor: backgroundColor,
                        hoverOffset: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "bottom" },
                    tooltip: {
                        callbacks: {
                            label: (c) => `${c.label}: ${c.raw} vendors`,
                        },
                    },
                },
            },
        });
    }

    /**
     * Renders the monthly collection trends bar chart.
     */
    renderCollectionTrendsChart() {
        const ctx = this.elements.collectionTrendsChart?.getContext("2d");
        if (!ctx) return;
        if (this.charts.collectionTrends)
            this.charts.collectionTrends.destroy();

        const allData = this.state.dashboardData.collectionTrends || [];
        const filterType = this.elements.collectionTypeFilter.value;
        const utilityColors = {
            Rent: "rgba(79, 70, 229, <alpha>)",
            Electricity: "rgba(245, 158, 11, <alpha>)",
            Water: "rgba(59, 130, 246, <alpha>)",
        };
        const labels = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ];
        let datasets = [];

        datasets = [
            {
                label: "Paid",
                data: allData.map((m) =>
                    m.paid && m.paid[filterType] ? m.paid[filterType] : 0
                ),
                backgroundColor: utilityColors[filterType].replace(
                    "<alpha>",
                    "1"
                ),
                stack: "stack1",
            },
            {
                label: "Unpaid",
                data: allData.map((m) =>
                    m.unpaid && m.unpaid[filterType] ? m.unpaid[filterType] : 0
                ),
                backgroundColor: utilityColors[filterType].replace(
                    "<alpha>",
                    "0.5"
                ),
                stack: "stack1",
            },
        ];

        this.charts.collectionTrends = new Chart(ctx, {
            type: "bar",
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { callback: (v) => `₱${v / 1000}k` },
                    },
                },
                plugins: {
                    legend: { position: "top" },
                    tooltip: {
                        callbacks: {
                            label: (c) =>
                                `${c.dataset.label}: ₱${parseFloat(
                                    c.raw
                                ).toLocaleString()}`,
                        },
                    },
                },
            },
        });
    }

    /**
     * Renders the utility consumption line chart.
     */
    renderUtilityConsumptionChart() {
        const ctx = this.elements.utilityConsumptionChart?.getContext("2d");
        if (!ctx) return;
        if (this.charts.utilityConsumption)
            this.charts.utilityConsumption.destroy();

        const data = this.state.dashboardData.utilityConsumption;
        const electricityData = Array(12).fill(0);
        if (Array.isArray(data)) {
            data.forEach((item) => {
                if (item.utility_type === "Electricity") {
                    electricityData[item.month - 1] = parseFloat(
                        item.total_consumption
                    );
                }
            });
        }
        const labels = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ];

        this.charts.utilityConsumption = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Electricity (kWh)",
                        data: electricityData,
                        borderColor: "#f59e0b",
                        backgroundColor: "rgba(245, 158, 11, 0.1)",
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => `${v}` },
                    },
                },
                plugins: {
                    legend: { position: "top" },
                    tooltip: {
                        callbacks: {
                            label: (c) =>
                                `${c.dataset.label}: ${parseFloat(
                                    c.raw
                                ).toLocaleString()}`,
                        },
                    },
                },
            },
        });
    }

    /**
     * Renders the Top Performers and Needs Support lists.
     */
    renderVendorPulse() {
        const { topPerformersContainer, vendorsNeedingSupportContainer } =
            this.elements;
        const { topPerformers, needsSupport } =
            this.state.dashboardData.vendorPulse;

        const renderList = (container, vendors, isSupportList = false) => {
            if (!container) return;
            if (!Array.isArray(vendors) || vendors.length === 0) {
                container.innerHTML = `<p class="text-sm text-gray-500 p-3">No data available.</p>`;
                return;
            }
            container.innerHTML = vendors
                .map((vendor) => {
                    const tooltipContent =
                        vendor.overdue_bills_details?.length > 0
                            ? `<ul class="list-disc list-inside">${vendor.overdue_bills_details
                                  .map((d) => `<li>${d}</li>`)
                                  .join("")}</ul>`
                            : "No details available.";
                    return `
                    <div class="group relative vendor-pulse-item flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <div>
                            <p class="font-semibold text-gray-800">${
                                vendor.name
                            }</p>
                            <p class="text-xs text-gray-500">Stall: ${
                                vendor.stall_number
                            }</p>
                        </div>
                        <span class="text-sm font-medium ${
                            isSupportList ? "text-red-500" : "text-green-600"
                        }">${vendor.metric}</span>
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max max-w-xs bg-gray-800 text-white text-xs rounded-lg py-2 px-3 shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity z-10">
                            <span class="font-bold block mb-1">Overdue Bills:</span>
                            ${tooltipContent}
                            <div class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-x-8 border-x-transparent border-t-8 border-t-gray-800"></div>
                        </div>
                    </div>`;
                })
                .join("");
        };

        renderList(topPerformersContainer, topPerformers);
        renderList(vendorsNeedingSupportContainer, needsSupport, true);
    }

    /**
     * Displays a toast notification.
     */
    showToast(message, type = "info") {
        if (!this.elements.toastContainer) return;
        const toast = document.createElement("div");
        const bgColor =
            type === "success"
                ? "bg-green-500"
                : type === "error"
                ? "bg-red-500"
                : "bg-blue-500";
        toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-all duration-300 flex items-center gap-3`;
        toast.innerHTML = `<i class="fas fa-info-circle"></i><span>${message}</span>`;
        this.elements.toastContainer.appendChild(toast);
        setTimeout(() => toast.classList.remove("translate-x-full"), 100);
        setTimeout(() => {
            toast.classList.add("translate-x-full");
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
}

export default AdminDashboard;
