/* ==========================
   GLOBAL CHART REGISTRY
========================== */

const charts = {};


/* ==========================
   INIT DASHBOARD
========================== */

document.addEventListener("DOMContentLoaded", () => {

    const data = window.analyticsData || {};
    renderDashboard(data);

});


/* ==========================
   RENDER DASHBOARD
========================== */

function renderDashboard(data){

    if(!data) return

    updateKPI(data.kpi || {})

    renderLineChart(
        "salesChart",
        data.salesTrend,
        "Revenue",
        "revenue",
        "#f59e0b",
        true,
        {},
        "Belum ada data penjualan"
    )

    renderBarChart(
        "customerChart",
        data.customerInsight,
        "Orders",
        "orders",
        "#3b82f6",
        "Belum ada aktivitas pelanggan"
    )

    renderHorizontalBarChart(
        "profitChart",
        data.productProfit,
        "Profit",
        "profit",
        "#10b981",
        "Belum ada data profit produk"
    )

    renderLineChart(
        "predictionChart",
        data.salesPrediction,
        "Predicted Revenue",
        "revenue",
        "#8b5cf6",
        true,
        {borderDash:[8,6]},
        "Belum cukup data untuk prediksi AI"
    )

    renderLineChart(
        "customerGrowthChart",
        data.customerGrowth,
        "New Customers",
        "new_customers",
        "#22c55e",
        false,
        {},
        "Belum ada pertumbuhan pelanggan"
    )

    renderDoughnutChart(
        "paymentChart",
        data.paymentDistribution,
        "payment_method",
        "orders",
        "Belum ada metode pembayaran tercatat"
    )

}


/* ==========================
   UPDATE KPI CARDS
========================== */

function updateKPI(kpi){

    if(!kpi) return

    setText("#kpiRevenue","Rp "+formatNumber(kpi.total_revenue))
    setText("#kpiOrders",formatNumber(kpi.total_orders))
    setText("#kpiCustomers",formatNumber(kpi.active_customers))
    setText("#kpiAvgOrder","Rp "+formatNumber(kpi.avg_order))

}


/* ==========================
   GENERIC CHART FACTORY
========================== */

function createChart(id,config){

    const canvas = document.getElementById(id)
    if(!canvas) return

    resetChartContainer(id)

    if(!config.data.labels.length){

        if(charts[id]){
            charts[id].destroy()
            delete charts[id]
        }

        return
    }

    const ctx = canvas.getContext("2d")

    if(charts[id]){
        charts[id].destroy()
        delete charts[id]
    }
    canvas.classList.add("chart-ready")
    charts[id] = new Chart(ctx,config)
}


/* ==========================
   GENERAL LINE CHART
========================== */

function renderLineChart(id,data,label,valueKey,color,currency=false,extra={},emptyMessage="Belum ada data",labelKey="date"){

    if(!data || data.length < 2){

        showChartMessage(
            id,
            emptyMessage + " (minimal 2 hari data)"
        )

        return
    }

    const labels = data.map(i => labelKey === "date" ? formatDate(i[labelKey]) : i[labelKey])
    const values = data.map(i => Number(i[valueKey]))

    createChart(id,{
        type:"line",

        data:{
            labels,
            datasets:[{
                label,
                data:values,
                borderColor:color,
                backgroundColor:getGradient(id,color),
                tension:0.35,
                borderWidth:3,
                fill:true,
                pointRadius:4,
                ...extra
            }]
        },

        options:getLineOptions(currency)

    })

}


/* ==========================
   GENERAL BAR CHART
========================== */

function renderBarChart(id,data,label,valueKey,color,emptyMessage="Belum ada data"){

    if(!data || !data.length){
        showChartMessage(id,emptyMessage)
        return
    }

    const labels = data.map(i => i.name)
    const values = data.map(i => Number(i[valueKey]))

    createChart(id,{

        type:"bar",

        data:{
            labels,
            datasets:[{
                label,
                data:values,
                backgroundColor:getGradient(id,color),
                borderRadius:8
            }]
        },

        options:getBarOptions()

    })

}


/* ==========================
   GENERAL HORIZONTAL BAR
========================== */

function renderHorizontalBarChart(id,data,label,valueKey,color,emptyMessage="Belum ada data"){

    if(!data || !data.length){
        showChartMessage(id,emptyMessage)
        return
    }

    const labels = data.map(i => i.name)
    const values = data.map(i => Number(i[valueKey]))

    createChart(id,{

        type:"bar",

        data:{
            labels,
            datasets:[{
                label,
                data:values,
                backgroundColor:getGradientHorizontal(id,color),
                borderRadius:10
            }]
        },

        options:getHorizontalBarOptions()

    })

}


/* ==========================
   GENERAL DOUGHNUT CHART
========================== */

function renderDoughnutChart(id,data,labelKey,valueKey,emptyMessage="Belum ada data"){

    if(!data || !data.length){
        showChartMessage(id,emptyMessage)
        return
    }

    const labels = data.map(i => i[labelKey])
    const values = data.map(i => Number(i[valueKey]))

    createChart(id,{

        type:"doughnut",

        data:{
            labels,
            datasets:[{
                data:values,
                backgroundColor:[
                    "#3b82f6",
                    "#10b981",
                    "#f59e0b",
                    "#ef4444",
                    "#8b5cf6"
                ]
            }]
        },

        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{
                legend:{
                    position:"bottom",
                    labels:{usePointStyle:true}
                }
            }
        }

    })

}


