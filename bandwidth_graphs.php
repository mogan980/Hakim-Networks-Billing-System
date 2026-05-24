<!DOCTYPE html>
<html>
<head>
<title>Live Bandwidth Graphs</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root{
  --bg:#07131f;--panel:#0f1b2d;--panel2:#111827;--border:#1e293b;
  --green:#22c55e;--blue:#38bdf8;--muted:#94a3b8;--text:#e5e7eb;
}
*{box-sizing:border-box}
body{margin:0;background:radial-gradient(circle at top,#10243d 0,#07131f 45%,#020617 100%);color:var(--text);font-family:Inter,Arial,sans-serif}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:245px;background:linear-gradient(180deg,#020617,#052e2b);padding:24px 16px;overflow:auto}
.sidebar h2{color:#22c55e;margin:0 0 5px;font-size:30px}.sidebar p{color:var(--muted);font-size:12px;margin:0 0 25px}
.sidebar a{display:flex;gap:10px;align-items:center;color:white;text-decoration:none;background:rgba(17,24,39,.82);margin:9px 0;padding:13px 15px;border-radius:14px;font-weight:800;transition:.2s}
.sidebar a:hover{background:#14532d;transform:translateX(3px)}
.sidebar a.active{background:#16a34a;box-shadow:0 12px 30px rgba(22,163,74,.25)}
.main{margin-left:270px;padding:28px}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
h1{font-size:34px;margin:0}.top p{color:var(--muted);margin:7px 0 0}
.badge{background:#064e3b;color:#86efac;border:1px solid #15803d;border-radius:999px;padding:11px 16px;font-weight:900}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:22px 0}
.card{position:relative;overflow:hidden;background:linear-gradient(145deg,#0f1b2d,#111827);border:1px solid var(--border);border-radius:24px;padding:22px;box-shadow:0 18px 45px rgba(0,0,0,.22)}
.card:after{content:"";position:absolute;right:-25px;top:-25px;width:90px;height:90px;border-radius:50%;background:rgba(34,197,94,.08)}
.card small{color:var(--muted);font-weight:800}.card b{display:block;font-size:30px;color:var(--green);margin-top:10px;letter-spacing:-.5px}
.panel{background:rgba(15,27,45,.92);border:1px solid var(--border);border-radius:26px;padding:24px;margin-bottom:22px;box-shadow:0 18px 45px rgba(0,0,0,.22)}
.panel-head{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:16px}
.panel h2{margin:0}
select{background:#020617;color:white;border:1px solid #334155;border-radius:14px;padding:12px 15px;font-weight:800}
table{width:100%;border-collapse:collapse;margin-top:14px}
th{background:#020617;padding:14px;text-align:left;color:#cbd5e1}
td{padding:14px;border-bottom:1px solid #1e293b}
tr:hover td{background:rgba(34,197,94,.05)}
.pill{border-radius:999px;padding:7px 11px;font-size:11px;font-weight:900}
.on{background:#dcfce7;color:#166534}.off{background:#fee2e2;color:#991b1b}
.toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.btn{background:#16a34a;color:white;text-decoration:none;border:0;border-radius:13px;padding:11px 14px;font-weight:900;cursor:pointer}
.btn-blue{background:#2563eb}
@media(max-width:1000px){.sidebar{position:relative;width:auto}.main{margin-left:0}.grid{grid-template-columns:1fr 1fr}.top{display:block}}
</style>

</head>
<body>

<div class="sidebar">
<h2>M.Hakim</h2>
<p>Hakim Networks ISP</p>
<a href="noc_final_clean.php">📊 Dashboard</a>
<a class="active" href="bandwidth_graphs.php">📈 Bandwidth Graphs</a>
<a href="queue_control_pro.php">⚡ Queue Control</a>
<a href="pppoe_live_monitor.php">📡 PPPoE Monitor</a>
<a href="client_expiry_manager.php">⏱ Client Expiry</a>
<a href="health_check.php">💙 Health Check</a>
</div>

<div class="main">
<div class="top">
<div>
<h1>📈 Live MikroTik Bandwidth Graphs</h1>
<p>Real-time upload/download traffic from MikroTik interfaces.</p>
</div>
<span class="badge" id="statusBadge">● Loading</span>
</div>

<div class="grid">
<div class="card"><small>Total Download</small><b id="rxTotal">0 Mbps</b></div>
<div class="card"><small>Total Upload</small><b id="txTotal">0 Mbps</b></div>
<div class="card"><small>Active Interfaces</small><b id="activeIfs">0</b></div>
<div class="card"><small>Last Update</small><b id="lastUpdate">-</b></div>
</div>

<div class="panel">
<div class="panel-head">
  <div>
    <h2>Live Traffic Chart</h2>
    <p style="color:#94a3b8;margin:6px 0 0">Real-time upload/download trend with 3-second sync.</p>
  </div>
  <div class="toolbar">
    <button class="btn" onclick="syncBandwidth()">↻ Refresh</button>
    <select id="interfaceSelect">
<option value="ALL">All Interfaces</option>
</select>
  </div>
</div>
<canvas id="trafficChart" height="95"></canvas>
</div>

<div class="panel">
<div class="panel-head">
  <div>
    <h2>Interface Traffic Table</h2>
    <p style="color:#94a3b8;margin:6px 0 0">Monitor running interfaces, RX download and TX upload in Mbps.</p>
  </div>
  <a class="btn btn-blue" href="queue_control_pro.php">⚡ Queue Control</a>
</div>
<table>
<thead>
<tr><th>Interface</th><th>Type</th><th>Status</th><th>RX Download</th><th>TX Upload</th></tr>
</thead>
<tbody id="ifaceRows"></tbody>
</table>
</div>
</div>

<script>
const ctx=document.getElementById("trafficChart");
const labels=[];
const rxData=[];
const txData=[];
let selected="ALL";

const chart=new Chart(ctx,{
  type:"line",
  data:{
    labels,
    datasets:[
      {label:"Download Mbps",data:rxData,borderColor:"#22c55e",backgroundColor:"rgba(34,197,94,.15)",tension:.35,fill:true},
      {label:"Upload Mbps",data:txData,borderColor:"#38bdf8",backgroundColor:"rgba(56,189,248,.12)",tension:.35,fill:true}
    ]
  },
  options:{
    responsive:true,
    animation:false,
    scales:{
      y:{beginAtZero:true,ticks:{color:"#94a3b8"},grid:{color:"rgba(148,163,184,.15)"}},
      x:{ticks:{color:"#94a3b8"},grid:{color:"rgba(148,163,184,.08)"}}
    },
    plugins:{legend:{labels:{color:"#e5e7eb"}}}
  }
});

document.getElementById("interfaceSelect").addEventListener("change",e=>{
  selected=e.target.value;
  labels.length=0;rxData.length=0;txData.length=0;
  chart.update();
});

async function syncBandwidth(){
  try{
    const r=await fetch("mikrotik_bandwidth_api.php?_="+Date.now(),{cache:"no-store"});
    const d=await r.json();

    document.getElementById("statusBadge").textContent=d.success?"● MikroTik Connected":"● Offline";
    document.getElementById("lastUpdate").textContent=d.time || "-";

    if(!d.success) return;

    const select=document.getElementById("interfaceSelect");
    const existing=[...select.options].map(o=>o.value);

    d.interfaces.forEach(i=>{
      if(!existing.includes(i.name)){
        const op=document.createElement("option");
        op.value=i.name;
        op.textContent=i.name;
        select.appendChild(op);
      }
    });

    let rx=0,tx=0,active=0;

    d.interfaces.forEach(i=>{
      if(selected==="ALL" || selected===i.name){
        rx+=Number(i.rx_mbps||0);
        tx+=Number(i.tx_mbps||0);
      }
      if(i.running==="true") active++;
    });

    document.getElementById("rxTotal").textContent=rx.toFixed(2)+" Mbps";
    document.getElementById("txTotal").textContent=tx.toFixed(2)+" Mbps";
    document.getElementById("activeIfs").textContent=active;

    labels.push(d.time);
    rxData.push(rx.toFixed(2));
    txData.push(tx.toFixed(2));

    if(labels.length>20){labels.shift();rxData.shift();txData.shift();}
    chart.update();

    document.getElementById("ifaceRows").innerHTML=d.interfaces.map(i=>`
      <tr>
        <td><b style="color:#5eead4">${i.name}</b></td>
        <td>${i.type}</td>
        <td><span class="pill ${i.running==="true"?"on":"off"}">${i.running==="true"?"RUNNING":"DOWN"}</span></td>
        <td>${Number(i.rx_mbps||0).toFixed(2)} Mbps</td>
        <td>${Number(i.tx_mbps||0).toFixed(2)} Mbps</td>
      </tr>
    `).join("");

  }catch(e){}
}

syncBandwidth();
setInterval(syncBandwidth,3000);
</script>

</body>
</html>
