document.addEventListener("alpine:init", () => {

    Alpine.data("notificationSystem", () => ({

        open: false,
        notifications: [],
        count: 0,
        polling: null,

        toggle() {

            this.open = !this.open

            if (this.open && this.notifications.length === 0) {
                this.load()
            }

        },

        async request(url, options = {}) {

            try {

                const res = await fetch(url, {
                    credentials: "same-origin",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    ...options
                })

                const text = await res.text()

                try {
                    return JSON.parse(text)
                } catch {
                    console.error("Invalid JSON response:", text)
                    return null
                }

            } catch (err) {

                console.error("Notification request error:", err)
                return null

            }

        },

        async load() {

            const data = await this.request("/rkd-cafe/api/notifications/get.php")

            if (!data || data.error) return

            this.notifications = data.notifications || []
            this.count = data.unread || 0

        },

        async markRead(id) {

            await this.request("/rkd-cafe/api/notifications/read.php", {
                method: "POST",
                body: JSON.stringify({ id })
            })

            this.load()

        },

        async markAllRead() {

            await this.request("/rkd-cafe/api/notifications/read_all.php", {
                method: "POST"
            })

            this.load()

        },

        startPolling() {

            if (this.polling) return

            this.polling = setInterval(() => {
                this.load()
            }, 10000)

        },

        stopPolling() {

            if (this.polling) {
                clearInterval(this.polling)
                this.polling = null
            }

        },

        init() {

            this.load()
            this.startPolling()

        }

    }))

})