/* ==========================
   HELPER FUNCTIONS
========================== */

function setText(selector,value){

    const el = document.querySelector(selector)
    if(el) el.innerText = value

}


function formatNumber(num){

    return (num || 0).toLocaleString("id-ID")

}


function formatDate(date){

    if(!date) return ""

    const d = new Date(date)

    if(isNaN(d)) return "Invalid"

    return d.toLocaleDateString("id-ID",{
        day:"2-digit",
        month:"short"
    })

}


function showChartsLoading(){

    const chartIds = [
        "salesChart",
        "customerChart",
        "predictionChart",
        "profitChart",
        "customerGrowthChart",
        "paymentChart"
    ]

    chartIds.forEach(id => {

        const canvas = document.getElementById(id)
        if(!canvas) return

        const container = canvas.parentElement

        // destroy chart lama
        if(charts[id]){
            charts[id].destroy()
            delete charts[id]
        }

        // HAPUS EMPTY STATE
        const empty = container.querySelector(".chart-empty")
        if(empty) empty.remove()

        // HAPUS LOADER LAMA
        const oldLoader = container.querySelector(".chart-loading")
        if(oldLoader) oldLoader.remove()

        canvas.style.display = "none"

        const loader = document.createElement("div")
        loader.className = "chart-loading flex items-center justify-center h-full"

        loader.innerHTML = `
            <div class="chart-skeleton w-full h-full flex flex-col justify-between px-4 py-4">

                <div class="flex justify-between items-end h-full gap-2">

                    <div class="skeleton-bar h-16 w-3"></div>
                    <div class="skeleton-bar h-20 w-3"></div>
                    <div class="skeleton-bar h-10 w-3"></div>
                    <div class="skeleton-bar h-24 w-3"></div>
                    <div class="skeleton-bar h-14 w-3"></div>
                    <div class="skeleton-bar h-28 w-3"></div>
                    <div class="skeleton-bar h-18 w-3"></div>

                </div>

                <div class="flex justify-between text-[10px] text-gray-400 mt-3 opacity-60">
                    <span>Loading</span>
                    <span>Analytics</span>
                </div>

            </div>
        `

        container.appendChild(loader)

    })

}


/* ==========================
   RESET CHART CONTAINER
========================== */

function resetChartContainer(id){

    const canvas = document.getElementById(id)
    if(!canvas) return

    const container = canvas.parentElement

    const msg = container.querySelector(".chart-empty")
    if(msg) msg.remove()

    const loader = container.querySelector(".chart-loading")
    if(loader) loader.remove()

    canvas.style.display = "block"
}


/* ==========================
   EMPTY CHART MESSAGE
========================== */

function showChartMessage(id,message){

    const canvas = document.getElementById(id)
    if(!canvas) return

    const container = canvas.parentElement

    // hapus chart lama jika ada
    if(charts[id]){
        charts[id].destroy()
        delete charts[id]
    }

     // HAPUS LOADER
    const loader = container.querySelector(".chart-loading")
    if(loader) loader.remove()

    canvas.style.display = "none"

    let msg = container.querySelector(".chart-empty")

    if(!msg){

        msg = document.createElement("div")
        msg.className = "chart-empty flex items-center justify-center h-full text-gray-400 text-sm animate-fade-in"
        container.appendChild(msg)

    }

    msg.innerHTML = `
        <div class="text-center">
            <i class="fa-solid fa-chart-line text-gray-300 text-xl mb-2"></i>
            <div>${message}</div>
        </div>
    `
}


/* ==========================
   GRADIENTS
========================== */

function getGradient(id,color){

    const canvas = document.getElementById(id)
    if(!canvas) return color

    const ctx = canvas.getContext("2d")
    const gradient = ctx.createLinearGradient(0,0,0,300)

    gradient.addColorStop(0,color+"66")
    gradient.addColorStop(1,color+"00")

    return gradient

}


function getGradientHorizontal(id,color){

    const canvas = document.getElementById(id)
    if(!canvas) return color

    const ctx = canvas.getContext("2d")
    const gradient = ctx.createLinearGradient(0,0,400,0)

    gradient.addColorStop(0,color+"99")
    gradient.addColorStop(1,color+"33")

    return gradient

}


/* ==========================
   CHART OPTIONS
========================== */

function getLineOptions(currency=false){

    return{

        responsive:true,
        maintainAspectRatio:false,
        animation:{duration:900},

        plugins:{
            legend:{labels:{usePointStyle:true}},
            tooltip:{
                callbacks:{
                    label:c=> currency
                        ? "Rp "+formatNumber(c.raw)
                        : c.raw
                }
            }
        },

        scales:{
            y:{
                beginAtZero:true,
                ticks:{
                    callback:v=> currency
                        ? "Rp "+formatNumber(v)
                        : v
                }
            },
            x:{grid:{display:false}}
        }

    }

}


function getBarOptions(){

    return{

        responsive:true,
        maintainAspectRatio:false,
        animation:{duration:900},
        plugins:{legend:{labels:{usePointStyle:true}}},
        scales:{
            y:{beginAtZero:true},
            x:{grid:{display:false}}
        }

    }

}


function getHorizontalBarOptions(){

    return{

        indexAxis:"y",
        responsive:true,
        maintainAspectRatio:false,
        animation:{duration:900},
        plugins:{legend:{labels:{usePointStyle:true}}},
        scales:{
            x:{ticks:{callback:v=>"Rp "+formatNumber(v)}},
            y:{grid:{display:false}}
        }

    }

}