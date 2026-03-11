document.addEventListener("DOMContentLoaded", () => {

    const data = window.salesAnalytics || {};

    /* ==========================
       HOURLY SALES CHART
    ========================== */

    if (document.getElementById("hourlySalesChart")) {

        const hourly = data.salesHourly || [];

        const labels = hourly.map(item => item.hour + ":00");

        const revenue = hourly.map(item => item.revenue);

        const ctx = document.getElementById("hourlySalesChart").getContext("2d");

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);

        gradient.addColorStop(0, "rgba(99,102,241,0.45)");
        gradient.addColorStop(1, "rgba(99,102,241,0)");

        new Chart(ctx, {

            type: "line",

            data: {

                labels: labels,

                datasets: [{
                    label: "Revenue",
                    data: revenue,
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

                    y: {

                        beginAtZero: true,

                        ticks: {

                            callback: function(value) {

                                return "Rp " + value.toLocaleString("id-ID");

                            }

                        }

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
       DAILY SALES CHART
    ========================== */

    if (document.getElementById("dailySalesChart")) {

        const daily = data.salesDaily || [];

        const labels = daily.map(item => item.day);

        const revenue = daily.map(item => item.revenue);

        const ctx = document.getElementById("dailySalesChart").getContext("2d");

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);

        gradient.addColorStop(0, "rgba(34,197,94,0.45)");
        gradient.addColorStop(1, "rgba(34,197,94,0)");

        new Chart(ctx, {

            type: "bar",

            data: {

                labels: labels,

                datasets: [{
                    label: "Revenue",
                    data: revenue,
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

                                return "Rp " + context.raw.toLocaleString("id-ID");

                            }

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            callback: function(value) {

                                return "Rp " + value.toLocaleString("id-ID");

                            }

                        }

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
       PAYMENT DISTRIBUTION
    ========================== */

    if (document.getElementById("paymentChart")) {

        const payment = data.paymentDistribution || [];

        const labels = payment.map(item => item.payment_method);

        const orders = payment.map(item => item.orders);

        const ctx = document.getElementById("paymentChart").getContext("2d");

        new Chart(ctx, {

            type: "doughnut",

            data: {

                labels: labels,

                datasets: [{

                    label: "Orders",

                    data: orders,

                    backgroundColor: [
                        "#6366f1",
                        "#22c55e",
                        "#f59e0b",
                        "#ef4444",
                        "#06b6d4"
                    ]

                }]

            },

            options: {

                responsive: true,
                maintainAspectRatio: false,

                animation: {
                    duration: 1200
                },

                plugins: {

                    legend: {
                        position: "bottom"
                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                const value = context.raw;

                                return value + " Orders";

                            }

                        }

                    }

                }

            }

        });

    }

});