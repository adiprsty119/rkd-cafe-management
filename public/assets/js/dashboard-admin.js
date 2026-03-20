function dashboard() {
    return {
        loading: false,
        error: null,
        lastUpdated: null,

        data: {
            total_sales: 0,
            orders_today: 0,
            menu_items: 0,
            customers: 0,
            recent_orders: [],
            top_products: []
        },

        async init() {
            await this.fetchData();

            setInterval(() => {
                this.fetchData(false);
            }, 30000);
        },

        async fetchData(showLoader = true) {

            if (showLoader) this.loading = true;
            this.error = null;

            try {

                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 8000);

                const res = await fetch('/rkd-cafe/app/controllers/DashboardAdminController.php', {
                    signal: controller.signal
                });

                clearTimeout(timeout);

                if (!res.ok) throw new Error("HTTP " + res.status);

                const json = await res.json();

                if (!json || typeof json !== 'object') {
                    throw new Error("Invalid response");
                }

                this.data = json;
                this.lastUpdated = new Date();

            } catch (e) {

                console.error("Dashboard error:", e);
                this.error = "Failed to load dashboard";

            } finally {
                this.loading = false;
            }
        },

        chunk(array, size = 3) {
            const result = [];
            for (let i = 0; i < array.length; i += size) {
                result.push(array.slice(i, i + size));
            }
            return result;
        },

        formatRupiah(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(value || 0);
        },

        statusClass(status) {
            return {
                'bg-green-100 text-green-600': status === 'paid',
                'bg-yellow-100 text-yellow-600': status === 'pending',
                'bg-red-100 text-red-600': status === 'cancelled'
            };
        },

        formattedTime() {
            if (!this.lastUpdated) return '-';
            return this.lastUpdated.toLocaleTimeString('id-ID');
        }
    }
}