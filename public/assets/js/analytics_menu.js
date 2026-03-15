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

    /* SALES TREND */

    renderLineChart(
        "salesChart",
        data.salesTrend?.data || [],
        "Revenue",
        "revenue",
        "#f59e0b",
        true,
        {},
        "Belum ada data penjualan"
    )

    updateChartInsight(
        "salesChart",
        data.salesTrend?.insight
    )


    /* CUSTOMER INSIGHT */

    renderBarChart(
        "customerChart",
        data.customerInsight?.data || [],
        "Orders",
        "orders",
        "#3b82f6",
        "Belum ada aktivitas pelanggan"
    )

    updateChartInsight(
        "customerChart",
        data.customerInsight?.insight
    )


    /* PRODUCT PROFIT */

    renderHorizontalBarChart(
        "profitChart",
        data.productProfit?.data || [],
        "Profit",
        "profit",
        "#10b981",
        "Belum ada data profit produk"
    )

    updateChartInsight(
        "profitChart",
        data.productProfit?.insight
    )


    /* SALES PREDICTION */

    renderLineChart(
        "predictionChart",
        data.salesPrediction?.data || [],
        "Predicted Revenue",
        "revenue",
        "#8b5cf6",
        true,
        {borderDash:[8,6]},
        "Belum cukup data untuk prediksi AI"
    )

    updateChartInsight(
        "predictionChart",
        data.salesPrediction?.insight
    )


    /* CUSTOMER GROWTH */

    renderLineChart(
        "customerGrowthChart",
        data.customerGrowth?.data || [],
        "New Customers",
        "new_customers",
        "#22c55e",
        false,
        {},
        "Belum ada pertumbuhan pelanggan"
    )

    updateChartInsight(
        "customerGrowthChart",
        data.customerGrowth?.insight
    )


    /* PAYMENT DISTRIBUTION */

    renderDoughnutChart(
        "paymentChart",
        data.paymentDistribution?.data || [],
        "payment_method",
        "orders",
        "Belum ada metode pembayaran tercatat"
    )

    updateChartInsight(
        "paymentChart",
        data.paymentDistribution?.insight
    )

    /* BUSINESS INSIGHT */
    renderBusinessInsight(data.businessInsight)

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


function updateChartInsight(id, text){

    const canvas = document.getElementById(id)
    if(!canvas) return

    const card = canvas.closest(".chart-card")
    if(!card) return

    const insightEl = card.querySelector(".chart-insight")

    if(!insightEl) return

    insightEl.textContent = text || "Belum ada insight tersedia"

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


/* ==========================
   ESC CLOSE FULLSCREEN
========================== */

document.addEventListener("keydown",e=>{

    if(e.key !== "Escape") return

    const expanded = document.querySelector(".chart-fullscreen")

    if(!expanded) return

    expanded.classList.remove("chart-fullscreen")

    const icon = expanded.querySelector(".chart-expand i")

    if(icon){
        icon.className = "fa-solid fa-up-right-and-down-left-from-center"
    }

})


/* ==========================
   CHART EXPAND MODE
========================== */

function expandChart(btn){

    const card = btn.closest(".chart-card")
    const canvas = card.querySelector("canvas")
    const overlay = document.getElementById("chartOverlay")

    if(!card || !canvas) return

    const icon = btn.querySelector("i")

    if(card.classList.contains("chart-fullscreen")){

        card.classList.remove("chart-fullscreen")

        canvas.parentElement.style.height = "16rem"

        icon.className = "fa-solid fa-up-right-and-down-left-from-center"

        overlay.classList.add("hidden")

    }else{

        card.classList.add("chart-fullscreen")

        canvas.parentElement.style.height = "70vh"

        icon.className = "fa-solid fa-xmark"

        overlay.classList.remove("hidden")

    }

    if(charts[canvas.id]){
        charts[canvas.id].resize()
    }

}


/* ==========================
   DOWNLOAD CHART
========================== */

function downloadChart(id){

    const chart = charts[id]

    if(!chart) return

    const url = chart.toBase64Image()

    const link = document.createElement("a")

    link.href = url
    link.download = id + ".png"

    link.click()

}


/* ==========================
   CLICK OUTSIDE CLOSE
========================== */

document.addEventListener("click",e=>{

    if(e.target.classList.contains("chart-overlay")){

        document
        .querySelectorAll(".chart-fullscreen")
        .forEach(c=>c.classList.remove("chart-fullscreen"))

    }

})

function refreshChart(id){

    renderDashboard(window.analyticsData)

}


/* ==========================
   OVERLAY CLICK CLOSE FULLSCREEN
========================== */

document.getElementById("chartOverlay")?.addEventListener("click",()=>{

    const expanded = document.querySelector(".chart-fullscreen")

    if(!expanded) return

    const canvas = expanded.querySelector("canvas")
    const icon = expanded.querySelector(".chart-expand i")

    expanded.classList.remove("chart-fullscreen")

    canvas.parentElement.style.height = "16rem"

    if(icon){
        icon.className = "fa-solid fa-up-right-and-down-left-from-center"
    }

    document.getElementById("chartOverlay").classList.add("hidden")

})


/* ==========================
   BUSINESS INSIGHT
========================== */

function renderBusinessInsight(data){

    const container = document.getElementById("businessInsightList")
    if(!container) return

    container.innerHTML = ""

    const insights = data?.insights || []

    if(!insights.length){

        container.innerHTML = `
            <div class="text-sm text-white/80">
                AI belum memiliki cukup data untuk menghasilkan insight.
            </div>
        `
        return
    }

    insights.forEach(text => {

        const icon = getInsightIcon(text)
        const category = getInsightCategory(text)

        const card = document.createElement("div")
        card.className = "insight-card"

        card.innerHTML = `

            <div class="insight-icon">
                <i class="${icon}"></i>
            </div>

            <div class="flex-1">

                <div class="flex items-center gap-2 mb-1">

                    <span class="insight-badge ${category.color}">
                        ${category.label}
                    </span>

                </div>

                <div class="text-sm text-white/90 leading-relaxed">
                    ${text}
                </div>

            </div>
        `

        container.appendChild(card)

    })

}


function getInsightCategory(text){

    text = text.toLowerCase()

    if(text.includes("penjualan") || text.includes("revenue"))
        return {label:"Sales", color:"bg-yellow-400/30 text-yellow-100"}

    if(text.includes("profit"))
        return {label:"Profit", color:"bg-green-400/30 text-green-100"}

    if(text.includes("pembayaran") || text.includes("payment"))
        return {label:"Payment", color:"bg-red-400/30 text-red-100"}

    if(text.includes("pelanggan") || text.includes("customer"))
        return {label:"Customer", color:"bg-purple-400/30 text-purple-100"}

    return {label:"Insight", color:"bg-white/30 text-white"}
}


function getInsightIcon(text){

    text = text.toLowerCase()

    if(text.includes("penjualan") || text.includes("revenue"))
        return "fa-solid fa-chart-line"

    if(text.includes("profit"))
        return "fa-solid fa-coins"

    if(text.includes("pembayaran") || text.includes("payment"))
        return "fa-solid fa-credit-card"

    if(text.includes("pelanggan") || text.includes("customer"))
        return "fa-solid fa-users"

    return "fa-solid fa-lightbulb"

}