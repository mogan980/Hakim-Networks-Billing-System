let bwChart = null;
let bwLabels = [];
let bwDownload = [];
let bwUpload = [];

async function loadBandwidthMonitor(){
  try{
    const r = await fetch("bandwidth_live_api.php?t=" + Date.now(), {cache:"no-store"});
    const d = await r.json();
    if(!d.ok) return;

    const now = new Date().toLocaleTimeString();
    bwLabels.push(now);
    bwDownload.push(Number(d.rx_bps || 0) / 1000000);
    bwUpload.push(Number(d.tx_bps || 0) / 1000000);

    if(bwLabels.length > 10){
      bwLabels.shift();
      bwDownload.shift();
      bwUpload.shift();
    }

    document.querySelectorAll("h2,h3").forEach(title=>{
      if(!title.innerText.includes("Bandwidth Monitor")) return;

      const card = title.closest("div");
      if(!card) return;

      if(!card.querySelector("#bandwidthLiveChart")){
        card.innerHTML = `
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <h2 style="margin:0;">Bandwidth Monitor</h2>
              <p style="color:#64748b;margin:6px 0 0;">Live MikroTik interface traffic RX/TX.</p>
            </div>
            <span style="background:#dcfce7;color:#166534;padding:8px 14px;border-radius:999px;font-weight:900;font-size:12px;">LIVE</span>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:18px 0;">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px;">
              <small style="color:#64748b;font-weight:800;">⬇ Download RX</small>
              <h3 id="rxLiveValue" style="margin:6px 0 0;font-size:22px;">0 Mbps</h3>
            </div>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px;">
              <small style="color:#64748b;font-weight:800;">⬆ Upload TX</small>
              <h3 id="txLiveValue" style="margin:6px 0 0;font-size:22px;">0 Mbps</h3>
            </div>
          </div>

          <div style="height:250px;">
            <canvas id="bandwidthLiveChart"></canvas>
          </div>
        `;
      }

      document.getElementById("rxLiveValue").innerText = d.rx || "0 Mbps";
      document.getElementById("txLiveValue").innerText = d.tx || "0 Mbps";

      const ctx = document.getElementById("bandwidthLiveChart");
      if(!bwChart && ctx){
        bwChart = new Chart(ctx, {
          type: "line",
          data: {
            labels: bwLabels,
            datasets: [
              { label: "Download RX Mbps", data: bwDownload, tension: .35, borderWidth: 3 },
              { label: "Upload TX Mbps", data: bwUpload, tension: .35, borderWidth: 3 }
            ]
          },
          options: {
            responsive:true,
            maintainAspectRatio:false,
            scales:{ y:{ beginAtZero:true } }
          }
        });
      } else if(bwChart){
        bwChart.data.labels = bwLabels;
        bwChart.data.datasets[0].data = bwDownload;
        bwChart.data.datasets[1].data = bwUpload;
        bwChart.update();
      }
    });

  }catch(e){
    console.log("Bandwidth monitor error", e);
  }
}

loadBandwidthMonitor();
setInterval(loadBandwidthMonitor,3000);
