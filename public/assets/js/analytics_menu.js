document.addEventListener("DOMContentLoaded", () => {

    const data = window.analyticsData || {};

    renderSalesChart(data.salesTrend || []);
    renderCustomerChart(data.customerInsight || []);
    renderProfitChart(data.productProfit || []);
    renderPredictionChart(data.salesPrediction || []);
    renderCustomerGrowthChart(data.customerGrowth || []);
    renderPaymentChart(data.paymentDistribution || []);

});



/* ==========================
   SALES TREND
========================== */

function renderSalesChart(salesTrend){

    if(!document.getElementById("salesChart")) return;

    const labels = salesTrend.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString("id-ID",{day:"2-digit",month:"short"});
    });

    const revenue = salesTrend.map(item => item.revenue);

    const ctx = document.getElementById("salesChart").getContext("2d");

    const gradient = ctx.createLinearGradient(0,0,0,300);
    gradient.addColorStop(0,"rgba(245,158,11,0.45)");
    gradient.addColorStop(1,"rgba(245,158,11,0)");

    new Chart(ctx,{
        type:"line",
        data:{
            labels,
            datasets:[{
                label:"Revenue",
                data:revenue,
                borderColor:"#f59e0b",
                backgroundColor:gradient,
                borderWidth:3,
                tension:0.35,
                fill:true,
                pointRadius:5
            }]
        },
        options:getLineChartOptions(true)
    });

}



/* ==========================
   CUSTOMER INSIGHT
========================== */

function renderCustomerChart(customerInsight){

    if(!document.getElementById("customerChart")) return;

    const labels = customerInsight.map(c=>c.name);
    const orders = customerInsight.map(c=>c.orders);

    const ctx = document.getElementById("customerChart").getContext("2d");

    const gradient = ctx.createLinearGradient(0,0,0,300);
    gradient.addColorStop(0,"rgba(59,130,246,0.6)");
    gradient.addColorStop(1,"rgba(59,130,246,0.1)");

    new Chart(ctx,{
        type:"bar",
        data:{
            labels,
            datasets:[{
                label:"Orders",
                data:orders,
                backgroundColor:gradient,
                borderRadius:8
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            animation:getAnimation(),
            plugins:getLegend(),
            scales:{
                y:{
                    beginAtZero:true,
                    ticks:{stepSize:1}
                },
                x:{grid:{display:false}}
            }
        }
    });

}



/* ==========================
   PRODUCT PROFIT
========================== */

function renderProfitChart(productProfit){

    if(!document.getElementById("profitChart")) return;

    const labels = productProfit.map(p=>p.name);
    const profit = productProfit.map(p=>p.profit);

    const ctx = document.getElementById("profitChart").getContext("2d");

    const gradient = ctx.createLinearGradient(0,0,400,0);
    gradient.addColorStop(0,"rgba(16,185,129,0.7)");
    gradient.addColorStop(1,"rgba(16,185,129,0.2)");

    new Chart(ctx,{
        type:"bar",
        data:{
            labels,
            datasets:[{
                label:"Profit",
                data:profit,
                backgroundColor:gradient,
                borderRadius:10
            }]
        },
        options:{
            indexAxis:"y",
            responsive:true,
            maintainAspectRatio:false,
            animation:getAnimation(),
            plugins:getLegend(),
            scales:{
                x:{
                    ticks:{
                        callback:value=>"Rp "+value.toLocaleString("id-ID")
                    }
                },
                y:{grid:{display:false}}
            }
        }
    });

}



/* ==========================
   SALES PREDICTION
========================== */

function renderPredictionChart(predictionData){

    if(!document.getElementById("predictionChart")) return;

    const labels = predictionData.map(p=>{
        const date=new Date(p.date);
        return date.toLocaleDateString("id-ID",{day:"2-digit",month:"short"});
    });

    const revenue = predictionData.map(p=>p.revenue);

    const ctx = document.getElementById("predictionChart").getContext("2d");

    const gradient = ctx.createLinearGradient(0,0,0,300);
    gradient.addColorStop(0,"rgba(139,92,246,0.45)");
    gradient.addColorStop(1,"rgba(139,92,246,0)");

    new Chart(ctx,{
        type:"line",
        data:{
            labels,
            datasets:[{
                label:"Predicted Revenue",
                data:revenue,
                borderColor:"#8b5cf6",
                backgroundColor:gradient,
                borderDash:[8,6],
                borderWidth:3,
                tension:0.35,
                fill:true
            }]
        },
        options:getLineChartOptions(true)
    });

}



/* ==========================
   REUSABLE OPTIONS
========================== */

function getAnimation(){
    return {
        duration:1200,
        easing:"easeOutQuart"
    };
}

function getLegend(){
    return {
        legend:{
            display:true,
            labels:{usePointStyle:true}
        }
    };
}

function getLineChartOptions(currency=false){

    return{

        responsive:true,
        maintainAspectRatio:false,
        animation:getAnimation(),

        plugins:{
            legend:{
                display:true,
                labels:{usePointStyle:true}
            },
            tooltip:{
                callbacks:{
                    label:function(context){
                        if(currency){
                            return "Rp "+context.raw.toLocaleString("id-ID");
                        }
                        return context.raw;
                    }
                }
            }
        },

        scales:{
            y:{
                beginAtZero:true,
                ticks:{
                    callback:value=> currency
                        ? "Rp "+value.toLocaleString("id-ID")
                        : value
                }
            },
            x:{grid:{display:false}}
        }

    };

}

/* ==========================
   CUSTOMER GROWTH
========================== */

function renderCustomerGrowthChart(customerGrowth){

    if(!document.getElementById("customerGrowthChart")) return;

    const labels = customerGrowth.map(item=>{
        const date = new Date(item.date);
        return date.toLocaleDateString("id-ID",{day:"2-digit",month:"short"});
    });

    const customers = customerGrowth.map(item=>item.new_customers);

    const ctx = document.getElementById("customerGrowthChart").getContext("2d");

    const gradient = ctx.createLinearGradient(0,0,0,300);
    gradient.addColorStop(0,"rgba(34,197,94,0.45)");
    gradient.addColorStop(1,"rgba(34,197,94,0)");

    new Chart(ctx,{
        type:"line",
        data:{
            labels,
            datasets:[{
                label:"New Customers",
                data:customers,
                borderColor:"#22c55e",
                backgroundColor:gradient,
                borderWidth:3,
                tension:0.35,
                fill:true,
                pointRadius:4
            }]
        },
        options:getLineChartOptions(false)
    });

}

/* ==========================
   PAYMENT DISTRIBUTION
========================== */

function renderPaymentChart(paymentData){

    if(!document.getElementById("paymentChart")) return;

    const labels = paymentData.map(p=>p.payment_method);
    const orders = paymentData.map(p=>p.orders);

    const ctx = document.getElementById("paymentChart").getContext("2d");

    new Chart(ctx,{
        type:"doughnut",
        data:{
            labels,
            datasets:[{
                data:orders,
                backgroundColor:[
                    "#3b82f6",
                    "#10b981",
                    "#f59e0b",
                    "#ef4444",
                    "#8b5cf6"
                ],
                borderWidth:0
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            animation:getAnimation(),
            plugins:{
                legend:{
                    position:"bottom",
                    labels:{usePointStyle:true}
                }
            }
        }
    });

}