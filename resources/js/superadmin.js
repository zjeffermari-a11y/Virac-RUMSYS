import Chart from "chart.js/auto";
import AdminDashboard from "./admin-dashboard.js";

window.Chart = Chart;

class SuperAdminDashboard {
    constructor() {
        this.elements = this.cacheDOMElements();
        this.state = this.createState();
        this.activeDropdown = null;
        this.originalParent = null;
        this.dashboardManager = new AdminDashboard(
            window.INITIAL_STATE || null
        );
        this.userPollingInterval = null; // Property to hold the interval ID
        this.rentalRates = [];
        this.allRentalRates = window.INITIAL_STATE?.rentalRates?.data || [];
        this.utilityRates =
            window.INITIAL_STATE?.utilityRates?.data ||
            window.INITIAL_STATE?.utilityRates ||
            [];
        this.utilityRateHistory =
            window.INITIAL_STATE?.utilityRateHistory?.data || [];
        this.currentSchedule = {
            id: window.INITIAL_STATE?.meterReadingSchedule?.id,
            day: parseInt(
                window.INITIAL_STATE?.meterReadingSchedule?.description
            ),
        };
        this.scheduleHistory =
            window.INITIAL_STATE?.meterReadingHistory?.data || [];
        this.billingDateSchedules = window.INITIAL_STATE?.billingDates || [];
        this.billingDateHistory =
            window.INITIAL_STATE?.billingDatesHistory?.data || [];
        this.billingSettings = window.INITIAL_STATE?.billingSettings || {};
        this.billingSettingsHistory =
            window.INITIAL_STATE?.billingSettingsHistory?.data || [];
        this.notificationTemplates =
            window.INITIAL_STATE?.notificationTemplates || {};
        //In-App and SMS Notification//
        this.notifications = [];
        this.unreadNotificationCount =
            window.INITIAL_STATE?.unreadNotificationsCount || 0;

        // New properties for SMS Schedules
        this.smsSchedules = window.INITIAL_STATE?.smsSchedules || [];
        this.smsScheduleHistory =
            window.INITIAL_STATE?.smsScheduleHistory?.data || [];
        this.readingEditRequests =
            window.INITIAL_STATE?.editRequests?.data?.map((req) => ({
                id: req.id,
                request_date: req.created_at,
                request_reason: req.reason,
                status: req.status,
            })) || [];

        this.users = window.INITIAL_STATE?.systemUsers || [];
        this.allUsers = window.INITIAL_STATE?.systemUsers || []; // New property to hold all users for client-side filtering
        this.userPagination = {
            data: this.users,
            current_page: 1,
            last_page: 1,
            total: this.users.length,
        };

        // Keep these for dynamic loading (pagination/infinite scroll)
        this.userPollingInterval = null;
        this.rentalRates = []; // This gets populated by filterAndRenderRates
        this.rentalRatesPagination = {};
        this.utilityRateHistoryPage = 2; // Start at page 2 for infinite scroll
        this.utilityRateHistoryHasMore =
            !!window.INITIAL_STATE?.utilityRateHistory?.next_page_url;
        this.isFetchingUtilityHistory = false;
        this.scheduleHistoryPage = 2;
        this.scheduleHistoryHasMore =
            !!window.INITIAL_STATE?.meterReadingHistory?.next_page_url;
        this.isFetchingScheduleHistory = false;
        this.currentEditId = null;
        this.isRentalRatesEditing = false;
        this.searchDebounce = null;
        this.billingDateHistoryPage = 2;
        this.billingDateHistoryHasMore =
            !!window.INITIAL_STATE?.billingDatesHistory?.next_page_url;
        this.isFetchingBillingHistory = false;
        this.activeNotificationEditor = null;
        //properties for SMS Schedule history infinite scroll
        this.smsScheduleHistoryPage = 2;
        this.smsScheduleHistoryHasMore =
            !!window.INITIAL_STATE?.smsScheduleHistory?.next_page_url;
        this.isFetchingSmsScheduleHistory = false;
        this.readingEditRequestsPage = 2;
        this.readingEditRequestsHasMore =
            !!window.INITIAL_STATE?.editRequests?.next_page_url;
        this.isFetchingReadingRequests = false;
        this.roleContacts = window.INITIAL_STATE?.smsSettings || [];
        this.userFilters = { search: "", role: "", page: 1 };
        this.roles = []; // Fetched dynamically
        //-Audit Trails--//
        this.auditTrails = [];
        this.auditTrailsPage = 1;
        this.auditTrailsHasMore = true;
        this.isFetchingAuditTrails = false;
        this.auditTrailFilters = {
            search: "",
            role: "",
            start_date: "",
            end_date: "",
        };
        //------------//
        this.billingSettingsHistoryPage = 2;
        this.billingSettingsHistoryHasMore =
            !!window.INITIAL_STATE?.billingSettingsHistory?.next_page_url;
        this.isFetchingBillingSettingsHistory = false;
        this.dataLoaded = {
            marketStallRentalRatesSection: false,
            electricityWaterRatesSection: false,
            electricityMeterReadingScheduleSection: false,
            dueDateDisconnectionDateScheduleSection: false,
            billingStatementSmsNotificationSettingsSection: false,
            notificationSection: false,
            systemUserManagementSection: false,
            auditTrailsSection: false,
            discountsSurchargesPenaltySection: false,
        };
        this.listenersInitialized = {
            rentalRates: false,
            utilityRates: false,
            schedule: false,
            billingDates: false,
            billingSmsSettings: false, // Combined listener flag
            notificationSection: false,
            userManagement: false,
            auditTrails: false,
            billingSettings: false,
        };
    }

    cacheDOMElements() {
        return {
            navLinks: document.querySelectorAll(".nav-link"),
            sections: document.querySelectorAll(".dashboard-section"),
            billingManagementDropdown: document.getElementById(
                "billingManagementDropdown"
            ),
            billingManagementSubmenu: document.getElementById(
                "billingManagementSubmenu"
            ),
            billingManagementArrow: document.getElementById(
                "billingManagementArrow"
            ),

            // Rental Rates Elements
            rentalRatesHeader: document.getElementById("rentalRatesHeader"), // Added header element
            rentalRatesActionHeader: document.querySelector(
                "#marketStallRentalRatesSection table thead tr th:last-child"
            ), // Select the action header
            rentalRatesTableBody: document.getElementById(
                "rentalRatesTableBody"
            ),
            rentalRatesPagination: document.getElementById(
                "rentalRatesPagination"
            ),
            sectionNavBtns: document.querySelectorAll(".section-nav-btn"),

            rentalRatesSearchInput: document.getElementById(
                "rentalRatesSearchInput"
            ),
            areaColumnHeader: document.getElementById("areaColumnHeader"),
            rateUnit: document.getElementById("rateUnit"), //
            rentalRatesActionHeader: document.querySelector(
                "#marketStallRentalRatesSection table thead tr th:last-child"
            ),

            // Batch Edit Buttons for Rental Rates
            rentalRatesDefaultButtons: document.getElementById(
                "rentalRatesDefaultButtons"
            ),
            rentalRatesEditButtons: document.getElementById(
                "rentalRatesEditButtons"
            ),
            addRentalRateBtn: document.getElementById("addRentalRateBtn"),
            editAllRatesBtn: document.getElementById("editAllRatesBtn"),
            saveAllRentalRatesBtn: document.getElementById(
                "saveAllRentalRatesBtn"
            ),
            cancelEditRatesBtn: document.getElementById("cancelEditRatesBtn"),

            // Utility Rates Elements
            utilityRatesTableBody: document.getElementById(
                "utilityRatesTableBody"
            ),

            editUtilityRatesBtn: document.getElementById("editUtilityRatesBtn"),
            saveUtilityRatesBtn: document.getElementById("saveUtilityRatesBtn"),
            cancelUtilityRatesBtn: document.getElementById(
                "cancelUtilityRatesBtn"
            ),
            utilityRatesDefaultButtons: document.getElementById(
                "utilityRatesDefaultButtons"
            ),
            utilityRatesEditButtons: document.getElementById(
                "utilityRatesEditButtons"
            ),
            utilityRatesActionHeader: document.getElementById(
                "utilityRatesActionHeader"
            ),

            utilityRateHistoryTableBody: document.getElementById(
                "utilityRateHistoryTableBody"
            ),
            utilityRateHistoryContainer: document.getElementById(
                "utilityRateHistoryContainer"
            ),
            utilityRateHistoryLoader: document.getElementById(
                "utilityRateHistoryLoader"
            ),

            // Modals & Notifications
            deleteModal: document.getElementById("deleteModal"),
            toastContainer: document.getElementById("toastContainer"),
            confirmDelete: document.getElementById("confirmDelete"),
            cancelDelete: document.getElementById("cancelDelete"),
            preloader: document.getElementById("globalPreloader"),
            content: document.getElementById("dashboardContent"),

            // Meter Reading Schedule Elements
            scheduleView: document.getElementById("scheduleView"),
            scheduleEdit: document.getElementById("scheduleEdit"),
            scheduleDayDisplay: document.getElementById("scheduleDayDisplay"),
            scheduleDayInput: document.getElementById("scheduleDayInput"),
            editScheduleBtn: document.getElementById("editScheduleBtn"),
            saveScheduleBtn: document.getElementById("saveScheduleBtn"),
            cancelScheduleBtn: document.getElementById("cancelScheduleBtn"),
            scheduleHistoryTableBody: document.getElementById(
                "scheduleHistoryTableBody"
            ),
            electricityMeterReadingScheduleContainer: document.getElementById(
                "electricityMeterReadingScheduleContainer"
            ),
            electricityMeterReadingLoader: document.getElementById(
                "electricityMeterReadingLoader"
            ),

            // Due Date & Disconnection Schedule Elements
            billingDatesTableBody: document.getElementById(
                "billingDatesTableBody"
            ),
            billingDatesHistoryTableBody: document.getElementById(
                "billingDatesHistoryTableBody"
            ),
            editBillingDatesBtn: document.getElementById("editBillingDatesBtn"),
            saveBillingDatesBtn: document.getElementById("saveBillingDatesBtn"),
            cancelBillingDatesBtn: document.getElementById(
                "cancelBillingDatesBtn"
            ),
            billingDatesDefaultButtons: document.getElementById(
                "billingDatesDefaultButtons"
            ),
            billingDatesEditButtons: document.getElementById(
                "billingDatesEditButtons"
            ),
            dueDateDisconnectionDateScheduleContainer: document.getElementById(
                "dueDateDisconnectionDateScheduleContainer"
            ),
            dueDateDisconnectionDateScheduleLoader: document.getElementById(
                "dueDateDisconnectionDateScheduleLoader"
            ),

            // Notification Template Elements
            notificationTabs: document.querySelectorAll(".notification-tab"),
            notificationTabContents: document.querySelectorAll(
                ".notification-tab-content"
            ),
            templateBillStatementWet: document.getElementById(
                "templateBillStatementWet"
            ),
            templateBillStatementDry: document.getElementById(
                "templateBillStatementDry"
            ),
            templatePaymentReminder: document.getElementById(
                "templatePaymentReminder"
            ),
            templateOverdueAlert: document.getElementById(
                "templateOverdueAlert"
            ),
            saveTemplatesBtn: document.getElementById("saveTemplatesBtn"),

            // New elements for SMS Sending Schedule
            smsScheduleTableBody: document.getElementById(
                "smsScheduleTableBody"
            ),
            smsScheduleHistoryTableBody: document.getElementById(
                "smsScheduleHistoryTableBody"
            ),
            smsScheduleHistoryContainer: document.getElementById(
                "smsScheduleHistoryContainer"
            ),
            smsScheduleHistoryLoader: document.getElementById(
                "smsScheduleHistoryLoader"
            ),
            editSmsSchedulesBtn: document.getElementById("editSmsSchedulesBtn"),
            saveSmsSchedulesBtn: document.getElementById("saveSmsSchedulesBtn"),
            cancelSmsSchedulesBtn: document.getElementById(
                "cancelSmsSchedulesBtn"
            ),
            smsSchedulesDefaultButtons: document.getElementById(
                "smsSchedulesDefaultButtons"
            ),
            smsSchedulesEditButtons: document.getElementById(
                "smsSchedulesEditButtons"
            ),

            // Notification Section Elements
            readingEditRequestsTableBody: document.getElementById(
                "readingEditRequestsTableBody"
            ),
            readingEditRequestsContainer: document.getElementById(
                "readingEditRequestsContainer"
            ),
            readingEditRequestsLoader: document.getElementById(
                "readingEditRequestsLoader"
            ),
            smsSettingsTableBody: document.getElementById(
                "smsSettingsTableBody"
            ),
            editSmsSettingsBtn: document.getElementById("editSmsSettingsBtn"),
            saveSmsSettingsBtn: document.getElementById("saveSmsSettingsBtn"),
            cancelSmsSettingsBtn: document.getElementById(
                "cancelSmsSettingsBtn"
            ),
            smsSettingsDefaultButtons: document.getElementById(
                "smsSettingsDefaultButtons"
            ),
            smsSettingsEditButtons: document.getElementById(
                "smsSettingsEditButtons"
            ),

            // System User Management Elements
            usersTableBody: document.getElementById("usersTableBody"),
            usersPagination: document.getElementById("usersPagination"),
            userSearchInput: document.getElementById("userSearchInput"),
            userRoleFilter: document.getElementById("userRoleFilter"),
            addUserBtn: document.getElementById("addUserBtn"),
            userModal: document.getElementById("userModal"),
            userModalTitle: document.getElementById("userModalTitle"),
            userForm: document.getElementById("userForm"),
            userId: document.getElementById("userId"),
            userName: document.getElementById("userName"),
            userUsername: document.getElementById("userUsername"),
            userRole: document.getElementById("userRole"),
            userStatus: document.getElementById("userStatus"),
            userContactNumber: document.getElementById("userContactNumber"),
            userApplicationDate: document.getElementById("userApplicationDate"),
            contactNumberError: document.getElementById("contactNumberError"),
            userPassword: document.getElementById("userPassword"),
            userPasswordConfirmation: document.getElementById(
                "userPasswordConfirmation"
            ),
            cancelUserModalBtn: document.getElementById("cancelUserModalBtn"),
            saveUserBtn: document.getElementById("saveUserBtn"),

            // Audit Trails Elements
            auditTrailsTableBody: document.getElementById(
                "auditTrailsTableBody"
            ),
            auditTrailsLoader: document.getElementById("auditTrailsLoader"),
            mainContent: document.querySelector(".main-content"),
            auditTrailSearchInput: document.getElementById(
                "auditTrailSearchInput"
            ),
            auditTrailRoleFilter: document.getElementById(
                "auditTrailRoleFilter"
            ),
            auditTrailDateFilter: document.getElementById(
                "auditTrailDateFilter"
            ),
            auditTrailStartDate: document.getElementById("auditTrailStartDate"),
            auditTrailEndDate: document.getElementById("auditTrailEndDate"),

            //Discounts, Surcharge, and Penalty
            rentSettingsTableBody: document.getElementById(
                "rentSettingsTableBody"
            ),
            utilitySettingsTableBody: document.getElementById(
                "utilitySettingsTableBody"
            ),
            billingSettingsHistoryTableBody: document.getElementById(
                "billingSettingsHistoryTableBody"
            ),
            billingSettingsHistoryContainer: document.getElementById(
                "billingSettingsHistoryContainer"
            ),
            billingSettingsHistoryLoader: document.getElementById(
                "billingSettingsHistoryLoader"
            ),
            billingSettingsDefaultButtons: document.getElementById(
                "billingSettingsDefaultButtons"
            ),
            billingSettingsEditButtons: document.getElementById(
                "billingSettingsEditButtons"
            ),
            editBillingSettingsBtn: document.getElementById(
                "editBillingSettingsBtn"
            ),
            saveBillingSettingsBtn: document.getElementById(
                "saveBillingSettingsBtn"
            ),
            cancelBillingSettingsBtn: document.getElementById(
                "cancelBillingSettingsBtn"
            ),
        };
    }

