let proBwChart=null, proLabels=[], proRx=[], proTx=[];

async function loadProBandwidthCard(){
  try{
    const r=await fetch("bandwidth_live_api.php?t="+Date.now(),{cache:"no-store"});
    const d=await r.json();
    if(!d.ok) return;

    const rx=Number(d.rx_bps||0)/1000000;
    const tx=Number(d.tx_bps||0)/1000000;

    const card=[...document.querySelectorAll("div")].find(el=>el.innerText && el.innerText.includes("Bandwidth Monitor"));
    if(!card) return;

    if(!document.getElementById("proBandwidthChart")){
      card.classList.add("pro-bandwidth-card");
      card.innerHTML=`
        <div class="bw-head">
          <div>
            <h2>Bandwidth Monitor</h2>
            <p><span></span>Live MikroTik interface traffic RX/TX.</p>
          </div>
          <div class="bw-live">📡 LIVE</div>
        </div>

        <div class="bw-metrics">
          <div class="bw-metric rx">
            <div class="bw-icon">⬇</div>
            <div><h4>DOWNLOAD RX</h4><strong id="proRxValue">0.00</strong> <small>Mbps</small></div>
          </div>
          <div class="bw-metric tx">
            <div class="bw-icon">⬆</div>
            <div><h4>UPLOAD TX</h4><strong id="proTxValue">0.00</strong> <small>Mbps</small></div>
          </div>
        </div>

        <div class="bw-chart-wrap"><canvas id="proBandwidthChart"></canvas></div>

        <div class="bw-footer">
          <div>🕒 Updating every 3s</div>
          <div>📶 MikroTik Connected</div>
          <div>〽 Live Data</div>
        </div>`;
    }

    document.getElementById("proRxValue").innerText=rx.toFixed(2);
    document.getElementById("proTxValue").innerText=tx.toFixed(2);

    proLabels.push(new Date().toLocaleTimeString());
    proRx.push(rx); proTx.push(tx);
    if(proLabels.length>8){proLabels.shift();proRx.shift();proTx.shift();}

    const ctx=document.getElementById("proBandwidthChart");
    if(!proBwChart && ctx){
      proBwChart=new Chart(ctx,{
        type:"line",
        data:{labels:proLabels,datasets:[
          {label:"Download RX (Mbps)",data:proRx,borderColor:"#0d6efd",backgroundColor:"rgba(13,110,253,.14)",fill:true,tension:.35,borderWidth:3,pointRadius:5},
          {label:"Upload TX (Mbps)",data:proTx,borderColor:"#ff2f6d",backgroundColor:"rgba(255,47,109,.14)",fill:true,tension:.35,borderWidth:3,pointRadius:5}
        ]},
        options:{responsive:true,maintainAspectRatio:false,animation:false,plugins:{legend:{position:"top"}},scales:{y:{beginAtZero:true}}}
      });
    }else if(proBwChart){
      proBwChart.data.labels=proLabels;
      proBwChart.data.datasets[0].data=proRx;
      proBwChart.data.datasets[1].data=proTx;
      proBwChart.update();
    }
  }catch(e){console.log("Bandwidth card error",e);}
}

loadProBandwidthCard();
setInterval(loadProBandwidthCard,3000);
