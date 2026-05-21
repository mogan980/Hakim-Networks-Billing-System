let cleanBwChart = null;
let cleanLabels = [];
let cleanRx = [];
let cleanTx = [];

async function cleanBandwidthLive(){
  try{
    const r = await fetch("bandwidth_live_api.php?t="+Date.now(), {cache:"no-store"});
    const d = await r.json();
    if(!d.ok) return;

    const rx = Number(d.rx_bps || 0) / 1000000;
    const tx = Number(d.tx_bps || 0) / 1000000;

    const cards = Array.from(document.querySelectorAll("div"));
    const card = cards.find(c => c.innerText && c.innerText.includes("Bandwidth Monitor"));
    if(!card) return;

    if(!document.getElementById("cleanBwChart")){
      card.innerHTML = `
        <h2 style="margin:0 0 4px;">Bandwidth Monitor</h2>
        <p style="color:#64748b;margin:0 0 18px;">Live MikroTik RX/TX traffic.</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px;">
            <small style="color:#64748b;font-weight:800;">⬇ Download RX</small>
            <h3 id="cleanRxValue" style="margin:8px 0 0;font-size:24px;">0 Mbps</h3>
          </div>

          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px;">
            <small style="color:#64748b;font-weight:800;">⬆ Upload TX</small>
            <h3 id="cleanTxValue" style="margin:8px 0 0;font-size:24px;">0 Mbps</h3>
          </div>
        </div>

        <div style="height:250px;">
          <canvas id="cleanBwChart"></canvas>
        </div>
      `;
    }

    document.getElementById("cleanRxValue").innerText = rx.toFixed(2)+" Mbps";
    document.getElementById("cleanTxValue").innerText = tx.toFixed(2)+" Mbps";

    cleanLabels.push(new Date().toLocaleTimeString());
    cleanRx.push(rx);
    cleanTx.push(tx);

    if(cleanLabels.length > 12){
      cleanLabels.shift(); cleanRx.shift(); cleanTx.shift();
    }

    const ctx = document.getElementById("cleanBwChart");

    if(!cleanBwChart && ctx){
      cleanBwChart = new Chart(ctx,{
        type:"line",
        data:{
          labels:cleanLabels,
          datasets:[
            {label:"Download RX Mbps",data:cleanRx,tension:.35,borderWidth:3},
            {label:"Upload TX Mbps",data:cleanTx,tension:.35,borderWidth:3}
          ]
        },
        options:{
          responsive:true,
          maintainAspectRatio:false,
          animation:false,
          scales:{y:{beginAtZero:true}}
        }
      });
    }else if(cleanBwChart){
      cleanBwChart.data.labels = cleanLabels;
      cleanBwChart.data.datasets[0].data = cleanRx;
      cleanBwChart.data.datasets[1].data = cleanTx;
      cleanBwChart.update();
    }

  }catch(e){
    console.log("clean bandwidth error", e);
  }
}

cleanBandwidthLive();
setInterval(cleanBandwidthLive,3000);
