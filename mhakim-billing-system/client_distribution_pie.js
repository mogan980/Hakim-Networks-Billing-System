async function loadClientDistributionPie(){
  try{
    const res = await fetch("mikrotik_cards_api.php?t=" + Date.now(), {cache:"no-store"});
    const d = await res.json();
    if(!d.ok) return;

    const hotspot = d.counts?.hotspot || 0;
    const pppoe = d.counts?.pppoe || 0;
    const dhcp = d.counts?.leases || 0;

    document.querySelectorAll("h2,h3").forEach(title=>{
      if(!title.innerText.includes("Client Distribution")) return;

      const card = title.closest("div");
      if(!card || card.dataset.pieReady === "yes") return;
      card.dataset.pieReady = "yes";

      card.innerHTML = `
        <h2>Client Distribution</h2>
        <p style="color:#64748b;margin-top:4px">Live client mix from MikroTik sessions.</p>

        <div style="height:260px;display:flex;align-items:center;justify-content:center;">
          <canvas id="clientDistributionPie"></canvas>
        </div>

        <div style="display:flex;justify-content:center;gap:14px;margin-top:10px;font-weight:700;font-size:13px">
          <span>🔵 Hotspot: ${hotspot}</span>
          <span>🌸 PPPoE: ${pppoe}</span>
          <span>🟠 DHCP: ${dhcp}</span>
        </div>
      `;

      const ctx = document.getElementById("clientDistributionPie");
      new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: ["Hotspot", "PPPoE", "DHCP"],
          datasets: [{
            data: [hotspot, pppoe, dhcp],
            borderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "62%",
          plugins: {
            legend: {
              position: "bottom"
            }
          }
        }
      });
    });

  }catch(e){
    console.log("Client distribution pie error", e);
  }
}

loadClientDistributionPie();
setInterval(loadClientDistributionPie, 10000);
