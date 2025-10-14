document.addEventListener("DOMContentLoaded", () => {
    const MeterApp = {
        readingsCache: {},
        state: {
            scheduleDay: window.scheduleDay || null,
            meterReadings: window.meterReadings || [],
            editRequests: window.editRequestsData || [],
            notifications: [],
            unreadCount: window.unreadNotificationsCount || 0,
            readingsSubmitted: false,
            currentlyEditingStallId: null,
            activeSection: "dashboardSection",
            currentRentalSection: "Wet Section",
            loading: true,
            currentPage: 1,
            itemsPerPage: 10,
            search: "",
            archiveReadings: [],
            archiveFilters: { search: "", month: "", section: "" },
            archivePage: 1,
            archiveHasMore: true,
            isFetchingArchives: false,
        },
        elements: {},
        computed: {
            filteredReadings() {
                const {
                    currentRentalSection,
                    search,
                    meterReadings,
                    currentPage,
                    itemsPerPage,
                } = MeterApp.state;
                const lowercasedSearch = search ? search.toLowerCase() : "";

                const filtered = meterReadings.filter((item) => {
                    const sectionMatch =
                        !currentRentalSection ||
                        item.section === currentRentalSection;
                    const searchMatch =
                        !lowercasedSearch ||
                        item.section.toLowerCase().includes(lowercasedSearch) ||
                        item.stallNumber
                            .toLowerCase()
                            .includes(lowercasedSearch);
                    return sectionMatch && searchMatch;
                });

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                return filtered.slice(startIndex, endIndex);
            },
            totalPages() {
                const {
                    currentRentalSection,
                    search,
                    meterReadings,
                    itemsPerPage,
                } = MeterApp.state;
                const lowercasedSearch = search ? search.toLowerCase() : "";

                const filtered = meterReadings.filter((item) => {
                    const sectionMatch =
                        !currentRentalSection ||
                        item.section === currentRentalSection;
                    const searchMatch =
                        !lowercasedSearch ||
                        item.section.toLowerCase().includes(lowercasedSearch) ||
                        item.stallNumber
                            .toLowerCase()
                            .includes(lowercasedSearch);
                    return sectionMatch && searchMatch;
                });

                return Math.ceil(filtered.length / itemsPerPage);
            },
            uniqueSections() {
                return [
                    ...new Set(
                        MeterApp.state.meterReadings.map((item) => item.section)
                    ),
                ];
            },
            allStalls() {
                return [
                    ...new Set(
                        MeterApp.state.meterReadings.map(
                            (item) => item.stallNumber
                        )
                    ),
                ];
            },
            currentMonthName() {
                return new Date().toLocaleString("default", { month: "long" });
            },
            sortedEditRequests() {
                return [...MeterApp.state.editRequests].sort(
                    (a, b) => new Date(b.requestDate) - new Date(a.requestDate)
                );
            },
        },
        methods: {
            // This is the new method
            updateReadingCache(readingId, value, field = "value") {
                // Find the corresponding reading object from the state
                const reading = MeterApp.state.meterReadings.find(
                    (r) => r.utility_reading_id == readingId
                );
                if (!reading) return;

                // Initialize the cache for this reading if it doesn't exist
                if (!MeterApp.readingsCache[readingId]) {
                    MeterApp.readingsCache[readingId] = {
                        id: reading.utility_reading_id,
                    };
                }

                // Update the specific field ('value' for current, 'previous' for previous)
                if (String(value).trim() === "") {
                    delete MeterApp.readingsCache[readingId][field];
                } else {
                    MeterApp.readingsCache[readingId][field] = value;
                }
            },
            // Replace the entire validateReadings() function in js/meter.js with this

            validateReadings() {
                const cachedReadings = Object.values(MeterApp.readingsCache);
                if (cachedReadings.length === 0) {
                    MeterApp.methods.showNotification(
                        "Please enter at least one meter reading before submitting.",
                        "error"
                    );
                    return false;
                }

                // CORRECTED LOGIC: Check the 'value' and 'previous' properties inside each object
                const allValid = cachedReadings.every((readingObject) => {
                    const currentValue = readingObject.value;
                    const previousValue = readingObject.previous;

                    // Validate the current reading value if it exists
                    const isCurrentValid =
                        currentValue === undefined ||
                        (!isNaN(currentValue) && Number(currentValue) >= 0);

                    // Validate the previous reading value if it exists
                    const isPreviousValid =
                        previousValue === undefined ||
                        (!isNaN(previousValue) && Number(previousValue) >= 0);

                    return isCurrentValid && isPreviousValid;
                });

                if (!allValid) {
                    MeterApp.methods.showNotification(
                        "One or more cached readings are invalid. Please enter positive numbers only.",
                        "error"
                    );
                }
                return allValid;
            },
            // Replace the entire submitReadings() function in js/meter.js with this

            async submitReadings() {
                // MODIFIED: Reads from the non-reactive MeterApp.readingsCache
                const { readingsCache } = MeterApp;

                // CORRECTED: Convert the cache object to an array for the backend
                const readingsPayload = Object.values(readingsCache);

                if (readingsPayload.length === 0) {
                    MeterApp.methods.showNotification(
                        "No new readings to submit.",
                        "error"
                    );
                    return;
                }

                MeterApp.methods.closeModal("confirmationModal");

                try {
                    const response = await fetch("/utility-readings/bulk", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        // CORRECTED: Send the array payload
                        body: JSON.stringify({ readings: readingsPayload }),
                    });

                    if (!response.ok) {
                        throw new Error("Server responded with an error.");
                    }

                    const savedReadings = await response.json();

                    const updatedMeterReadings =
                        MeterApp.state.meterReadings.map((reading) => {
                            const savedData = savedReadings.find(
                                (s) =>
                                    s.utility_reading_id ===
                                    reading.utility_reading_id
                            );
                            if (savedData) {
                                return {
                                    ...reading,
                                    ...savedData,
                                    status: "submitted",
                                };
                            }
                            return reading;
                        });

                    MeterApp.state.meterReadings = updatedMeterReadings;
                    MeterApp.readingsCache = {}; // Clear the cache after successful submission

                    MeterApp.methods.showNotification(
                        `${savedReadings.length} meter reading(s) have been submitted!`,
                        "success"
                    );

                    // --- START: Instantaneous Archive Update ---
                    const newlySubmittedForArchive = savedReadings
                        .map((saved) => {
                            const fullReadingData = updatedMeterReadings.find(
                                (r) =>
                                    r.utility_reading_id ===
                                    saved.utility_reading_id
                            );
                            if (!fullReadingData) return null;

                            // Construct a new object in the format the archive renderer expects
                            return {
                                reading_date: fullReadingData.reading_date,
                                stall: {
                                    section: { name: fullReadingData.section },
                                    table_number: fullReadingData.stallNumber,
                                },
                                previous_reading: saved.previousReading,
                                current_reading: saved.currentReading,
                            };
                        })
                        .filter(Boolean); // Remove any null entries

                    if (newlySubmittedForArchive.length > 0) {
                        // Prepend the new entries to the local archive state
                        MeterApp.state.archiveReadings.unshift(
                            ...newlySubmittedForArchive
                        );

                        // If the user is currently viewing the archives, re-render the list instantly
                        if (
                            MeterApp.state.activeSection === "archivesSection"
                        ) {
                            MeterApp.render.archives(); // Re-render the entire archive view from state
                        }
                    }
                    // --- END: Instantaneous Archive Update ---
                } catch (error) {
                    console.error("Failed to submit readings:", error);
                    MeterApp.methods.showNotification(
                        "Failed to submit readings. Please try again.",
                        "error"
                    );
                }
            },
            openRequestEditModal(stallId) {
                MeterApp.state.currentlyEditingStallId = stallId;
                MeterApp.elements.requestEditModal.classList.remove("hidden");
            },
            async sendEditRequest() {
                const reason =
                    MeterApp.elements.editReasonTextarea.value.trim();
                if (!reason) {
                    MeterApp.methods.showNotification(
                        "Please provide a reason for the edit request.",
                        "error"
                    );
                    return;
                }

                const reading = MeterApp.state.meterReadings.find(
                    (r) => r.stallId == MeterApp.state.currentlyEditingStallId
                );

                if (!reading || !reading.utility_reading_id) {
                    MeterApp.methods.showNotification(
                        "Could not find the reading to request an edit for.",
                        "error"
                    );
                    return;
                }

                try {
                    const response = await fetch("/reading-edit-requests", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: JSON.stringify({
                            utility_reading_id: reading.utility_reading_id,
                            reason: reason,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error("Request failed");
                    }

                    const newRequest = await response.json();

                    const newMeterReadings = MeterApp.state.meterReadings.map(
                        (r) => {
                            if (
                                r.stallId ==
                                MeterApp.state.currentlyEditingStallId
                            ) {
                                // THIS IS THE FIX: Changed to 'request_pending' to match the renderer
                                return { ...r, status: "request_pending" };
                            }
                            return r;
                        }
                    );
                    MeterApp.state.meterReadings = newMeterReadings;

                    const newRequestState = {
                        requestId: newRequest.id,
                        stallNumber: reading.stallNumber,
                        requestDate: new Date().toISOString().split("T")[0],
                        reason: newRequest.reason,
                        status: "Pending",
                    };
                    MeterApp.state.editRequests = [
                        ...MeterApp.state.editRequests,
                        newRequestState,
                    ];

                    MeterApp.methods.closeModal("requestEditModal");
                    MeterApp.elements.editReasonTextarea.value = "";
                    MeterApp.methods.showNotification(
                        "Edit request submitted successfully!",
                        "success"
                    );
                } catch (error) {
                    MeterApp.methods.showNotification(
                        "Failed to submit edit request. Please try again.",
                        "error"
                    );
                }
            },
            navigate(sectionId) {
                window.location.hash = sectionId;
                MeterApp.state.activeSection = sectionId;
            },
            openModal(modalId) {
                const modal = MeterApp.elements[modalId];
                if (modal) modal.classList.remove("hidden");
            },
            closeModal(modalId) {
                const modal = MeterApp.elements[modalId];
                if (modal) modal.classList.add("hidden");
            },
            showNotification(message, type = "success") {
                const notification = document.createElement("div");
                const baseClasses =
                    "fixed bottom-5 right-5 p-4 rounded-lg shadow-lg text-white transition-opacity duration-300 z-50";
                const typeClasses =
                    type === "success" ? "bg-green-500" : "bg-red-500";
                notification.className = `${baseClasses} ${typeClasses}`;
                notification.textContent = message;
                document.body.appendChild(notification);
                setTimeout(() => {
                    notification.classList.add("opacity-0");
                    notification.addEventListener("transitionend", () =>
                        notification.remove()
                    );
                }, 3000);
            },
            goToPage(page) {
                if (page >= 1 && page <= MeterApp.computed.totalPages()) {
                    MeterApp.state.currentPage = page;
                }
            },

            async ForStatusUpdates() {
                // Find all readings that are currently in a "locked" state
                const lockedReadings = MeterApp.state.meterReadings.filter(
                    (r) =>
                        r.status === "submitted" ||
                        r.status === "request_pending" ||
                        r.status === "request_rejected"
                );

                if (lockedReadings.length === 0) {
                    return; // Nothing to check
                }

                // Get the IDs of the readings to check
                const readingIds = lockedReadings.map(
                    (r) => r.utility_reading_id
                );

                try {
                    const response = await fetch("/meter-readings/statuses", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: JSON.stringify({ reading_ids: readingIds }),
                    });

                    if (!response.ok) return; // Silently fail on error

                    const updatedStatuses = await response.json();
                    let stateWasChanged = false;

                    const newMeterReadings = MeterApp.state.meterReadings.map(
                        (reading) => {
                            const updatedStatus =
                                updatedStatuses[reading.utility_reading_id];
                            if (updatedStatus) {
                                let newStatus =
                                    "request_" + updatedStatus.status; // e.g., request_approved
                                // If approved, unlock the field by setting status back to 'pending'
                                if (updatedStatus.status === "approved") {
                                    newStatus = "pending";
                                }

                                if (reading.status !== newStatus) {
                                    stateWasChanged = true;
                                    return { ...reading, status: newStatus };
                                }
                            }
                            return reading;
                        }
                    );

                    // Only update the state (and trigger a re-render) if something actually changed
                    if (stateWasChanged) {
                        MeterApp.state.meterReadings = newMeterReadings;
                    }
                } catch (error) {
                    console.error("Polling error:", error);
                }
            },

            async ForScheduleUpdate() {
                try {
                    const response = await fetch("/meter-reading-schedule");
                    if (!response.ok) {
                        // Fail silently if there's a temporary network issue
                        return;
                    }

                    const data = await response.json();
                    const newDay = data.day;

                    // Only update the state (and trigger a re-render) if the day has changed
                    if (newDay !== MeterApp.state.scheduleDay) {
                        MeterApp.state.scheduleDay = newDay;
                    }
                } catch (error) {
                    // We don't show a notification for this, just log it for debugging
                    console.error("Error polling for schedule update:", error);
                }
            },

            //--ARCHIVE READINGS--//
            async fetchArchives(reset = false) {
                if (MeterApp.state.isFetchingArchives) return;
                if (!reset && !MeterApp.state.archiveHasMore) return;

                MeterApp.state.isFetchingArchives = true;
                MeterApp.elements.archiveLoadingIndicator.classList.remove(
                    "hidden"
                );

                const container = MeterApp.elements.archiveResultsContainer;

                if (reset) {
                    MeterApp.state.archivePage = 1;
                    MeterApp.state.archiveReadings = []; // Clear the data state
                    MeterApp.state.archiveHasMore = true;
                    if (container) container.innerHTML = ""; // Clear the UI
                }

                const { search, month, section } =
                    MeterApp.state.archiveFilters;
                const params = new URLSearchParams({
                    page: MeterApp.state.archivePage,
                    search,
                    month,
                    section,
                });

                try {
                    const response = await fetch(
                        `/meter/archives?${params.toString()}`,
                        {
                            headers: { "X-Requested-With": "XMLHttpRequest" },
                        }
                    );
                    if (!response.ok) throw new Error("Network error");

                    const data = await response.json();

                    // Append new data to state
                    MeterApp.state.archiveReadings.push(...data.data);

                    // Call the new rendering logic for just the new items
                    MeterApp.render.appendArchives(data.data);

                    MeterApp.state.archiveHasMore = data.next_page_url !== null;
                    if (MeterApp.state.archiveHasMore) {
                        MeterApp.state.archivePage++;
                    }
                } catch (error) {
                    console.error("Failed to fetch archives:", error);
                    if (container) {
                        container.innerHTML =
                            '<p class="text-center text-red-500">Failed to load data.</p>';
                    }
                } finally {
                    MeterApp.state.isFetchingArchives = false;
                    MeterApp.elements.archiveLoadingIndicator.classList.add(
                        "hidden"
                    );
                }
            },
            //In-App and SMS Notifications//
            async checkForNotifications() {
                try {
                    const response = await fetch("/notification/unread");
                    if (!response.ok) {
                        console.error("Could not poll for notifications.");
                        return;
                    }
                    const notifications = await response.json();
                    if (notifications.length > 0) {
                        MeterApp.elements.notificationDot.classList.remove(
                            "hidden"
                        );
                        notifications.forEach((notification) => {
                            const data = JSON.parse(notification.message);
                            MeterApp.methods.showNotification(
                                data.text || notification.title,
                                "success"
                            );

                            // If a request was approved/rejected, update the local state to unlock the field
                            if (data.request_id) {
                                MeterApp.methods.ForStatusUpdates(); // Re-run the status check to update UI
                            }
                        });
                    }
                } catch (error) {
                    console.error("Error checking for notifications:", error);
                }
            },
            //In-App and SMS Notification//
            async fetchNotifications() {
                try {
                    const response = await fetch("/notifications/fetch");
                    if (!response.ok) return;

                    const data = await response.json();

                    // Use the Proxy to set state, which will trigger a re-render
                    MeterApp.state.notifications = data.notifications;
                    MeterApp.state.unreadCount = data.unread_count;
                } catch (error) {
                    console.error("Error fetching notifications:", error);
                }
            },
            async markAsRead() {
                if (MeterApp.state.unreadCount === 0) return;

                // --- OPTIMISTIC UI UPDATE ---
                const now = new Date().toISOString();

                const updatedNotifications = MeterApp.state.notifications.map(
                    (notification) => {
                        if (notification.read_at === null) {
                            // Create a new object with the 'read_at' timestamp
                            return { ...notification, read_at: now };
                        }
                        return notification;
                    }
                );

                MeterApp.state.notifications = updatedNotifications;
                MeterApp.state.unreadCount = 0;

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
                } catch (error) {
                    console.error(
                        "Failed to mark notifications as read on server:",
                        error
                    );
                }
            },
            formatTimeAgo(dateString) {
                const date = new Date(dateString);
                const seconds = Math.floor((new Date() - date) / 1000);

                let interval = seconds / 31536000;
                if (interval >= 1) {
                    const value = Math.floor(interval);
                    return value === 1
                        ? `${value} year ago`
                        : `${value} years ago`;
                }
                interval = seconds / 2592000;
                if (interval >= 1) {
                    const value = Math.floor(interval);
                    return value === 1
                        ? `${value} month ago`
                        : `${value} months ago`;
                }
                interval = seconds / 86400;
                if (interval >= 1) {
                    const value = Math.floor(interval);
                    return value === 1
                        ? `${value} day ago`
                        : `${value} days ago`;
                }
                interval = seconds / 3600;
                if (interval >= 1) {
                    const value = Math.floor(interval);
                    return value === 1
                        ? `${value} hour ago`
                        : `${value} hours ago`;
                }
                interval = seconds / 60;
                if (interval >= 1) {
                    const value = Math.floor(interval);
                    return value === 1
                        ? `${value} minute ago`
                        : `${value} minutes ago`;
                }
                return "Just now";
            },
        },
        render: {
            all() {
                this.header();
                this.readingTable();
                this.notificationTable(); // This is for the main "Edit Request" page table
                this.notificationDropdown();
                this.viewState();
                this.navigation();
                this.pagination();
            },
            //--ARCHIVES READING RENDDR--//
            appendArchives(newReadings) {
                const container = MeterApp.elements.archiveResultsContainer;
                if (!container) return;

                // Handle case where it's the first load and it's empty
                if (
                    MeterApp.state.archiveReadings.length === 0 &&
                    !MeterApp.state.isFetchingArchives
                ) {
                    container.innerHTML = `
                        <div class="card-table p-8 rounded-2xl shadow-soft text-center text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-4 text-gray-400"></i>
                            <h3 class="text-xl font-medium">No Archived Readings Found</h3>
                            <p>There is no historical data matching your filters.</p>
                        </div>`;
                    return;
                }

                // Group only the newly fetched readings
                const groupedNewReadings = newReadings.reduce(
                    (acc, reading) => {
                        const monthYear = new Date(
                            reading.reading_date
                        ).toLocaleString("default", {
                            month: "long",
                            year: "numeric",
                        });
                        if (!acc[monthYear]) acc[monthYear] = [];
                        acc[monthYear].push(reading);
                        return acc;
                    },
                    {}
                );

                Object.entries(groupedNewReadings).forEach(
                    ([month, readings]) => {
                        const monthId = `archive-${month.replace(/\s+/g, "-")}`;
                        let monthContainer = document.getElementById(monthId);

                        // If the month container doesn't exist, create it
                        if (!monthContainer) {
                            const newContainer = document.createElement("div");
                            newContainer.id = monthId;
                            newContainer.className =
                                "card-table rounded-2xl shadow-soft overflow-hidden";
                            newContainer.innerHTML = `
                            <button class="accordion-toggle w-full p-6 text-left flex justify-between items-center transition-colors hover:bg-gray-50 focus:outline-none">
                                <h3 class="text-xl font-bold text-gray-800">Readings for ${month}</h3>
                                <i class="fas fa-chevron-down transition-transform duration-300"></i>
                            </button>
                            <div class="accordion-content p-6 pt-0">
                                <div class="overflow-x-auto border-t pt-4">
                                    <table class="min-w-full responsive-table">
                                        <thead>
                                            <tr class="table-header">
                                                <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Section</th>
                                                <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Stall Number</th>
                                                <th class="px-6 py-4 text-right text-sm font-medium uppercase tracking-wider">Previous Reading (kWh)</th>
                                                <th class="px-6 py-4 text-right text-sm font-medium uppercase tracking-wider">Current Reading (kWh)</th>
                                                <th class="px-6 py-4 text-right text-sm font-medium uppercase tracking-wider">Consumption (kWh)</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>`;
                            container.appendChild(newContainer);
                            monthContainer = newContainer;

                            // Add event listener to the new accordion toggle
                            monthContainer
                                .querySelector(".accordion-toggle")
                                .addEventListener("click", function () {
                                    this.nextElementSibling.classList.toggle(
                                        "hidden"
                                    );
                                    this.querySelector(
                                        "i.fa-chevron-down"
                                    ).classList.toggle("rotate-180");
                                });
                        }

                        // Append the new rows to the correct tbody
                        const tbody = monthContainer.querySelector("tbody");
                        const newRowsHtml = readings
                            .map(
                                (reading) => `
                        <tr class="table-row">
                            <td data-label="Section" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${
                                reading.stall?.section?.name ?? "N/A"
                            }</td>
                            <td data-label="Stall Number" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${
                                reading.stall?.table_number ?? "N/A"
                            }</td>
                            <td data-label="Previous Reading (kWh)" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">${Number(
                                reading.previous_reading
                            ).toFixed(2)}</td>
                            <td data-label="Current Reading (kWh)" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">${Number(
                                reading.current_reading
                            ).toFixed(2)}</td>
                            <td data-label="Consumption (kWh)" class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800 text-right">${(
                                Number(reading.current_reading) -
                                Number(reading.previous_reading)
                            ).toFixed(2)}</td>
                        </tr>
                    `
                            )
                            .join("");

                        tbody.insertAdjacentHTML("beforeend", newRowsHtml);
                    }
                );
            },

            archives() {
                const container = MeterApp.elements.archiveResultsContainer;
                if (!container) return;

                container.innerHTML = ""; // Clear existing content to prevent duplicates

                if (MeterApp.state.archiveReadings.length === 0) {
                    container.innerHTML = `
                        <div class="card-table p-8 rounded-2xl shadow-soft text-center text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-4 text-gray-400"></i>
                            <h3 class="text-xl font-medium">No Archived Readings Found</h3>
                            <p>There is no historical data matching your filters.</p>
                        </div>`;
                    return;
                }

                // Group all readings in the state by month
                const groupedReadings = MeterApp.state.archiveReadings.reduce(
                    (acc, reading) => {
                        const monthYear = new Date(
                            reading.reading_date
                        ).toLocaleString("default", {
                            month: "long",
                            year: "numeric",
                        });
                        if (!acc[monthYear]) acc[monthYear] = [];
                        acc[monthYear].push(reading);
                        return acc;
                    },
                    {}
                );

                // Sort months so the newest is always on top
                const sortedMonths = Object.keys(groupedReadings).sort(
                    (a, b) => new Date(b) - new Date(a)
                );

                // Rebuild the entire archive UI from the sorted, grouped data
                sortedMonths.forEach((month) => {
                    const readings = groupedReadings[month];
                    const monthId = `archive-${month.replace(/\s+/g, "-")}`;
                    const monthContainer = document.createElement("div");
                    monthContainer.id = monthId;
                    monthContainer.className =
                        "card-table rounded-2xl shadow-soft overflow-hidden";
                    monthContainer.innerHTML = `
                        <button class="accordion-toggle w-full p-6 text-left flex justify-between items-center transition-colors hover:bg-gray-50 focus:outline-none">
                            <h3 class="text-xl font-bold text-gray-800">Readings for ${month}</h3>
                            <i class="fas fa-chevron-down transition-transform duration-300"></i>
                        </button>
                        <div class="accordion-content p-6 pt-0">
                            <div class="overflow-x-auto border-t pt-4">
                                <table class="min-w-full responsiev-table">
                                    <thead>
                                        <tr class="table-header">
                                            <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Section</th>
                                            <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Stall Number</th>
                                            <th class="px-6 py-4 text-right text-sm font-medium uppercase tracking-wider">Previous Reading (kWh)</th>
                                            <th class="px-6 py-4 text-right text-sm font-medium uppercase tracking-wider">Current Reading (kWh)</th>
                                            <th class="px-6 py-4 text-right text-sm font-medium uppercase tracking-wider">Consumption (kWh)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${readings
                                            .map(
                                                (reading) => `
                                        <tr class="table-row">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${
                                                reading.stall?.section?.name ??
                                                "N/A"
                                            }</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${
                                                reading.stall?.table_number ??
                                                "N/A"
                                            }</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">${Number(
                                                reading.previous_reading
                                            ).toFixed(2)}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">${Number(
                                                reading.current_reading
                                            ).toFixed(2)}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800 text-right">${(
                                                Number(
                                                    reading.current_reading
                                                ) -
                                                Number(reading.previous_reading)
                                            ).toFixed(2)}</td>
                                        </tr>
                                    `
                                            )
                                            .join("")}
                                    </tbody>
                                </table>
                            </div>
                        </div>`;
                    container.appendChild(monthContainer);

                    monthContainer
                        .querySelector(".accordion-toggle")
                        .addEventListener("click", function () {
                            this.nextElementSibling.classList.toggle("hidden");
                            this.querySelector(
                                "i.fa-chevron-down"
                            ).classList.toggle("rotate-180");
                        });
                });
            },
            //---//
            header() {
                if (MeterApp.elements.electricityHeader) {
                    // MODIFIED: This now uses the correct month name from the server
                    const month = window.billingMonthName || "Month";
                    const day = MeterApp.state.scheduleDay;
                    MeterApp.elements.electricityHeader.textContent = `Electricity Meter Reading for ${month}`;
                }
            },
            // This is the new render method
            // In resources/js/meter.js, replace the entire readingTable method inside the render object

            readingTable() {
                const tableElement = document.getElementById("readingTable");
                if (!tableElement) return;

                const readings = MeterApp.computed.filteredReadings();
                const showActionsColumn = readings.some(
                    (item) =>
                        item.status !== "pending" &&
                        item.status !== "request_approved"
                );

                // Define Headers
                let headers = [
                    "Stall Number",
                    "Previous Reading (KWH)",
                    "Current Reading (KWH)",
                    "Consumption (KWH)",
                ];
                if (showActionsColumn) {
                    headers.push("Actions");
                }

                // Generate Header HTML
                const headerHtml = `

                <tr class="table-header">
                    ${headers
                        .map(
                            (h) =>
                                `<th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">${h}</th>`
                        )
                        .join("")}
                </tr>
            </thead>
            `;

                // Generate Body HTML
                const bodyHtml = `
            <tbody>
                ${
                    readings
                        .map((item) => {
                            const status = item.status;
                            let rowClass = "table-row";
                            if (status === "request_pending")
                                rowClass += " bg-yellow-100";
                            else if (status === "request_rejected")
                                rowClass += " bg-red-100";
                            else if (status === "submitted")
                                rowClass += " bg-gray-100";

                            const isInitialSetup =
                                !item.previousReading ||
                                parseFloat(item.previousReading) === 0;

                            const previousReadingValue =
                                MeterApp.readingsCache[item.utility_reading_id]
                                    ?.previous ??
                                (isInitialSetup ? "" : item.previousReading);
                            const currentReadingValue =
                                MeterApp.readingsCache[item.utility_reading_id]
                                    ?.value ??
                                (item.status === "pending" ||
                                item.status === "request_approved"
                                    ? ""
                                    : item.currentReading);

                            let previousReadingCell = `<td data-label="Previous Reading" class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">${parseFloat(
                                item.previousReading || 0
                            ).toLocaleString()}</td>`;
                            if (
                                isInitialSetup &&
                                (status === "pending" ||
                                    status === "request_approved")
                            ) {
                                previousReadingCell = `<td data-label="Previous Reading" class="px-6 py-4"><input type="number" class="no-spinner previous-reading-input w-full p-2 border border-blue-300 rounded-md" placeholder="Enter previous..." data-reading-id="${item.utility_reading_id}" value="${previousReadingValue}"></td>`;
                            }

                            let currentReadingCell = `<td data-label="Current Reading" class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">${parseFloat(
                                item.currentReading || 0
                            ).toLocaleString()}</td>`;
                            if (
                                status === "pending" ||
                                status === "request_approved"
                            ) {
                                currentReadingCell = `<td data-label="Current Reading" class="px-6 py-4"><input type="number" class="no-spinner reading-input w-full p-2 border border-gray-300 rounded-md" placeholder="Enter reading..." data-reading-id="${item.utility_reading_id}" value="${currentReadingValue}"></td>`;
                            }

                            const consumption =
                                (parseFloat(currentReadingValue) || 0) -
                                (parseFloat(previousReadingValue) || 0);
                            const consumptionCell = `<td data-label="Consumption" class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold consumption-cell">${consumption.toFixed(
                                2
                            )}</td>`;

                            let actionsCell = "";
                            if (showActionsColumn) {
                                let actionContent = "";
                                if (status === "submitted")
                                    actionContent = `<button class="request-edit-btn text-sm text-blue-600 hover:underline" data-stall-id="${item.stallId}">Request Edit</button>`;
                                else if (status === "request_pending")
                                    actionContent = `<span class="text-sm text-yellow-600 font-semibold">Pending Approval</span>`;
                                else if (status === "request_rejected")
                                    actionContent = `<button class="request-edit-btn text-sm text-blue-600 hover:underline" data-stall-id="${item.stallId}">Request Edit Again</button>`;

                                actionsCell = `<td data-label="Actions" class="px-6 py-4 whitespace-nowrap text-center">${actionContent}</td>`;
                            }

                            return `
                        <tr class="${rowClass}">
                            <td data-label="Stall Number" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">${item.stallNumber}</td>
                            ${previousReadingCell}
                            ${currentReadingCell}
                            ${consumptionCell}
                            ${actionsCell}
                        </tr>
                    `;
                        })
                        .join("") ||
                    `<tr><td colspan="${headers.length}" class="text-center py-8 text-gray-500">No readings found for this section.</td></tr>`
                }
            </tbody>
            `;

                tableElement.innerHTML = headerHtml + bodyHtml;
            },

            notificationTable() {
                const {
                    notificationTableBody,
                    noNotificationsMessage,
                    notificationTableHeader,
                } = MeterApp.elements;
                if (!notificationTableBody) return;

                const requests = MeterApp.computed.sortedEditRequests();
                notificationTableBody.innerHTML = "";

                if (requests.length === 0) {
                    noNotificationsMessage.classList.remove("hidden");
                    notificationTableHeader.classList.add("hidden");
                } else {
                    noNotificationsMessage.classList.add("hidden");
                    notificationTableHeader.classList.remove("hidden");
                    requests.forEach((req) => {
                        const row = document.createElement("tr");
                        row.className = "table-row";
                        const statusClasses = {
                            Approved: "status-approved",
                            Rejected: "status-rejected",
                            Pending: "status-pending",
                        };
                        const statusClass =
                            statusClasses[req.status] || "status-pending";

                        row.innerHTML = `
                            <td data-label="Request Date" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(
                                req.requestDate
                            ).toLocaleDateString()}</td>
                            <td data-label="Stall Number" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${
                                req.stallNumber
                            }</td>
                            <td data-label="Reason" class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="${
                                req.reason
                            }">${req.reason}</td>
                            <td data-label="Status" class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="status-badge ${statusClass}">${
                            req.status
                        }</span>
                            </td>
                        `;
                        notificationTableBody.appendChild(row);
                    });
                }
            },
            viewState() {
                const { actionsHeader, submitButtonContainer } =
                    MeterApp.elements;

                const visibleReadings = MeterApp.computed.filteredReadings();

                // Hide submit button if all visible readings are submitted/pending
                const allSubmittedOrPending = visibleReadings.every(
                    (r) => r.status !== "pending"
                );

                if (submitButtonContainer) {
                    submitButtonContainer.classList.toggle(
                        "hidden",
                        allSubmittedOrPending
                    );
                }

                // Show actions header ONLY if any visible reading has a status other than 'pending'
                const showActionsColumn = visibleReadings.some(
                    (r) => r.status !== "pending"
                );
                if (actionsHeader) {
                    actionsHeader.classList.toggle(
                        "hidden",
                        !showActionsColumn
                    );
                }
            },
            navigation() {
                const { navLinks, sections, archiveSectionButtons } =
                    MeterApp.elements;

                // Toggle visibility of main dashboard sections
                sections.forEach((section) => {
                    section.classList.toggle(
                        "active",
                        section.id === MeterApp.state.activeSection
                    );
                });

                // Update active state for main navigation links
                navLinks.forEach((link) => {
                    link.classList.toggle(
                        "active",
                        link.dataset.section === MeterApp.state.activeSection
                    );
                });

                // CORRECTED: This block handles the logic for the archives section
                if (MeterApp.state.activeSection === "archivesSection") {
                    // This ensures the active button always matches the current filter state,
                    // fixing the UI bug where "All Sections" would remain active.
                    archiveSectionButtons.forEach((button) => {
                        button.classList.toggle(
                            "active",
                            button.dataset.section ===
                                MeterApp.state.archiveFilters.section
                        );
                    });

                    // Fetch initial data only if the archive is empty and not already being fetched
                    if (
                        MeterApp.state.archiveReadings.length === 0 &&
                        !MeterApp.state.isFetchingArchives
                    ) {
                        MeterApp.methods.fetchArchives(true);
                    }
                }
            },

            pagination() {
                const { paginationContainer } = MeterApp.elements;
                if (!paginationContainer) return;

                paginationContainer.innerHTML = "";
                const totalPages = MeterApp.computed.totalPages();
                const currentPage = MeterApp.state.currentPage;

                if (totalPages <= 1) return;

                const buttonClasses = "px-4 py-2 mx-1 rounded-lg font-medium";
                const activeButtonClasses = "bg-blue-500 text-white";
                const disabledButtonClasses =
                    "bg-gray-200 text-gray-500 cursor-not-allowed";

                const prevButton = document.createElement("button");
                prevButton.innerHTML = "&laquo; Previous";
                prevButton.className = `${buttonClasses} ${
                    currentPage === 1 ? disabledButtonClasses : "bg-white"
                }`;
                prevButton.disabled = currentPage === 1;
                prevButton.addEventListener("click", () =>
                    MeterApp.methods.goToPage(currentPage - 1)
                );
                paginationContainer.appendChild(prevButton);

                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = document.createElement("button");
                    pageButton.textContent = i;
                    pageButton.className = `${buttonClasses} ${
                        i === currentPage ? activeButtonClasses : "bg-white"
                    }`;
                    pageButton.addEventListener("click", () =>
                        MeterApp.methods.goToPage(i)
                    );
                    paginationContainer.appendChild(pageButton);
                }

                const nextButton = document.createElement("button");
                nextButton.innerHTML = "Next &raquo;";
                nextButton.className = `${buttonClasses} ${
                    currentPage === totalPages
                        ? disabledButtonClasses
                        : "bg-white"
                }`;
                nextButton.disabled = currentPage === totalPages;
                nextButton.addEventListener("click", () =>
                    MeterApp.methods.goToPage(currentPage + 1)
                );
                paginationContainer.appendChild(nextButton);
            },
            //In-App and SMS Notification
            notificationDropdown() {
                // Find elements dynamically every time a render happens
                const activeSection = document.querySelector(
                    ".dashboard-section.active"
                );
                if (!activeSection) return;

                const notificationList =
                    activeSection.querySelector(".notificationList");
                const notificationDot =
                    activeSection.querySelector(".notificationDot");
                if (!notificationList || !notificationDot) return;

                notificationDot.classList.toggle(
                    "hidden",
                    MeterApp.state.unreadCount === 0
                );

                const notifications = MeterApp.state.notifications;
                if (notifications.length === 0) {
                    notificationList.innerHTML = `<p class="text-center text-gray-500 p-4">You have no new notifications.</p>`;
                    return;
                }

                notificationList.innerHTML = notifications
                    .map((notification) => {
                        const data = JSON.parse(notification.message);
                        const isUnread = notification.read_at === null;
                        const timeAgo = MeterApp.methods.formatTimeAgo(
                            notification.created_at
                        );

                        return `
                        <a href="#notificationSection" data-section="notificationSection" class="nav-link block p-3 transition-colors hover:bg-gray-100 ${
                            isUnread ? "bg-blue-50" : ""
                        }">
                            <div class="flex items-start">
                                ${
                                    isUnread
                                        ? '<div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 mr-3 flex-shrink-0"></div>'
                                        : '<div class="w-2 h-2 mr-3"></div>'
                                }
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-800">${
                                        data.text || notification.title
                                    }</p>
                                    <p class="text-xs text-blue-600 font-semibold mt-1">${timeAgo}</p>
                                </div>
                            </div>
                        </a>
                    `;
                    })
                    .join("");
            },
        },

        setupInfiniteScroll(container, loader, fetchFunction) {
            if (!container) return;

            const scrollHandler = () => {
                if (MeterApp.state.activeSection !== "archivesSection") {
                    return;
                }

                const rect = container.getBoundingClientRect();
                const viewportHeight = window.innerHeight;

                // This condition works for both the window scrolling and the container scrolling
                const isNearBottom =
                    container.scrollTop + container.clientHeight >=
                        container.scrollHeight - 200 ||
                    rect.bottom <= viewportHeight + 200;

                const isModalOpen =
                    !document
                        .getElementById("confirmationModal")
                        ?.classList.contains("hidden") ||
                    !document
                        .getElementById("requestEditModal")
                        ?.classList.contains("hidden");

                if (isNearBottom && !isModalOpen) {
                    fetchFunction.call(MeterApp, false);
                }
            };

            // Listen on both the container and the main window for maximum compatibility
            container.addEventListener("scroll", scrollHandler);
            document.addEventListener("scroll", scrollHandler);
        },
        init() {
            this.elements = {
                navLinks: document.querySelectorAll(".nav-link"),
                sections: document.querySelectorAll(".dashboard-section"),
                electricityHeader: document.getElementById("electricityHeader"),
                readingTable: document.getElementById("readingTable"),
                readingTableBody: document.getElementById("readingTableBody"),
                submitReadingsBtn: document.getElementById("submitReadingsBtn"),
                actionsHeader: document.getElementById("actionsHeader"),
                searchInput: document.getElementById("rentalRatesSearchInput"),
                notificationTableBody: document.getElementById(
                    "notificationTableBody"
                ),
                noNotificationsMessage: document.getElementById(
                    "noNotificationsMessage"
                ),
                notificationTableHeader: document.getElementById(
                    "notificationTableHeader"
                ),
                confirmationModal: document.getElementById("confirmationModal"),
                requestEditModal: document.getElementById("requestEditModal"),
                editReasonTextarea: document.getElementById("editReason"),
                paginationContainer: document.getElementById(
                    "paginationContainer"
                ),
                archivesSection: document.getElementById("archivesSection"),
                archiveFilterForm: document.getElementById("archiveFilterForm"),
                archiveSearchInput: document.querySelector(
                    '#archiveFilterForm input[name="search"]'
                ),
                archiveMonthSelect: document.querySelector(
                    '#archiveFilterForm select[name="month"]'
                ),
                archiveResultsContainer: document.getElementById(
                    "archiveResultsContainer"
                ),
                archiveLoadingIndicator: document.getElementById(
                    "archiveLoadingIndicator"
                ),
                sectionNavBtns: document.querySelectorAll(
                    "#electricitySection .section-nav-btn"
                ),
                archiveSectionButtons: document.querySelectorAll(
                    "#archivesSection .section-nav-btn"
                ),
            };

            this.setupEventListeners();

            this.setupInfiniteScroll(
                this.elements.archiveScrollContainer,
                this.elements.archiveLoadingIndicator,
                this.methods.fetchArchives
            );

            setInterval(this.methods.ForStatusUpdates, 5000);
            setInterval(this.methods.ForScheduleUpdate, 5000);
            //In-App and SMS Notification//
            setInterval(this.methods.fetchNotifications, 7000);

            const hash = window.location.hash.substring(1);
            if (hash && document.getElementById(hash)) {
                this.state.activeSection = hash;
            } else {
                this.state.activeSection = "homeSection";
                window.location.hash = "homeSection";
            }

            const handler = {
                set: (target, property, value) => {
                    target[property] = value;
                    this.render.all();
                    return true;
                },
            };
            this.state = new Proxy(this.state, handler);

            this.render.all();
            setTimeout(() => {
                this.state.loading = false;
                document
                    .getElementById("globalPreloader")
                    ?.classList.add("hidden");
                document
                    .getElementById("dashboardContent")
                    ?.classList.remove("hidden");
            }, 300);
        },

        setupEventListeners() {
            // This listener handles the main navigation tabs
            this.elements.navLinks.forEach((link) => {
                link.addEventListener("click", (e) => {
                    const sectionId = link.dataset.section;
                    if (sectionId) {
                        e.preventDefault();
                        this.methods.navigate(sectionId);
                    }
                });
            });

            //Listeners for Archive Filters
            let archiveSearchTimeout;
            this.elements.archiveSearchInput?.addEventListener("input", (e) => {
                clearTimeout(archiveSearchTimeout);
                archiveSearchTimeout = setTimeout(() => {
                    MeterApp.state.archiveFilters.search = e.target.value;
                    MeterApp.methods.fetchArchives(true);
                }, 500);
            });

            this.elements.archiveMonthSelect?.addEventListener(
                "change",
                (e) => {
                    MeterApp.state.archiveFilters.month = e.target.value;
                    MeterApp.methods.fetchArchives(true);
                }
            );

            // This listener will now work correctly because the selector in init() is fixed
            this.elements.archiveSectionButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    this.elements.archiveSectionButtons.forEach((btn) =>
                        btn.classList.remove("active")
                    );
                    button.classList.add("active");
                    MeterApp.state.archiveFilters.section =
                        button.dataset.section;
                    MeterApp.methods.fetchArchives(true);
                });
            });

            // This listener will handle fetching more archives as the user scrolls
            document.addEventListener("scroll", () => {
                // First, check if a modal is open. If so, do nothing.
                // This prevents the background from trying to load more items.
                const isModalOpen =
                    !document
                        .getElementById("confirmationModal")
                        ?.classList.contains("hidden") ||
                    !document
                        .getElementById("requestEditModal")
                        ?.classList.contains("hidden");

                if (isModalOpen) {
                    return;
                }

                // Only proceed if the archives section is active, we are not already fetching,
                // and there is more data to load.
                if (
                    MeterApp.state.activeSection !== "archivesSection" ||
                    MeterApp.state.isFetchingArchives ||
                    !MeterApp.state.archiveHasMore
                ) {
                    return;
                }

                const { scrollTop, scrollHeight, clientHeight } =
                    document.documentElement;

                // Trigger the fetch when the user is 200px from the bottom of the page.
                if (scrollTop + clientHeight >= scrollHeight - 200) {
                    // The 'false' argument tells fetchArchives we are appending, not resetting.
                    MeterApp.methods.fetchArchives(false);
                }
            });

            // This listener updates the page when the URL hash changes
            window.addEventListener("hashchange", () => {
                const hash = window.location.hash.substring(1) || "homeSection";
                if (this.state.activeSection !== hash) {
                    this.state.activeSection = hash;
                }
            });

            //The listener is now attached to the permanent <table> element
            this.elements.readingTable?.addEventListener("input", (e) => {
                const target = e.target;
                const readingId = target.dataset.readingId;
                if (!readingId) return;

                // Logic to update the data cache as the user types
                if (target.matches(".reading-input")) {
                    this.methods.updateReadingCache(
                        readingId,
                        target.value,
                        "value"
                    );
                } else if (target.matches(".previous-reading-input")) {
                    this.methods.updateReadingCache(
                        readingId,
                        target.value,
                        "previous"
                    );
                }

                // Logic for real-time consumption calculation
                const row = target.closest("tr");
                if (!row) return;

                const prevInput = row.querySelector(".previous-reading-input");
                const currentInput = row.querySelector(".reading-input");
                const consumptionCell = row.querySelector(".consumption-cell");

                const readingData = MeterApp.state.meterReadings.find(
                    (r) => r.utility_reading_id == readingId
                );

                const prevReading =
                    parseFloat(
                        prevInput
                            ? prevInput.value
                            : readingData
                            ? readingData.previousReading
                            : 0
                    ) || 0;
                const currentReading =
                    parseFloat(
                        currentInput
                            ? currentInput.value
                            : readingData
                            ? readingData.currentReading
                            : 0
                    ) || 0;

                if (consumptionCell) {
                    const consumption = currentReading - prevReading;
                    consumptionCell.textContent = consumption.toFixed(2);
                }
            });

            // Event listener for the main submit button
            this.elements.submitReadingsBtn?.addEventListener("click", () => {
                if (this.methods.validateReadings()) {
                    this.methods.openModal("confirmationModal");
                }
            });

            // Event listeners for the Wet/Dry/Semi-Wet section buttons
            this.elements.sectionNavBtns.forEach((btn) => {
                btn.addEventListener("click", () => {
                    this.elements.sectionNavBtns.forEach((b) =>
                        b.classList.remove("active")
                    );
                    btn.classList.add("active");
                    this.state.currentRentalSection = btn.dataset.section;
                    this.state.currentPage = 1;
                });
            });

            // Event listener for the search input
            this.elements.searchInput?.addEventListener("input", (e) => {
                this.state.search = e.target.value;
                this.state.currentPage = 1;
            });

            // Event listener for handling clicks on "Request Edit" buttons
            this.elements.readingTable?.addEventListener("click", (e) => {
                if (e.target.matches(".request-edit-btn")) {
                    this.methods.openRequestEditModal(e.target.dataset.stallId);
                }
            });
            //In-App and SMS Notification//
            const mainContent = document.querySelector(".main-content");
            if (mainContent) {
                // Use event delegation on the permanent main content area
                mainContent.addEventListener("click", (e) => {
                    const bellButton = e.target.closest(
                        ".notificationBell button"
                    );
                    if (bellButton) {
                        e.stopPropagation();
                        // Find the dropdown that is inside the currently active section
                        const activeSection = document.querySelector(
                            ".dashboard-section.active"
                        );
                        if (activeSection) {
                            const dropdown = activeSection.querySelector(
                                ".notificationDropdown"
                            );
                            if (dropdown) {
                                const isHidden =
                                    dropdown.classList.toggle("hidden");
                                if (
                                    !isHidden &&
                                    MeterApp.state.unreadCount > 0
                                ) {
                                    MeterApp.methods.markAsRead();
                                }
                            }
                        }
                    }
                });
            }

            // This listener closes the dropdown when clicking anywhere else on the page
            window.addEventListener("click", (e) => {
                // Find the active dropdown
                const activeSection = document.querySelector(
                    ".dashboard-section.active"
                );
                if (activeSection) {
                    const dropdown = activeSection.querySelector(
                        ".notificationDropdown"
                    );
                    const bell =
                        activeSection.querySelector(".notificationBell");
                    // If the dropdown exists, is not hidden, and the click was outside the bell
                    if (
                        dropdown &&
                        !dropdown.classList.contains("hidden") &&
                        !bell.contains(e.target)
                    ) {
                        dropdown.classList.add("hidden");
                    }
                }
            });

            // Event listeners for all modal confirmation and cancel buttons
            document
                .getElementById("confirmSubmit")
                ?.addEventListener("click", this.methods.submitReadings);
            document
                .getElementById("cancelSubmit")
                ?.addEventListener("click", () =>
                    this.methods.closeModal("confirmationModal")
                );
            document
                .getElementById("closeModalTop")
                ?.addEventListener("click", () =>
                    this.methods.closeModal("confirmationModal")
                );
            document
                .getElementById("sendRequestBtn")
                ?.addEventListener("click", this.methods.sendEditRequest);
            document
                .getElementById("cancelRequestBtn")
                ?.addEventListener("click", () =>
                    this.methods.closeModal("requestEditModal")
                );
            document
                .getElementById("closeRequestModalBtn")
                ?.addEventListener("click", () =>
                    this.methods.closeModal("requestEditModal")
                );
        },
    };

    MeterApp.init();
});
