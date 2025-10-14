// In: js/vendor.js

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
        };
    }

    createReactiveState(initialData) {
        const defaultMonth =
            typeof isStaffView !== "undefined" && isStaffView
                ? "all"
                : new Date().getMonth() + 1;
        const state = {
            searchTerm: "",
            selectedYear: new Date().getFullYear(),
            selectedMonth: defaultMonth,
            isOutstandingModalOpen: false,
            activeSection: "homeSection",
            paymentHistory: [],
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
            await this.fetchAndRenderPayments();
        }
        setInterval(this.checkForUpdates.bind(this), 5000);
    }

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
                <td data-label="Amount After Due" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">${item.amountAfterDue}</td>
                <td data-label="Disconnection Date" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">${item.disconnectionDate}</td>
                <td data-label="Payment Status" class="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <span class="status-badge status-paid">${item.statusText}</span>
                </td>
            </tr>
        `
            )
            .join("");

        paymentTableBody.innerHTML = rowsHtml;
    }

    async fetchAndRenderPayments() {
        const { selectedYear, selectedMonth, searchTerm } = this.state;
        const query = new URLSearchParams({
            year: selectedYear,
            month: selectedMonth,
            search: searchTerm,
        });
        let url =
            typeof isStaffView !== "undefined" && isStaffView
                ? `/api/staff/vendors/${vendorData.id}/payment-history-filtered?${query}`
                : `/api/vendor/payments?${query}`;

        try {
            const response = await fetch(url);
            if (!response.ok)
                throw new Error(`HTTP error! Status: ${response.status}`);
            const data = await response.json();
            this.state.paymentHistory = this.formatPaymentHistory(data);
        } catch (error) {
            console.error("Failed to fetch payment history:", error);
            this.state.paymentHistory = [];
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
                    } else if (bill.utility_type === "Water" && utilityRatesData.Water) {
                        const daysInMonth = new Date(bill.period_end).getDate();
                        const calculatedAmount = (utilityRatesData.Water.rate || 0) * daysInMonth;
                        baseAmountForCalc = calculatedAmount;
                        detailsHtml = `${this.helpers.formatCurrency(utilityRatesData.Water.rate || 0)} x ${daysInMonth} days = <strong>${this.helpers.formatCurrency(calculatedAmount)}</strong>`;
                    } else if (bill.utility_type === "Electricity") {
                        const consumption = (bill.current_reading || 0) - (bill.previous_reading || 0);
                        detailsHtml = `(${consumption.toFixed(2)} kWh) x ${this.helpers.formatCurrency(bill.rate || 0)} = <strong>${this.helpers.formatCurrency(originalAmount)}</strong>`;
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
            this.fetchAndRenderPayments();
        });

        this.elements.monthDropdown?.addEventListener("change", (e) => {
            this.state.selectedMonth = e.target.value;
            this.fetchAndRenderPayments();
        });

        this.elements.searchInput?.addEventListener("input", (e) => {
            this.state.searchTerm = e.target.value;
            this.debouncedSearch();
        });
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