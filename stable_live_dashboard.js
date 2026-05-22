let dashboardLoaded = false;
let liveChart = null;

async function loadStableDashboard(){

    try{

        const res = await fetch("live_noc_api.php?t=" + Date.now(),{
            cache:"no-store"
        });

        const data = await res.json();

        if(!data.status) return;

        const isOnline = data.status === "online";

        /* ---------------- ONLINE STATUS ---------------- */

        document.querySelectorAll("*").forEach(el=>{

            if(el.innerText && el.innerText.trim() === "OFFLINE"){
                if(isOnline){
                    el.innerText = "ONLINE";
                    el.style.color = "#16a34a";
                }
            }

            if(el.innerText && el.innerText.trim() === "ONLINE"){
                if(!isOnline){
                    el.innerText = "OFFLINE";
                    el.style.color = "#dc2626";
                }
            }

        });

        /* ---------------- TOP CARDS ---------------- */

        const cards = document.querySelectorAll("div");

        cards.forEach(card=>{

            const txt = card.innerText || "";

            if(txt.includes("Router Identity")){
                const h = card.querySelector("h2,h3,h1");
                if(h) h.innerText = data.identity || "MikroTik";
            }

            if(txt.includes("CPU Load")){
                const h = card.querySelector("h2,h3,h1");
                if(h) h.innerText = (data.cpu || 0) + "%";
            }

            if(txt.includes("Router Uptime")){
                const h = card.querySelector("h2,h3,h1");
                if(h) h.innerText = data.uptime || "-";
            }

            if(txt.includes("Simple Queues")){
                const h = card.querySelector("h2,h3,h1");
                if(h) h.innerText = data.simple_queues || 0;
            }

            if(txt.includes("Hotspot Online")){
                const h = card.querySelector("h2,h3,h1");
                if(h) h.innerText = data.hotspot_online || 0;
            }

            if(txt.includes("PPPoE Online")){
                const h = card.querySelector("h2,h3,h1");
                if(h) h.innerText = data.pppoe_online || 0;
            }

        });

        /* ---------------- BANDWIDTH ---------------- */

        const rx = Number(data.rx_mbps || 0);
        const tx = Number(data.tx_mbps || 0);

        document.querySelectorAll("h2,h3").forEach(title=>{

            if(!title.innerText.includes("Bandwidth Monitor")) return;

            const card = title.closest("div");

            if(!card) return;

            if(!dashboardLoaded){

                card.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                        <div>
                            <h2 style="margin:0;font-size:34px;">Bandwidth Monitor</h2>
                            <p style="margin-top:6px;color:#64748b;">
                                Live MikroTik traffic monitoring
                            </p>
                        </div>

                        <span style="
                            background:#dcfce7;
                            color:#166534;
                            padding:10px 16px;
                            border-radius:999px;
                            font-weight:900;
                        ">LIVE</span>
                    </div>

                    <div style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:18px;
                        margin-bottom:18px;
                    ">

                        <div style="
                            background:#f8fafc;
                            border:1px solid #e2e8f0;
                            border-radius:18px;
                            padding:18px;
                        ">
                            <div style="font-size:13px;color:#64748b;font-weight:800;">
                                ⬇ Download RX
                            </div>

                            <div id="rxSpeed" style="
                                font-size:34px;
                                font-weight:900;
                                margin-top:10px;
                                color:#020617;
                            ">0 Mbps</div>
                        </div>

                        <div style="
                            background:#f8fafc;
                            border:1px solid #e2e8f0;
                            border-radius:18px;
                            padding:18px;
                        ">
                            <div style="font-size:13px;color:#64748b;font-weight:800;">
                                ⬆ Upload TX
                            </div>

                            <div id="txSpeed" style="
                                font-size:34px;
                                font-weight:900;
                                margin-top:10px;
                                color:#020617;
                            ">0 Mbps</div>
                        </div>

                    </div>

                    <div style="
                        background:white;
                        border-radius:20px;
                        padding:16px;
                        border:1px solid #e2e8f0;
                    ">
                        <canvas id="stableBandwidthChart" height="120"></canvas>
                    </div>
                `;

                dashboardLoaded = true;

                const ctx = document.getElementById("stableBandwidthChart");

                liveChart = new Chart(ctx,{
                    type:"line",
                    data:{
                        labels:[],
                        datasets:[
                            {
                                label:"Download RX",
                                data:[],
                                borderWidth:3,
                                tension:.4
                            },
                            {
                                label:"Upload TX",
                                data:[],
                                borderWidth:3,
                                tension:.4
                            }
                        ]
                    },
                    options:{
                        responsive:true,
                        maintainAspectRatio:false,
                        animation:false,
                        scales:{
                            y:{
                                beginAtZero:true
                            }
                        }
                    }
                });

            }

            const rxEl = document.getElementById("rxSpeed");
            const txEl = document.getElementById("txSpeed");

            if(rxEl) rxEl.innerText = rx.toFixed(2) + " Mbps";
            if(txEl) txEl.innerText = tx.toFixed(2) + " Mbps";

            if(liveChart){

                const now = new Date().toLocaleTimeString();

                liveChart.data.labels.push(now);

                liveChart.data.datasets[0].data.push(rx);
                liveChart.data.datasets[1].data.push(tx);

                if(liveChart.data.labels.length > 12){
                    liveChart.data.labels.shift();
                    liveChart.data.datasets[0].data.shift();
                    liveChart.data.datasets[1].data.shift();
                }

                liveChart.update();
            }

        });

    }catch(e){
        console.log("Dashboard live sync error",e);
    }

}

loadStableDashboard();

setInterval(loadStableDashboard,5000);