    createState() {
        return new Proxy(
            {
                activeSection: "dashboardSection",
                currentRentalSection: "Wet Section",
            },
            {
                set: (target, property, value) => {
                    target[property] = value;

                    if (property === "activeSection") {
                        // Stop any existing polling when the section changes
                        if (this.userPollingInterval) {
                            clearInterval(this.userPollingInterval);
                            this.userPollingInterval = null;
                        }

                        // Start polling only if the new section is the user management section
                        if (value === "systemUserManagementSection") {
                            this.userPollingInterval = setInterval(() => {
                                // Don't re-fetch if a modal is open
                                if (
                                    this.elements.userModal.classList.contains(
                                        "hidden"
                                    )
                                ) {
                                    this.fetchUsers();
                                }
                            }, 15000); // Poll every 15 seconds
                        }
                    }

                    this.render();
                    return true;
                },
            }
        );
    }

    setupInfiniteScroll(container, loader, fetchFunction) {
        if (!container) return;

        container.addEventListener("scroll", () => {
            const isNearBottom =
                container.scrollTop + container.clientHeight >=
                container.scrollHeight - 200;

            if (isNearBottom) {
                fetchFunction.call(this);
            }
        });
    }

    async init() {
        if (this.elements.preloader)
            this.elements.preloader.classList.add("hidden");
        if (this.elements.content) {
            this.elements.content.classList.remove("content-hidden");
            this.elements.content.classList.add("content-visible");
        }
        if (this.dashboardManager) {
            this.dashboardManager.init();
        }

        this.setupEventListeners();
        this.setInitialSection();
        this.render();
        this.setupInfiniteScroll(
            this.elements.utilityRateHistoryContainer,
            this.elements.utilityRateHistoryLoader,
            this.fetchUtilityRateHistory
        );
        this.setupInfiniteScroll(
            this.elements.electricityMeterReadingScheduleContainer,
            this.elements.electricityMeterReadingScheduleLoader,
            this.fetchMeterReadingScheduleHistory
        );
        this.setupInfiniteScroll(
            this.elements.dueDateDisconnectionDateScheduleContainer,
            this.elements.dueDateDisconnectionDateScheduleLoader,
            this.fetchBillingDateHistory
        );
        this.setupInfiniteScroll(
            this.elements.readingEditRequestsContainer,
            this.elements.readingEditRequestsLoader,
            this.fetchReadingEditRequests
        );
        // New infinite scroll for SMS schedule history
        this.setupInfiniteScroll(
            this.elements.smsScheduleHistoryContainer,
            this.elements.smsScheduleHistoryLoader,
            this.fetchSmsScheduleHistory
        );

        this.filterAndRenderRates();
        this.renderUtilityRatesTable();
        this.renderUtilityRateHistoryTable();
        this.renderMeterReadingSchedule();
        this.renderScheduleHistoryTable();
        this.renderBillingDateSchedules();
        this.renderBillingDateHistory();
        this.renderBillingSettingsTables();
        this.renderBillingSettingsHistory();
        this.renderNotificationTemplates();
        this.renderSmsSchedulesTable(); // New render call
        this.renderSmsScheduleHistory(); // New render call
        this.renderReadingEditRequestsTable();
        this.filterAndPaginateUsers();
        this.renderSmsSettingsTable();

        if (this.unreadNotificationCount > 0) {
            this.elements.notificationDot.classList.remove("hidden");
        }
        setInterval(this.fetchUnreadNotifications.bind(this), 7000);

        await this.fetchRoles();

        this.state.currentRentalSection = "Wet Section";
        this.setInitialRentalSection();

        this.setupInfiniteScroll(
            this.elements.billingSettingsHistoryContainer,
            this.elements.billingSettingsHistoryLoader,
            this.fetchBillingSettingsHistory
        );
    }

    initializeSection(sectionId) {
        // This function ensures data is loaded and listeners are attached for a given section
        this.loadDataForSection(sectionId);

        switch (sectionId) {
            case "marketStallRentalRatesSection":
                if (!this.listenersInitialized.rentalRates) {
                    this.setupRentalRatesEventListeners();
                    this.listenersInitialized.rentalRates = true;
                }
                break;
            case "electricityWaterRatesSection":
                if (!this.listenersInitialized.utilityRates) {
                    this.setupUtilityRatesEventListeners();
                    this.listenersInitialized.utilityRates = true;
                }
                break;
            case "electricityMeterReadingScheduleSection":
                if (!this.listenersInitialized.schedule) {
                    this.setupScheduleEventListeners();
                    this.listenersInitialized.schedule = true;
                }
                break;
            case "dueDateDisconnectionDateScheduleSection":
                if (!this.listenersInitialized.billingDates) {
                    this.setupBillingDateEventListeners();
                    this.listenersInitialized.billingDates = true;
                }
                break;
            case "billingStatementSmsNotificationSettingsSection":
                if (!this.listenersInitialized.billingSmsSettings) {
                    this.setupBillingSmsSettingsEventListeners(); // Combined listener
                    this.listenersInitialized.billingSmsSettings = true;
                }
                break;
            case "discountsSurchargesPenaltySection":
                if (!this.listenersInitialized.billingSettings) {
                    this.setupBillingSettingsEventListeners();
                    this.listenersInitialized.billingSettings = true;
                }
                break;
            case "notificationSection":
                if (!this.listenersInitialized.notificationSection) {
                    this.setupNotificationSectionEventListeners();
                    this.listenersInitialized.notificationSection = true;
                }
                break;
            case "systemUserManagementSection":
                if (!this.listenersInitialized.userManagement) {
                    this.setupUserManagementEventListeners();
                    this.listenersInitialized.userManagement = true;
                }
                break;
            case "auditTrailsSection":
                if (!this.listenersInitialized.auditTrails) {
                    this.setupAuditTrailEventListeners();
                    this.listenersInitialized.auditTrails = true;
                }
                break;
        }
    }

    async fetchUnreadNotifications() {
        try {
            const response = await fetch("/notifications/fetch");
            if (!response.ok) return;

            const data = await response.json();
            this.notifications = data.notifications;
            this.unreadNotificationCount = data.unread_count; // Use the count directly from the server

            const activeSection = document.querySelector(
                ".dashboard-section.active"
            );
            if (activeSection) {
                const notificationDot =
                    activeSection.querySelector(".notificationDot");
                if (notificationDot) {
                    notificationDot.classList.toggle(
                        "hidden",
                        this.unreadNotificationCount === 0
                    );
                }
            }

            this.renderNotificationDropdown();
        } catch (error) {
            console.error("Error fetching notifications:", error);
        }
    }

