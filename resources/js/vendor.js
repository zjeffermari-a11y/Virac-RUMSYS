// In: js/vendor.js

import Chart from "chart.js/auto";

window.Chart = Chart;

class Dashboard {
    constructor(initialData) {
        this.helpers = {
            formatCurrency: (amount) =>
                new Intl.NumberFormat("en-PH", {
                    style: "currency",
                    currency: "PHP",
                }).format(amount),

            formatDate: (dateInput, options) =>
                new Date(dateInput).toLocaleDateString("en-US", options),

            debounce: (func, delay) => {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), delay);
                };
            },
            showToast: (message, type = "success") => {
                const toastContainer =
                    document.getElementById("toastContainer") || document.body;
                const toast = document.createElement("div");
                const iconClass =
                    type === "success"
                        ? "fa-check-circle"
                        : "fa-exclamation-circle";
                const bgColor =
                    type === "success" ? "bg-green-500" : "bg-red-500";

                toast.className = `fixed top-4 right-4 z-[100] flex items-center gap-3 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-all duration-300`;
                toast.innerHTML = `<i class="fas ${iconClass}"></i><span>${message}</span>`;

                toastContainer.appendChild(toast);
                setTimeout(
                    () => toast.classList.remove("translate-x-full"),
                    100
                );
                setTimeout(() => {
                    toast.classList.add("translate-x-full");
                    toast.addEventListener("transitionend", () =>
                        toast.remove()
                    );
                }, 5000);
            },
        };

        this.elements = this.cacheDOMElements();
        this.state = this.createReactiveState(initialData);
        this.debouncedSearch = this.helpers.debounce(
            this.fetchAndRenderPayments.bind(this),
            300
        );
        this.charts = {};
        this.notifications = {
            list: [],
            unreadCount: 0,
            allNotifications: [],
        };
    }

    cacheDOMElements() {
        return {
            navLinks: document.querySelectorAll(".nav-link"),
            sections: document.querySelectorAll(".dashboard-section"),
            yearDropdown: document.getElementById("yearDropdown"),
            monthDropdown: document.getElementById("monthDropdown"),
            searchInput: document.getElementById("searchInput"),
            noResultsMessage: document.getElementById("noResultsMessage"),
            paymentTableBody: document.getElementById("paymentTableBody"),
            singleTableContainer: document.getElementById(
                "singleTableContainer"
            ),
            paymentAccordionContainer: document.getElementById(
                "paymentAccordionContainer"
            ),
            paymentHistoryBtn: document.getElementById("paymentHistoryBtn"),
            outstandingDetailsModal: document.getElementById(
                "outstandingDetailsModal"
            ),
            outstandingBreakdownDetails: document.getElementById(
                "outstandingBreakdownDetails"
            ),
            billBreakdownModal: document.getElementById("billBreakdownModal"),
            billBreakdownDetails: document.getElementById(
                "billBreakdownDetails"
            ),
            homeContent: document.getElementById("homeContent"),
            analyticsContent: document.getElementById("analyticsContent"),
            electricityChart: document.getElementById("electricityConsumptionChart"),
            paymentStatusChart: document.getElementById("paymentStatusChart"),
            paymentTimelineChart: document.getElementById("paymentTimelineChart"),
            changePasswordForm: document.getElementById("changePasswordForm"),
            changePasswordBtn: document.getElementById("changePasswordBtn"),
            profileSectionImg: document.getElementById("profileSectionImg"),
            profileSectionIcon: document.getElementById("profileSectionIcon"),
            notificationsLoader: document.getElementById("notificationsLoader"),
            notificationsList: document.getElementById("notificationsList"),
            noNotificationsMessage: document.getElementById("noNotificationsMessage"),
            markAllAsReadBtn: document.getElementById("markAllAsReadBtn"),
            unreadCountBadge: document.getElementById("unreadCountBadge"),
            unreadCountText: document.getElementById("unreadCountText"),
        };
    }

    createReactiveState(initialData) {
        const defaultMonth =
            typeof isStaffView !== "undefined" && isStaffView
                ? "all"
                : new Date().getMonth() + 1;
        // Pre-load payment history from server if available
        const initialPaymentHistory = typeof paymentHistoryInitialData !== 'undefined' 
            ? paymentHistoryInitialData 
            : { data: [], total: 0, has_more: false };
        
        const state = {
            searchTerm: "",
            selectedYear: new Date().getFullYear(),
            selectedMonth: defaultMonth,
            isOutstandingModalOpen: false,
            activeSection: "homeSection",
            paymentHistory: [], // Will be set after formatPaymentHistory is available
            paymentHistoryPage: initialPaymentHistory.current_page || 1,
            paymentHistoryHasMore: initialPaymentHistory.has_more || false,
            paymentHistoryTotal: initialPaymentHistory.total || 0,
            paymentHistoryInitialData: initialPaymentHistory, // Store raw data for later formatting
            modalBills: [],
            isLoading: false,
        };

        return new Proxy(state, {
            set: (target, property, value) => {
                target[property] = value;
                if (
                    property === "isOutstandingModalOpen" ||
                    property === "modalBills"
                ) {
                    this.renderOutstandingModal();
                }
                if (property === "activeSection") {
                    this.renderActiveSection();
                    // Update notifications when section changes
                    this.renderNotificationDropdown();
                    // Fetch fresh notifications when section changes
                    this.fetchNotifications();
                    // Load analytics when section is shown
                    if (target.activeSection === "analyticsSection") {
                        this.loadAnalytics();
                    }
                    // Load all notifications when notifications section is shown
                    if (target.activeSection === "notificationsSection") {
                        this.loadAllNotifications();
                        this.setupNotificationsEventListeners();
                    }
                }
                if (property === "paymentHistory") {
                    this.renderPaymentHistory();
                }
                return true;
            },
        });
    }

    async init() {
        const preloader = document.getElementById("globalPreloader");
        const content = document.getElementById("dashboardContent");

        if (preloader) preloader.style.display = "none";
        if (content) content.style.opacity = 1;

        this.setupEventListeners();
        this.setInitialSection();
        if (this.elements.yearDropdown) {
            await this.populateYearFilter();
            this.setDefaultFilters();
            
            // If we have pre-loaded payment history data, format and render it immediately
            if (this.state.paymentHistoryInitialData && this.state.paymentHistoryInitialData.data && this.state.paymentHistoryInitialData.data.length > 0) {
                this.state.paymentHistory = this.formatPaymentHistory(this.state.paymentHistoryInitialData.data);
                this.state.paymentHistoryPage = this.state.paymentHistoryInitialData.current_page || 1;
                this.state.paymentHistoryHasMore = this.state.paymentHistoryInitialData.has_more || false;
                this.state.paymentHistoryTotal = this.state.paymentHistoryInitialData.total || 0;
                this.renderPaymentHistory();
            } else {
                // Otherwise fetch it
                await this.fetchAndRenderPayments(1);
            }
        }
        // Announcements are now shown as notifications in the bell dropdown
        await this.fetchNotifications();
        // Announcement banner removed - announcements now appear as notifications in the bell dropdown
        setInterval(this.checkForUpdates.bind(this), 5000);
        // Poll for notifications every 2 seconds for faster updates
        setInterval(this.fetchNotifications.bind(this), 2000);
        
        // Also fetch immediately when page becomes visible (user switches tabs/windows)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.fetchNotifications();
            }
        });
    }

    // Announcement banner removed - announcements now appear as notifications in the bell dropdown

    async checkForUpdates() {
        try {
            const url =
                typeof isStaffView !== "undefined" && isStaffView
                    ? `/api/staff/vendor/${vendorData.id}/dashboard-data`
                    : `/api/vendor/dashboard-data`;

            const response = await fetch(url);
            if (!response.ok) return;

            const data = await response.json();

            const newDataSignature = JSON.stringify(
                data.outstandingBills.map((b) => ({
                    id: b.id,
                    status: b.status,
                }))
            );
            const oldDataSignature = JSON.stringify(
                outstandingBillsData.map((b) => ({
                    id: b.id,
                    status: b.status,
                }))
            );

            if (newDataSignature !== oldDataSignature) {
                this.helpers.showToast("Your bills have been updated!", "info");
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            console.error("Polling for updates failed:", error);
        }
    }

    renderPaymentHistory() {
        const {
            paymentTableBody,
            noResultsMessage,
            singleTableContainer,
            paymentAccordionContainer,
        } = this.elements;
        if (!paymentTableBody) return;

        paymentTableBody.innerHTML = "";
        noResultsMessage.classList.add("hidden");
        singleTableContainer.classList.remove("hidden");
        paymentAccordionContainer.classList.add("hidden");

        if (
            !this.state.paymentHistory ||
            this.state.paymentHistory.length === 0
        ) {
            noResultsMessage.classList.remove("hidden");
            return;
        }

        const rowsHtml = this.state.paymentHistory
            .map(
                (item) => `
            <tr class="table-row">
                <td data-label="Category" class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">${item.category}</td>
                <td data-label="Period Covered" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">${item.period}</td>
                <td data-label="Bill Amount" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">${item.amount}</td>
                <td data-label="Due Date" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">${item.dueDate}</td>
                <td data-label="Payment Status" class="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <span class="status-badge status-paid">${item.statusText}</span>
                </td>
            </tr>
        `
            )
            .join("");

        paymentTableBody.innerHTML = rowsHtml;
        
        // Add pagination controls if there are more pages
        this.renderPaymentHistoryPagination();
    }
    
    renderPaymentHistoryPagination() {
        // Remove existing pagination if any
        const existingPagination = document.getElementById('paymentHistoryPagination');
        if (existingPagination) {
            existingPagination.remove();
        }
        
        if (!this.state.paymentHistoryHasMore && this.state.paymentHistoryTotal <= 20) {
            return; // No pagination needed
        }
        
        const tableContainer = this.elements.singleTableContainer;
        if (!tableContainer) return;
        
        const paginationHtml = `
            <div id="paymentHistoryPagination" class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing ${this.state.paymentHistory.length} of ${this.state.paymentHistoryTotal} records
                </div>
                ${this.state.paymentHistoryHasMore ? `
                    <button 
                        id="loadMorePaymentsBtn" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        <i class="fas fa-arrow-down mr-2"></i>Load More
                    </button>
                ` : ''}
            </div>
        `;
        
        tableContainer.insertAdjacentHTML('beforeend', paginationHtml);
        
        // Add event listener for load more button
        const loadMoreBtn = document.getElementById('loadMorePaymentsBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.fetchAndRenderPayments(this.state.paymentHistoryPage + 1);
            });
        }
    }

    async fetchAndRenderPayments(page = 1) {
        const { selectedYear, selectedMonth, searchTerm } = this.state;
        const query = new URLSearchParams({
            year: selectedYear,
            month: selectedMonth,
            search: searchTerm,
            page: page,
        });
        let url =
            typeof isStaffView !== "undefined" && isStaffView
                ? `/api/staff/vendors/${vendorData.id}/payment-history-filtered?${query}`
                : `/api/vendor/payments?${query}`;

        try {
            // Show loading state
            const tableBody = this.elements.paymentTableBody;
            if (tableBody && page === 1) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-indigo-500"></i> Loading...</td></tr>`;
            }

            const response = await fetch(url);
            if (!response.ok)
                throw new Error(`HTTP error! Status: ${response.status}`);
            const data = await response.json();
            
            // Handle paginated response
            if (data.data) {
                const formattedData = this.formatPaymentHistory(data.data);
                if (page === 1) {
                    // First page - replace data
                    this.state.paymentHistory = formattedData;
                } else {
                    // Subsequent pages - append data
                    this.state.paymentHistory = [...this.state.paymentHistory, ...formattedData];
                }
                this.state.paymentHistoryPage = data.current_page || 1;
                this.state.paymentHistoryHasMore = data.has_more || false;
                this.state.paymentHistoryTotal = data.total || 0;
            } else {
                // Fallback for non-paginated responses
                this.state.paymentHistory = this.formatPaymentHistory(data);
                this.state.paymentHistoryPage = 1;
                this.state.paymentHistoryHasMore = false;
                this.state.paymentHistoryTotal = data.length || 0;
            }
            
            this.renderPaymentHistory();
        } catch (error) {
            console.error("Failed to fetch payment history:", error);
            if (page === 1) {
                this.state.paymentHistory = [];
            }
            const tableBody = this.elements.paymentTableBody;
            if (tableBody && page === 1) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-red-500">Failed to load payment history. Please try again.</td></tr>`;
            }
        }
    }

    async populateYearFilter() {
        try {
            const response = await fetch("/api/vendor/payment-years");
            if (!response.ok) throw new Error("Failed to fetch years");
            const years = await response.json();
            if (years.length > 0) {
                this.elements.yearDropdown.innerHTML = years
                    .sort((a, b) => b - a)
                    .map((year) => `<option value="${year}">${year}</option>`)
                    .join("");
            } else {
                throw new Error("No years returned");
            }
        } catch (error) {
            const currentYear = new Date().getFullYear();
            this.elements.yearDropdown.innerHTML = `<option value="${currentYear}">${currentYear}</option>`;
        }
    }

    formatPaymentHistory(history) {
        return (history || [])
            .filter((item) => item.status === "paid")
            .map((item) => {
                const paymentDate = item.payment
                    ? new Date(item.payment.payment_date)
                    : null;
                return {
                    id: item.id,
                    year: paymentDate
                        ? paymentDate.getFullYear()
                        : new Date(item.period_start).getFullYear(),
                    month: paymentDate
                        ? paymentDate.getMonth() + 1
                        : new Date(item.period_start).getMonth() + 1,
                    category: item.utility_type.toUpperCase(),

                    period: `${this.helpers.formatDate(item.period_start, {
                        month: "short",
                        day: "2-digit",
                    })} - ${this.helpers.formatDate(item.period_end, {
                        month: "short",
                        day: "2-digit",
                        year: "numeric",
                    })}`,
                    amount: this.helpers.formatCurrency(item.payment.amount_paid), 
                    dueDate: this.helpers.formatDate(item.due_date, {
                        month: "short",
                        day: "2-digit",
                        year: "numeric",
                    }),
                    amountAfterDue: this.helpers.formatCurrency(
                        item.payment ? item.payment.amount_paid : item.amount
                    ),
                    disconnectionDate: item.disconnection_date
                        ? this.helpers.formatDate(item.disconnection_date, {
                              month: "short",
                              day: "numeric",
                              year: "numeric",
                          })
                        : "-",
                    status: "paid",
                    statusText: paymentDate
                        ? `Paid on ${this.helpers.formatDate(paymentDate, {
                              month: "short",
                              day: "numeric",
                          })}`
                        : "Paid",
                };
            });
    }

    renderActiveSection() {
        this.elements.sections.forEach((section) =>
            section.classList.toggle(
                "active",
                section.id === this.state.activeSection
            )
        );
        this.elements.navLinks.forEach((link) =>
            link.classList.toggle(
                "active",
                link.getAttribute("data-section") === this.state.activeSection
            )
        );
    }

    setDefaultFilters() {
        this.elements.yearDropdown.value = this.state.selectedYear;
        this.elements.monthDropdown.value = this.state.selectedMonth;
    }

    renderOutstandingModal() {
        // Safety check: ensure elements are initialized
        if (!this.elements || !this.elements.outstandingDetailsModal) {
            console.warn("Modal elements not yet initialized");
            return;
        }
        
        const modal = this.elements.outstandingDetailsModal;
        if (!modal) return;

        if (this.state.isOutstandingModalOpen) {
            const detailsBody = this.elements.outstandingBreakdownDetails;
            const billsToDisplay = this.state.modalBills || [];

            if (billsToDisplay.length > 0) {
                // Initialize totals
                let totalOriginal = 0;
                let totalDiscount = 0;
                let totalSurcharge = 0;
                let totalAmount = 0;

                detailsBody.innerHTML = billsToDisplay
                    .map((bill) => {
                        const originalAmount = parseFloat(bill.original_amount);
                        const penaltyApplied = parseFloat(bill.penalty_applied || 0);
                        const discountApplied = parseFloat(bill.discount_applied || 0);

                        let baseAmountForCalc = originalAmount;
                        let detailsHtml = `<strong>${this.helpers.formatCurrency(originalAmount)}</strong>`;

                        // Calculate Original Payment with formula
                        if (bill.utility_type === "Rent" && stallData) {
                            const dailyRate = parseFloat(stallData.daily_rate || 0);
                            const area = parseFloat(stallData.area || 0);
                            
                            // Check if vendor is in Dry Section (has area)
                            if (area > 0) {
                                // Dry Section: (area × rate_per_sqm) × 30
                                const calculatedAmount = (area * dailyRate) * 30;
                                baseAmountForCalc = calculatedAmount;
                                detailsHtml = `(${area.toFixed(2)} m² x ${this.helpers.formatCurrency(dailyRate)}) x 30 days = <strong>${this.helpers.formatCurrency(calculatedAmount)}</strong>`;
                            } else {
                                // Regular Section: daily_rate × 30
                                const calculatedAmount = dailyRate * 30;
                                baseAmountForCalc = calculatedAmount;
                                detailsHtml = `${this.helpers.formatCurrency(dailyRate)} x 30 days = <strong>${this.helpers.formatCurrency(calculatedAmount)}</strong>`;
                            }
                        } else if (bill.utility_type === "Water") {
                            const daysInMonth = new Date(bill.period_end).getDate();
                            // Get rate from database, or calculate backwards from stored amount
                            let waterRate = utilityRatesData?.Water?.rate || 0;
                            if (waterRate === 0 && originalAmount > 0 && daysInMonth > 0) {
                                // Calculate rate backwards from stored amount
                                waterRate = originalAmount / daysInMonth;
                            }
                            const calculatedAmount = waterRate * daysInMonth;
                            baseAmountForCalc = calculatedAmount;
                            // Always display as formula: rate x days = amount
                            detailsHtml = `${this.helpers.formatCurrency(waterRate)} x ${daysInMonth} days = <strong>${this.helpers.formatCurrency(calculatedAmount)}</strong>`;
                        } else if (bill.utility_type === "Electricity") {
                            // Priority 1: Use bill.consumption if available (most accurate)
                            // Priority 2: Calculate from current_reading - previous_reading
                            // Priority 3: Only calculate backwards if both are missing
                            let consumption = null;
                            if (bill.consumption !== null && bill.consumption !== undefined && !isNaN(parseFloat(bill.consumption))) {
                                consumption = parseFloat(bill.consumption);
                            } else if (bill.current_reading !== null && bill.previous_reading !== null) {
                                consumption = (parseFloat(bill.current_reading) || 0) - (parseFloat(bill.previous_reading) || 0);
                            } else {
                                consumption = 0;
                            }
                            
                            // Get rate: Priority 1: bill.rate, Priority 2: database rate
                            let electricityRate = bill.rate || utilityRatesData?.Electricity?.rate || 0;
                            
                            // Only calculate backwards if we're missing actual data
                            if (consumption === 0 && originalAmount > 0) {
                                // If we have a rate, calculate consumption from amount
                                if (electricityRate > 0) {
                                    consumption = originalAmount / electricityRate;
                                } else {
                                    // If no rate, try to get it from database
                                    electricityRate = utilityRatesData?.Electricity?.rate || 0;
                                    if (electricityRate > 0) {
                                        consumption = originalAmount / electricityRate;
                                    }
                                }
                            } else if (electricityRate === 0 && originalAmount > 0 && consumption > 0) {
                                // If we have consumption but no rate, calculate rate from amount
                                electricityRate = originalAmount / consumption;
                            }
                            
                            // Calculate amount from consumption × rate
                            const calculatedAmount = consumption * electricityRate;
                            baseAmountForCalc = calculatedAmount > 0 ? calculatedAmount : originalAmount;
                            
                            // Always display as formula: (consumption kWh) x rate = amount
                            detailsHtml = `(${consumption.toFixed(2)} kWh) x ${this.helpers.formatCurrency(electricityRate)} = <strong>${this.helpers.formatCurrency(calculatedAmount > 0 ? calculatedAmount : originalAmount)}</strong>`;
                        }

                        // ===== Discount Column with Formula =====
                        let discountHtml = '-';
                        if (discountApplied > 0 && billingSettingsData) {
                            const settings = billingSettingsData[bill.utility_type];
                            if (settings && settings.discount_rate) {
                                const discountRate = parseFloat(settings.discount_rate);
                                discountHtml = `<strong class="text-green-600">${this.helpers.formatCurrency(baseAmountForCalc)} x ${(discountRate * 100).toFixed(0)}% = -${this.helpers.formatCurrency(discountApplied)}</strong>`;
                            } else {
                                discountHtml = `<strong class="text-green-600">-${this.helpers.formatCurrency(discountApplied)}</strong>`;
                            }
                        }

                        // ===== Surcharge/Penalty Column with Formula =====
                        let penaltyHtml = '-';
                        if (penaltyApplied > 0 && billingSettingsData) {
                            const settings = billingSettingsData[bill.utility_type];
                            if (settings && bill.utility_type === 'Rent') {
                                // For Rent: Show surcharge + interest breakdown
                                const surchargeRate = parseFloat(settings.surcharge_rate || 0);
                                const interestRate = parseFloat(settings.monthly_interest_rate || 0);
                                const interestMonths = parseInt(bill.interest_months || 0);
                                
                                const surchargeAmount = baseAmountForCalc * surchargeRate;
                                const interestAmount = baseAmountForCalc * interestRate * interestMonths;
                                
                                penaltyHtml = `<strong class="text-red-600">Surcharge (${(surchargeRate * 100).toFixed(0)}%): + ${this.helpers.formatCurrency(surchargeAmount)}<br>`;
                                penaltyHtml += `Interest (${(interestRate * 100).toFixed(0)}% x ${interestMonths} mo): + ${this.helpers.formatCurrency(interestAmount)}<br>`;
                                penaltyHtml += `--- Total Penalty: + ${this.helpers.formatCurrency(penaltyApplied)}</strong>`;
                            } else if (settings && settings.penalty_rate) {
                                // For Utilities with penalty rate configured
                                const penaltyRate = parseFloat(settings.penalty_rate);
                                penaltyHtml = `<strong class="text-red-600">Penalty (${(penaltyRate * 100).toFixed(0)}%): + ${this.helpers.formatCurrency(penaltyApplied)}</strong>`;
                            } else {
                                // Fallback: just show the amount
                                penaltyHtml = `<strong class="text-red-600">+ ${this.helpers.formatCurrency(penaltyApplied)}</strong>`;
                            }
                        }

                        const finalTotal = parseFloat(bill.display_amount_due || 0);
                        
                        // Add to totals
                        totalOriginal += baseAmountForCalc;
                        totalDiscount += discountApplied;
                        totalSurcharge += penaltyApplied;
                        totalAmount += finalTotal;
                        
                        const categoryText = bill.utility_type === 'Rent'
                            ? `Rent<br><span class="text-sm font-normal text-gray-500">(Standard Payment)</span>`
                            : bill.utility_type;

                        return `
                            <tr class="text-lg border-b border-gray-200">
                                <td class="px-4 py-3 align-top font-bold">${categoryText}</td>
                                <td class="px-4 py-3 align-top">${detailsHtml}</td>
                                <td class="px-4 py-3 align-top">${discountHtml}</td>
                                <td class="px-4 py-3 align-top">${penaltyHtml}</td>
                                <td class="px-4 py-3 align-top font-bold text-market-primary">${this.helpers.formatCurrency(finalTotal)}</td>
                            </tr>
                        `;
                    })
                    .join("");

                // Update footer totals
                const totalOriginalEl = document.getElementById('totalOriginalPayment');
                const totalDiscountEl = document.getElementById('totalDiscount');
                const totalSurchargeEl = document.getElementById('totalSurcharge');
                const totalAmountDueEl = document.getElementById('totalAmountDue');

                if (totalOriginalEl) totalOriginalEl.textContent = this.helpers.formatCurrency(totalOriginal);
                if (totalDiscountEl) totalDiscountEl.textContent = totalDiscount > 0 ? `-${this.helpers.formatCurrency(totalDiscount)}` : this.helpers.formatCurrency(0);
                if (totalSurchargeEl) totalSurchargeEl.textContent = this.helpers.formatCurrency(totalSurcharge);
                if (totalAmountDueEl) totalAmountDueEl.textContent = this.helpers.formatCurrency(totalAmount);
            } else {
                detailsBody.innerHTML = `<tr><td colspan="5" class="text-center py-4">No bills for this month.</td></tr>`;
                
                // Reset footer totals
                const totalOriginalEl = document.getElementById('totalOriginalPayment');
                const totalDiscountEl = document.getElementById('totalDiscount');
                const totalSurchargeEl = document.getElementById('totalSurcharge');
                const totalAmountDueEl = document.getElementById('totalAmountDue');

                if (totalOriginalEl) totalOriginalEl.textContent = this.helpers.formatCurrency(0);
                if (totalDiscountEl) totalDiscountEl.textContent = this.helpers.formatCurrency(0);
                if (totalSurchargeEl) totalSurchargeEl.textContent = this.helpers.formatCurrency(0);
                if (totalAmountDueEl) totalAmountDueEl.textContent = this.helpers.formatCurrency(0);
            }
            modal.classList.remove("hidden");
        } else {
            modal.classList.add("hidden");
        }
    }

    setupEventListeners() {
        this.elements.navLinks.forEach((link) => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                const sectionId = link.getAttribute("data-section");
                history.pushState(
                    { section: sectionId },
                    "",
                    link.getAttribute("href")
                );
                this.state.activeSection = sectionId;
            });
        });
        window.addEventListener("popstate", () => this.setInitialSection());

        this.elements.homeContent?.addEventListener("click", (e) => {
            const markPaidBtn = e.target.closest(".mark-paid-btn");
            const monthlyTable = e.target.closest(".monthly-table-container");

            if (markPaidBtn) {
                this.handlePayment(markPaidBtn);
            } else if (monthlyTable) {
                if (e.target.closest("a, button")) return;
                const month = monthlyTable.dataset.month;
                this.showOutstandingDetailsForMonth(month);
            }
        });

        document.body.addEventListener("click", (e) => {
            if (
                e.target.closest(".close-modal-btn") ||
                e.target.classList.contains("modal-container")
            ) {
                this.state.isOutstandingModalOpen = false;
            }
        });

        this.elements.yearDropdown?.addEventListener("change", (e) => {
            this.state.selectedYear = e.target.value;
            this.state.paymentHistoryPage = 1; // Reset to first page
            this.fetchAndRenderPayments(1);
        });

        this.elements.monthDropdown?.addEventListener("change", (e) => {
            this.state.selectedMonth = e.target.value;
            this.state.paymentHistoryPage = 1; // Reset to first page
            this.fetchAndRenderPayments(1);
        });

        this.elements.searchInput?.addEventListener("input", (e) => {
            this.state.searchTerm = e.target.value;
            this.state.paymentHistoryPage = 1; // Reset to first page
            this.debouncedSearch();
        });

        // Notification bell click handler
        document.addEventListener("click", (e) => {
            const bellButton = e.target.closest(".notificationBell button");
            if (bellButton) {
                e.stopPropagation();
                // Find the dropdown in the active section
                const activeSection = document.querySelector(".dashboard-section.active");
                if (activeSection) {
                    const dropdown = activeSection.querySelector(".notificationDropdown");
                    if (dropdown) {
                        const isHidden = dropdown.classList.toggle("hidden");
                        if (!isHidden && this.notifications.unreadCount > 0) {
                            this.markNotificationsAsRead();
                        }
                    }
                }
            }

            // Close dropdown when clicking outside
            const activeSection = document.querySelector(".dashboard-section.active");
            if (activeSection) {
                const dropdown = activeSection.querySelector(".notificationDropdown");
                const bell = activeSection.querySelector(".notificationBell");
                if (dropdown && !dropdown.classList.contains("hidden") && bell && !bell.contains(e.target)) {
                    dropdown.classList.add("hidden");
                }
            }
        });


        // Change password form handler
        if (this.elements.changePasswordForm) {
            this.elements.changePasswordForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const btn = this.elements.changePasswordBtn;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';

                const formData = new FormData(this.elements.changePasswordForm);
                const data = {
                    current_password: formData.get("current_password"),
                    password: formData.get("password"),
                    password_confirmation: formData.get("password_confirmation"),
                };

                try {
                    const response = await fetch("/api/user-settings/change-password", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        credentials: "include",
                        body: JSON.stringify(data),
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.helpers.showToast(result.message || "Password changed successfully!", "success");
                        this.elements.changePasswordForm.reset();
                    } else {
                        const errorMsg = result.message || result.errors?.current_password?.[0] || result.errors?.password?.[0] || "Failed to change password";
                        this.helpers.showToast(errorMsg, "error");
                    }
                } catch (error) {
                    console.error("Password change error:", error);
                    this.helpers.showToast("An error occurred. Please try again.", "error");
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        }
    }

    showOutstandingDetailsForMonth(month) {
        const billsForMonth = outstandingBillsData.filter((bill) => {
            const periodDate = new Date(bill.period_start);
            let billGroupMonthYear;

            if (
                bill.utility_type === "Water" ||
                bill.utility_type === "Electricity"
            ) {
                periodDate.setMonth(periodDate.getMonth() + 1);
            }

            billGroupMonthYear = periodDate.toLocaleString("en-US", {
                month: "long",
                year: "numeric",
            });

            return billGroupMonthYear === month;
        });

        this.state.modalBills = billsForMonth;
        this.state.isOutstandingModalOpen = true;
    }

    async handlePayment(button) {
        const billingId = parseInt(button.dataset.billingId, 10);
        if (
            confirm(
                "Are you sure you want to record this payment? This action cannot be undone."
            )
        ) {
            button.disabled = true;
            button.innerHTML =
                '<i class="fas fa-spinner fa-spin mr-2"></i><span>Processing...</span>';

            try {
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
                    }
                );
                if (!response.ok) throw new Error("Failed to record payment.");

                this.helpers.showToast(
                    "Payment recorded successfully! Refreshing...",
                    "success"
                );

                setTimeout(() => {
                    location.reload();
                }, 1500);
            } catch (error) {
                console.error("Payment Error:", error);
                this.helpers.showToast(
                    "An error occurred. Please try again.",
                    "error"
                );
                button.disabled = false;
                button.innerHTML =
                    '<i class="fas fa-check-circle mr-2"></i><span>Record Payment</span>';
            }
        }
    }

    setInitialSection() {
        const hash = window.location.hash.substring(1) || "homeSection";
        this.state.activeSection = hash;
        // Load analytics if analytics section is active
        if (hash === "analyticsSection") {
            this.loadAnalytics();
        }
    }

    async loadAnalytics() {
        try {
            const url = typeof isStaffView !== "undefined" && isStaffView
                ? `/api/staff/vendor/${vendorData.id}/analytics`
                : `/api/vendor/analytics`;

            const response = await fetch(url);
            if (!response.ok) throw new Error("Failed to fetch analytics data");
            
            const data = await response.json();
            this.renderElectricityChart(data.electricity);
            this.renderPaymentStatusChart(data.paymentTracking);
            this.renderPaymentTimelineChart(data.paymentTimeline);
        } catch (error) {
            console.error("Failed to load analytics:", error);
            this.helpers.showToast("Failed to load analytics data", "error");
        }
    }

    renderElectricityChart(data) {
        const ctx = this.elements.electricityChart?.getContext("2d");
        if (!ctx) return;

        // Destroy existing chart if it exists
        if (this.charts.electricity) {
            this.charts.electricity.destroy();
        }

        this.charts.electricity = new Chart(ctx, {
            type: "line",
            data: {
                labels: data.labels || [],
                datasets: [{
                    label: "Electricity Consumption (kWh)",
                    data: data.data || [],
                    borderColor: "rgb(255, 165, 0)",
                    backgroundColor: "rgba(255, 165, 0, 0.1)",
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: "rgb(255, 165, 0)",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                        callbacks: {
                            label: (context) => {
                                return `${context.dataset.label}: ${context.parsed.y.toFixed(2)} kWh`;
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Consumption (kWh)",
                        },
                    },
                    x: {
                        title: {
                            display: true,
                            text: "Month",
                        },
                    },
                },
            },
        });
    }

    renderPaymentStatusChart(data) {
        const ctx = this.elements.paymentStatusChart?.getContext("2d");
        if (!ctx) return;

        // Update stats
        const onTimeEl = document.getElementById("onTimeCount");
        const lateEl = document.getElementById("lateCount");
        if (onTimeEl) onTimeEl.textContent = data.onTime || 0;
        if (lateEl) lateEl.textContent = data.late || 0;

        // Destroy existing chart if it exists
        if (this.charts.paymentStatus) {
            this.charts.paymentStatus.destroy();
        }

        this.charts.paymentStatus = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["On Time", "Late"],
                datasets: [{
                    data: [data.onTime || 0, data.late || 0],
                    backgroundColor: [
                        "rgba(34, 197, 94, 0.8)",
                        "rgba(239, 68, 68, 0.8)",
                    ],
                    borderColor: [
                        "rgb(34, 197, 94)",
                        "rgb(239, 68, 68)",
                    ],
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: "bottom",
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.label || "";
                                const value = context.parsed || 0;
                                const total = data.total || 1;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    renderPaymentTimelineChart(data) {
        const ctx = this.elements.paymentTimelineChart?.getContext("2d");
        if (!ctx) return;

        // Destroy existing chart if it exists
        if (this.charts.paymentTimeline) {
            this.charts.paymentTimeline.destroy();
        }

        // Format labels to be more readable
        const formattedLabels = (data.labels || []).map(label => {
            const [year, month] = label.split("-");
            const date = new Date(year, month - 1);
            return date.toLocaleDateString("en-US", { month: "short", year: "numeric" });
        });

        this.charts.paymentTimeline = new Chart(ctx, {
            type: "bar",
            data: {
                labels: formattedLabels,
                datasets: [
                    {
                        label: "On Time",
                        data: data.onTime || [],
                        backgroundColor: "rgba(34, 197, 94, 0.8)",
                        borderColor: "rgb(34, 197, 94)",
                        borderWidth: 1,
                    },
                    {
                        label: "Late",
                        data: data.late || [],
                        backgroundColor: "rgba(239, 68, 68, 0.8)",
                        borderColor: "rgb(239, 68, 68)",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: "Month",
                        },
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                        },
                        title: {
                            display: true,
                            text: "Number of Payments",
                        },
                    },
                },
            },
        });
    }

    // Announcement banner removed - announcements now appear as notifications in the bell dropdown

    async fetchNotifications() {
        try {
            const response = await fetch("/notifications/fetch");
            if (!response.ok) return;

            const data = await response.json();
            this.notifications.list = data.notifications || [];
            this.notifications.unreadCount = data.unread_count || 0;
            this.renderNotificationDropdown();
        } catch (error) {
            console.error("Error fetching notifications:", error);
        }
    }

    renderNotificationDropdown() {
        // Find notification elements in the active section
        const activeSection = document.querySelector(".dashboard-section.active");
        if (!activeSection) return;

        const notificationList = activeSection.querySelector(".notificationList");
        const notificationDot = activeSection.querySelector(".notificationDot");

        if (!notificationList || !notificationDot) return;

        // Show/hide notification dot
        notificationDot.classList.toggle("hidden", this.notifications.unreadCount === 0);

        if (this.notifications.list.length === 0) {
            notificationList.innerHTML = `<p class="text-center text-gray-500 p-4">You have no new notifications.</p>`;
            return;
        }

        notificationList.innerHTML = this.notifications.list
            .map((notification) => {
                // Try to parse message as JSON, fallback to plain text
                let notificationText = notification.title || "Notification";
                try {
                    if (notification.message) {
                        const data = JSON.parse(notification.message);
                        notificationText = data.text || data.message || notification.title || notificationText;
                    }
                } catch (e) {
                    // If not JSON, use message as plain text
                    notificationText = notification.message || notification.title || notificationText;
                }
                
                const isUnread = notification.read_at === null;
                const timeAgo = this.formatTimeAgo(notification.created_at);

                return `
                    <div class="block p-3 transition-colors hover:bg-gray-100 ${isUnread ? "bg-blue-50" : ""}">
                        <div class="flex items-start">
                            ${isUnread ? '<div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 mr-3 flex-shrink-0"></div>' : '<div class="w-2 h-2 mr-3"></div>'}
                            <div class="flex-grow">
                                <p class="text-sm text-gray-800">${this.escapeHtml(notificationText)}</p>
                                <p class="text-xs text-blue-600 font-semibold mt-1">${timeAgo}</p>
                            </div>
                        </div>
                    </div>
                `;
            })
            .join("");
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const seconds = Math.floor((new Date() - date) / 1000);

        let interval = seconds / 31536000;
        if (interval >= 1) {
            const value = Math.floor(interval);
            return value === 1 ? `${value} year ago` : `${value} years ago`;
        }
        interval = seconds / 2592000;
        if (interval >= 1) {
            const value = Math.floor(interval);
            return value === 1 ? `${value} month ago` : `${value} months ago`;
        }
        interval = seconds / 86400;
        if (interval >= 1) {
            const value = Math.floor(interval);
            return value === 1 ? `${value} day ago` : `${value} days ago`;
        }
        interval = seconds / 3600;
        if (interval >= 1) {
            const value = Math.floor(interval);
            return value === 1 ? `${value} hour ago` : `${value} hours ago`;
        }
        interval = seconds / 60;
        if (interval >= 1) {
            const value = Math.floor(interval);
            return value === 1 ? `${value} minute ago` : `${value} minutes ago`;
        }
        return "Just now";
    }

    async loadAllNotifications() {
        const loader = document.getElementById("notificationsLoader");
        const list = document.getElementById("notificationsList");
        const noMessage = document.getElementById("noNotificationsMessage");

        if (loader) loader.classList.remove("hidden");
        if (list) list.classList.add("hidden");
        if (noMessage) noMessage.classList.add("hidden");

        try {
            // Fetch both notifications and announcements in parallel with timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

            const [notificationsResponse, announcementsResponse] = await Promise.all([
                fetch("/notifications/fetch-all", {
                    signal: controller.signal,
                    credentials: "include",
                }),
                fetch("/api/announcements/all-for-user", {
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
                    },
                    credentials: "include",
                    signal: controller.signal,
                }).catch(() => ({ ok: false, json: async () => [] })) // Gracefully handle announcements failure
            ]);

            clearTimeout(timeoutId);

            if (!notificationsResponse.ok) throw new Error("Failed to fetch notifications");

            const notificationsData = await notificationsResponse.json();
            const announcements = announcementsResponse.ok ? await announcementsResponse.json() : [];

            // Combine notifications and announcements
            const allItems = [];
            
            // Add regular notifications
            (notificationsData.notifications || []).forEach(notif => {
                allItems.push({
                    ...notif,
                    type: 'notification',
                });
            });

            // Add announcements (convert to notification-like format)
            if (Array.isArray(announcements)) {
                announcements.forEach(announcement => {
                    allItems.push({
                        id: `announcement_${announcement.id}`,
                        title: announcement.title,
                        message: announcement.content,
                        created_at: announcement.created_at,
                        read_at: announcement.is_dismissed ? announcement.created_at : null, // Treat dismissed as read
                        sender_name: null,
                        type: 'announcement',
                        announcement_id: announcement.id,
                        is_dismissed: announcement.is_dismissed,
                    });
                });
            }

            // Sort by created_at descending (newest first)
            allItems.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            this.notifications.allNotifications = allItems;
            this.notifications.unreadCount = notificationsData.unread_count || 0;

            this.renderNotificationsList();
        } catch (error) {
            console.error("Error loading notifications:", error);
            if (error.name === 'AbortError') {
                this.helpers.showToast("Request timed out. Please try again.", "error");
            } else {
                this.helpers.showToast("Failed to load notifications", "error");
            }
            // Show empty state on error
            if (noMessage) {
                noMessage.classList.remove("hidden");
                noMessage.innerHTML = `
                    <i class="fas fa-exclamation-triangle text-4xl mb-4 text-yellow-400"></i>
                    <p class="text-lg">Failed to load notifications.</p>
                    <button onclick="location.reload()" class="mt-4 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg">
                        Retry
                    </button>
                `;
            }
        } finally {
            if (loader) loader.classList.add("hidden");
        }
    }

    renderNotificationsList() {
        const list = document.getElementById("notificationsList");
        const noMessage = document.getElementById("noNotificationsMessage");
        const unreadBadge = document.getElementById("unreadCountBadge");
        const unreadText = document.getElementById("unreadCountText");

        if (!list || !noMessage) return;

        const notifications = this.notifications.allNotifications || [];

        if (notifications.length === 0) {
            list.classList.add("hidden");
            noMessage.classList.remove("hidden");
            if (unreadBadge) unreadBadge.classList.add("hidden");
            return;
        }

        list.classList.remove("hidden");
        noMessage.classList.add("hidden");

        // Update unread badge
        if (unreadBadge && this.notifications.unreadCount > 0) {
            unreadBadge.classList.remove("hidden");
            if (unreadText) unreadText.textContent = this.notifications.unreadCount;
        } else if (unreadBadge) {
            unreadBadge.classList.add("hidden");
        }

        list.innerHTML = notifications.map((notification) => {
            const isUnread = !notification.read_at;
            const isAnnouncement = notification.type === 'announcement';
            const isDismissed = notification.is_dismissed;
            const timeAgo = this.formatTimeAgo(notification.created_at);
            const formattedDate = new Date(notification.created_at).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Parse message if it's JSON
            let messageText = notification.message || notification.title || "Notification";
            try {
                const parsed = JSON.parse(notification.message);
                messageText = parsed.text || parsed.message || notification.title || messageText;
            } catch (e) {
                // Not JSON, use as is
            }

            // Determine background color
            let bgColor = 'bg-white';
            if (isUnread && !isDismissed) {
                bgColor = 'bg-blue-50 border-blue-200';
            } else if (isDismissed) {
                bgColor = 'bg-gray-50 border-gray-300';
            }

            return `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow ${bgColor}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-start gap-3">
                                ${isUnread && !isDismissed ? '<div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>' : ''}
                                ${isDismissed ? '<div class="w-2 h-2 bg-gray-400 rounded-full mt-2 flex-shrink-0"></div>' : ''}
                                ${!isUnread && !isDismissed ? '<div class="w-2 h-2 mr-3"></div>' : ''}
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        ${isAnnouncement ? '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-indigo-100 text-indigo-800"><i class="fas fa-bullhorn mr-1"></i>Announcement</span>' : ''}
                                        ${isDismissed ? '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-200 text-gray-600"><i class="fas fa-check-circle mr-1"></i>Dismissed</span>' : ''}
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-1">${this.escapeHtml(notification.title || 'Notification')}</h3>
                                    <p class="text-sm text-gray-600 mb-2">${this.escapeHtml(messageText)}</p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                        <span><i class="fas fa-clock mr-1"></i>${timeAgo}</span>
                                        <span><i class="fas fa-calendar mr-1"></i>${formattedDate}</span>
                                        ${notification.sender_name ? `<span><i class="fas fa-user mr-1"></i>From: ${this.escapeHtml(notification.sender_name)}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${isUnread && !isAnnouncement ? `
                            <button class="mark-notification-read-btn ml-4 text-indigo-600 hover:text-indigo-800 transition-colors" 
                                    data-id="${notification.id}" 
                                    title="Mark as read">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join("");

        // Attach event listeners to mark as read buttons
        list.querySelectorAll(".mark-notification-read-btn").forEach(btn => {
            btn.addEventListener("click", async (e) => {
                const notificationId = e.currentTarget.dataset.id;
                await this.markSingleNotificationAsRead(notificationId);
            });
        });
    }

    async markSingleNotificationAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                },
            });

            if (response.ok) {
                // Update local state
                this.notifications.allNotifications = this.notifications.allNotifications.map(n => {
                    if (n.id == notificationId) {
                        return { ...n, read_at: new Date().toISOString() };
                    }
                    return n;
                });
                this.notifications.unreadCount = Math.max(0, this.notifications.unreadCount - 1);
                this.renderNotificationsList();
            }
        } catch (error) {
            console.error("Failed to mark notification as read:", error);
        }
    }

    setupNotificationsEventListeners() {
        const markAllBtn = document.getElementById("markAllAsReadBtn");
        if (markAllBtn && !markAllBtn.dataset.listenerAttached) {
            markAllBtn.dataset.listenerAttached = "true";
            markAllBtn.addEventListener("click", async () => {
                if (this.notifications.unreadCount === 0) {
                    this.helpers.showToast("All notifications are already read", "info");
                    return;
                }

                try {
                    const response = await fetch("/notifications/mark-as-read", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                        },
                    });

                    if (response.ok) {
                        // Update all notifications to read
                        this.notifications.allNotifications = this.notifications.allNotifications.map(n => ({
                            ...n,
                            read_at: n.read_at || new Date().toISOString()
                        }));
                        this.notifications.unreadCount = 0;
                        this.renderNotificationsList();
                        this.helpers.showToast("All notifications marked as read", "success");
                    }
                } catch (error) {
                    console.error("Failed to mark all as read:", error);
                    this.helpers.showToast("Failed to mark all as read", "error");
                }
            });
        }
    }

    async markNotificationsAsRead() {
        if (this.notifications.unreadCount === 0) return;

        // Optimistic update
        const now = new Date().toISOString();
        this.notifications.list = this.notifications.list.map((notification) => {
            if (notification.read_at === null) {
                return { ...notification, read_at: now };
            }
            return notification;
        });
        this.notifications.unreadCount = 0;
        this.renderNotificationDropdown();

        try {
            await fetch("/notifications/mark-as-read", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                },
            });
        } catch (error) {
            console.error("Failed to mark notifications as read:", error);
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const dashboard = new Dashboard({
        outstandingBills:
            typeof outstandingBillsData !== "undefined"
                ? outstandingBillsData
                : [],
        utilityRates:
            typeof utilityRatesData !== "undefined" ? utilityRatesData : {},
    });
    dashboard
        .init()
        .catch((err) => console.error("Dashboard initialization failed:", err));
});