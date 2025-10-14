import AdminDashboard from "./admin-dashboard.js";
import Chart from "chart.js/auto";

window.Chart = Chart;

const MarketApp = {
    state: {
        activeSection: "",
        dailyCollections: [],
        selectedCollections: new Set(),
        allVendors: [],
        allMarketSections: [],
        filters: {
            section: "Wet Section",
            search: "",
        },
        sort: {
            key: "vendorName",
            direction: "asc",
        },
        currentVendorId: null,
        isDetailView: false,
        isEditModalOpen: false,
        isLoading: true,
        vendorDashboardData: null,
        isLoadingModal: false,
        dataLoaded: {
            homeSection: false,
            vendorManagementSection: false,
            stallAssignmentSection: false,
            dashboardSection: false,
            reportsSection: false,
        },
        isOutstandingModalOpen: false,
        modalBills: [],
        unassignedVendors: [],
        availableStalls: [],
        charts: {},
    },

    dashboardInstance: null,

    database: {
        async fetchDailyCollections() {
            try {
                const response = await fetch("/api/staff/bill-management");
                if (!response.ok)
                    throw new Error(`HTTP error! Status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error("Failed to fetch bill management data:", error);
                return [];
            }
        },
        async fetchVendors() {
            try {
                const response = await fetch("/api/staff/vendors");
                if (!response.ok)
                    throw new Error(`HTTP error! Status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error("Failed to fetch vendors:", error);
                return [];
            }
        },
        async fetchSections() {
            try {
                const response = await fetch("/api/staff/sections");
                if (!response.ok)
                    throw new Error(`HTTP error! Status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error("Failed to fetch sections:", error);
                return [];
            }
        },
        async updateVendor(updatedVendor) {
            try {
                const response = await fetch(
                    `/api/staff/vendors/${updatedVendor.id}`,
                    {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: JSON.stringify(updatedVendor),
                    }
                );
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(
                        errorData.message || "Failed to update vendor"
                    );
                }
                return { success: true };
            } catch (error) {
                console.error("Vendor update failed.", error);
                return { success: false, message: error.message };
            }
        },
        async addBill(vendorId, billData) {
            try {
                const response = await fetch(
                    `/api/staff/vendors/${vendorId}/billings`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: JSON.stringify(billData),
                    }
                );
                const result = await response.json();
                if (!response.ok)
                    throw new Error(result.message || "Failed to add bill");
                return { success: true, ...result };
            } catch (error) {
                console.error("Add Bill Error:", error);
                return { success: false, message: error.message };
            }
        },

        //--Vendor Stall Assignment--//
        async fetchUnassignedVendors() {
            try {
                const response = await fetch("/api/staff/unassigned-vendors");
                if (!response.ok) throw new Error("Failed to fetch");
                return await response.json();
            } catch (error) {
                console.error("Failed to fetch unassigned vendors:", error);
                return [];
            }
        },
        async fetchAvailableStalls(section = "") {
            try {
                const url = `/api/staff/available-stalls?section=${encodeURIComponent(
                    section
                )}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error("Failed to fetch");
                return await response.json();
            } catch (error) {
                console.error("Failed to fetch available stalls:", error);
                return [];
            }
        },
        async assignStall(vendorId, stallId) {
            try {
                const response = await fetch("/api/staff/assign-stall", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        vendor_id: vendorId,
                        stall_id: stallId,
                    }),
                });
                const result = await response.json();
                if (!response.ok)
                    throw new Error(result.message || "Assignment failed");
                return { success: true, message: result.message };
            } catch (error) {
                console.error("Stall assignment error:", error);
                return { success: false, message: error.message };
            }
        },
    },

    elements: {},

    getters: {
        filteredVendors(state) {
            const filtered = state.allVendors.filter((vendor) => {
                const sectionMatch =
                    !state.filters.section ||
                    vendor.section.toLowerCase() ===
                        state.filters.section.toLowerCase();
                const searchMatch =
                    !state.filters.search ||
                    vendor.vendorName
                        .toLowerCase()
                        .includes(state.filters.search.toLowerCase()) ||
                    vendor.stallNumber
                        .toLowerCase()
                        .includes(state.filters.search.toLowerCase());
                return sectionMatch && searchMatch;
            });

            const { key, direction } = state.sort;
            if (key) {
                filtered.sort((a, b) => {
                    const valA = a[key] ? a[key].toString().toLowerCase() : "";
                    const valB = b[key] ? b[key].toString().toLowerCase() : "";
                    if (valA < valB) return direction === "asc" ? -1 : 1;
                    if (valA > valB) return direction === "asc" ? 1 : -1;
                    return 0;
                });
            }
            return filtered;
        },
        currentVendor(state) {
            return (
                state.allVendors.find((v) => v.id === state.currentVendorId) ||
                null
            );
        },
    },

    /**
     * [NEWLY ADDED]
     * MarketApp object contains helper/utility functions. The 'debounce' function was
     * missing, which caused the script to crash.
     */
    helpers: {
        debounce(func, delay) {
            let timeout;
            return function (...args) {
                const context = MarketApp;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        },
    },

    initializeDashboard() {
        if (MarketApp.dashboardInstance) {
            console.log("Dashboard already initialized.");
            return;
        }

        const dashboardState = window.DASHBOARD_STATE;

        if (!dashboardState) {
            console.error("Dashboard state not found");
            MarketApp.methods.showToast(
                "Failed to load dashboard data.",
                "error"
            );
            return;
        }

        MarketApp.dashboardInstance = new AdminDashboard(dashboardState);
        MarketApp.dashboardInstance.init();
    },

    methods: {
        setSectionFromHash() {
            let hash = window.location.hash.substring(1);

            if (hash.startsWith("vendorDetail/")) {
                const vendorId = parseInt(hash.split("/")[1], 10);
                if (!isNaN(vendorId)) {
                    MarketApp.state.activeSection = "vendorManagementSection";
                    MarketApp.state.isDetailView = true;
                    MarketApp.state.currentVendorId = vendorId;
                    MarketApp.render.updateDashboardView();
                    MarketApp.render.updateVendorView();
                    return;
                }
            }

            if (
                !hash ||
                !Array.from(MarketApp.elements.navLinks).some(
                    (l) => l.getAttribute("data-section") === hash
                )
            ) {
                hash = "homeSection";
            }

            if (MarketApp.state.activeSection !== hash) {
                MarketApp.state.activeSection = hash;
            }

            MarketApp.render.updateDashboardView();
            // Call the new initializer method every time the section changes
            MarketApp.methods.initializeSection(MarketApp.state.activeSection);
        },

        async initializeSection(sectionId) {
            // If already loaded, do nothing
            if (MarketApp.state.dataLoaded[sectionId]) {
                return;
            }

            MarketApp.state.isLoading = true;

            try {
                switch (sectionId) {
                    case "reportsSection":
                        // Fetch sections if they haven't been loaded for other parts of the app
                        if (MarketApp.state.allMarketSections.length === 0) {
                            MarketApp.state.allMarketSections =
                                await MarketApp.database.fetchSections();
                        }
                        // IMPORTANT: Attach the event listeners for the report buttons
                        MarketApp.methods.setupReportsEventListeners();
                        break;

                    case "dashboardSection":
                        MarketApp.initializeDashboard();
                        break;
                    case "stallAssignmentSection":
                        if (MarketApp.state.allMarketSections.length === 0) {
                            MarketApp.state.allMarketSections =
                                await MarketApp.database.fetchSections();
                        }
                        MarketApp.methods.setupStallAssignmentEventListeners();
                        await MarketApp.methods.loadStallAssignmentData();
                        break;
                }
                // Mark MarketApp section as loaded so we don't run setup again
                MarketApp.state.dataLoaded[sectionId] = true;
            } catch (error) {
                console.error(
                    `Failed to initialize section ${sectionId}:`,
                    error
                );
                MarketApp.methods.showToast(
                    `Error loading ${sectionId}.`,
                    "error"
                );
            } finally {
                MarketApp.state.isLoading = false;
            }
        },

        toggleCollectionSelection(collectionId, isChecked) {
            if (isChecked) {
                MarketApp.state.selectedCollections.add(collectionId);
            } else {
                MarketApp.state.selectedCollections.delete(collectionId);
            }
            MarketApp.render.renderDailyCollectionsTable();
        },

        toggleAllCollections(isChecked) {
            if (isChecked) {
                const allIds = MarketApp.state.dailyCollections.map(
                    (c) => c.id
                );
                MarketApp.state.selectedCollections = new Set(allIds);
            } else {
                MarketApp.state.selectedCollections.clear();
            }
            MarketApp.render.renderDailyCollectionsTable();
        },

        printIndividualReceipt(collectionId) {
            // START OF FIX: Get the current month and year to build the correct URL
            const now = new Date();
            const month = now.toLocaleString("en-US", { month: "long" });
            const year = now.getFullYear();
            const monthYearString = `${month} ${year}`;

            // Construct the full, correct URL with both the user ID and the month
            const url = `/printing/${collectionId}/print/${monthYearString}`;

            window.open(url, "_blank");
            // END OF FIX
        },

        printBulkReceipts() {
            const selectedIds = Array.from(MarketApp.state.selectedCollections);
            if (selectedIds.length === 0) {
                MarketApp.methods.showToast(
                    "No receipts selected to print.",
                    "info"
                );
                return;
            }

            const url = `/printing/bulk-print?users=${selectedIds.join(",")}`;
            window.open(url, "_blank");

            MarketApp.state.selectedCollections.clear();
            MarketApp.render.renderDailyCollectionsTable();
        },

        sortTable(key) {
            const { sort } = MarketApp.state;
            if (sort.key === key) {
                sort.direction = sort.direction === "asc" ? "desc" : "asc";
            } else {
                sort.key = key;
                sort.direction = "asc";
            }
            MarketApp.render.renderTable();
        },

        updateFilters(filterName, value) {
            MarketApp.state.filters[filterName] = value;
            if (
                filterName === "section" &&
                MarketApp.elements.vendorManagementTableHeader
            ) {
                MarketApp.elements.vendorManagementTableHeader.textContent =
                    value;
            }
            MarketApp.render.renderTable();
        },

        showVendorDetails(vendorId) {
            MarketApp.state.currentVendorId = vendorId;
            MarketApp.state.isDetailView = true;
            MarketApp.render.updateVendorView();
        },

        showVendorList() {
            MarketApp.state.isDetailView = false;
            MarketApp.render.updateVendorView();
        },

        openEditModal() {
            MarketApp.state.isEditModalOpen = true;
            MarketApp.render.updateModal();
        },

        closeEditModal() {
            MarketApp.state.isEditModalOpen = false;
            MarketApp.render.updateModal();
        },

        async saveVendorChanges() {
            const vendorId = MarketApp.state.currentVendorId;
            const vendorIndex = MarketApp.state.allVendors.findIndex(
                (v) => v.id === vendorId
            );
            if (vendorIndex === -1) return;

            const originalVendorData = {
                ...MarketApp.state.allVendors[vendorIndex],
            };
            const updatedVendorData = { ...originalVendorData };
            MarketApp.elements.editVendorForm
                .querySelectorAll("[data-field]")
                .forEach((input) => {
                    updatedVendorData[input.dataset.field] = input.value;
                });

            // Optimistic UI Update
            MarketApp.state.allVendors[vendorIndex] = updatedVendorData;
            MarketApp.methods.closeEditModal();
            MarketApp.render.updateVendorView();
            MarketApp.render.renderTable();
            MarketApp.methods.showToast("Changes saved!", "success");

            // Background Save & Rollback on Failure
            try {
                const result = await MarketApp.database.updateVendor(
                    updatedVendorData
                );
                if (!result.success) {
                    throw new Error(
                        result.message || "Server rejected the update."
                    );
                }
                // Refresh related data if needed
                const collections =
                    await MarketApp.database.fetchDailyCollections();
                MarketApp.state.dailyCollections = collections;
                MarketApp.render.renderDailyCollectionsTable();
            } catch (error) {
                console.error("Optimistic save failed:", error);
                MarketApp.methods.showToast(
                    `Error: ${error.message || "Could not save changes."}`,
                    "error"
                );

                // Rollback UI
                MarketApp.state.allVendors[vendorIndex] = originalVendorData;
                MarketApp.render.updateVendorView();
                MarketApp.render.renderTable();
                MarketApp.methods.openEditModal();
            }
        },

        showToast(message, type = "success") {
            const toastContainer = document.getElementById("toastContainer");
            if (!toastContainer) {
                alert(message);
                return;
            }

            const toast = document.createElement("div");
            const iconClass =
                type === "success"
                    ? "fa-check-circle"
                    : "fa-exclamation-circle";
            const bgColor = type === "success" ? "bg-green-500" : "bg-red-500";

            toast.className = `flex items-center gap-3 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-all duration-300`;
            toast.innerHTML = `<i class="fas ${iconClass}"></i><span>${message}</span>`;

            toastContainer.appendChild(toast);

            setTimeout(() => toast.classList.remove("translate-x-full"), 100);

            setTimeout(() => {
                toast.classList.add("translate-x-full");
                toast.addEventListener("transitionend", () => toast.remove());
            }, 5000);
        },

        toggleModal: function (modalId, show) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(`${modalId}Content`);
            if (show) {
                modal.classList.remove("hidden");
                setTimeout(
                    () => content.classList.remove("scale-95", "opacity-0"),
                    50
                );
            } else {
                content.classList.add("scale-95", "opacity-0");
                setTimeout(() => modal.classList.add("hidden"), 300);
            }
        },

        showVendorSubSection(sectionId) {
            const sections = [
                "outstandingBalanceSection",
                "PaymentHistorySection",
            ];

            sections.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.classList.toggle("hidden", id !== sectionId);
                }
            });
        },

        async loadVendorSubSection(url) {
            const { vendorDetailSection, vendorSubSectionContainer } =
                MarketApp.elements;

            // Show a loading state
            vendorSubSectionContainer.innerHTML = `<div class="text-center p-12"><i class="fas fa-spinner fa-spin text-4xl text-indigo-500"></i></div>`;
            vendorDetailSection.classList.add("hidden");
            vendorSubSectionContainer.classList.remove("hidden");

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error("Failed to load content.");

                const html = await response.text();
                vendorSubSectionContainer.innerHTML = html;

                // Add a listener for the new back button inside the loaded content
                vendorSubSectionContainer
                    .querySelector('[data-action="back-to-details"]')
                    ?.addEventListener("click", () => {
                        vendorSubSectionContainer.classList.add("hidden");
                        vendorDetailSection.classList.remove("hidden");
                        vendorSubSectionContainer.innerHTML = ""; // Clear content
                    });
            } catch (error) {
                console.error("Failed to load subsection:", error);
                vendorSubSectionContainer.innerHTML = `<p class="text-center text-red-500 p-12">Could not load content. Please try again.</p>`;
                MarketApp.methods.showToast(
                    "Failed to load information.",
                    "error"
                );
            }
        },

        showVendorDetails(vendorId) {
            MarketApp.state.currentVendorId = vendorId;
            MarketApp.state.isDetailView = true;

            // Make sure the detail view is visible and the sub-section is hidden
            MarketApp.elements.vendorListView.classList.add("hidden");
            MarketApp.elements.vendorDetailSection.classList.remove("hidden");
            MarketApp.elements.vendorSubSectionContainer.classList.add(
                "hidden"
            );

            MarketApp.render.renderVendorDetails();
        },

        async fetchAndRenderPaymentHistory() {
            const vendor = MarketApp.getters.currentVendor(MarketApp.state);
            if (!vendor) {
                console.warn("No vendor found for payment history");
                return;
            }

            const yearFilter = document.getElementById("ph_year_filter");
            const monthFilter = document.getElementById("ph_month_filter");
            const searchInput = document.getElementById("ph_search_input");

            if (!yearFilter || !monthFilter || !searchInput) {
                console.warn("Payment history filters not found in DOM");
                return;
            }

            const year = yearFilter.value;
            const month = monthFilter.value;
            const search = searchInput.value;
            const tableBody = document.getElementById(
                "paymentHistoryTableBody"
            );
            const noResultsEl = document.getElementById("ph_no_results");

            if (!tableBody || !noResultsEl) {
                console.warn("Payment history table elements not found");
                return;
            }

            const query = new URLSearchParams({ year, month, search });
            const url = `/api/staff/vendors/${vendor.id}/payment-history-filtered?${query}`;

            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-indigo-500"></i></td></tr>`;
            noResultsEl.classList.add("hidden");

            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error("Failed to fetch payment history");
                }

                const payments = await response.json();

                if (payments.length === 0) {
                    tableBody.innerHTML = "";
                    noResultsEl.classList.remove("hidden");
                    return;
                }

                tableBody.innerHTML = payments
                    .map((bill) => {
                        const formatShortDate = (dateStr) =>
                            dateStr
                                ? new Date(dateStr).toLocaleDateString(
                                      "en-US",
                                      {
                                          month: "short",
                                          day: "numeric",
                                          year: "numeric",
                                      }
                                  )
                                : "N/A";
                        const formatPeriod = (start, end) =>
                            `${formatShortDate(start)} - ${formatShortDate(
                                end
                            )}`;
                        const formatStatusDate = (dateStr) =>
                            dateStr
                                ? new Date(dateStr).toLocaleDateString(
                                      "en-US",
                                      { month: "short", day: "numeric" }
                                  )
                                : "";

                        return `
                    <tr class="table-row">
                        <td data-label="Category" class="px-4 py-2 text-center">${
                            bill.utility_type
                        }</td>
                        <td data-label="Period Covered" class="px-4 py-2 text-center">${formatPeriod(
                            bill.period_start,
                            bill.period_end
                        )}</td>
                        <td data-label="Amount Due" class="px-4 py-2 text-center">₱${parseFloat(
                            bill.payment.amount_paid
                        ).toFixed(2)}</td>
                        <td data-label="Due Date" class="px-4 py-2 text-center">${formatShortDate(
                            bill.due_date
                        )}</td>
                        <td data-label="Amount After Due" class="px-4 py-2 text-center">₱${parseFloat(
                            bill.amount_after_due
                        ).toFixed(2)}</td>
                        <td data-label="Disconnection Date" class="px-4 py-2 text-center">${formatShortDate(
                            bill.disconnection_date
                        )}</td>
                        <td data-label="Payment Status" class="px-4 py-2 text-center">
                            <span class="whitespace-nowrap px-4 py-1.5 text-xs font-semibold text-white bg-gray-800 rounded-full">
                                Paid on ${formatStatusDate(
                                    bill.payment.payment_date
                                )}
                            </span>
                        </td>
                    </tr>
                `;
                    })
                    .join("");
            } catch (error) {
                console.error("Failed to fetch payment history:", error);
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-red-500">Error loading data.</td></tr>`;
                MarketApp.methods.showToast(
                    "Failed to load payment history.",
                    "error"
                );
            }
        },

        // MarketApp method sets up the filters for the new component
        async initializePaymentHistoryComponent() {
            const yearFilter = document.getElementById("ph_year_filter");
            if (!yearFilter) {
                console.warn("Payment history year filter not found");
                return;
            }

            // 1. Populate year filter using the new API route
            const vendor = MarketApp.getters.currentVendor(MarketApp.state);
            if (!vendor) {
                console.warn("No vendor selected");
                return;
            }

            try {
                const response = await fetch(
                    `/api/staff/vendors/${vendor.id}/payment-years`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch payment years");
                }

                const years = await response.json();

                if (years.length > 0) {
                    yearFilter.innerHTML = years
                        .map((y) => `<option value="${y}">${y}</option>`)
                        .join("");
                } else {
                    const currentYear = new Date().getFullYear();
                    yearFilter.innerHTML = `<option value="${currentYear}">${currentYear}</option>`;
                }

                // 2. Attach event listeners - Remove existing ones first to prevent duplicates
                const debounceFetch = MarketApp.helpers.debounce(
                    MarketApp.methods.fetchAndRenderPaymentHistory,
                    300
                );

                // Clone and replace elements to remove old event listeners
                const newYearFilter = yearFilter.cloneNode(true);
                yearFilter.parentNode.replaceChild(newYearFilter, yearFilter);

                const monthFilter = document.getElementById("ph_month_filter");
                const newMonthFilter = monthFilter.cloneNode(true);
                monthFilter.parentNode.replaceChild(
                    newMonthFilter,
                    monthFilter
                );

                const searchInput = document.getElementById("ph_search_input");
                const newSearchInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(
                    newSearchInput,
                    searchInput
                );

                // Attach fresh event listeners
                newYearFilter.addEventListener(
                    "change",
                    MarketApp.methods.fetchAndRenderPaymentHistory
                );
                newMonthFilter.addEventListener(
                    "change",
                    MarketApp.methods.fetchAndRenderPaymentHistory
                );
                newSearchInput.addEventListener("input", debounceFetch);

                // 3. Perform the initial data load now that the year filter is populated
                await MarketApp.methods.fetchAndRenderPaymentHistory();
            } catch (error) {
                console.error("Failed to initialize payment history:", error);
                MarketApp.methods.showToast(
                    "Failed to load payment history filters.",
                    "error"
                );
            }
        },

        async handlePayment(button) {
            const billingId = parseInt(button.dataset.billingId, 10);
            if (
                confirm(
                    "Are you sure you want to record MarketApp payment? MarketApp action cannot be undone."
                )
            ) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fas fa-spinner fa-spin mr-2"></i><span>Processing...</span>';

                try {
                    // Add timeout to prevent infinite waiting
                    const controller = new AbortController();
                    const timeoutId = setTimeout(
                        () => controller.abort(),
                        10000
                    ); // 10 second timeout

                    const response = await fetch(
                        `/api/staff/bills/${billingId}/pay`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                "X-CSRF-TOKEN": document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content"),
                            },
                            signal: controller.signal,
                        }
                    );

                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(
                            errorData.message || "Failed to record payment."
                        );
                    }

                    MarketApp.methods.showToast(
                        "Payment recorded successfully!",
                        "success"
                    );

                    // Reload the partial view immediately for better user experience
                    const vendor = MarketApp.getters.currentVendor(
                        MarketApp.state
                    );
                    if (vendor) {
                        await MarketApp.methods.loadVendorSubSection(
                            `/staff/vendor/${vendor.id}/view-as-vendor-partial`
                        );
                    }
                } catch (error) {
                    console.error("Payment Error:", error);
                    let errorMessage = "An error occurred. Please try again.";

                    if (error.name === "AbortError") {
                        errorMessage =
                            "Request timed out. The server might be slow. Please try again.";
                    } else if (error.message) {
                        errorMessage = error.message;
                    }

                    MarketApp.methods.showToast(errorMessage, "error");
                    // Important: Re-enable the button on failure
                    button.disabled = false;
                    button.innerHTML = "<span>Record Payment</span>";
                }
            }
        },

        setupReportsEventListeners() {
            MarketApp.elements.generateReportBtn?.addEventListener(
                "click",
                () => MarketApp.methods.fetchAndRenderMonthlyReport()
            );
            MarketApp.elements.printReportBtn?.addEventListener("click", () =>
                MarketApp.methods.printReport()
            );
            MarketApp.elements.downloadReportBtn?.addEventListener(
                "click",
                () => MarketApp.methods.downloadReport()
            );
        },

        async fetchAndRenderMonthlyReport() {
            const {
                reportMonth,
                reportResultContainer,
                reportLoader,
                noReportDataMessage,
            } = MarketApp.elements;

            reportResultContainer.classList.add("hidden");
            noReportDataMessage.classList.add("hidden");
            reportLoader.classList.remove("hidden");

            const params = new URLSearchParams({
                month: reportMonth.value,
            });

            try {
                const response = await fetch(
                    `/api/staff/reports/monthly?${params.toString()}`
                );
                if (!response.ok)
                    throw new Error("Failed to fetch report data.");
                const data = await response.json();

                if (
                    data.kpis.total_collection === 0 &&
                    data.delinquent_vendors.length === 0
                ) {
                    noReportDataMessage.classList.remove("hidden");
                    reportLoader.classList.add("hidden"); // Also hide loader here
                    return;
                }

                // MarketApp block contains the correct function calls
                MarketApp.render.renderReportHeader(data);
                MarketApp.render.renderReportKPIs(data.kpis);
                MarketApp.render.renderReportCharts(data.chart_data);
                MarketApp.render.renderReportTables(
                    data.collections_breakdown,
                    data.delinquent_vendors
                );

                reportResultContainer.classList.remove("hidden");
            } catch (error) {
                console.error("Report Generation Error:", error);
                MarketApp.methods.showToast(
                    "Could not generate report.",
                    "error"
                );
            } finally {
                reportLoader.classList.add("hidden");
            }
        },

        printReport() {
            document.body.classList.add("printing");
            window.print();
            setTimeout(() => {
                document.body.classList.remove("printing");
            }, 500);
        },

        downloadReport() {
            const month = MarketApp.elements.reportMonth.value;
            const notes = MarketApp.elements.reportNotes.value;

            const url = `/staff/reports/download?month=${month}&notes=${encodeURIComponent(
                notes
            )}`;
            window.open(url, "_blank");
        },

        showToast(message, type = "info") {
            const toastContainer =
                document.getElementById("toastContainer") || document.body;
            const toast = document.createElement("div");
            const icon =
                type === "success" ? "fa-check-circle" : "fa-times-circle";
            const bgColor = type === "success" ? "bg-green-500" : "bg-red-500";
            toast.className = `fixed top-4 right-4 z-[100] flex items-center gap-3 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-all duration-300`;
            toast.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
            toastContainer.appendChild(toast);
            setTimeout(() => toast.classList.remove("translate-x-full"), 100);
            setTimeout(() => {
                toast.classList.add("translate-x-full");
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        },

        //--Vendor Stall Assignment--//
        async loadStallAssignmentData() {
            MarketApp.state.unassignedVendors =
                await MarketApp.database.fetchUnassignedVendors();
            MarketApp.render.renderStallAssignmentView();
        },

        async handleStallSectionFilterChange() {
            const section = MarketApp.elements.stallSectionFilter.value;
            MarketApp.elements.availableStallSelect.innerHTML = `<option>Loading stalls...</option>`;
            MarketApp.state.availableStalls =
                await MarketApp.database.fetchAvailableStalls(section);
            MarketApp.render.renderAvailableStalls();
        },

        async handleAssignStall() {
            const vendorId = MarketApp.elements.unassignedVendorSelect.value;
            const stallId = MarketApp.elements.availableStallSelect.value;

            if (!vendorId || !stallId) {
                MarketApp.methods.showToast(
                    "Please select both a vendor and a stall.",
                    "error"
                );
                return;
            }

            const result = await MarketApp.database.assignStall(
                vendorId,
                stallId
            );

            if (result.success) {
                MarketApp.methods.showToast(result.message, "success");
                // Refresh data to update the lists
                await MarketApp.methods.loadStallAssignmentData();
            } else {
                MarketApp.methods.showToast(result.message, "error");
            }
        },

        setupStallAssignmentEventListeners() {
            MarketApp.elements.stallSectionFilter?.addEventListener(
                "change",
                MarketApp.methods.handleStallSectionFilterChange
            );
            MarketApp.elements.assignStallBtn?.addEventListener(
                "click",
                MarketApp.methods.handleAssignStall
            );
        },
        //-Bill Breakdwon--//
        showOutstandingDetailsForMonth(month) {
            // FIX 1: Read data from the data-bills attribute on the newly loaded HTML.
            const container = document.getElementById(
                "vendor-outstanding-balance-view"
            );
            if (!container || !container.dataset.bills) {
                console.error(
                    "Bill data container or data attribute not found."
                );
                // FIX 2: Correctly call showToast using the MarketApp object.
                MarketApp.methods.showToast(
                    "Could not load bill details.",
                    "error"
                );
                return;
            }
            const currentVendorDetailedBills = JSON.parse(
                container.dataset.bills
            );

            const billsForMonth =
                currentVendorDetailedBills.filter((bill) => {
                    let periodDate = new Date(bill.period_start);
                    // Replicate the backend's grouping logic for an exact match
                    if (
                        bill.utility_type === "Water" ||
                        bill.utility_type === "Electricity"
                    ) {
                        periodDate.setMonth(periodDate.getMonth() + 1);
                    }
                    const monthYearString = periodDate.toLocaleString("en-US", {
                        month: "long",
                        year: "numeric",
                    });
                    return monthYearString === month;
                }) || [];

            // FIX 2: Correctly reference MarketApp.state instead of this.state.
            MarketApp.state.modalBills = billsForMonth;
            MarketApp.state.isOutstandingModalOpen = true;
            MarketApp.render.renderOutstandingModal();
        },
    },

    render: {
        renderOutstandingModal() {
            // Safety check: ensure elements are initialized
            if (
                !MarketApp.elements ||
                !MarketApp.elements.outstandingDetailsModal
            ) {
                console.warn("Modal elements not yet initialized");
                return;
            }

            const modal = MarketApp.elements.outstandingDetailsModal;
            if (!modal) return;

            // ADD THIS DEBUG CODE:
            console.log("=== MODAL DEBUG ===");
            console.log(
                "Is modal open?",
                MarketApp.state.isOutstandingModalOpen
            );
            console.log("Bills to display:", MarketApp.state.modalBills);
            console.log("First bill details:", MarketApp.state.modalBills[0]);
            console.log("Billing settings:", window.BILLING_SETTINGS);
            // END DEBUG CODE

            const formatCurrency = (amount) =>
                new Intl.NumberFormat("en-PH", {
                    style: "currency",
                    currency: "PHP",
                }).format(amount);

            if (MarketApp.state.isOutstandingModalOpen) {
                const detailsBody =
                    MarketApp.elements.outstandingBreakdownDetails;
                const billsToDisplay = MarketApp.state.modalBills || [];

                if (billsToDisplay.length > 0) {
                    // Initialize totals
                    let totalOriginal = 0;
                    let totalDiscount = 0;
                    let totalSurcharge = 0;
                    let totalAmount = 0;

                    detailsBody.innerHTML = billsToDisplay
                        .map((bill) => {
                            // Get the base amounts from backend
                            const originalAmount = parseFloat(
                                bill.original_amount || 0
                            );
                            const penaltyApplied = parseFloat(
                                bill.penalty_applied || 0
                            );
                            const discountApplied = parseFloat(
                                bill.discount_applied || 0
                            );

                            let baseAmountForCalc = originalAmount;
                            let detailsHtml = `<strong>${formatCurrency(
                                originalAmount
                            )}</strong>`;

                            const currentVendor =
                                MarketApp.getters.currentVendor(
                                    MarketApp.state
                                );

                            // Calculate Original Payment column with formula
                            if (
                                bill.utility_type === "Rent" &&
                                currentVendor &&
                                currentVendor.daily_rate
                            ) {
                                const dailyRate = parseFloat(
                                    currentVendor.daily_rate
                                );
                                const area = parseFloat(
                                    currentVendor.area || 0
                                );

                                // Check if vendor is in Dry Section (has area)
                                if (area > 0) {
                                    // Dry Section: (area × rate_per_sqm) × 30
                                    const calculatedAmount =
                                        area * dailyRate * 30;
                                    baseAmountForCalc = calculatedAmount;
                                    detailsHtml = `(${area.toFixed(
                                        2
                                    )} m² x ${formatCurrency(
                                        dailyRate
                                    )}) x 30 days = <strong>${formatCurrency(
                                        calculatedAmount
                                    )}</strong>`;
                                } else {
                                    // Regular Section: daily_rate × 30
                                    const calculatedAmount = dailyRate * 30;
                                    baseAmountForCalc = calculatedAmount;
                                    detailsHtml = `${formatCurrency(
                                        dailyRate
                                    )} x 30 days = <strong>${formatCurrency(
                                        calculatedAmount
                                    )}</strong>`;
                                }
                            } else if (bill.utility_type === "Water") {
                                const daysInMonth = new Date(
                                    bill.period_end
                                ).getDate();
                                const waterRate = 5.0;
                                const calculatedAmount =
                                    waterRate * daysInMonth;
                                baseAmountForCalc = calculatedAmount;
                                detailsHtml = `${formatCurrency(
                                    waterRate
                                )} x ${daysInMonth} days = <strong>${formatCurrency(
                                    calculatedAmount
                                )}</strong>`;
                            } else if (
                                bill.utility_type === "Electricity" &&
                                bill.consumption
                            ) {
                                const consumption =
                                    parseFloat(bill.consumption) || 0;
                                detailsHtml = `(${consumption.toFixed(
                                    2
                                )} kWh) x ${formatCurrency(
                                    bill.rate || 0
                                )} = <strong>${formatCurrency(
                                    originalAmount
                                )}</strong>`;
                            }

                            // ===== Discount Column with Formula =====
                            let discountHtml = "-";
                            if (
                                discountApplied > 0 &&
                                window.BILLING_SETTINGS
                            ) {
                                const settings =
                                    window.BILLING_SETTINGS[bill.utility_type];
                                if (settings && settings.discount_rate) {
                                    const discountRate = parseFloat(
                                        settings.discount_rate
                                    );
                                    discountHtml = `<strong class="text-green-600">${formatCurrency(
                                        baseAmountForCalc
                                    )} x ${(discountRate * 100).toFixed(
                                        0
                                    )}% = -${formatCurrency(
                                        discountApplied
                                    )}</strong>`;
                                } else {
                                    discountHtml = `<strong class="text-green-600">-${formatCurrency(
                                        discountApplied
                                    )}</strong>`;
                                }
                            }

                            // ===== Surcharge/Penalty Column with Formula =====
                            let penaltyHtml = "-";
                            if (penaltyApplied > 0 && window.BILLING_SETTINGS) {
                                const settings =
                                    window.BILLING_SETTINGS[bill.utility_type];
                                if (settings && bill.utility_type === "Rent") {
                                    // For Rent: Show surcharge + interest breakdown
                                    const surchargeRate = parseFloat(
                                        settings.surcharge_rate || 0
                                    );
                                    const interestRate = parseFloat(
                                        settings.monthly_interest_rate || 0
                                    );
                                    const interestMonths = parseInt(
                                        bill.interest_months || 0
                                    );

                                    const surchargeAmount =
                                        baseAmountForCalc * surchargeRate;
                                    const interestAmount =
                                        baseAmountForCalc *
                                        interestRate *
                                        interestMonths;

                                    penaltyHtml = `<strong class="text-red-600">Surcharge (${(
                                        surchargeRate * 100
                                    ).toFixed(0)}%): + ${formatCurrency(
                                        surchargeAmount
                                    )}<br>`;
                                    penaltyHtml += `Interest (${(
                                        interestRate * 100
                                    ).toFixed(
                                        0
                                    )}% x ${interestMonths} mo): + ${formatCurrency(
                                        interestAmount
                                    )}<br>`;
                                    penaltyHtml += `--- Total Penalty: + ${formatCurrency(
                                        penaltyApplied
                                    )}</strong>`;
                                } else if (settings && settings.penalty_rate) {
                                    // For Utilities with penalty rate configured
                                    const penaltyRate = parseFloat(
                                        settings.penalty_rate
                                    );
                                    penaltyHtml = `<strong class="text-red-600">Penalty (${(
                                        penaltyRate * 100
                                    ).toFixed(0)}%): + ${formatCurrency(
                                        penaltyApplied
                                    )}</strong>`;
                                } else {
                                    // Fallback: just show the amount
                                    penaltyHtml = `<strong class="text-red-600">+ ${formatCurrency(
                                        penaltyApplied
                                    )}</strong>`;
                                }
                            }

                            // Final total from backend
                            const finalTotal = parseFloat(
                                bill.display_amount_due || 0
                            );

                            // Add to totals
                            totalOriginal += baseAmountForCalc;
                            totalDiscount += discountApplied;
                            totalSurcharge += penaltyApplied;
                            totalAmount += finalTotal;

                            // Category text with note for Rent
                            const categoryText =
                                bill.utility_type === "Rent"
                                    ? `Rent<br><span class="text-sm font-normal text-gray-500">(Standard Payment)</span>`
                                    : bill.utility_type;

                            return `
                        <tr class="text-lg border-b border-gray-200">
                            <td data-label="Category" class="px-4 py-3 align-top font-bold">${categoryText}</td>
                            <td data-label="Original Payment" class="px-4 py-3 align-top">${detailsHtml}</td>
                            <td data-label="Discount" class="px-4 py-3 align-top">${discountHtml}</td>
                            <td data-label="Surcharge/Penalty" class="px-4 py-3 align-top">${penaltyHtml}</td>
                            <td data-label="Total Amount to be Paid" class="px-4 py-3 align-top font-bold text-market-primary">${formatCurrency(
                                finalTotal
                            )}</td>
                        </tr>
                    `;
                        })
                        .join("");

                    // Update footer totals
                    const totalOriginalEl = document.getElementById(
                        "totalOriginalPayment"
                    );
                    const totalDiscountEl =
                        document.getElementById("totalDiscount");
                    const totalSurchargeEl =
                        document.getElementById("totalSurcharge");
                    const totalAmountDueEl =
                        document.getElementById("totalAmountDue");

                    if (totalOriginalEl)
                        totalOriginalEl.textContent =
                            formatCurrency(totalOriginal);
                    if (totalDiscountEl)
                        totalDiscountEl.textContent =
                            totalDiscount > 0
                                ? `-${formatCurrency(totalDiscount)}`
                                : formatCurrency(0);
                    if (totalSurchargeEl)
                        totalSurchargeEl.textContent =
                            formatCurrency(totalSurcharge);
                    if (totalAmountDueEl)
                        totalAmountDueEl.textContent =
                            formatCurrency(totalAmount);
                } else {
                    detailsBody.innerHTML = `<tr><td colspan="5" class="text-center py-4">No bills for this month.</td></tr>`;

                    // Reset footer totals
                    const totalOriginalEl = document.getElementById(
                        "totalOriginalPayment"
                    );
                    const totalDiscountEl =
                        document.getElementById("totalDiscount");
                    const totalSurchargeEl =
                        document.getElementById("totalSurcharge");
                    const totalAmountDueEl =
                        document.getElementById("totalAmountDue");

                    if (totalOriginalEl)
                        totalOriginalEl.textContent = formatCurrency(0);
                    if (totalDiscountEl)
                        totalDiscountEl.textContent = formatCurrency(0);
                    if (totalSurchargeEl)
                        totalSurchargeEl.textContent = formatCurrency(0);
                    if (totalAmountDueEl)
                        totalAmountDueEl.textContent = formatCurrency(0);
                }
                modal.classList.remove("hidden");
            } else {
                modal.classList.add("hidden");
            }
        },

        renderTable() {
            if (!MarketApp.elements.tableBody) return;

            const { tableBody } = MarketApp.elements;
            const vendors = MarketApp.getters.filteredVendors(MarketApp.state);

            tableBody.innerHTML = "";
            if (vendors.length > 0) {
                vendors.forEach((vendor) => {
                    const row = document.createElement("tr");
                    row.className = "table-row";
                    row.innerHTML = `
                        <td data-label="Stall/Table Number" class="px-8 py-4 whitespace-nowrap text-sm text-gray-700">${vendor.stallNumber}</td>
                        <td data-label="Vendor Name" class="px-8 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${vendor.vendorName}</td>
                        <td data-label="Action" class="px-8 py-4 whitespace-nowrap text-center text-sm">
                            <button class="view-vendor-btn bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg transition-smooth" data-vendor-id="${vendor.id}">
                                View Info
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-8 text-gray-500">No vendors found.</td></tr>`;
            }
        },

        populateFilters() {
            const { vendorSectionNav } = MarketApp.elements;
            const sections = MarketApp.state.allMarketSections;

            if (vendorSectionNav) {
                vendorSectionNav.innerHTML = "";
                sections.forEach((section) => {
                    if (section) {
                        const button = document.createElement("button");
                        button.dataset.section = section;
                        button.className =
                            "section-nav-btn px-4 py-2 sm:px-6 sm:py-3 rounded-lg font-medium transition-all duration-200 text-sm sm:text-base";
                        button.textContent = section;
                        if (section === MarketApp.state.filters.section) {
                            button.classList.add("active");
                        }
                        vendorSectionNav.appendChild(button);
                    }
                });
            }
        },

        updateDashboardView() {
            const { activeSection } = MarketApp.state;

            MarketApp.elements.sections.forEach((section) => {
                const isSectionActive = section.id === activeSection;
                section.classList.toggle("active", isSectionActive);
            });

            MarketApp.elements.navLinks.forEach((link) => {
                const linkIsActive =
                    link.getAttribute("data-section") === activeSection;
                link.classList.toggle("active", linkIsActive);
            });

            if (activeSection === "dashboardSection") {
                MarketApp.initializeDashboard();
            }
        },

        renderDailyCollectionsTable() {
            const { dailyCollectionsTableBody, selectAllCheckbox } =
                MarketApp.elements;
            if (!dailyCollectionsTableBody) return;

            if (MarketApp.state.isLoading) {
                dailyCollectionsTableBody.innerHTML = `<tr><td colspan="4" class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Loading data...</td></tr>`;
                return;
            }

            dailyCollectionsTableBody.innerHTML = "";
            const collections = MarketApp.state.dailyCollections;
            const selected = MarketApp.state.selectedCollections;

            if (collections.length > 0) {
                collections.forEach((item) => {
                    const isChecked = selected.has(item.id);
                    const row = document.createElement("tr");
                    row.className = "table-row";
                    row.innerHTML = `
                        <td data-label="Select" class="p-4 text-right lg:text-center"> 
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600 rounded row-checkbox" data-id="${
                                item.id
                            }" ${isChecked ? "checked" : ""}>
                        </td>
                        <td data-label="Stall/Table Number" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${
                            item.stallNumber
                        }</td>
                        <td data-label="Vendor Name" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${
                            item.vendorName
                        }</td>
                        <td data-label="Print Receipt" class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <button class="print-receipt-btn text-indigo-600 hover:text-indigo-800 transition-smooth" title="Print" data-id="${
                                item.id
                            }">
                                <i class="fas fa-print fa-lg"></i>
                            </button>
                        </td>
                    `;
                    dailyCollectionsTableBody.appendChild(row);
                });
            } else {
                dailyCollectionsTableBody.innerHTML = `<tr><td colspan="4" class="text-center py-8 text-gray-500">All vendors have complete information.</td></tr>`;
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked =
                    selected.size === collections.length &&
                    collections.length > 0;
                selectAllCheckbox.indeterminate =
                    selected.size > 0 && selected.size < collections.length;
            }
        },

        updateVendorView() {
            const { isDetailView } = MarketApp.state;
            if (MarketApp.elements.vendorListView) {
                MarketApp.elements.vendorListView.classList.toggle(
                    "hidden",
                    isDetailView
                );
                MarketApp.elements.vendorDetailSection.classList.toggle(
                    "hidden",
                    !isDetailView
                );
                if (isDetailView) MarketApp.render.renderVendorDetails();
            }
        },

        renderVendorDetails() {
            const { profileInfoContainer } = MarketApp.elements;
            const vendor = MarketApp.getters.currentVendor(MarketApp.state);

            // MarketApp function should ONLY populate the vendor's profile information.
            // The event listeners are now responsible for handling clicks.
            // We have removed all lines that were incorrectly setting the `href` attribute.

            if (!profileInfoContainer || !vendor) {
                console.warn(
                    "Could not render vendor details. Element or vendor data not found."
                );
                return;
            }

            profileInfoContainer
                .querySelectorAll("span[data-field]")
                .forEach((span) => {
                    const field = span.dataset.field;
                    let value = vendor[field];

                    if (value !== undefined && value !== null) {
                        if (
                            field === "appDate" &&
                            value &&
                            String(value).includes("-")
                        ) {
                            const date = new Date(value + "T00:00:00Z"); // Treat string as UTC
                            value = date.toLocaleDateString("en-US", {
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                                timeZone: "Asia/Manila",
                            });
                        }
                        span.textContent = value;
                    } else {
                        span.textContent = "N/A";
                    }
                });
        },

        populateSectionDropdown(sections) {
            const { sectionDropdown } = MarketApp.elements;
            if (!sectionDropdown) return;

            const vendor = MarketApp.getters.currentVendor(MarketApp.state);
            sectionDropdown.innerHTML = "";

            sections.forEach((section) => {
                const option = document.createElement("option");
                option.value = section;
                option.textContent = section;
                if (vendor && vendor.section === section) {
                    option.selected = true;
                }
                sectionDropdown.appendChild(option);
            });
        },

        updateModal() {
            const { editModal, editModalContent, editVendorForm } =
                MarketApp.elements;
            const { isEditModalOpen, allMarketSections } = MarketApp.state;
            const vendor = MarketApp.getters.currentVendor(MarketApp.state);

            if (isEditModalOpen) {
                if (vendor) {
                    editVendorForm.querySelector(
                        '[data-field="vendorName"]'
                    ).value = vendor.vendorName;
                    editVendorForm.querySelector(
                        '[data-field="stallNumber"]'
                    ).value = vendor.stallNumber;
                    editVendorForm.querySelector(
                        '[data-field="contact"]'
                    ).value = vendor.contact;
                    editVendorForm.querySelector(
                        '[data-field="appDate"]'
                    ).value = vendor.appDate;

                    MarketApp.render.populateSectionDropdown(allMarketSections);
                }
                editModal.classList.remove("hidden");
                setTimeout(() => {
                    editModalContent.classList.remove("scale-95", "opacity-0");
                }, 50);
            } else {
                editModalContent.classList.add("scale-95", "opacity-0");
                setTimeout(() => {
                    editModal.classList.add("hidden");
                }, 300);
            }
        },

        renderReportHeader(data) {
            MarketApp.elements.reportTitle.textContent =
                "Monthly Operations Report";
            MarketApp.elements.reportPeriod.textContent = `For the period of ${data.report_period}`;
        },

        renderReportKPIs(kpis) {
            const kpiData = [
                {
                    label: "Total Collections",
                    value: `₱${kpis.total_collection.toLocaleString("en-US", {
                        minimumFractionDigits: 2,
                    })}`,
                    icon: "fa-hand-holding-dollar",
                },
                {
                    label: "Delinquent Vendors",
                    value: kpis.delinquent_vendors_count,
                    icon: "fa-users-slash",
                },
                {
                    label: "New Vendors",
                    value: kpis.new_vendors,
                    icon: "fa-user-plus",
                },
            ];
            MarketApp.elements.reportKpis.innerHTML = kpiData
                .map(
                    (kpi) => `
                <div class="bg-gray-50 border p-4 rounded-lg flex items-center gap-4">
                    <i class="fas ${kpi.icon} text-2xl text-indigo-500 w-8 text-center"></i>
                    <div>
                        <div class="text-sm text-gray-500">${kpi.label}</div>
                        <div class="text-xl font-bold text-gray-800">${kpi.value}</div>
                    </div>
                </div>
            `
                )
                .join("");
        },

        renderReportCharts(chartData) {
            const utilityColors = {
                Rent: {
                    paid: "rgba(79, 70, 229, 1)",
                    unpaid: "rgba(79, 70, 229, 0.5)",
                },
                Electricity: {
                    paid: "rgba(245, 158, 11, 1)",
                    unpaid: "rgba(245, 158, 11, 0.5)",
                },
                Water: {
                    paid: "rgba(59, 130, 246, 1)",
                    unpaid: "rgba(59, 130, 246, 0.5)",
                },
            };

            const createBarChart = (canvasId, title, data, colors) => {
                const canvas = document.getElementById(canvasId);
                const ctx = canvas?.getContext("2d");
                if (!ctx) return;

                if (MarketApp.state.charts[canvasId]) {
                    MarketApp.state.charts[canvasId].destroy();
                }

                MarketApp.state.charts[canvasId] = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: ["Paid", "Unpaid"],
                        datasets: [
                            {
                                label: title,
                                data: [
                                    parseFloat(data.paid) || 0,
                                    parseFloat(data.unpaid) || 0,
                                ],
                                backgroundColor: [colors.paid, colors.unpaid],
                                borderWidth: 0,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: title,
                                font: { size: 16 },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) =>
                                        "₱" +
                                        new Intl.NumberFormat("en-US").format(
                                            value
                                        ),
                                },
                            },
                        },
                    },
                });
            };

            const utilities = ["Rent", "Electricity", "Water"];
            utilities.forEach((util) => {
                const data = chartData.by_utility[util] || {
                    paid: 0,
                    unpaid: 0,
                };
                createBarChart(
                    `${util.toLowerCase()}Chart`,
                    `${util} Collections`,
                    data,
                    utilityColors[util]
                );
            });
        },

        renderReportTables(breakdown, delinquents) {
            const pivot = {};
            const totals = { Rent: 0, Electricity: 0, Water: 0, grandTotal: 0 };

            breakdown.forEach((item) => {
                if (!pivot[item.section_name]) pivot[item.section_name] = {};
                pivot[item.section_name][item.utility_type] = parseFloat(
                    item.total
                );
            });

            const sections = [
                ...new Set(breakdown.map((i) => i.section_name)),
            ].sort();
            const utilities = ["Rent", "Electricity", "Water"];

            let breakdownHtml = `<table class="min-w-full"><thead><tr class="table-header">
                <th class="px-4 py-2 text-left">Section</th>`;
            utilities.forEach(
                (u) =>
                    (breakdownHtml += `<th class="px-4 py-2 text-right">${u}</th>`)
            );
            breakdownHtml += `<th class="px-4 py-2 text-right font-bold">Section Total</th></tr></thead><tbody>`;

            sections.forEach((s) => {
                let rowTotal = 0;
                breakdownHtml += `<tr class="table-row"><td class="px-4 py-2 font-medium">${s}</td>`;
                utilities.forEach((u) => {
                    const amount = pivot[s][u] || 0;
                    rowTotal += amount;
                    totals[u] += amount;
                    breakdownHtml += `<td class="px-4 py-2 text-right">₱${amount.toLocaleString(
                        "en-US",
                        { minimumFractionDigits: 2 }
                    )}</td>`;
                });
                totals.grandTotal += rowTotal;
                breakdownHtml += `<td class="px-4 py-2 text-right font-bold">₱${rowTotal.toLocaleString(
                    "en-US",
                    { minimumFractionDigits: 2 }
                )}</td></tr>`;
            });
            breakdownHtml += `</tbody><tfoot><tr class="bg-gray-200 font-bold text-gray-800">
                <td class="px-4 py-2 text-left">Grand Total</td>
                <td class="px-4 py-2 text-right">₱${totals.Rent.toLocaleString(
                    "en-US",
                    { minimumFractionDigits: 2 }
                )}</td>
                <td class="px-4 py-2 text-right">₱${totals.Electricity.toLocaleString(
                    "en-US",
                    { minimumFractionDigits: 2 }
                )}</td>
                <td class="px-4 py-2 text-right">₱${totals.Water.toLocaleString(
                    "en-US",
                    { minimumFractionDigits: 2 }
                )}</td>
                <td class="px-4 py-2 text-right">₱${totals.grandTotal.toLocaleString(
                    "en-US",
                    { minimumFractionDigits: 2 }
                )}</td>
            </tr></tfoot></table>`;
            MarketApp.elements.collectionsBreakdownContainer.innerHTML =
                breakdownHtml;

            MarketApp.elements.delinquentVendorsTableBody.innerHTML =
                delinquents
                    .map(
                        (v) => `
                <tr class="table-row">
                    <td class="px-4 py-2">
                        <div>${v.name}</div>
                        <div class="text-xs text-gray-500">Stall: ${
                            v.stall.table_number
                        }</div>
                    </td>
                    <td class="px-4 py-2 text-right text-red-600 font-medium">₱${parseFloat(
                        v.total_due
                    ).toLocaleString("en-US", {
                        minimumFractionDigits: 2,
                    })}</td>
                </tr>
            `
                    )
                    .join("");
        },

        //--Vendor Stall Assignment--//
        renderStallAssignmentView() {
            // Render unassigned vendors
            const vendorSelect = MarketApp.elements.unassignedVendorSelect;
            if (vendorSelect) {
                if (MarketApp.state.unassignedVendors.length > 0) {
                    vendorSelect.innerHTML =
                        '<option value="">-- Select a Vendor --</option>' +
                        MarketApp.state.unassignedVendors
                            .map(
                                (v) =>
                                    `<option value="${v.id}">${v.name}</option>`
                            )
                            .join("");
                } else {
                    vendorSelect.innerHTML =
                        '<option value="">No unassigned vendors</option>';
                }
            }

            // Render section filter
            const sectionFilter = MarketApp.elements.stallSectionFilter;
            if (sectionFilter) {
                sectionFilter.innerHTML =
                    '<option value="">-- Select Section --</option>' +
                    MarketApp.state.allMarketSections
                        .map((s) => `<option value="${s}">${s}</option>`)
                        .join("");
            }

            // Clear available stalls dropdown initially
            const stallSelect = MarketApp.elements.availableStallSelect;
            if (stallSelect) {
                stallSelect.innerHTML =
                    '<option value="">Select a section first...</option>';
            }
        },

        renderAvailableStalls() {
            const stallSelect = MarketApp.elements.availableStallSelect;
            if (stallSelect) {
                if (MarketApp.state.availableStalls.length > 0) {
                    stallSelect.innerHTML =
                        '<option value="">-- Select a Stall --</option>' +
                        MarketApp.state.availableStalls
                            .map(
                                (s) =>
                                    `<option value="${s.id}">${s.table_number}</option>`
                            )
                            .join("");
                } else {
                    stallSelect.innerHTML =
                        '<option value="">No available stalls in MarketApp section</option>';
                }
            }
        },
    },

    cacheElements() {
        MarketApp.elements = {
            navLinks: document.querySelectorAll(".nav-link"),
            sections: document.querySelectorAll(".dashboard-section"),
            dailyCollectionsTableBody: document.getElementById(
                "dailyCollectionsTableBody"
            ),
            bulkPrintBtn: document.getElementById("bulkPrintBtn"),
            selectAllCheckbox: document.getElementById("selectAllCheckbox"),
            vendorListView: document.getElementById("vendorListView"),
            searchInput: document.getElementById("vendorSearchInput"),
            vendorSectionNav: document.getElementById("vendorSectionNav"),
            vendorManagementTableHeader: document.getElementById(
                "vendorManagementTableHeader"
            ),
            tableBody: document.getElementById("vendorTableBody"),
            tableHeaders: document.querySelectorAll(".sortable-header"),
            vendorDetailSection: document.getElementById("vendorDetailSection"),
            backToVendorListBtn: document.getElementById("backToVendorList"),
            profileInfoContainer: document.getElementById(
                "profileInfoContainer"
            ),
            vendorSubSectionContainer: document.getElementById(
                "vendorSubSectionContainer"
            ),
            editVendorBtn: document.getElementById("editVendorBtn"),
            outstandingBalanceLink: document.getElementById(
                "outstandingBalanceLink"
            ),
            outstandingDetailsModal: document.getElementById(
                "outstandingDetailsModal"
            ),
            outstandingBreakdownDetails: document.getElementById(
                "outstandingBreakdownDetails"
            ),
            paymentHistoryLink: document.getElementById("paymentHistoryLink"),
            editModal: document.getElementById("editVendorModal"),
            editModalContent: document.getElementById("editVendorModalContent"),
            closeModalBtn: document.getElementById("closeModalBtn"),
            editVendorForm: document.getElementById("editVendorForm"),
            saveVendorBtn: document.getElementById("saveVendorBtn"),
            cancelEditBtn: document.getElementById("cancelEditBtn"),
            sectionDropdown: document.getElementById("modal_section"),
            staffSectionLoader: document.getElementById("staff-section-loader"),
            vendorInfoModal: document.getElementById("vendorInfoModal"),
            modalContentContainer: document.getElementById(
                "modal-content-container"
            ),
            closeVendorInfoModal: document.getElementById(
                "closeVendorInfoModal"
            ),
            modalLoader: document.getElementById("modal-loader"),

            //--Report--//
            reportMonth: document.getElementById("reportMonth"),
            generateReportBtn: document.getElementById("generateReportBtn"),
            reportLoader: document.getElementById("reportLoader"),
            reportResultContainer: document.getElementById(
                "reportResultContainer"
            ),
            reportTitle: document.getElementById("reportTitle"),
            reportPeriod: document.getElementById("reportPeriod"),
            printReportBtn: document.getElementById("printReportBtn"),
            downloadReportBtn: document.getElementById("downloadReportBtn"),
            reportKpis: document.getElementById("reportKpis"),
            rentChart: document.getElementById("rentChart"),
            electricityChart: document.getElementById("electricityChart"),
            waterChart: document.getElementById("waterChart"),
            collectionsBreakdownContainer: document.getElementById(
                "collectionsBreakdownContainer"
            ),
            delinquentVendorsTableBody: document.getElementById(
                "delinquentVendorsTableBody"
            ),
            noReportDataMessage: document.getElementById("noReportDataMessage"),
            toastContainer: document.getElementById("toastContainer"),
            reportNotes: document.querySelector(".print-area textarea"),

            //--Vendor Stall Assignment--//
            unassignedVendorSelect: document.getElementById(
                "unassignedVendorSelect"
            ),
            availableStallSelect: document.getElementById(
                "availableStallSelect"
            ),
            stallSectionFilter: document.getElementById("stallSectionFilter"),
            assignStallBtn: document.getElementById("assignStallBtn"),
        };
    },

    bindEventListeners() {
        const { methods, elements } = MarketApp;
        window.addEventListener("hashchange", methods.setSectionFromHash);

        elements.navLinks.forEach((link) => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                window.location.hash =
                    e.currentTarget.getAttribute("data-section");
            });
        });

        elements.selectAllCheckbox?.addEventListener("change", (e) => {
            methods.toggleAllCollections(e.target.checked);
        });
        elements.dailyCollectionsTableBody?.addEventListener("click", (e) => {
            const checkbox = e.target.closest(".row-checkbox");
            const printBtn = e.target.closest(".print-receipt-btn");
            if (checkbox) {
                methods.toggleCollectionSelection(
                    parseInt(checkbox.dataset.id, 10),
                    checkbox.checked
                );
            }
            if (printBtn) {
                methods.printIndividualReceipt(
                    parseInt(printBtn.dataset.id, 10)
                );
            }
        });
        elements.bulkPrintBtn?.addEventListener(
            "click",
            methods.printBulkReceipts
        );

        elements.tableHeaders.forEach((header) => {
            header.addEventListener("click", () =>
                methods.sortTable(header.dataset.sortKey)
            );
        });
        elements.searchInput?.addEventListener("input", (e) =>
            methods.updateFilters("search", e.target.value)
        );

        elements.tableBody?.addEventListener("click", (e) => {
            const viewButton = e.target.closest(".view-vendor-btn");
            if (viewButton) {
                methods.showVendorDetails(
                    parseInt(viewButton.dataset.vendorId, 10)
                );
            }
        });

        elements.vendorSectionNav?.addEventListener("click", (e) => {
            const button = e.target.closest(".section-nav-btn");
            if (button) {
                e.preventDefault();
                const sectionName = button.dataset.section;
                methods.updateFilters("section", sectionName);
                elements.vendorSectionNav
                    .querySelectorAll(".section-nav-btn")
                    .forEach((btn) => {
                        btn.classList.remove("active");
                    });
                button.classList.add("active");
            }
        });

        elements.backToVendorListBtn?.addEventListener(
            "click",
            methods.showVendorList
        );
        elements.editVendorBtn?.addEventListener(
            "click",
            methods.openEditModal
        );

        elements.closeModalBtn?.addEventListener(
            "click",
            methods.closeEditModal
        );
        elements.cancelEditBtn?.addEventListener(
            "click",
            methods.closeEditModal
        );
        elements.saveVendorBtn?.addEventListener(
            "click",
            methods.saveVendorChanges
        );
        elements.editModal?.addEventListener("click", (e) => {
            if (e.target === elements.editModal) methods.closeEditModal();
        });

        MarketApp.elements.outstandingBalanceLink?.addEventListener(
            "click",
            (e) => {
                e.preventDefault();
                const vendor = MarketApp.getters.currentVendor(MarketApp.state);
                if (vendor) {
                    // V V V  CHANGE MarketApp URL V V V
                    MarketApp.methods.loadVendorSubSection(
                        `/staff/vendor/${vendor.id}/view-as-vendor-partial`
                    );
                }
            }
        );

        MarketApp.elements.paymentHistoryLink?.addEventListener(
            "click",
            async (e) => {
                e.preventDefault();
                const vendor = MarketApp.getters.currentVendor(MarketApp.state);
                if (vendor) {
                    await MarketApp.methods.loadVendorSubSection(
                        `/staff/vendor/${vendor.id}/payment-history-container`
                    );
                    // Initialize the payment history component after loading
                    await MarketApp.methods.initializePaymentHistoryComponent();
                }
            }
        );

        if (MarketApp.elements.vendorSubSectionContainer) {
            MarketApp.elements.vendorSubSectionContainer.addEventListener(
                "click",
                (e) => {
                    const markPaidBtn = e.target.closest(".mark-paid-btn");
                    if (markPaidBtn) {
                        MarketApp.methods.handlePayment(markPaidBtn);
                    }

                    const monthlyTable = e.target.closest(
                        ".monthly-table-container"
                    );
                    if (monthlyTable) {
                        // Prevent triggering if a button or link inside was clicked
                        if (e.target.closest("a, button")) return;
                        const month = monthlyTable.dataset.month;
                        MarketApp.methods.showOutstandingDetailsForMonth(month);
                    }
                }
            );
        }
        document.body.addEventListener("click", (e) => {
            if (
                e.target.closest(".close-modal-btn") ||
                e.target.classList.contains("modal-container")
            ) {
                MarketApp.state.isOutstandingModalOpen = false;
                MarketApp.render.renderOutstandingModal();
            }
        });
    },

    async loadVendorInfoInModal(url) {
        // 1. Show the modal and the loader
        MarketApp.elements.modalContentContainer.innerHTML =
            MarketApp.elements.modalLoader.outerHTML; // Show loader
        MarketApp.elements.vendorInfoModal.classList.remove("hidden");

        try {
            // 2. Fetch the HTML from the server
            const response = await fetch(url);
            if (!response.ok) throw new Error("Failed to load content.");
            const html = await response.text();

            // 3. Inject the fetched HTML into the modal
            MarketApp.elements.modalContentContainer.innerHTML = html;
        } catch (error) {
            console.error("Failed to load modal content:", error);
            MarketApp.elements.modalContentContainer.innerHTML =
                '<p class="text-red-500 text-center">Sorry, could not load the information.</p>';
            MarketApp.methods.showToast("Failed to load information.", "error");
        }
    },

    async init() {
        MarketApp.cacheElements();
        MarketApp.bindEventListeners();

        // Set the active section from the URL hash immediately
        MarketApp.methods.setSectionFromHash();

        // Hide the preloader to show the app shell instantly
        const preloader = document.getElementById("globalPreloader");
        if (preloader) {
            preloader.style.display = "none";
        }

        // Show a loading state in the table while data is fetched
        MarketApp.state.isLoading = true;
        MarketApp.render.renderDailyCollectionsTable();

        try {
            const [collections, vendors, sections] = await Promise.all([
                MarketApp.database.fetchDailyCollections(),
                MarketApp.database.fetchVendors(),
                MarketApp.database.fetchSections(),
            ]);

            MarketApp.state.dailyCollections = collections;
            MarketApp.state.allVendors = vendors;
            MarketApp.state.allMarketSections = sections;
            MarketApp.methods.setSectionFromHash();
        } catch (error) {
            console.error("Failed to initialize the application:", error);
            MarketApp.methods.showToast(
                "Failed to load initial data.",
                "error"
            );
        } finally {
            // Once data is loaded (or fails), update the UI
            MarketApp.state.isLoading = false;

            // Re-render the components that depend on the fetched data
            MarketApp.render.renderDailyCollectionsTable();

            if (MarketApp.elements.tableBody) {
                MarketApp.elements.vendorManagementTableHeader.textContent =
                    MarketApp.state.filters.section;
                MarketApp.render.populateFilters();
                MarketApp.render.renderTable();
            }
            if (MarketApp.state.activeSection === "dashboardSection") {
                MarketApp.initializeDashboard();
            }
        }
    },
};

document.addEventListener("DOMContentLoaded", () => {
    MarketApp.init();
});

window.addEventListener("load", () => {
    const preloader = document.getElementById("globalPreloader");
    if (preloader) {
        preloader.style.display = "none";
    }
});