    async markNotificationsAsRead() {
        if (this.unreadNotificationCount === 0) return;

        // --- OPTIMISTIC UI UPDATE ---
        this.notifications.forEach((notification) => {
            if (notification.status === "pending") {
                notification.status = "read"; // Mark as read locally
            }
        });
        this.unreadNotificationCount = 0;

        const activeSection = document.querySelector(
            ".dashboard-section.active"
        );
        if (activeSection) {
            const notificationDot =
                activeSection.querySelector(".notificationDot");
            if (notificationDot) {
                notificationDot.classList.add("hidden");
            }
        }

        this.renderNotificationDropdown();

        try {
            await fetch("/notifications/mark-as-read", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });
            // The slow fetch call that caused the delay has been removed.
        } catch (error) {
            console.error(
                "Failed to mark notifications as read on server:",
                error
            );
        }
    }

    renderNotificationDropdown() {
        let list = null;

        // First, check if there is an active dropdown that has been moved to the body
        if (this.activeDropdown) {
            list = this.activeDropdown.querySelector(".notificationList");
        }
        // If not, find it the old way inside the current page section
        else {
            const activeSection = document.querySelector(
                ".dashboard-section.active"
            );
            if (activeSection) {
                list = activeSection.querySelector(".notificationList");
            }
        }

        if (!list) return; // If no list is found, do nothing

        if (this.notifications.length === 0) {
            list.innerHTML = `<p class="text-center text-gray-500 p-4">You have no notifications.</p>`;
            return;
        }

        list.innerHTML = this.notifications
            .map((notification) => {
                const data = JSON.parse(notification.message);
                const isUnread = notification.status === "pending";
                const timeAgo = this.formatTimeAgo(notification.created_at);

                return `
                <a href="#notificationSection" data-section="notificationSection" class="nav-link block p-3 transition-colors hover:bg-gray-100 ${
                    isUnread ? "bg-blue-50" : ""
                } border-b border-gray-100 last:border-b-0">
                    <div class="flex items-start gap-3">
                        ${
                            isUnread
                                ? '<div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>'
                                : '<div class="w-2 h-2 bg-transparent mt-1.5 flex-shrink-0"></div>'
                        }
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 break-words">${
                                data.text || notification.title
                            }</p>
                            <p class="text-xs text-blue-600 font-semibold mt-1">${timeAgo}</p>
                        </div>
                    </div>
                </a>
            `;
            })
            .join("");
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

    render() {
        this.renderActiveSection();
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

    async loadDataForSection(sectionId) {
        // All primary data is now loaded instantly.
        // We only need to handle sections that are still lazy-loaded.
        if (this.dataLoaded[sectionId]) {
            return;
        }

        switch (sectionId) {
            case "marketStallRentalRatesSection":
                await this.fetchAllRentalRates();
                this.filterAndRenderRates();
                break;
            case "auditTrailsSection":
                await this.fetchAuditTrails();
                break;
            // The 'notificationSection' still needs to fetch SMS settings dynamically
        }

        this.dataLoaded[sectionId] = true;
    }

    async fetchAllRentalRates() {
        try {
            const url = `/api/rental-rates`;
            const response = await fetch(url);
            if (!response.ok) throw new Error("Network response was not ok");
            const data = await response.json();
            this.allRentalRates = data.data;
        } catch (error) {
            console.error("Failed to fetch all rental rates:", error);
            this.showToast(
                "Failed to load all rental data from the server.",
                "error"
            );
        }
    }

    filterAndRenderRates(page = 1) {
        // --- MODIFICATION START ---
        // Trim whitespace from user input for more reliable matching.
        const searchTerm = this.elements.rentalRatesSearchInput.value
            .toLowerCase()
            .trim();
        // --- MODIFICATION END ---
        const currentSection = this.state.currentRentalSection;

        let filteredRates = this.allRentalRates.filter((rate) => {
            const matchesSection = rate.section === currentSection;
            // --- MODIFICATION START ---
            // Ensure tableNumber is a string, trim it, and then check for inclusion.
            const matchesSearch = searchTerm
                ? String(rate.tableNumber)
                      .toLowerCase()
                      .trim()
                      .includes(searchTerm)
                : true;
            // --- MODIFICATION END ---
            return matchesSection && matchesSearch;
        });

        const perPage = 15;
        const total = filteredRates.length;
        const totalPages = Math.ceil(total / perPage);
        const pageNumber = parseInt(page) || 1;
        const offset = (pageNumber - 1) * perPage;
        const paginatedRates = filteredRates.slice(offset, offset + perPage);

        this.rentalRates = paginatedRates;
        this.renderRentalRatesTable(this.rentalRates);

        this.rentalRatesPagination = {
            current_page: pageNumber,
            data: paginatedRates,
            from: offset + 1,
            to: offset + paginatedRates.length,
            last_page: totalPages,
            total: total,
            links: this.generatePaginationLinks(pageNumber, totalPages),
        };
        this.renderRentalRatesPagination();
    }

    generatePaginationLinks(currentPage, lastPage) {
        const links = [];
        links.push({
            url: currentPage > 1 ? `?page=${currentPage - 1}` : null,
            label: "&laquo; Previous",
            active: false,
        });

        for (let i = 1; i <= lastPage; i++) {
            links.push({
                url: `?page=${i}`,
                label: i.toString(),
                active: i === currentPage,
            });
        }

        links.push({
            url: currentPage < lastPage ? `?page=${currentPage + 1}` : null,
            label: "Next &raquo;",
            active: false,
        });
        return links;
    }

    async fetchUtilityRates() {
        try {
            const response = await fetch("/api/utility-rates");
            if (!response.ok) throw new Error("Network response was not ok");

            // This new code correctly handles a wrapped API response
            const responseData = await response.json();
            this.utilityRates = responseData.data || responseData;

            this.renderUtilityRatesTable();
        } catch (error) {
            console.error("Failed to fetch utility rates:", error);
            this.showToast(
                "Failed to load utility rates from the server.",
                "error"
            );
        }
    }

    async fetchUtilityRateHistory() {
        if (this.isFetchingUtilityHistory || !this.utilityRateHistoryHasMore)
            return;
        this.isFetchingUtilityHistory = true;
        if (this.elements.utilityRateHistoryLoader)
            this.elements.utilityRateHistoryLoader.style.display = "block";

        try {
            const response = await fetch(
                `/api/utility-rate-history?page=${this.utilityRateHistoryPage}`
            );
            if (!response.ok) throw new Error("Network response was not ok");
            const data = await response.json();

            this.utilityRateHistory.push(...data.data);
            this.utilityRateHistoryHasMore = data.next_page_url !== null;
            this.utilityRateHistoryPage++;
            this.renderUtilityRateHistoryTable();
        } catch (error) {
            // ... error handling
        } finally {
            this.isFetchingUtilityHistory = false;
            if (this.elements.utilityRateHistoryLoader)
                this.elements.utilityRateHistoryLoader.style.display = "none";
        }
    }

    async fetchAuditTrails() {
        if (this.isFetchingAuditTrails || !this.auditTrailsHasMore) return;

        this.isFetchingAuditTrails = true;
        if (this.elements.auditTrailsLoader) {
            this.elements.auditTrailsLoader.classList.remove("hidden");
        }

        const params = new URLSearchParams(this.auditTrailFilters);
        params.append("page", this.auditTrailsPage);

        try {
            const response = await fetch(
                `/api/audit-trails?${params.toString()}`
            );
            if (!response.ok) throw new Error("Could not fetch audit trails.");

            const data = await response.json();

            this.auditTrails.push(...data.data);
            this.auditTrailsHasMore = data.next_page_url !== null;
            this.auditTrailsPage++;
            this.renderAuditTrails();
        } catch (error) {
            console.error("Failed to fetch audit trails:", error);
            this.showToast("Failed to load audit trail data.", "error");
        } finally {
            this.isFetchingAuditTrails = false;
            if (this.elements.auditTrailsLoader) {
                this.elements.auditTrailsLoader.classList.add("hidden");
            }
        }
    }

    async fetchNotificationTemplates() {
        try {
            const response = await fetch("/api/notification-templates");
            if (!response.ok) throw new Error("Network response was not ok");
            this.notificationTemplates = await response.json();
            this.renderNotificationTemplates();
        } catch (error) {
            console.error("Failed to fetch notification templates:", error);
            this.showToast("Failed to load SMS templates.", "error");
        }
    }

    renderNotificationTemplates() {
        this.elements.templateBillStatementWet.value =
            this.notificationTemplates.bill_statement.wet_section;
        this.elements.templateBillStatementDry.value =
            this.notificationTemplates.bill_statement.dry_section;
        this.elements.templatePaymentReminder.value =
            this.notificationTemplates.payment_reminder.template;
        this.elements.templateOverdueAlert.value =
            this.notificationTemplates.overdue_alert.template;

        document.querySelectorAll(".template-editor").forEach((editor) => {
            this.updateCharacterCount(editor);
            this.updateLivePreview(editor);
        });
    }
    //Test SMS Template
    async sendTestSms(vendorId, templateName) {
        this.showToast(`Sending test SMS...`, "info");

        try {
            const response = await fetch("/superadmin/notifications/test-sms", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    user_id: vendorId,
                    template_name: templateName,
                }),
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || "Failed to send test SMS.");
            }
            this.showToast(result.message, "success");
        } catch (error) {
            console.error("Test SMS Error:", error);
            this.showToast(error.message, "error");
        }
    }

    // Combined event listener setup for the "Billing Statement / SMS..." section
    setupBillingSmsSettingsEventListeners() {
        // --- Notification Template Listeners ---
        this.elements.notificationTabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                const tabId = tab.dataset.tab;
                this.elements.notificationTabs.forEach((t) =>
                    t.classList.remove(
                        "active",
                        "text-market-primary",
                        "border-b-2",
                        "border-market-primary"
                    )
                );
                tab.classList.add(
                    "active",
                    "text-market-primary",
                    "border-b-2",
                    "border-market-primary"
                );
                this.elements.notificationTabContents.forEach((content) => {
                    content.classList.toggle(
                        "hidden",
                        content.dataset.content !== tabId
                    );
                });
            });
        });

        this.elements.saveTemplatesBtn.addEventListener("click", () =>
            this.saveNotificationTemplates()
        );

        const templateEditors = document.querySelectorAll(".template-editor");
        templateEditors.forEach((editor) => {
            editor.addEventListener("focus", () => {
                this.activeNotificationEditor = editor;
            });
            editor.addEventListener("input", () => {
                this.updateCharacterCount(editor);
                this.updateLivePreview(editor);
            });
        });

        document.querySelectorAll(".placeholder-btn").forEach((button) => {
            button.addEventListener("click", () => {
                if (this.activeNotificationEditor) {
                    this.insertPlaceholder(
                        this.activeNotificationEditor,
                        button.textContent
                    );
                } else {
                    this.showToast("Please select a text area first.", "info");
                }
            });
        });

        // --- New SMS Schedule Listeners ---
        this.elements.editSmsSchedulesBtn.addEventListener("click", () =>
            this.toggleSmsSchedulesEditMode(true)
        );
        this.elements.cancelSmsSchedulesBtn.addEventListener("click", () =>
            this.toggleSmsSchedulesEditMode(false)
        );
        this.elements.saveSmsSchedulesBtn.addEventListener("click", () =>
            this.saveSmsSchedules()
        );
    }

    updateCharacterCount(editorElement) {
        const counterElement = document.querySelector(
            `[data-counter-for="${editorElement.id}"]`
        );
        if (!counterElement) return;

        const charCount = editorElement.value.length;
        const smsSegments = Math.ceil(charCount / 160) || 1;
        counterElement.textContent = `${charCount}/160 characters (${smsSegments} SMS)`;
    }

    updateLivePreview(editorElement) {
        const previewElement = document.querySelector(
            `[data-preview-for="${editorElement.id}"]`
        );
        if (!previewElement) return;

        let previewText = editorElement.value;

        // This is the new line to add the timestamp to the preview data
        const timestamp = new Date().toLocaleString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            hour12: true,
        });

        const data = this.livePreviewData || {
            vendor_name: "Juan Dela Cruz",
            stall_number: "WS-01",
            total_due: "3500.00",
            due_date: "September 15, 2025",
            disconnection_date: "September 25, 2025",
            rent_amount: "1250.00",
            water_amount: "550.00",
            electricity_amount: "1700.00",
            unpaid_items: "Rent, Electricity",
            bill_details:
                "Rent (due Sep 30): P2500.00, Electricity (due Sep 15): P1000.00",
            upcoming_bill_details:
                "Rent (due Oct 31): P2500.00, Water (due Oct 15): P250.00",
            overdue_bill_details: "Electricity (due Sep 15): P1000.00",
            overdue_items: "Rent, Electricity",
            new_total_due: "3850.00",
            timestamp: timestamp, // Add the timestamp here
        };

        for (const [key, value] of Object.entries(data)) {
            const placeholderRegex = new RegExp(`{{\\s*${key}\\s*}}`, "g");

            const displayValue =
                typeof value === "number" ? value.toFixed(2) : value || "";

            previewText = previewText.replace(
                placeholderRegex,
                `<strong class="text-blue-600">${displayValue}</strong>`
            );
        }

        previewElement.innerHTML = previewText;
    }

    insertPlaceholder(editorElement, placeholder) {
        editorElement.focus();
        document.execCommand("insertText", false, placeholder);
        this.updateCharacterCount(editorElement);
        this.updateLivePreview(editorElement);
    }

    async saveNotificationTemplates() {
        const updatedTemplates = {
            bill_statement: {
                wet_section: this.elements.templateBillStatementWet.value,
                dry_section: this.elements.templateBillStatementDry.value,
            },
            payment_reminder: {
                template: this.elements.templatePaymentReminder.value,
            },
            overdue_alert: {
                template: this.elements.templateOverdueAlert.value,
            },
        };

        try {
            const response = await fetch("/api/notification-templates", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(updatedTemplates),
            });
            if (!response.ok) throw new Error("Failed to save templates.");

            this.showToast("SMS templates saved successfully!", "success");
            await this.fetchNotificationTemplates();
        } catch (error) {
            this.showToast(error.message, "error");
        }
    }

    // New methods for SMS Sending Schedule

    async fetchSmsScheduleHistory() {
        if (
            this.isFetchingSmsScheduleHistory ||
            !this.smsScheduleHistoryHasMore
        )
            return;
        this.isFetchingSmsScheduleHistory = true;
        if (this.elements.smsScheduleHistoryLoader)
            this.elements.smsScheduleHistoryLoader.style.display = "block";

        try {
            const response = await fetch(
                `/api/schedules/sms/history?page=${this.smsScheduleHistoryPage}`
            );
            if (!response.ok) throw new Error("Network response was not ok");
            const data = await response.json();

            this.smsScheduleHistory.push(...data.data);
            this.smsScheduleHistoryHasMore = data.next_page_url !== null;
            this.smsScheduleHistoryPage++;
            this.renderSmsScheduleHistory();
        } catch (error) {
            console.error("Failed to fetch SMS schedule history:", error);
            this.showToast("Failed to load SMS schedule history.", "error");
        } finally {
            this.isFetchingSmsScheduleHistory = false;
            if (this.elements.smsScheduleHistoryLoader)
                this.elements.smsScheduleHistoryLoader.style.display = "none";
        }
    }

    renderSmsSchedulesTable(isEditing = false) {
        const { smsScheduleTableBody } = this.elements;
        if (!smsScheduleTableBody) return;

        const scheduleTypes = [
            "SMS - Billing Statements",
            "SMS - Payment Reminders",
            "SMS - Overdue Alerts",
        ];

        const formatTime12hr = (timeString) => {
            if (!timeString || !timeString.includes(":")) return "Not Set";
            const [hours, minutes] = timeString.split(":");
            const h = parseInt(hours, 10);
            const suffix = h >= 12 ? "PM" : "AM";
            const h12 = h % 12 || 12;
            return `${String(h12).padStart(2, "0")}:${minutes} ${suffix}`;
        };

        smsScheduleTableBody.innerHTML = scheduleTypes
            .map((type) => {
                const schedule = this.smsSchedules.find(
                    (s) => s.schedule_type === type
                );
                const currentTime = schedule ? schedule.description : "00:00";

                return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td data-label="Notification Type" class="border border-gray-200 px-4 py-3 text-gray-700 font-medium">${type.replace(
                        "SMS - ",
                        ""
                    )}</td>
                    <td data-label="Scheduled Time" class="border border-gray-200 px-4 py-3 text-gray-700">
                        ${
                            isEditing
                                ? `<input type="time" class="sms-schedule-input w-full border border-gray-300 rounded-lg px-3 py-2" data-type="${type}" value="${currentTime}">`
                                : `<span>${formatTime12hr(currentTime)}</span>`
                        }
                    </td>
                </tr>
            `;
            })
            .join("");
    }

    renderSmsScheduleHistory() {
        const { smsScheduleHistoryTableBody } = this.elements;
        if (!smsScheduleHistoryTableBody) return;
        if (this.smsScheduleHistory.length === 0) {
            smsScheduleHistoryTableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">No history logs found.</td></tr>`;
            return;
        }

        const formatTime12hr = (timeString) => {
            if (
                !timeString ||
                !timeString.includes(":") ||
                timeString === "Not Set"
            )
                return "Not Set";
            const [hours, minutes] = timeString.split(":");
            const h = parseInt(hours, 10);
            const suffix = h >= 12 ? "PM" : "AM";
            const h12 = h % 12 || 12;
            return `${String(h12).padStart(2, "0")}:${minutes} ${suffix}`;
        };

        smsScheduleHistoryTableBody.innerHTML = this.smsScheduleHistory
            .map((log) => {
                const formattedDate = new Date(log.changed_at).toLocaleString(
                    "en-US",
                    {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: true,
                    }
                );
                return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td data-label="Date & Time" class="border border-gray-200 px-4 py-3 text-gray-700">${formattedDate}</td>
                    <td data-label="Item Changed" class="border border-gray-200 px-4 py-3 text-gray-700">${log.item_changed.replace(
                        "SMS - ",
                        ""
                    )}</td>
                    <td data-label="Old Time" class="border border-gray-200 px-4 py-3 text-gray-700">${formatTime12hr(
                        log.old_value
                    )}</td>
                    <td data-label="New Time" class="border border-gray-200 px-4 py-3 text-gray-700">${formatTime12hr(
                        log.new_value
                    )}</td>
                </tr>
            `;
            })
            .join("");
    }

    toggleSmsSchedulesEditMode(isEditing) {
        this.elements.smsSchedulesDefaultButtons.classList.toggle(
            "hidden",
            isEditing
        );
        this.elements.smsSchedulesEditButtons.classList.toggle(
            "hidden",
            !isEditing
        );
        this.renderSmsSchedulesTable(isEditing);
    }

    async saveSmsSchedules() {
        const updatedSchedulesPayload = [];
        this.elements.smsScheduleTableBody
            .querySelectorAll(".sms-schedule-input")
            .forEach((input) => {
                updatedSchedulesPayload.push({
                    type: input.dataset.type,
                    time: input.value,
                });
            });

        // Optimistic UI update
        const oldSchedules = JSON.parse(JSON.stringify(this.smsSchedules));
        updatedSchedulesPayload.forEach((updated) => {
            let schedule = this.smsSchedules.find(
                (s) => s.schedule_type === updated.type
            );
            if (schedule) {
                schedule.description = updated.time;
            } else {
                this.smsSchedules.push({
                    schedule_type: updated.type,
                    description: updated.time,
                });
            }
        });
        this.toggleSmsSchedulesEditMode(false);

        try {
            const response = await fetch("/api/schedules/sms", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ schedules: updatedSchedulesPayload }),
            });

            if (!response.ok)
                throw new Error(
                    (await response.json()).message ||
                        "Failed to save schedules."
                );

            this.showToast("SMS schedules updated successfully!", "success");

            // Silently refresh history
            this.smsScheduleHistory = [];
            this.smsScheduleHistoryPage = 1;
            this.smsScheduleHistoryHasMore = true;
            await this.fetchSmsScheduleHistory();
        } catch (error) {
            this.showToast(error.message, "error");
            // Rollback UI on failure
            this.smsSchedules = oldSchedules;
            this.renderSmsSchedulesTable(false);
        }
    }

    setupUserManagementEventListeners() {
        this.elements.addUserBtn.addEventListener("click", () =>
            this.openUserModal()
        );
        this.elements.cancelUserModalBtn.addEventListener("click", () =>
            this.closeUserModal()
        );
        this.elements.userForm.addEventListener("submit", (e) => {
            e.preventDefault();
            this.saveUser();
        });

        this.elements.userContactNumber.addEventListener("input", () => {
            this.validateContactNumber();
        });

        this.elements.usersTableBody.addEventListener("click", (e) => {
            const editBtn = e.target.closest(".edit-user-btn");
            const deleteBtn = e.target.closest(".delete-user-btn");
            if (editBtn) {
                const userId = editBtn.dataset.id;
                const user = this.users.find((u) => u.id == userId);
                this.openUserModal(user);
            }
            if (deleteBtn) {
                this.openDeleteModal(parseInt(deleteBtn.dataset.id), "user");
            }
        });

        this.elements.userSearchInput.addEventListener("input", () => {
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(() => {
                this.handleUserFilterChange(
                    "search",
                    this.elements.userSearchInput.value
                );
            }, 300);
        });

        // FIX: Role filter now calls the server
        this.elements.userRoleFilter.addEventListener("change", () =>
            this.handleUserFilterChange(
                "role",
                this.elements.userRoleFilter.value
            )
        );

        // FIX: Pagination clicks now call the server
        this.elements.usersPagination.addEventListener("click", (e) => {
            const link = e.target.closest("a");
            if (link && link.href) {
                e.preventDefault();
                const url = new URL(link.href);
                const page = url.searchParams.get("page");
                if (
                    page &&
                    !link.parentElement.classList.contains("disabled")
                ) {
                    this.userFilters.page = parseInt(page, 10);
                    this.filterAndPaginateUsers(parseInt(page, 10));
                }
            }
        });
    }

    handleUserFilterChange(key, value) {
        this.userFilters[key] = value;
        this.userFilters.page = 1; // Reset to page 1 on any new filter
        this.filterAndPaginateUsers(1); // Triggers a new fetch from the server
    }

    // New method to perform client-side filtering and pagination.

    filterAndPaginateUsers(page = 1) {
        const searchTerm = this.userFilters.search.toLowerCase().trim();
        const roleFilter = this.userFilters.role;

        let filteredUsers = this.allUsers.filter((user) => {
            const matchesSearch = searchTerm
                ? user.name.toLowerCase().includes(searchTerm) ||
                  user.username.toLowerCase().includes(searchTerm)
                : true;
            const matchesRole = roleFilter ? user.role_id == roleFilter : true;
            return matchesSearch && matchesRole;
        });

        const perPage = 10;
        const total = filteredUsers.length;
        const totalPages = Math.ceil(total / perPage);
        const pageNumber = parseInt(page) || 1;
        const offset = (pageNumber - 1) * perPage;
        const paginatedUsers = filteredUsers.slice(offset, offset + perPage);

        this.users = paginatedUsers;
        this.userPagination = {
            current_page: pageNumber,
            data: paginatedUsers,
            from: offset + 1,
            to: offset + paginatedUsers.length,
            last_page: totalPages,
            total: total,
            links: this.generatePaginationLinks(pageNumber, totalPages),
        };

        this.renderUsersTable();
        this.renderUsersPagination();
    }

    renderUsersTable() {
        const tableBody = this.elements.usersTableBody;
        if (this.users.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">No users found.</td></tr>`;
            return;
        }

        const statusClasses = {
            active: "bg-green-100 text-green-800",
            inactive: "bg-red-100 text-red-800",
        };

        tableBody.innerHTML = this.users
            .map(
                (user) => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td data-label="Role" class="border border-gray-200 px-4 py-3">${
                user.role
            }</td>
            <td data-label="Name" class="border border-gray-200 px-4 py-3">${
                user.name
            }</td>
            <td data-label="Username" class="border border-gray-200 px-4 py-3">${
                user.username
            }</td>
            <td data-label="Last Login" class="border border-gray-200 px-4 py-3">${
                user.last_login
                    ? new Date(user.last_login).toLocaleString()
                    : "Never"
            }</td>
            <td data-label="Status" class="border border-gray-200 px-4 py-3">
                <span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs ${
                    statusClasses[user.status]
                }">
                    ${
                        user.status.charAt(0).toUpperCase() +
                        user.status.slice(1)
                    }
                </span>
            </td>
            <td data-label="Action" class="border border-gray-200 px-4 py-3 text-center">
                <button data-id="${
                    user.id
                }" class="edit-user-btn text-blue-600 hover:text-blue-900 mr-2" title="Edit User"><i class="fas fa-edit"></i></button>
                <button data-id="${
                    user.id
                }" class="delete-user-btn text-red-600 hover:text-red-900" title="Delete User"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `
            )
            .join("");
    }

    renderUsersPagination() {
        const { links, from, to, total } = this.userPagination;
        if (!total || total <= 10) {
            this.elements.usersPagination.innerHTML = "";
            return;
        }

        const pageInfo = `<span class="text-sm text-gray-700">Showing ${from} to ${to} of ${total} results</span>`;
        const pageLinks = links
            .map(
                (link) =>
                    `<a href="${link.url}" class="px-3 py-1 border rounded ${
                        link.active
                            ? "bg-market-primary text-white"
                            : "bg-white"
                    } ${
                        !link.url
                            ? "text-gray-400 cursor-not-allowed"
                            : "text-gray-700"
                    }">${
                        link.label === "&laquo; Previous"
                            ? '<i class="fas fa-chevron-left"></i>'
                            : link.label === "Next &raquo;"
                            ? '<i class="fas fa-chevron-right"></i>'
                            : link.label
                    }</a>`
            )
            .join("");

        this.elements.usersPagination.innerHTML = `${pageInfo}<div class="flex gap-1">${pageLinks}</div>`;
    }

    openUserModal(user = null) {
        this.elements.userForm.reset();
        if (user) {
            this.elements.userModalTitle.textContent = "Edit User";
            this.elements.userId.value = user.id;
            this.elements.userName.value = user.name;
            this.elements.userUsername.value = user.username;
            this.elements.userContactNumber.value = user.contact_number || "";
            this.elements.userApplicationDate.value =
                user.application_date || "";
            this.elements.contactNumberError.classList.add("hidden");
            this.elements.userContactNumber.classList.remove("border-red-500");
            this.elements.saveUserBtn.disabled = false;
            this.elements.userRole.value = user.role_id;
            this.elements.userStatus.value = user.status;
            this.elements.userPassword.placeholder =
                "Leave blank to keep current password";
            this.elements.userPassword.required = false;
            this.elements.userPasswordConfirmation.required = false;
        } else {
            this.elements.userModalTitle.textContent = "Add New User";
            this.elements.userId.value = "";
            this.elements.userPassword.placeholder = "";
            this.elements.userPassword.required = true;
            this.elements.userPasswordConfirmation.required = true;
        }
        this.elements.userModal.classList.remove("hidden");
    }

    closeUserModal() {
        this.elements.userModal.classList.add("hidden");
    }

    async fetchUsers() {
        try {
            const response = await fetch("/api/admin/system-users");
            if (!response.ok) {
                throw new Error("Could not fetch users from the server.");
            }
            this.allUsers = await response.json();
            // Re-apply current filters and pagination to the new data
            this.filterAndPaginateUsers(this.userPagination.current_page);
        } catch (error) {
            console.error("Failed to fetch users:", error);
            this.showToast(error.message, "error");
        }
    }

    async saveUser() {
        const id = this.elements.userId.value;
        const url = id
            ? `/api/admin/system-users/${id}`
            : "/api/admin/system-users";
        const method = id ? "PUT" : "POST";

        const formData = {
            name: this.elements.userName.value,
            username: this.elements.userUsername.value,
            role_id: this.elements.userRole.value,
            status: this.elements.userStatus.value,
            contact_number: this.elements.userContactNumber.value,
            application_date: this.elements.userApplicationDate.value,
            password: this.elements.userPassword.value,
            password_confirmation: this.elements.userPasswordConfirmation.value,
        };

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(formData),
            });

            if (!response.ok) {
                const errorData = await response.json();
                const errorMessage = errorData.errors
                    ? Object.values(errorData.errors).flat().join(" ")
                    : errorData.message;
                throw new Error(errorMessage || "An error occurred.");
            }

            this.showToast(
                `User ${id ? "updated" : "created"} successfully!`,
                "success"
            );
            this.closeUserModal();
            this.fetchUsers();
        } catch (error) {
            this.showToast(error.message, "error");
        }
    }

    async deleteUser(id) {
        try {
            const response = await fetch(`/api/admin/system-users/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || "Failed to delete user.");
            }

            this.showToast("User deleted successfully!", "success");
            this.closeDeleteModal();
            await this.fetchUsers();
            this.auditTrails = [];
            this.auditTrailsPage = 1;
            this.auditTrailsHasMore = true;
            await this.fetchAuditTrails();
        } catch (error) {
            this.showToast(error.message, "error");
            this.closeDeleteModal();
        }
    }

    async fetchRoles() {
        try {
            const response = await fetch("/api/admin/roles");
            if (!response.ok) throw new Error("Could not fetch roles");
            this.roles = await response.json();
            this.populateRoleDropdowns();
        } catch (error) {
            this.showToast("Failed to load roles for filters.", "error");
        }
    }

    populateRoleDropdowns() {
        this.elements.userRoleFilter.innerHTML =
            '<option value="">All Roles</option>';
        this.elements.userRole.innerHTML =
            '<option value="">Select a Role</option>';
        this.elements.auditTrailRoleFilter.innerHTML =
            '<option value="">All Roles</option>';

        this.roles.forEach((role) => {
            if (role.name !== "Admin") {
                const filterOption = new Option(role.name, role.id);
                this.elements.userRoleFilter.add(filterOption);
                const modalOption = new Option(role.name, role.id);
                this.elements.userRole.add(modalOption);
            }
            const auditOption = new Option(role.name, role.id);
            this.elements.auditTrailRoleFilter.add(auditOption);
        });
    }

    validateContactNumber() {
        const contactInput = this.elements.userContactNumber;
        const errorElement = this.elements.contactNumberError;
        const saveButton = this.elements.saveUserBtn;

        contactInput.value = contactInput.value.replace(/[^0-9]/g, "");
        const philippineNumberRegex = /^09\d{9}$/;

        if (contactInput.value === "") {
            errorElement.classList.add("hidden");
            contactInput.classList.remove("border-red-500");
            saveButton.disabled = false;
            return true;
        }

        if (philippineNumberRegex.test(contactInput.value)) {
            errorElement.classList.add("hidden");
            contactInput.classList.remove("border-red-500");
            saveButton.disabled = false;
            return true;
        } else {
            errorElement.textContent =
                "Must be a valid 11-digit number starting with 09.";
            errorElement.classList.remove("hidden");
            contactInput.classList.add("border-red-500");
            saveButton.disabled = true;
            return false;
        }
    }

    setupAuditTrailEventListeners() {
        if (!this.elements.mainContent) return;

        // Infinite scroll listener
        this.elements.mainContent.addEventListener("scroll", () => {
            if (this.state.activeSection === "auditTrailsSection") {
                const isNearBottom =
                    this.elements.mainContent.scrollTop +
                        this.elements.mainContent.clientHeight >=
                    this.elements.mainContent.scrollHeight - 200;
                if (isNearBottom) {
                    this.fetchAuditTrails();
                }
            }
        });

        // Search input listener
        this.elements.auditTrailSearchInput.addEventListener("input", (e) => {
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(() => {
                this.handleAuditFilterChange("search", e.target.value);
            }, 500); // Debounce for 500ms
        });

        // Role filter listener
        this.elements.auditTrailRoleFilter.addEventListener("change", (e) => {
            this.handleAuditFilterChange("role", e.target.value);
        });

        // Date range buttons listener
        this.elements.auditTrailDateFilter.addEventListener("click", (e) => {
            const button = e.target.closest(".date-range-btn");
            if (button) {
                this.elements.auditTrailDateFilter
                    .querySelectorAll(".date-range-btn")
                    .forEach((btn) => btn.classList.remove("active"));
                button.classList.add("active");
                this.applyDateFilter(button.dataset.range);
            }
        });
    }

    applyDateFilter(range) {
        const today = new Date();
        let startDate = "";
        let endDate = "";

        const formatDate = (date) => date.toISOString().split("T")[0];

        switch (range) {
            case "today":
                startDate = formatDate(today);
                endDate = formatDate(today);
                break;
            case "last7days":
                const pastDate = new Date();
                pastDate.setDate(today.getDate() - 6);
                startDate = formatDate(pastDate);
                endDate = formatDate(today);
                break;
            case "this_month":
                startDate = formatDate(
                    new Date(today.getFullYear(), today.getMonth(), 1)
                );
                endDate = formatDate(today);
                break;
            case "all":
            default:
                // No date filter needed for 'all'
                break;
        }

        this.handleAuditFilterChange("start_date", startDate);
        this.handleAuditFilterChange("end_date", endDate);
    }

    handleAuditFilterChange(key, value) {
        this.auditTrailFilters[key] = value;
        // Reset state for a new filtered search
        this.auditTrails = [];
        this.auditTrailsPage = 1;
        this.auditTrailsHasMore = true;
        this.elements.auditTrailsTableBody.innerHTML = ""; // Clear table immediately
        this.fetchAuditTrails();
    }

    async fetchAuditTrails() {
        if (this.isFetchingAuditTrails || !this.auditTrailsHasMore) return;

        this.isFetchingAuditTrails = true;
        this.elements.auditTrailsLoader.classList.remove("hidden");

        const params = new URLSearchParams(this.auditTrailFilters);
        params.append("page", this.auditTrailsPage);

        try {
            const response = await fetch(
                `/api/audit-trails?${params.toString()}`
            );
            if (!response.ok) throw new Error("Could not fetch audit trails.");

            const data = await response.json();

            this.auditTrails.push(...data.data);
            this.auditTrailsHasMore = data.next_page_url !== null;
            this.auditTrailsPage++;
            this.renderAuditTrails();
        } catch (error) {
            console.error("Failed to fetch audit trails:", error);
            this.showToast("Failed to load audit trail data.", "error");
        } finally {
            this.isFetchingAuditTrails = false;
            this.elements.auditTrailsLoader.classList.add("hidden");
        }
    }

    renderAuditTrails() {
        const tableBody = this.elements.auditTrailsTableBody;

        if (this.auditTrails.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-500">No audit trail records found for the selected filters.</td></tr>`;
            return;
        }

        const resultClasses = {
            Success: "text-green-600",
            Failed: "text-red-600",
            Error: "text-orange-600",
        };

        // Append new rows instead of replacing innerHTML for infinite scroll
        const newRowsHtml = this.auditTrails
            .slice((this.auditTrailsPage - 2) * 25) // Only render the newly fetched items
            .map((log) => {
                const formattedDate = new Date(log.date_time).toLocaleString(
                    "en-US",
                    {
                        year: "numeric",
                        month: "short",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit",
                        hour12: true,
                    }
                );
                const resultClass =
                    resultClasses[log.result] || "text-gray-600";
                return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td data-label="Date & Time" class="border border-gray-200 px-4 py-3">${formattedDate}</td>
                    <td data-label="User" class="border border-gray-200 px-4 py-3">${log.user_name}</td>
                    <td data-label="Role" class="border border-gray-200 px-4 py-3">${log.user_role}</td>
                    <td data-label="Action" class="border border-gray-200 px-4 py-3">${log.action}</td>
                    <td data-label="Module" class="border border-gray-200 px-4 py-3">${log.module}</td>
                    <td data-label="Result" class="border border-gray-200 px-4 py-3 font-medium ${resultClass}">${log.result}</td>
                </tr>
            `;
            })
            .join("");

        tableBody.insertAdjacentHTML("beforeend", newRowsHtml);
    }

    setupNotificationSectionEventListeners() {
        // The flag at the top is correct, keep it.
        if (this.listenersInitialized.notificationSection) return;

        // Listeners for the main "Notification" page elements
        if (this.elements.editSmsSettingsBtn) {
            this.elements.editSmsSettingsBtn.addEventListener("click", () =>
                this.toggleSmsSettingsEditMode(true)
            );
        }
        if (this.elements.cancelSmsSettingsBtn) {
            this.elements.cancelSmsSettingsBtn.addEventListener("click", () =>
                this.toggleSmsSettingsEditMode(false)
            );
        }
        if (this.elements.saveSmsSettingsBtn) {
            this.elements.saveSmsSettingsBtn.addEventListener("click", () =>
                this.saveSmsSettings()
            );
        }

        if (this.elements.readingEditRequestsTableBody) {
            this.elements.readingEditRequestsTableBody.addEventListener(
                "click",
                (e) => {
                    const approveBtn = e.target.closest(".approve-request-btn");
                    const rejectBtn = e.target.closest(".reject-request-btn");

                    if (approveBtn) {
                        this.updateEditRequestStatus(
                            approveBtn.dataset.id,
                            "approved"
                        );
                    }
                    if (rejectBtn) {
                        this.updateEditRequestStatus(
                            rejectBtn.dataset.id,
                            "rejected"
                        );
                    }
                }
            );
        }

        this.listenersInitialized.notificationSection = true;
    }

    async fetchReadingEditRequests() {
        if (this.isFetchingReadingRequests || !this.readingEditRequestsHasMore)
            return;

        this.isFetchingReadingRequests = true;
        if (this.elements.readingEditRequestsLoader)
            this.elements.readingEditRequestsLoader.style.display = "block";

        try {
            // UPDATED: Removed '/api' prefix from the URL
            const response = await fetch(
                `/reading-edit-requests?page=${this.readingEditRequestsPage}`
            );
            if (!response.ok) throw new Error("Could not fetch requests");

            const data = await response.json();

            const formattedData = data.data.map((req) => ({
                id: req.id,
                request_date: req.created_at,
                request_reason: req.reason,
                status: req.status,
            }));

            this.readingEditRequests.push(...formattedData);
            this.readingEditRequestsHasMore = data.next_page_url !== null;
            this.readingEditRequestsPage++;
            this.renderReadingEditRequestsTable();
        } catch (error) {
            this.showToast("Failed to load edit requests.", "error");
        } finally {
            this.isFetchingReadingRequests = false;
            if (this.elements.readingEditRequestsLoader)
                this.elements.readingEditRequestsLoader.style.display = "none";
        }
    }

    async fetchSmsSettings() {
        try {
            const response = await fetch("/api/user-settings/role-contacts");
            if (!response.ok) throw new Error("Could not fetch contacts");
            this.roleContacts = await response.json();
            this.renderSmsSettingsTable();
        } catch (error) {
            this.showToast("Failed to load SMS settings.", "error");
        }
    }

    renderReadingEditRequestsTable() {
        const tableBody = this.elements.readingEditRequestsTableBody;
        if (!tableBody) return; // Add a guard clause

        if (this.readingEditRequests.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">No edit requests found.</td></tr>`;
            return;
        }

        const statusClasses = {
            pending: "bg-yellow-100 text-yellow-800",
            approved: "bg-green-100 text-green-800",
            rejected: "bg-red-100 text-red-800",
        };

        tableBody.innerHTML = this.readingEditRequests
            .map((req) => {
                const formattedDate = new Date(
                    req.request_date
                ).toLocaleDateString("en-US", {
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                });
                return `
            <tr class="hover:bg-gray-50">
                <td data-label="Reuest Date" class="border p-3">${formattedDate}</td>
                <td data-label="Request Reason" class="border p-3">${
                    req.request_reason
                }</td>
                <td data-label="Status" class="border p-3">
                    <span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs ${
                        statusClasses[req.status]
                    }">
                        ${
                            req.status.charAt(0).toUpperCase() +
                            req.status.slice(1)
                        }
                    </span>
                </td>
                <td data-label="Action" class="border p-3 text-center">
                    ${
                        req.status === "pending"
                            ? `
                    <button data-id="${req.id}" class="approve-request-btn text-green-600 hover:text-green-900 mr-2" title="Approve"><i class="fas fa-check-circle fa-lg"></i></button>
                    <button data-id="${req.id}" class="reject-request-btn text-red-600 hover:text-red-900" title="Reject"><i class="fas fa-times-circle fa-lg"></i></button>
                    `
                            : "-"
                    }
                </td>
            </tr>
        `;
            })
            .join("");
    }

    toggleSmsSettingsEditMode(isEditing) {
        this.elements.smsSettingsDefaultButtons.classList.toggle(
            "hidden",
            isEditing
        );
        this.elements.smsSettingsEditButtons.classList.toggle(
            "hidden",
            !isEditing
        );
        this.renderSmsSettingsTable(isEditing);
    }

    async updateEditRequestStatus(requestId, newStatus) {
        try {
            // UPDATED: Removed '/api' prefix from the URL
            const response = await fetch(
                `/reading-edit-requests/${requestId}/status`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({ status: newStatus }),
                }
            );

            if (!response.ok) {
                throw new Error("Failed to update status.");
            }

            this.showToast(`Request has been ${newStatus}.`, "success");

            const requestIndex = this.readingEditRequests.findIndex(
                (req) => req.id == requestId
            );
            if (requestIndex > -1) {
                this.readingEditRequests[requestIndex].status = newStatus;
                this.renderReadingEditRequestsTable();
            }
        } catch (error) {
            this.showToast("Failed to update request status.", "error");
        }
    }

    renderSmsSettingsTable(isEditing = false) {
        const tableBody = this.elements.smsSettingsTableBody;
        tableBody.innerHTML = this.roleContacts
            .map(
                (contact) => `
        <tr class="hover:bg-gray-50 transition-colors" data-id="${contact.id}">
            <td data-label="User" class="border border-gray-200 px-4 py-3 font-medium text-gray-700">${
                contact.role_name
            }</td>
            <td data-label="Number" class="border border-gray-200 px-4 py-3 text-gray-700">
                ${
                    isEditing
                        ? `<input type="text" class="sms-contact-input w-full border border-gray-300 rounded-lg px-3 py-2" value="${
                              contact.contact_number || ""
                          }" placeholder="Enter phone number">`
                        : contact.contact_number ||
                          '<span class="text-gray-400">Not set</span>'
                }
            </td>
        </tr>
    `
            )
            .join("");
    }

    async handleEditRequest(id, status) {
        try {
            const response = await fetch(`/api/reading-edit-requests/${id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ status: status }),
            });
            if (!response.ok)
                throw new Error(`Failed to ${status} the request.`);

            this.showToast(`Request has been ${status}.`, "success");
            await this.fetchEditRequests();
        } catch (error) {
            this.showToast(error.message, "error");
        }
    }

    async saveSmsSettings() {
        const updatedContacts = [];
        this.elements.smsSettingsTableBody
            .querySelectorAll("tr")
            .forEach((row) => {
                updatedContacts.push({
                    id: parseInt(row.dataset.id),
                    contact_number:
                        row.querySelector(".sms-contact-input").value,
                });
            });

        try {
            const response = await fetch("/api/user-settings/role-contacts", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ contacts: updatedContacts }),
            });

            if (!response.ok) {
                throw new Error("Server returned an error. Please try again.");
            }

            this.showToast("Contact numbers updated!", "success");
            this.toggleSmsSettingsEditMode(false);
            await this.fetchSmsSettings();
        } catch (error) {
            this.showToast(
                error.message || "Failed to save contact numbers.",
                "error"
            );
        }
    }

    renderUtilityRatesTable(isEditing = false) {
        if (!this.elements.utilityRatesTableBody) return;

        if (isEditing) {
            // Edit mode with styling that matches the view mode
            this.elements.utilityRatesTableBody.innerHTML = this.utilityRates
                .map(
                    (rate) => `
                <tr class="bg-gradient-to-r from-gray-50 to-gray-100" data-id="util-${
                    rate.id
                }">
                    <td data-label="Utility" class="border border-gray-200 px-4 py-3 text-gray-700">${
                        rate.utility
                    }</td>
                    
                    <td data-label="Rate" class="border border-gray-200 px-4 py-3 text-gray-700"> 
                        
                        <input type="number" 
                               class="edit-utility-rate no-spinner w-full h-full bg-transparent text-left text-gray-700 focus:outline-none focus:bg-gray-100 rounded-lg px-4 py-1 transition" 
                               value="${parseFloat(rate.rate)}" 
                               min="0" 
                               step="0.01">
                    </td>
                </tr>`
                )
                .join("");
        } else {
            // View mode (remains the same)
            this.elements.utilityRatesTableBody.innerHTML = this.utilityRates
                .map(
                    (rate) => `
                <tr class="hover:bg-gray-50 transition-colors" data-id="util-${
                    rate.id
                }">
                    <td data-label="Utility" class="border border-gray-200 px-4 py-3 text-gray-700 font-medium">${
                        rate.utility
                    }</td>
                    <td data-label="Rate" class="border border-gray-200 px-4 py-3 text-gray-700">₱${rate.rate.toFixed(
                        2
                    )} / ${rate.unit}</td>
                </tr>`
                )
                .join("");
        }
    }

    toggleUtilityRatesEditMode(isEditing) {
        this.elements.utilityRatesDefaultButtons.classList.toggle(
            "hidden",
            isEditing
        );
        this.elements.utilityRatesEditButtons.classList.toggle(
            "hidden",
            !isEditing
        );

        this.renderUtilityRatesTable(isEditing);
    }

    async saveAllUtilityRates() {
        const updatedRatesPayload = [];
        const newLocalRatesState = JSON.parse(
            JSON.stringify(this.utilityRates)
        );
        let hasError = false;

        this.elements.utilityRatesTableBody
            .querySelectorAll("tr")
            .forEach((row) => {
                const id = parseInt(row.dataset.id.split("-")[1]);
                const rateInput = row.querySelector(".edit-utility-rate");
                const rateValue = parseFloat(rateInput.value);

                if (isNaN(rateValue) || rateValue < 0) {
                    hasError = true;
                    rateInput.classList.add("border-red-500");
                } else {
                    const rateToUpdate = newLocalRatesState.find(
                        (r) => r.id === id
                    );
                    if (rateToUpdate) {
                        rateToUpdate.rate = rateValue;
                    }
                    updatedRatesPayload.push({ id, rate: rateValue });
                }
            });

        if (hasError) {
            this.showToast("Please enter valid, non-negative rates.", "error");
            return;
        }

        const oldUtilityRates = JSON.parse(JSON.stringify(this.utilityRates)); // Deep copy for rollback

        //  Immediately update the main rates table UI
        this.utilityRates = newLocalRatesState;
        this.toggleUtilityRatesEditMode(false);

        //  IMMEDIATELY CREATE and DISPLAY temporary history logs
        const newHistoryLogs = [];
        updatedRatesPayload.forEach((updatedRate) => {
            const oldRate = oldUtilityRates.find(
                (r) => r.id === updatedRate.id
            );
            // Only create a log if the rate actually changed
            if (oldRate && oldRate.rate !== updatedRate.rate) {
                const optimisticLog = {
                    utility_type: oldRate.utility,
                    old_rate: oldRate.rate,
                    new_rate: updatedRate.rate,
                    changed_at: new Date().toISOString(), // Use browser time for instant display
                };
                newHistoryLogs.push(optimisticLog);
            }
        });

        // Add the new temporary logs to the top of the list and re-render
        if (newHistoryLogs.length > 0) {
            this.utilityRateHistory.unshift(...newHistoryLogs);
            this.renderUtilityRateHistoryTable();
        }

        //  Save to the server in the background
        try {
            const response = await fetch("/api/utility-rates/batch-update", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ rates: updatedRatesPayload }),
            });

            if (!response.ok) {
                throw new Error(
                    (await response.json()).message || "Failed to save changes."
                );
            }

            // On SUCCESS: The UI is already correct. Just show a success message.
            this.showToast("Utility rates updated successfully!", "success");

            // Silently refresh the history in the background to get server-authoritative data
            this.utilityRateHistory = [];
            this.utilityRateHistoryPage = 1;
            this.utilityRateHistoryHasMore = true;
            await this.fetchUtilityRateHistory();
        } catch (error) {
            //On FAILURE: Roll back both UI changes and show an error
            this.showToast(error.message, "error");
            this.utilityRates = oldUtilityRates; // Restore the old rates data
            this.renderUtilityRatesTable(false); // Re-render the main table

            // Remove the temporary history logs we added
            this.utilityRateHistory.splice(0, newHistoryLogs.length);
            this.renderUtilityRateHistoryTable();
        }
    }

    renderUtilityRateHistoryTable() {
        if (!this.elements.utilityRateHistoryTableBody) return;

        if (this.utilityRateHistory.length === 0) {
            this.elements.utilityRateHistoryTableBody.innerHTML = `
                  <tr>
                      <td colspan="4" class="text-center py-4 text-gray-500">No history logs found.</td>
                  </tr>
              `;
            return;
        }

        this.elements.utilityRateHistoryTableBody.innerHTML =
            this.utilityRateHistory
                .map((log) => {
                    const formattedDate = new Date(
                        log.changed_at
                    ).toLocaleString("en-US", {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: true,
                    });
                    return `
                          <tr class="hover:bg-gray-50 transition-colors">
                              <td data-label="Date & Time" class="border border-gray-200 px-4 py-3 text-gray-700">${formattedDate}</td>
                              <td data-label="Utility Type" class="border border-gray-200 px-4 py-3 text-gray-700">${
                                  log.utility_type
                              }</td>
                              <td data-label="Old Rate" class="border border-gray-200 px-4 py-3 text-gray-700">₱${parseFloat(
                                  log.old_rate
                              ).toFixed(2)}</td>
                              <td data-label="New Rate" class="border border-gray-200 px-4 py-3 text-gray-700">₱${parseFloat(
                                  log.new_rate
                              ).toFixed(2)}</td>
                          </tr>
                      `;
                })
                .join("");
    }

    setupScheduleEventListeners() {
        this.elements.editScheduleBtn.addEventListener("click", () =>
            this.toggleScheduleEditMode(true)
        );
        this.elements.cancelScheduleBtn.addEventListener("click", () =>
            this.toggleScheduleEditMode(false)
        );
        this.elements.saveScheduleBtn.addEventListener("click", () =>
            this.saveMeterReadingSchedule()
        );
    }

    async fetchMeterReadingSchedule() {
        try {
            const response = await fetch("/api/schedules/meter-reading");
            if (!response.ok) throw new Error("Network response was not ok");

            const data = await response.json();

            this.currentSchedule = {
                id: data.id,
                day: parseInt(data.description),
            };

            this.renderMeterReadingSchedule();
        } catch (error) {
            console.error("Failed to fetch meter reading schedule:", error);
            this.showToast("Failed to load schedule from the server.", "error");
        }
    }

    async fetchMeterReadingScheduleHistory() {
        if (this.isFetchingScheduleHistory || !this.scheduleHistoryHasMore)
            return;
        this.isFetchingScheduleHistory = true;
        if (this.elements.scheduleHistoryLoader)
            this.elements.scheduleHistoryLoader.style.display = "block";

        try {
            const response = await fetch(
                `/api/schedules/meter-reading/history?page=${this.scheduleHistoryPage}`
            );
            if (!response.ok) throw new Error("Network response was not ok");
            const data = await response.json();

            this.scheduleHistory.push(...data.data);
            this.scheduleHistoryHasMore = data.next_page_url !== null;
            this.scheduleHistoryPage++;
            this.renderScheduleHistoryTable();
        } catch (error) {
            console.error("Failed to fetch schedule history:", error);
            this.showToast(
                "Failed to load schedule history from the server.",
                "error"
            );
        } finally {
            this.isFetchingScheduleHistory = false;
            if (this.elements.scheduleHistoryLoader)
                this.elements.scheduleHistoryLoader.style.display = "none";
        }
    }

    async fetchBillingDateSchedules() {
        try {
            const response = await fetch("/api/schedules/billing-dates");
            if (!response.ok) throw new Error("Network response was not ok");
            this.billingDateSchedules = await response.json();
            this.renderBillingDateSchedules(); // Render in view mode initially
        } catch (error) {
            console.error("Failed to fetch billing date schedules:", error);
            this.showToast("Failed to load billing schedules.", "error");
        }
    }

    async fetchBillingDateHistory() {
        if (this.isFetchingBillingHistory || !this.billingDateHistoryHasMore)
            return;
        this.isFetchingBillingHistory = true;
        if (this.elements.billingDatesHistoryLoader)
            this.elements.billingDatesHistoryLoader.style.display = "block";

        try {
            const response = await fetch(
                `/api/schedules/billing-dates/history?page=${this.billingDateHistoryPage}`
            );
            if (!response.ok) throw new Error("Network response was not ok");
            const data = await response.json();

            this.billingDateHistory.push(...data.data);
            this.billingDateHistoryHasMore = data.next_page_url !== null;
            this.billingDateHistoryPage++;
            this.renderBillingDateHistory();
        } catch (error) {
            console.error("Failed to fetch billing date history:", error);
            this.showToast("Failed to load billing schedule history.", "error");
        } finally {
            this.isFetchingBillingHistory = false;
            if (this.elements.billingDatesHistoryLoader)
                this.elements.billingDatesHistoryLoader.style.display = "none";
        }
    }

    renderBillingDateSchedules(isEditing = false) {
        const tableBody = this.elements.billingDatesTableBody;
        if (!tableBody) return;

        const findSchedule = (type) =>
            this.billingDateSchedules.find((s) => s.schedule_type === type);

        const createDayDropdown = (schedule, scheduleType, isEnabled) => {
            let options = '<option value="Not Set">Not Set</option>';
            for (let i = 1; i <= 31; i++) {
                options += `<option value="${i}">${i}</option>`;
            }
            const select = document.createElement("select");
            select.innerHTML = options;
            // Use the schedule's description if it exists, otherwise default to "Not Set"
            select.value = schedule ? schedule.description : "Not Set";
            select.disabled = !isEnabled;
            select.dataset.id = schedule ? schedule.id : "";
            select.dataset.type = scheduleType; // Always use the explicitly passed scheduleType
            select.className =
                "billing-date-select w-full border border-gray-300 rounded-lg px-3 py-2 bg-white";
            return select.outerHTML;
        };

        const formatDay = (day) => {
            if (["Not Set", "N/A", "End of the month"].includes(day) || !day) {
                return `<strong>${day || "Not Set"}</strong>`;
            }
            const dayNum = parseInt(day);
            if (isNaN(dayNum)) return `<strong>${day}</strong>`;
            const suffix =
                ["th", "st", "nd", "rd"][
                    ((((dayNum + 90) % 100) - 10) % 10) - 1
                ] || "th";
            return `<strong>${dayNum}${suffix} day of the month</strong>`;
        };

        const utilities = ["Rent", "Electricity", "Water"];
        let tableHTML = "";

        utilities.forEach((util) => {
            const dueDateType = `Due Date - ${util}`;
            const discoDateType = `Disconnection - ${util}`;

            const dueDateSchedule = findSchedule(dueDateType);
            const discoDateSchedule = findSchedule(discoDateType);

            let dueDateCell = "";
            let discoDateCell = "";

            if (util === "Rent") {
                dueDateCell = formatDay("End of the month");
                discoDateCell = formatDay("N/A");
            } else {
                dueDateCell = isEditing
                    ? createDayDropdown(dueDateSchedule, dueDateType, true)
                    : formatDay(dueDateSchedule?.description);
                discoDateCell = isEditing
                    ? createDayDropdown(discoDateSchedule, discoDateType, true)
                    : formatDay(discoDateSchedule?.description);
            }

            tableHTML += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td data-label="Utility Category" class="border border-gray-200 px-4 py-3 text-gray-700 font-medium">${util}</td>
                    <td data-label="Due Date" class="border border-gray-200 px-4 py-3 text-gray-700">${dueDateCell}</td>
                    <td data-label="Disconnection Date" class="border border-gray-200 px-4 py-3 text-gray-700">${discoDateCell}</td>
                </tr>
            `;
        });

        tableBody.innerHTML = tableHTML;
    }

    renderBillingDateHistory() {
        const tableBody = this.elements.billingDatesHistoryTableBody;
        if (!tableBody) return;

        if (this.billingDateHistory.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">No history logs found.</td></tr>`;
            return;
        }

        // Helper to format day values for display
        const formatDayValue = (day) => {
            if (day === "Not Set" || !day) return "Not Set";
            const dayNum = parseInt(day);
            if (isNaN(dayNum)) return day; // Should not happen but good practice
            const suffix =
                ["th", "st", "nd", "rd"][
                    ((((dayNum + 90) % 100) - 10) % 10) - 1
                ] || "th";
            return `${dayNum}${suffix} day`;
        };

        tableBody.innerHTML = this.billingDateHistory
            .map((log) => {
                const formattedDate = new Date(log.changed_at).toLocaleString(
                    "en-US",
                    {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: true,
                    }
                );

                return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td data-label="Date & Time" class="border border-gray-200 px-4 py-3 text-gray-700">${formattedDate}</td>
                    <td data-label="Item Changed" class="border border-gray-200 px-4 py-3 text-gray-700">${
                        log.item_changed
                    }</td>
                    <td data-label="Old Schedule" class="border border-gray-200 px-4 py-3 text-gray-700">${formatDayValue(
                        log.old_value
                    )}</td>
                    <td data-label="New Schedule" class="border border-gray-200 px-4 py-3 text-gray-700">${formatDayValue(
                        log.new_value
                    )}</td>
                </tr>
            `;
            })
            .join("");
    }

    setupBillingDateEventListeners() {
        this.elements.editBillingDatesBtn.addEventListener("click", () =>
            this.toggleBillingDatesEditMode(true)
        );
        this.elements.cancelBillingDatesBtn.addEventListener("click", () =>
            this.toggleBillingDatesEditMode(false)
        );
        this.elements.saveBillingDatesBtn.addEventListener("click", () =>
            this.saveBillingDateSchedules()
        );
    }

    toggleBillingDatesEditMode(isEditing) {
        this.elements.billingDatesDefaultButtons.classList.toggle(
            "hidden",
            isEditing
        );
        this.elements.billingDatesEditButtons.classList.toggle(
            "hidden",
            !isEditing
        );
        this.renderBillingDateSchedules(isEditing);
    }

    async saveBillingDateSchedules() {
        const updatedSchedulesPayload = [];
        const selects = this.elements.billingDatesTableBody.querySelectorAll(
            ".billing-date-select:not([disabled])"
        );
        selects.forEach((select) => {
            updatedSchedulesPayload.push({
                type: select.dataset.type,
                day: select.value,
            });
        });

        if (updatedSchedulesPayload.length === 0) {
            this.toggleBillingDatesEditMode(false);
            return;
        }

        const oldSchedules = JSON.parse(
            JSON.stringify(this.billingDateSchedules)
        );
        const oldHistory = JSON.parse(JSON.stringify(this.billingDateHistory));
        const newHistoryLogs = [];

        //  Immediately update local state and create temporary history logs
        updatedSchedulesPayload.forEach((updated) => {
            const oldSchedule = oldSchedules.find(
                (s) => s.schedule_type === updated.type
            );
            const oldValue = oldSchedule ? oldSchedule.description : "Not Set";

            if (oldValue !== updated.day) {
                newHistoryLogs.push({
                    item_changed: updated.type,
                    old_value: oldValue,
                    new_value: updated.day,
                    changed_at: new Date().toISOString(),
                });
            }

            let existingSchedule = this.billingDateSchedules.find(
                (s) => s.schedule_type === updated.type
            );
            if (existingSchedule) {
                existingSchedule.description = updated.day;
            } else {
                this.billingDateSchedules.push({
                    schedule_type: updated.type,
                    description: updated.day,
                });
            }
        });

        //  Immediately update the UI
        this.toggleBillingDatesEditMode(false); // Re-renders the main table
        if (newHistoryLogs.length > 0) {
            this.billingDateHistory.unshift(...newHistoryLogs);
            this.renderBillingDateHistory(); // Re-renders the history table
        }

        // . Save to the server in the background
        try {
            const response = await fetch("/api/schedules/billing-dates", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ schedules: updatedSchedulesPayload }),
            });

            if (!response.ok) {
                throw new Error(
                    (await response.json()).message ||
                        "Failed to update schedules."
                );
            }

            //  On success, show confirmation and silently refresh history
            this.showToast("Schedules updated successfully!", "success");
            this.billingDateHistory = [];
            this.billingDateHistoryPage = 1;
            this.billingDateHistoryHasMore = true;
            await this.fetchBillingDateHistory();
        } catch (error) {
            //  On failure, roll back all UI changes
            this.showToast(error.message, "error");
            this.billingDateSchedules = oldSchedules;
            this.billingDateHistory = oldHistory;
            this.renderBillingDateSchedules(false);
            this.renderBillingDateHistory();
        }
    }

    renderMeterReadingSchedule() {
        if (!this.currentSchedule) return;
        const day = this.currentSchedule.day;
        const suffix =
            day % 10 === 1 && day !== 11
                ? "st"
                : day % 10 === 2 && day !== 12
                ? "nd"
                : day % 10 === 3 && day !== 13
                ? "rd"
                : "th";
        this.elements.scheduleDayDisplay.textContent = `${day}${suffix}`;
        this.elements.scheduleDayInput.value = day;
    }

    renderScheduleHistoryTable() {
        if (!this.elements.scheduleHistoryTableBody) return;

        if (!this.scheduleHistory || this.scheduleHistory.length === 0) {
            this.elements.scheduleHistoryTableBody.innerHTML = `
                  <tr>
                      <td colspan="3" class="text-center py-4 text-gray-500">No history logs found.</td>
                  </tr>
              `;
            return;
        }

        this.elements.scheduleHistoryTableBody.innerHTML = this.scheduleHistory
            .map((log) => {
                const formattedDate = new Date(log.changed_at).toLocaleString(
                    "en-US",
                    {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: true,
                    }
                );

                const formatDay = (day) => {
                    const dayNum = parseInt(day);
                    if (isNaN(dayNum)) return day;
                    const suffix =
                        dayNum % 10 === 1 && dayNum !== 11
                            ? "st"
                            : dayNum % 10 === 2 && dayNum !== 12
                            ? "nd"
                            : dayNum % 10 === 3 && dayNum !== 13
                            ? "rd"
                            : "th";
                    return `${dayNum}${suffix}`;
                };

                return `
                      <tr class="hover:bg-gray-50 transition-colors">
                          <td data-label="Date & Time" class="border border-gray-200 px-4 py-3 text-gray-700">${formattedDate}</td>
                          <td data-label="Old Schedule Day" class="border border-gray-200 px-4 py-3 text-gray-700">${formatDay(
                              log.old_value
                          )}</td>
                          <td data-label="New Schedule Day" class="border border-gray-200 px-4 py-3 text-gray-700">${formatDay(
                              log.new_value
                          )}</td>
                      </tr>
                  `;
            })
            .join("");
    }

    toggleScheduleEditMode(isEditing) {
        this.elements.scheduleView.classList.toggle("hidden", isEditing);
        this.elements.scheduleEdit.classList.toggle("hidden", !isEditing);
    }

    async saveMeterReadingSchedule() {
        const newDay = parseInt(this.elements.scheduleDayInput.value);

        if (isNaN(newDay) || newDay < 1 || newDay > 31) {
            this.showToast(
                "Please enter a valid day between 1 and 31.",
                "error"
            );
            return;
        }
        if (!this.currentSchedule || !this.currentSchedule.id) {
            this.showToast(
                "Could not find schedule ID. Please refresh.",
                "error"
            );
            return;
        }

        // --- Optimistic Update Starts Here ---
        const oldSchedule = { ...this.currentSchedule };
        const oldHistory = JSON.parse(JSON.stringify(this.scheduleHistory));

        // 1. Immediately update the UI
        this.currentSchedule.day = newDay;
        this.toggleScheduleEditMode(false); // Instantly re-renders the schedule display

        // 2. Immediately create and display a temporary history log
        const optimisticLog = {
            old_value: oldSchedule.day,
            new_value: newDay,
            changed_at: new Date().toISOString(), // Use browser time for instant display
        };
        this.scheduleHistory.unshift(optimisticLog);
        this.renderScheduleHistoryTable();

        // 3. Save to the server in the background
        try {
            const response = await fetch(
                `/api/schedules/meter-reading/${this.currentSchedule.id}`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({ day: newDay }),
                }
            );
            if (!response.ok) {
                throw new Error(
                    (await response.json()).message ||
                        "Failed to update schedule."
                );
            }

            // On success, show confirmation and silently refresh history for server-authoritative data
            this.showToast("Schedule updated successfully!", "success");
            this.scheduleHistory = [];
            this.scheduleHistoryPage = 1;
            this.scheduleHistoryHasMore = true;
            await this.fetchMeterReadingScheduleHistory();
        } catch (error) {
            // On failure, roll back UI changes and show an error
            this.showToast(error.message, "error");
            this.currentSchedule = oldSchedule; // Restore old schedule data
            this.scheduleHistory = oldHistory; // Restore old history
            this.renderMeterReadingSchedule(); // Re-render with old data
            this.renderScheduleHistoryTable();
        }
    }

    setupUtilityRatesEventListeners() {
        //  Added listeners for the main Edit/Save/Cancel buttons
        this.elements.editUtilityRatesBtn.addEventListener("click", () =>
            this.toggleUtilityRatesEditMode(true)
        );
        this.elements.cancelUtilityRatesBtn.addEventListener("click", () =>
            this.toggleUtilityRatesEditMode(false)
        );
        this.elements.saveUtilityRatesBtn.addEventListener("click", () =>
            this.saveAllUtilityRates()
        );

        // This part for individual row editing is still needed
        this.elements.utilityRatesTableBody.addEventListener("click", (e) => {
            const editBtn = e.target.closest(".edit-utility-btn");
            const saveBtn = e.target.closest(".save-utility-btn");
            const cancelBtn = e.target.closest(".cancel-utility-edit-btn");
            if (editBtn)
                this.enableUtilityRateEditing(parseInt(editBtn.dataset.id));
            if (saveBtn) this.saveUtilityRateEdit(parseInt(saveBtn.dataset.id));
            if (cancelBtn) this.cancelUtilityRateEdit();
        });
    }

    enableUtilityRateEditing(id) {
        // First, cancel any other edit that might be in progress
        this.cancelUtilityRateEdit();

        const row = this.elements.utilityRatesTableBody.querySelector(
            `tr[data-id="util-${id}"]`
        );
        const utilityRate = this.utilityRates.find((rate) => rate.id === id);

        if (!row || !utilityRate) return;

        // Replace the row content with an editing interface
        row.innerHTML = `
            <td class="border border-gray-200 px-4 py-3 text-gray-700 font-medium">${
                utilityRate.utility
            }</td>
            <td class="border border-gray-200 px-4 py-3">
                <input type="number" class="edit-utility-rate-input no-spinner w-full border border-gray-300 rounded px-2 py-1" value="${parseFloat(
                    utilityRate.rate
                ).toFixed(2)}" min="0" step="0.01">
            </td>
            
            <td class="border border-gray-200 px-4 py-3">
                <input type="number" class="edit-monthly-rate-input no-spinner w-full border border-gray-300 rounded px-2 py-1" value="${parseFloat(
                    utilityRate.monthlyRate
                ).toFixed(2)}" min="0" step="0.01">
            </td>
            
            <td class="border border-gray-200 px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                    <button class="save-utility-btn bg-green-500 text-white px-3 py-1 rounded-lg" data-id="${id}" title="Save"><i class="fas fa-save"></i></button>
                    <button class="cancel-utility-edit-btn bg-gray-500 text-white px-3 py-1 rounded-lg" title="Cancel"><i class="fas fa-times"></i></button>
                </div>
            </td>`;

        // Focus the input field
        row.querySelector(".edit-utility-rate-input").focus();
    }

    async saveUtilityRateEdit(id) {
        const row = this.elements.utilityRatesTableBody.querySelector(
            `tr[data-id="util-${id}"]`
        );
        if (!row) return;

        const newRate = parseFloat(
            row.querySelector(".edit-utility-rate-input").value
        );
        // ADDED: Get the value from the new monthly rate input
        const newMonthlyRate = parseFloat(
            row.querySelector(".edit-monthly-rate-input").value
        );

        if (
            isNaN(newRate) ||
            newRate < 0 ||
            isNaN(newMonthlyRate) ||
            newMonthlyRate < 0
        ) {
            this.showToast("Please enter valid, non-negative rates.", "error");
            return;
        }

        try {
            const response = await fetch(`/api/utility-rates/${id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                // MODIFIED: Send both rate and monthlyRate in the request
                body: JSON.stringify({
                    rate: newRate,
                    monthlyRate: newMonthlyRate,
                }),
            });

            if (!response.ok) throw new Error("Failed to update the rate.");

            this.showToast("Utility rate updated successfully!", "success");

            // Refetch all data to ensure consistency
            this.utilityRateHistory = [];
            this.utilityRateHistoryPage = 1;
            this.utilityRateHistoryHasMore = true;
            await this.fetchUtilityRates(); // Refetches and re-renders the table
            await this.fetchUtilityRateHistory();
        } catch (error) {
            this.showToast(error.message, "error");
            this.renderUtilityRatesTable(); // Rollback UI on failure
        }
    }

    cancelUtilityRateEdit() {
        this.renderUtilityRatesTable();
    }

    // In resources/js/superadmin.js

    setupRentalRatesEventListeners() {
        this.elements.addRentalRateBtn.addEventListener("click", () =>
            this.addNewInlineRow()
        );
        this.elements.editAllRatesBtn.addEventListener("click", () =>
            this.toggleRentalRatesEditMode(true)
        );
        this.elements.saveAllRentalRatesBtn.addEventListener("click", () =>
            this.saveAllRentalRates()
        );
        this.elements.cancelEditRatesBtn.addEventListener("click", () =>
            this.toggleRentalRatesEditMode(false)
        );

        this.elements.rentalRatesTableBody.addEventListener("click", (e) => {
            const deleteBtn = e.target.closest(".delete-btn");
            if (deleteBtn) {
                this.openDeleteModal(parseInt(deleteBtn.dataset.id));
            }
        });

        // V V V ADD THIS NEW, EFFICIENT LISTENER V V V
        this.elements.rentalRatesTableBody.addEventListener("input", (e) => {
            // Check if the user is typing in the Rate (per day) or area input
            if (
                e.target.classList.contains("edit-daily-rate") ||
                e.target.classList.contains("edit-area")
            ) {
                const row = e.target.closest("tr"); // Find the parent table row
                this.calculateMonthlyRate(row); // Trigger the calculation for that row
            }
        });
        // ^ ^ ^ END OF NEW LISTENER ^ ^ ^

        this.elements.rentalRatesPagination.addEventListener("click", (e) => {
            const button = e.target.closest(".pagination-link");
            if (button && button.dataset.url) {
                const url = new URL(button.dataset.url, window.location.origin);
                const page = url.searchParams.get("page");
                this.filterAndRenderRates(page);
            }
        });

        this.elements.confirmDelete.addEventListener("click", () =>
            this.deleteRentalRate()
        );
        this.elements.cancelDelete.addEventListener("click", () =>
            this.closeDeleteModal()
        );
        this.elements.sectionNavBtns.forEach((btn) =>
            btn.addEventListener("click", () =>
                this.handleSectionNavigation(btn)
            )
        );
        this.elements.rentalRatesTableBody.addEventListener("input", (e) => {
            // Check if the user is typing in either the Rate (per day) or area input
            if (
                e.target.classList.contains("edit-daily-rate") ||
                e.target.classList.contains("edit-area")
            ) {
                const row = e.target.closest("tr"); // Find the parent table row
                this.calculateMonthlyRate(row); // Trigger the calculation for that row
            }
        });
    }

    toggleRentalRatesEditMode(isEditing) {
        this.isRentalRatesEditing = isEditing;
        this.elements.rentalRatesDefaultButtons.classList.toggle(
            "hidden",
            isEditing
        );
        this.elements.rentalRatesEditButtons.classList.toggle(
            "hidden",
            !isEditing
        );
        if (this.elements.rentalRatesActionHeader) {
            this.elements.rentalRatesActionHeader.classList.toggle(
                "hidden",
                !isEditing
            );
        }
        this.filterAndRenderRates(this.rentalRatesPagination.current_page || 1);
    }

    async saveAllRentalRates() {
        const updatedStallsPayload = [];
        const newLocalRatesState = JSON.parse(
            JSON.stringify(this.allRentalRates)
        );
        let hasError = false;

        this.elements.rentalRatesTableBody
            .querySelectorAll("tr")
            .forEach((row) => {
                const id = parseInt(row.dataset.id);
                if (!id) return;

                const stallData = {
                    id: id,
                    tableNumber: row.querySelector(".edit-table-number").value,
                    dailyRate: parseFloat(
                        row.querySelector(".edit-daily-rate").value
                    ),
                    monthlyRate: parseFloat(
                        row.querySelector(".edit-monthly-rate").value
                    ),
                    area: row.querySelector(".edit-area")
                        ? parseFloat(row.querySelector(".edit-area").value)
                        : null,
                };

                if (
                    !stallData.tableNumber ||
                    isNaN(stallData.dailyRate) ||
                    isNaN(stallData.monthlyRate)
                ) {
                    hasError = true;
                }

                const rateToUpdate = newLocalRatesState.find(
                    (r) => r.id === id
                );
                if (rateToUpdate) {
                    Object.assign(rateToUpdate, stallData);
                }

                updatedStallsPayload.push(stallData);
            });

        if (hasError) {
            this.showToast("Please fill all fields with valid data.", "error");
            return;
        }

        const oldAllRentalRates = this.allRentalRates;
        this.allRentalRates = newLocalRatesState;
        // FIXED: Added a missing semicolon to the end of this line
        this.toggleRentalRatesEditMode(false);

        try {
            const response = await fetch("/api/rental-rates/batch-update", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ stalls: updatedStallsPayload }),
            });

            if (!response.ok) {
                throw new Error(
                    (await response.json()).message || "Failed to save changes."
                );
            }

            this.showToast("Rates updated successfully!", "success");
        } catch (error) {
            this.showToast(error.message, "error");
            this.allRentalRates = oldAllRentalRates;
            this.filterAndRenderRates(
                this.rentalRatesPagination.current_page || 1
            );
        }
    }

    filterRentalRates(sectionFilter) {
        const filteredRates = this.rentalRates.filter(
            (rate) => rate.section === sectionFilter
        );
        this.renderRentalRatesTable(filteredRates);
    }

    renderRentalRatesTable(rates = []) {
        if (this.isRentalRatesEditing) {
            this.renderRentalRatesTableForEditing(rates);
        } else {
            this.renderRentalRatesTableForViewing(rates);
        }
    }

    renderRentalRatesPagination() {
        const { links, from, to, total } = this.rentalRatesPagination;
        if (!total || total <= 15) {
            this.elements.rentalRatesPagination.innerHTML = "";
            return;
        }

        const pageInfo = `<span class="text-sm text-gray-700">Showing ${from} to ${to} of ${total} results</span>`;
        const pageLinks = links
            .map(
                (link) =>
                    `<button data-url="${
                        link.url
                    }" class="pagination-link px-3 py-1 border rounded ${
                        link.active
                            ? "bg-market-primary text-white"
                            : "bg-white"
                    } ${
                        !link.url
                            ? "text-gray-400 cursor-not-allowed"
                            : "text-gray-700"
                    }" ${!link.url ? "disabled" : ""}>${link.label}</button>`
            )
            .join("");

        this.elements.rentalRatesPagination.innerHTML = `${pageInfo}<div class="flex gap-1">${pageLinks}</div>`;
    }

    renderRentalRatesTableForViewing(rates = []) {
        // First, handle the case where there are no rates to display.
        if (rates.length === 0) {
            this.elements.rentalRatesTableBody.innerHTML = `
                <tr class="text-center bg-gray-50">
                    <td colspan="5" class="py-10 px-4 text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search fa-2x text-gray-400 mb-2"></i>
                            <p class="font-semibold">No Stalls Found</p>
                            <p class="text-sm text-gray-400">Try adjusting your search criteria.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        // If there are rates, build the table rows.
        this.elements.rentalRatesTableBody.innerHTML = rates
            .map((rate) => {
                // Conditionally create the 'Area' cell.
                // It will be an HTML string if the section is 'Dry Section', otherwise it's an empty string.
                const areaCell =
                    this.state.currentRentalSection === "Dry Section"
                        ? `<td data-label="Area" class="border border-gray-200 px-4 py-3 text-gray-700">${
                              rate.area || "N/A"
                          } m²</td>`
                        : "";

                // Return the complete table row template string.
                return `
                    <tr class="hover:bg-gray-50 transition-colors" data-id="${
                        rate.id
                    }">
                        <td data-label="Table Number" class="border border-gray-200 px-4 py-3 text-gray-700">${
                            rate.tableNumber
                        }</td>
                        
                        ${areaCell}
                        
                        <td data-label="Rate (per day)" class="border border-gray-200 px-4 py-3 text-gray-700">₱${parseFloat(
                            rate.dailyRate
                        ).toFixed(2)}</td>
                        <td data-label="Monthly Rental" class="border border-gray-200 px-4 py-3 text-gray-700">₱${parseFloat(
                            rate.monthlyRate
                        ).toFixed(2)}</td>
                    </tr>
                `;
            })
            .join("");
    }

    renderRentalRatesTableForEditing(rates = []) {
        // Handle the case where there are no rates.
        if (rates.length === 0) {
            this.elements.rentalRatesTableBody.innerHTML = `
                <tr class="text-center bg-gray-50">
                    <td colspan="5" class="py-10 px-4 text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search fa-2x text-gray-400 mb-2"></i>
                            <p class="font-semibold">No Stalls Found</p>
                            <p class="text-sm text-gray-400">Try adjusting your search criteria.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        // Build the table rows with input fields.
        this.elements.rentalRatesTableBody.innerHTML = rates
            .map((rate) => {
                // Conditionally create the 'Area' input cell.
                const areaCell =
                    this.state.currentRentalSection === "Dry Section"
                        ? `<td data-label="Area" class="border border-gray-200 px-4 py-3"><input type="number" class="edit-area no-spinner w-full border border-gray-300 rounded px-2 py-1" value="${
                              rate.area || ""
                          }" placeholder="0.00" min="0" step="0.01"></td>`
                        : "";

                // Return the complete table row for editing.
                return `
                    <tr class="hover:bg-gray-50 transition-colors" data-id="${
                        rate.id
                    }">
                       <td data-label="Table Number" class="border border-gray-200 px-4 py-3 bg-gray-100">
                            <input type="text" class="edit-table-number w-full border-gray-200 bg-gray-100 rounded px-2 py-1 text-gray-600 cursor-not-allowed" value="${
                                rate.tableNumber
                            }" readonly>
                        </td>
                        
                        ${areaCell}
                        
                        <td data-label="Rate (per day)" class="border border-gray-200 px-4 py-3"><input type="number" class="edit-daily-rate no-spinner w-full border border-gray-300 rounded px-2 py-1" value="${parseFloat(
                            rate.dailyRate
                        ).toFixed(2)}" min="0" step="0.01"></td>
                        <td data-label="Monthly Rental" class="border border-gray-200 px-4 py-3">
                         <input type="number" class="edit-monthly-rate no-spinner w-full border-gray-200 bg-gray-100 rounded px-2 py-1" readonly>
                        </td>
                        <td data-label="Action" class="border border-gray-200 px-4 py-3 text-center">
                            <button class="delete-btn bg-red-500 text-white px-3 py-1 rounded-lg" data-id="${
                                rate.id
                            }"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            })
            .join("");

        this.elements.rentalRatesTableBody
            .querySelectorAll("tr")
            .forEach((row) => {
                this.calculateMonthlyRate(row);
            });
    }

    calculateMonthlyRate(row) {
        const dailyRateInput = row.querySelector(".edit-daily-rate");
        const areaInput = row.querySelector(".edit-area");
        const monthlyRateInput = row.querySelector(".edit-monthly-rate");

        if (!dailyRateInput || !monthlyRateInput) return;

        const dailyRate = parseFloat(dailyRateInput.value) || 0;
        const area = areaInput ? parseFloat(areaInput.value) || 0 : 0;

        let monthlyRate = 0;
        if (area > 0) {
            monthlyRate = dailyRate * area * 30;
        } else {
            monthlyRate = dailyRate * 30;
        }

        monthlyRateInput.value = monthlyRate.toFixed(2);
    }

    handleSectionNavigation(clickedBtn) {
        this.elements.sectionNavBtns.forEach((btn) =>
            btn.classList.remove("active")
        );
        clickedBtn.classList.add("active");
        this.state.currentRentalSection = clickedBtn.dataset.section;

        // This logic now handles both the 'Area' column and the unit text
        if (this.state.currentRentalSection === "Dry Section") {
            this.elements.areaColumnHeader.classList.remove("hidden");
            this.elements.rateUnit.textContent = "(per sq.m. per day)"; // Correctly targets the span
        } else {
            this.elements.areaColumnHeader.classList.add("hidden");
            this.elements.rateUnit.textContent = "(per day)"; // Reverts the span text
        }

        if (this.elements.rentalRatesHeader) {
            this.elements.rentalRatesHeader.textContent = `${this.state.currentRentalSection} Rental Rates`;
        }

        this.filterAndRenderRates(1);
    }

    async addNewInlineRow() {
        if (this.isRentalRatesEditing) {
            this.showToast(
                "Please save or cancel before adding a new row.",
                "info"
            );
            return;
        }

        const currentSection = this.state.currentRentalSection;
        let nextTableNumber = 1;

        try {
            const response = await fetch(
                `/api/sections/${encodeURIComponent(
                    currentSection
                )}/next-table-number`
            );
            if (!response.ok)
                throw new Error("Could not fetch next table number.");
            const data = await response.json();
            nextTableNumber = data.next_table_number;
        } catch (error) {
            this.showToast("Error getting next table number.", "error");
            return;
        }

        const tempId = "new_" + Date.now();
        const newRow = document.createElement("tr");
        newRow.className = "hover:bg-gray-50 transition-colors bg-blue-50";
        newRow.setAttribute("data-id", tempId);

        if (this.elements.rentalRatesActionHeader) {
            this.elements.rentalRatesActionHeader.classList.remove("hidden");
        }

        // Conditionally create the 'Area' input cell if the section is 'Dry Section'
        const areaCellHtml =
            currentSection === "Dry Section"
                ? `
            <td data-label="Area" class="border border-gray-200 px-4 py-3">
                <input type="number" class="edit-area no-spinner w-full border border-gray-300 rounded px-2 py-1" placeholder="0.00" min="0" step="0.01">
            </td>
        `
                : "";

        newRow.innerHTML = `
            <td data-label="Table Number" class="border border-gray-200 px-4 py-3">
                <input type="text" class="edit-table-number w-full border rounded px-2 py-1" value="${nextTableNumber}">
            </td>
            ${areaCellHtml}
            <td data-label="Daily Rate" class="border border-gray-200 px-4 py-3">
                <input type="number" class="edit-daily-rate no-spinner w-full border rounded px-2 py-1" placeholder="0.00" min="0" step="0.01">
            </td>
            <td data-label="Monthly Rate" class="border border-gray-200 px-4 py-3">
                <input type="number" class="edit-monthly-rate no-spinner w-full border-gray-200 bg-gray-100 rounded px-2 py-1" placeholder="0.00" readonly>
            </td>
            <td data-label="Action" class="border border-gray-200 px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                    <button class="save-new-btn bg-green-500 text-white px-3 py-1 rounded-lg" data-id="${tempId}"><i class="fas fa-save"></i></button>
                    <button class="cancel-new-btn bg-red-500 text-white px-3 py-1 rounded-lg" data-id="${tempId}"><i class="fas fa-times"></i></button>
                </div>
            </td>`;

        this.elements.rentalRatesTableBody.insertBefore(
            newRow,
            this.elements.rentalRatesTableBody.firstChild
        );
        this.attachNewRowEventListeners(tempId);
        newRow.querySelector(".edit-daily-rate")?.focus();
    }

    attachNewRowEventListeners(tempId) {
        const row = document.querySelector(`tr[data-id="${tempId}"]`);
        if (!row) return;
        row.querySelector(".save-new-btn").addEventListener("click", () =>
            this.saveNewRow(tempId)
        );
        row.querySelector(".cancel-new-btn").addEventListener("click", () =>
            this.cancelNewRow(tempId)
        );
        row.querySelectorAll("input").forEach((input) => {
            input.addEventListener("keydown", (e) => {
                if (e.key === "Enter") this.saveNewRow(tempId);
                else if (e.key === "Escape") this.cancelNewRow(tempId);
            });
        });
    }

    async saveNewRow(tempId) {
        const row = document.querySelector(`tr[data-id="${tempId}"]`);
        if (!row) return;

        const dailyRate =
            parseFloat(row.querySelector(".edit-daily-rate").value) || 0;

        // Find the area input if it exists
        const areaInput = row.querySelector(".edit-area");
        const area = areaInput ? parseFloat(areaInput.value) || null : null;

        const newRate = {
            section: this.state.currentRentalSection,
            tableNumber: row.querySelector(".edit-table-number").value,
            dailyRate: dailyRate,
            area: area, // Include area in the data sent to the backend
        };
        try {
            const response = await fetch("/api/rental-rates", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(newRate),
            });
            if (!response.ok)
                throw new Error(
                    (await response.json()).message || "Failed to save data."
                );
            this.showToast("New row added successfully!", "success");
            await this.fetchAllRentalRates();
            this.filterAndRenderRates(this.rentalRatesPagination.current_page);
            if (!this.isRentalRatesEditing) {
                this.elements.rentalRatesActionHeader.classList.add("hidden");
            }
        } catch (error) {
            this.showToast(error.message, "error");
            this.filterRentalRates(this.state.currentRentalSection);
        }
    }

    cancelNewRow(tempId) {
        document.querySelector(`tr[data-id="${tempId}"]`)?.remove();
        if (!this.isRentalRatesEditing) {
            this.elements.rentalRatesActionHeader.classList.add("hidden");
        }
    }

    async deleteRentalRate(id) {
        if (!id) return;
        try {
            const response = await fetch(`/api/rental-rates/${id}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json", // ✅ Added comma here
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });
            if (!response.ok) throw new Error("Failed to delete the rate.");
            this.showToast("Rental rate deleted successfully!", "success");
            this.closeDeleteModal();
            await this.fetchAllRentalRates();
            this.filterAndRenderRates(this.rentalRatesPagination.current_page);
        } catch (error) {
            this.showToast(error.message, "error");
        }
    }

    showToast(message, type = "info") {
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

    closeDeleteModal() {
        this.elements.deleteModal.classList.add("hidden");
        this.elements.confirmDelete.onclick = null;
        this.currentEditId = null;
    }

    openDeleteModal(id, type) {
        this.currentEditId = id;
        const modal = this.elements.deleteModal;
        const title = modal.querySelector("h3");
        const message = modal.querySelector("p");
        const confirmBtn = this.elements.confirmDelete;

        if (type === "user") {
            title.textContent = "Confirm User Deletion";
            message.textContent =
                "Are you sure you want to delete this user? This action cannot be undone.";
            confirmBtn.textContent = "Delete User";
            confirmBtn.onclick = () => this.deleteUser(id);
        } else {
            title.textContent = "Confirm Deletion";
            message.textContent =
                "Are you sure you want to delete this rental rate? This action cannot be undone.";
            confirmBtn.textContent = "Delete";
            confirmBtn.onclick = () => this.deleteRentalRate(id);
        }

        modal.classList.remove("hidden");
    }

    setupEventListeners() {
        this.elements.navLinks.forEach((link) => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                const sectionId = link.dataset.section;
                const href = link.getAttribute("href");
                if (sectionId && sectionId !== this.state.activeSection) {
                    this.state.activeSection = sectionId;
                    history.pushState({ section: sectionId }, "", href);

                    // This new function handles initializing the section
                    this.initializeSection(sectionId);
                }
            });
        });

        this.elements.billingManagementDropdown.addEventListener(
            "click",
            () => {
                this.elements.billingManagementSubmenu.classList.toggle(
                    "hidden"
                );
                this.elements.billingManagementArrow.classList.toggle(
                    "rotate-180"
                );
            }
        );

        this.elements.rentalRatesSearchInput.addEventListener("input", (e) => {
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(() => {
                this.filterAndRenderRates(1);
            }, 300);
        });

        this.elements.cancelDelete.addEventListener("click", () =>
            this.closeDeleteModal()
        );
        this.elements.sectionNavBtns.forEach((btn) =>
            btn.addEventListener("click", () =>
                this.handleSectionNavigation(btn)
            )
        );
        //In-app and SMS Notification//
        const mainContent = document.querySelector(".main-content");
        if (mainContent) {
            const closeDropdown = () => {
                if (this.activeDropdown && this.originalParent) {
                    this.activeDropdown.classList.add("hidden");
                    this.activeDropdown.style.top = "";
                    this.activeDropdown.style.left = "";
                    this.activeDropdown.style.position = "";

                    // Return the dropdown to its original home
                    this.originalParent.appendChild(this.activeDropdown);

                    // Clear the tracking variables
                    this.activeDropdown = null;
                    this.originalParent = null;
                }
            };

            mainContent.addEventListener("click", (e) => {
                const bellButton = e.target.closest(".notificationBell button");

                if (bellButton) {
                    e.stopPropagation();

                    if (this.activeDropdown) {
                        closeDropdown();
                        return;
                    }

                    const bellContainer =
                        bellButton.closest(".notificationBell");
                    const dropdown = bellContainer.querySelector(
                        ".notificationDropdown"
                    );

                    if (dropdown) {
                        this.activeDropdown = dropdown;
                        this.originalParent = bellContainer;

                        document.body.appendChild(this.activeDropdown);

                        this.activeDropdown.classList.remove("hidden");
                        const dropdownWidth = this.activeDropdown.offsetWidth;

                        const bellRect = bellButton.getBoundingClientRect();

                        this.activeDropdown.style.position = "absolute"; // Use absolute to scroll
                        this.activeDropdown.style.top = `${
                            bellRect.bottom + window.scrollY + 5
                        }px`;
                        this.activeDropdown.style.left = `${
                            bellRect.right + window.scrollX - dropdownWidth
                        }px`; // Align right edges

                        this.renderNotificationDropdown();

                        // Mark notifications as read
                        if (this.unreadNotificationCount > 0) {
                            this.markNotificationsAsRead();
                        }
                    }
                }
            });

            // Add a listener to the window to close the dropdown when clicking anywhere else
            window.addEventListener("click", () => {
                if (this.activeDropdown) {
                    closeDropdown();
                }
            });
        }
    }

    setInitialSection() {
        const hash = window.location.hash.substring(1);
        let sectionId = "dashboardSection";
        if (hash && document.getElementById(hash)) {
            sectionId = hash;
        } else {
            const pathSegments = window.location.pathname.split("/");
            const lastSegment = pathSegments.pop() || pathSegments.pop();
            const pathMap = {
                billing_management: "marketStallRentalRatesSection",
                notifications: "notificationSection",
                system_user_management: "systemUserManagementSection",
                audit_trails: "auditTrailsSection",
                dashboard: "dashboardSection",
            };
            if (pathMap[lastSegment]) sectionId = pathMap[lastSegment];
        }
        this.state.activeSection = sectionId;
        this.setInitialDropdownState();

        // Call our new function to initialize the starting section
        this.initializeSection(sectionId);
    }

    setInitialDropdownState() {
        const activeLink = document.querySelector(
            `.nav-link[data-section="${this.state.activeSection}"]`
        );
        if (
            activeLink &&
            this.elements.billingManagementSubmenu.contains(activeLink)
        ) {
            this.elements.billingManagementSubmenu.classList.add("open");
            this.elements.billingManagementArrow.classList.add("rotate-180");
        }
    }

    setInitialRentalSection() {
        this.elements.sectionNavBtns.forEach((btn) => {
            btn.classList.remove("active");
            if (btn.dataset.section === "Wet Section")
                btn.classList.add("active");
        });
    }

    //Discounts, Surcharges, and Penatly
    setupBillingSettingsEventListeners() {
        if (!this.elements.editBillingSettingsBtn) return;

        this.elements.editBillingSettingsBtn.addEventListener("click", () =>
            this.toggleBillingSettingsEditMode(true)
        );
        this.elements.cancelBillingSettingsBtn.addEventListener("click", () =>
            this.toggleBillingSettingsEditMode(false)
        );
        this.elements.saveBillingSettingsBtn.addEventListener("click", () =>
            this.saveBillingSettings()
        );
    }

    async fetchBillingSettings() {
        try {
            const response = await fetch("/api/billing-settings");
            if (!response.ok)
                throw new Error("Failed to fetch billing settings.");
            this.billingSettings = await response.json();
            this.renderBillingSettingsTables();
        } catch (error) {
            console.error("Error fetching billing settings:", error);
            this.showToast(error.message, "error");
        }
    }

    async fetchBillingSettingsHistory() {
        if (
            this.isFetchingBillingSettingsHistory ||
            !this.billingSettingsHistoryHasMore
        )
            return;
        this.isFetchingBillingSettingsHistory = true;
        if (this.elements.billingSettingsHistoryLoader)
            this.elements.billingSettingsHistoryLoader.style.display = "block";

        try {
            const response = await fetch(
                `/api/billing-settings/history?page=${this.billingSettingsHistoryPage}`
            );
            if (!response.ok)
                throw new Error("Could not fetch settings history.");
            const data = await response.json();

            this.billingSettingsHistory.push(...data.data);
            this.billingSettingsHistoryHasMore = data.next_page_url !== null;
            this.billingSettingsHistoryPage++;
            this.renderBillingSettingsHistory();
        } catch (error) {
            this.showToast("Failed to load settings history.", "error");
        } finally {
            this.isFetchingBillingSettingsHistory = false;
            if (this.elements.billingSettingsHistoryLoader)
                this.elements.billingSettingsHistoryLoader.style.display =
                    "none";
        }
    }

    renderBillingSettingsTables(isEditing = false) {
        const { rentSettingsTableBody, utilitySettingsTableBody } =
            this.elements;
        if (!rentSettingsTableBody || !utilitySettingsTableBody) return;

        const formatDisplay = (value) =>
            parseFloat(value) > 0
                ? `${(parseFloat(value) * 100).toFixed(2)}%`
                : "Not Set";

        const createPercentageDropdown = (id, currentValue) => {
            const selectedValue = Math.round(parseFloat(currentValue) * 100);

            let optionsHTML = "";

            // "Not Set" option
            const notSetSelected = selectedValue === 0 ? " selected" : "";
            optionsHTML += `<option value="0"${notSetSelected}>Not Set</option>`;

            // 1-100% options
            for (let i = 1; i <= 100; i++) {
                const selected = selectedValue === i ? " selected" : "";
                optionsHTML += `<option value="${i}"${selected}>${i}%</option>`;
            }

            return `
                <select data-id="${id}" class="setting-input w-full border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    ${optionsHTML}
                </select>
            `;
        };

        // Render Rent Table
        const rent = this.billingSettings.Rent;
        if (rent) {
            rentSettingsTableBody.innerHTML = `
                <tr data-id="${rent.id}">
                    <td data-label="Category" class="border border-gray-200 p-3 font-medium">Rent</td>
                    <td data-label="Discount (%)" class="border border-gray-200 p-3 discount-cell">${
                        isEditing
                            ? createPercentageDropdown(
                                  "discount_rate",
                                  rent.discount_rate
                              )
                            : formatDisplay(rent.discount_rate)
                    }</td>
                    <td data-label="Surcharge (%)" class="border border-gray-200 p-3 surcharge-cell">${
                        isEditing
                            ? createPercentageDropdown(
                                  "surcharge_rate",
                                  rent.surcharge_rate
                              )
                            : formatDisplay(rent.surcharge_rate)
                    }</td>
                    <td data-label="Monthly Interest (%)" class="border border-gray-200 p-3 interest-cell">${
                        isEditing
                            ? createPercentageDropdown(
                                  "monthly_interest_rate",
                                  rent.monthly_interest_rate
                              )
                            : formatDisplay(rent.monthly_interest_rate)
                    }</td>
                </tr>
            `;
        }

        // Render Utilities Table
        const utilities = ["Electricity", "Water"];
        utilitySettingsTableBody.innerHTML = utilities
            .map((util) => {
                const setting = this.billingSettings[util];
                if (!setting) return "";
                return `
                <tr data-id="${setting.id}">
                    <td data-label="Category" class="border border-gray-200 p-3 font-medium">${util}</td>
                    <td data-label="Discount (%)" class="border border-gray-200 p-3 discount-cell">${
                        isEditing
                            ? createPercentageDropdown(
                                  "discount_rate",
                                  setting.discount_rate
                              )
                            : formatDisplay(setting.discount_rate)
                    }</td>
                    <td data-label="Penalty (%)" class="border border-gray-200 p-3 penalty-cell">${
                        isEditing
                            ? createPercentageDropdown(
                                  "penalty_rate",
                                  setting.penalty_rate
                              )
                            : formatDisplay(setting.penalty_rate)
                    }</td>
                </tr>
            `;
            })
            .join("");
    }

    renderBillingSettingsHistory() {
        const tableBody = this.elements.billingSettingsHistoryTableBody;
        if (!tableBody) return;

        if (this.billingSettingsHistory.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-gray-500">No history logs found.</td></tr>`;
            return;
        }

        tableBody.innerHTML = this.billingSettingsHistory
            .map(
                (log) => `
            <tr class="hover:bg-gray-50">
                <td data-label="Date & Time" class="border p-3">${new Date(
                    log.changed_at
                ).toLocaleString()}</td>
                <td data-label="Category" class="border p-3">${
                    log.utility_type
                }</td>
                <td data-label="Item Changed" class="border p-3">${
                    log.field_changed
                }</td>
                <td data-label="Old Value" class="border p-3">${parseFloat(
                    log.old_value
                ).toFixed(2)}%</td>
                <td data-label="New Value" class="border p-3">${parseFloat(
                    log.new_value
                ).toFixed(2)}%</td>
            </tr>
        `
            )
            .join("");
    }

    toggleBillingSettingsEditMode(isEditing) {
        this.elements.billingSettingsDefaultButtons.classList.toggle(
            "hidden",
            isEditing
        );
        this.elements.billingSettingsEditButtons.classList.toggle(
            "hidden",
            !isEditing
        );
        this.renderBillingSettingsTables(isEditing);
    }

    async saveBillingSettings() {
        const settingsPayload = [];
        const rows = document.querySelectorAll(
            "#rentSettingsTableBody tr, #utilitySettingsTableBody tr"
        );
        let hasError = false;

        rows.forEach((row) => {
            const id = parseInt(row.dataset.id);
            const setting = { id };
            row.querySelectorAll(".setting-input").forEach((input) => {
                const value = parseFloat(input.value) / 100; // Convert from % to decimal
                if (isNaN(value) || value < 0) {
                    hasError = true;
                    input.classList.add("border-red-500");
                } else {
                    input.classList.remove("border-red-500");
                }
                setting[input.dataset.id] = value;
            });
            settingsPayload.push(setting);
        });

        if (hasError) {
            this.showToast(
                "Please enter valid, non-negative numbers for all fields.",
                "error"
            );
            return;
        }

        try {
            const response = await fetch("/api/billing-settings", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    settings: settingsPayload,
                    user_id: window.loggedInUserId,
                }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(
                    errorData.message || "Failed to save settings."
                );
            }

            this.showToast("Settings updated successfully!", "success");
            this.toggleBillingSettingsEditMode(false);

            // Reset and refetch history
            this.billingSettingsHistory = [];
            this.billingSettingsHistoryPage = 1;
            this.billingSettingsHistoryHasMore = true;
            await this.fetchBillingSettings();
            await this.fetchBillingSettingsHistory();
        } catch (error) {
            this.showToast(error.message, "error");
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const dashboard = new SuperAdminDashboard();
    dashboard.init();
});
