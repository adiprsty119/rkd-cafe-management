function notificationSystem() {
    return {

        open: false,
        notifications: [],
        count: 0,

        toggle() {
            this.open = !this.open

            if (this.open) {
                this.load()
            }
        },

        async load() {

            try {

                let res = await fetch('/rkd-cafe/api/notifications/get.php')
                let data = await res.json()

                this.notifications = data.notifications
                this.count = data.unread

            } catch (err) {
                console.error('Notification load error:', err)
            }

        },

        async markRead(id) {

            try {

                await fetch('/rkd-cafe/api/notifications/read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })

                this.load()

            } catch (err) {
                console.error('Mark read error:', err)
            }

        },

        async markAllRead() {

            try {

                await fetch('/rkd-cafe/api/notifications/read_all.php', {
                    method: 'POST'
                })

                this.load()

            } catch (err) {
                console.error('Mark all read error:', err)
            }

        },

        init() {

            this.load()

            setInterval(() => {
                this.load()
            }, 5000)

        }

    }
}