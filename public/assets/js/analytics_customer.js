document.addEventListener("DOMContentLoaded", () => {

    const data = window.customerAnalytics || {};

    /* ==========================
       CUSTOMER GROWTH
    ========================== */

    if (document.getElementById("customerGrowthChart")) {

        const growth = data.customerGrowth || [];

        const labels = growth.map(item => {

            const date = new Date(item.date);

            return date.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "short"
            });

        });

        const values = growth.map(item => item.new_customers);

        const ctx = document.getElementById("customerGrowthChart").getContext("2d");

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);

        gradient.addColorStop(0, "rgba(99,102,241,0.45)");
        gradient.addColorStop(1, "rgba(99,102,241,0)");

        new Chart(ctx, {

            type: "line",

            data: {

                labels: labels,

                datasets: [{
                    label: "New Customers",
                    data: values,
                    borderColor: "#6366f1",
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: "#6366f1"
                }]

            },

            options: {

                responsive: true,
                maintainAspectRatio: false,

                animation: {
                    duration: 1000,
                    easing: "easeOutQuart"
                },

                plugins: {

                    legend: {
                        labels: {
                            usePointStyle: true
                        }
                    }

                },

                scales: {

                    y: {
                        beginAtZero: true
                    },

                    x: {
                        grid: {
                            display: false
                        }
                    }

                }

            }

        });

    }



    /* ==========================
       CUSTOMER ORDERS
    ========================== */

    if (document.getElementById("customerOrdersChart")) {

        const insight = data.customerInsight || [];

        const labels = insight.map(item => item.name);

        const orders = insight.map(item => item.orders);

        const ctx = document.getElementById("customerOrdersChart").getContext("2d");

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);

        gradient.addColorStop(0, "rgba(34,197,94,0.45)");
        gradient.addColorStop(1, "rgba(34,197,94,0)");

        new Chart(ctx, {

            type: "bar",

            data: {

                labels: labels,

                datasets: [{
                    label: "Orders",
                    data: orders,
                    backgroundColor: gradient,
                    borderRadius: 6
                }]

            },

            options: {

                responsive: true,
                maintainAspectRatio: false,

                animation: {
                    duration: 1000
                },

                plugins: {

                    legend: {
                        labels: {
                            usePointStyle: true
                        }
                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return context.raw + " orders";

                            }

                        }

                    }

                },

                scales: {

                    y: {
                        beginAtZero: true
                    },

                    x: {
                        grid: {
                            display: false
                        }
                    }

                }

            }

        });

    }



    /* ==========================
       CUSTOMER LIFETIME VALUE
    ========================== */

    if (document.getElementById("customerLifetimeChart")) {

        const lifetime = data.customerLifetime || [];

        const labels = lifetime.map(item => item.name);

        const spend = lifetime.map(item => item.total_spend);

        const ctx = document.getElementById("customerLifetimeChart").getContext("2d");

        const gradient = ctx.createLinearGradient(0, 0, 400, 0);

        gradient.addColorStop(0, "rgba(245,158,11,0.6)");
        gradient.addColorStop(1, "rgba(245,158,11,0.2)");

        new Chart(ctx, {

            type: "bar",

            data: {

                labels: labels,

                datasets: [{
                    label: "Customer Value",
                    data: spend,
                    backgroundColor: gradient,
                    borderRadius: 8
                }]

            },

            options: {

                indexAxis: "y",

                responsive: true,
                maintainAspectRatio: false,

                animation: {
                    duration: 1200,
                    easing: "easeOutQuart"
                },

                plugins: {

                    legend: {
                        labels: {
                            usePointStyle: true
                        }
                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return "Rp " + context.raw.toLocaleString("id-ID");

                            }

                        }

                    }

                },

                scales: {

                    x: {

                        ticks: {

                            callback: function(value) {

                                return "Rp " + value.toLocaleString("id-ID");

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

});