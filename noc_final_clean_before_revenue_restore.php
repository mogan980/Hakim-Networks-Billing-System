<!DOCTYPE html>
<html>
<head>
<title>Hakim Networks NOC</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js">
async function loadRevenueSummary(){
    try{
        const r = await fetch("revenue_summary_api.php?_=" + Date.now());
        const x = await r.json();
        revToday.textContent = "Ksh " + Number(x.today).toLocaleString();
        revWeek.textContent = "Ksh " + Number(x.week).toLocaleString();
        revMonth.textContent = "Ksh " + Number(x.month).toLocaleString();
        bizRevenue.textContent = "Ksh " + Number(x.total).toLocaleString();
    }catch(e){ console.log(e); }
}
loadRevenueSummary();
setInterval(loadRevenueSummary, 10000);


async function loadRevenueChartPro(){
    try{
        const r = await fetch("revenue_summary_api.php?_=" + Date.now());
        const x = await r.json();

        revChartToday.textContent = "Ksh " + Number(x.today).toLocaleString();
        revChartMonth.textContent = "Ksh " + Number(x.month).toLocaleString();
        revChartTotal.textContent = "Ksh " + Number(x.total).toLocaleString();

        revenueChart.data.datasets[0].data = [x.today, x.week, x.month, x.total];
        revenueChart.data.datasets[1].data = [x.today, x.week, x.month, x.total];
        revenueChart.update();
    }catch(e){ console.log(e); }
}
loadRevenueChartPro();
setInterval(loadRevenueChartPro, 10000);

function filterHotspotTable(q){
    q = String(q || "").toLowerCase();
    document.querySelectorAll("#hotspotTable tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}
function filterPppoeTable(q){
    q = String(q || "").toLowerCase();
    document.querySelectorAll("#pppoeTable tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}
function filterQueueTable(q){
    q = String(q || "").toLowerCase();
    document.querySelectorAll("#queueTable tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}
function filterDhcpTable(q){
    q = String(q || "").toLowerCase();

    document.querySelectorAll("#leaseTable tbody tr").forEach(row=>{
        row.style.display =
            row.innerText.toLowerCase().includes(q)
            ? ""
            : "none";
    });
}
function filterIfaceTable(q){
    q = String(q || "").toLowerCase();

    document.querySelectorAll("#ifaceTable tbody tr").forEach(row=>{
        row.style.display =
            row.innerText.toLowerCase().includes(q)
            ? ""
            : "none";
    });
}

async function controlClient(action,user,ip){
    if(!confirm("Confirm action: " + action + " for " + user + "?")) return;

    const form = new FormData();
    form.append("action", action);
    form.append("user", user);
    form.append("ip", ip);

    const r = await fetch("client_control_api.php", {
        method:"POST",
        body:form
    });

    const d = await r.json();
    alert(d.message);
    live();
}


async function loadBandwidthLeaderboard(){
    try{
        const r = await fetch("bandwidth_api.php?_=" + Date.now());
        const d = await r.json();

        bwUsers.textContent = d.total.users || 0;
        bwRx.textContent = bytes(d.total.total_rx || 0);
        bwTx.textContent = bytes(d.total.total_tx || 0);
        bwTop.textContent = d.rows.length ? d.rows[0].username : "-";

        document.querySelector("#bandwidthTable tbody").innerHTML = d.rows.length
            ? d.rows.map(x => `
                <tr>
                    <td><span class="user-pill">${esc(x.username)}</span></td>
                    <td>${esc(x.ip_address)}</td>
                    <td>${esc(x.rx_rate)}</td>
                    <td>${esc(x.tx_rate)}</td>
                    <td>${esc(x.updated_at)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="5">No bandwidth records yet</td></tr>`;
    }catch(e){
        console.log(e);
    }
}
loadBandwidthLeaderboard();
setInterval(loadBandwidthLeaderboard, 5000);


async function loadBypassUsers(){
    try{
        const r = await fetch("bypass_users_api.php?_=" + Date.now());
        const d = await r.json();

        if(document.getElementById("bypassCount")){
            bypassCount.textContent = d.rows.length;
        }

        const body = document.querySelector("#bypassTable tbody");
        if(!body) return;

        body.innerHTML = d.rows.length
            ? d.rows.map(x => `
                <tr>
                    <td><span class="ip-pill">${esc(x.ip)}</span></td>
                    <td>${esc(x.mac)}</td>
                    <td>${esc(x.comment)}</td>
                    <td><span class="bypass-status">${esc(x.status)}</span></td>
                </tr>
            `).join("")
            : `<tr><td colspan="4">No bypassed users online</td></tr>`;
    }catch(e){console.log(e);}
}
loadBypassUsers();
setInterval(loadBypassUsers,5000);

</script>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#064e3b;color:#0f172a;overflow-x:hidden}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:230px;background:#020617;color:white;padding:18px;overflow-y:auto}
.logo{font-size:25px;font-weight:900}.sub{font-size:12px;color:#94a3b8;margin-bottom:22px}
.nav a{display:block;color:white;text-decoration:none;background:#111827;margin:8px 0;padding:12px;border-radius:12px;font-weight:700}
.nav a.active,.nav a:hover{background:#16a34a}
.main{margin-left:230px;width:calc(100vw - 230px);padding:22px}
.header{display:flex;justify-content:space-between;align-items:center;color:white;margin-bottom:18px}
.header h1{margin:0}.header p{margin:5px 0;color:#d1fae5}
.badge{padding:7px 12px;border-radius:999px;font-size:12px;font-weight:900}
.online{background:#dcfce7;color:#047857}.online{background:#fee2e2;color:#b91c1c}.warn{background:#fef3c7;color:#92400e}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}
.card,.section{background:white;border-radius:18px;padding:18px;box-shadow:0 12px 30px rgba(0,0,0,.12)}
.card h4{margin:0;color:#64748b;font-size:13px}.card h2{margin:8px 0 0;font-size:25px}
.grid{display:grid;grid-template-columns:1.3fr 1fr;gap:16px;margin-bottom:16px}
.grid3{display:grid;grid-template-columns:1.2fr .9fr .9fr;gap:16px;margin-bottom:16px}
.section{margin-bottom:16px;overflow:hidden}
.tablebox{width:100%;overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:760px}
th{background:#020617;color:white;text-align:left;padding:10px;font-size:13px}
td{border-bottom:1px solid #e5e7eb;padding:9px;font-size:13px}
.search{width:100%;padding:11px;border:1px solid #cbd5e1;border-radius:12px;margin-bottom:10px}
.quick{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.quick a,.footer a{color:white;text-decoration:none;background:#111827;padding:12px;border-radius:12px;font-weight:800;font-size:13px}
.quick a{color:#0f172a;background:#f8fafc;border:1px solid #dbeafe}
.quick a:hover,.footer a:hover{background:#16a34a;color:white}
.footer{background:#020617;color:white;border-radius:18px;padding:22px;margin-top:16px}
.footergrid{display:grid;grid-template-columns:1fr 2fr 1fr;gap:18px}
.footerlinks{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
canvas{max-height:250px}
@media(max-width:1000px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0;width:100%;padding:14px}.cards{grid-template-columns:repeat(2,1fr)}.grid,.grid3,.footergrid{grid-template-columns:1fr}.footerlinks{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.cards,.footerlinks{grid-template-columns:1fr}}
/* ===== MIKROTIK HEALTH PRO CARD ===== */
.health-pro{
background:linear-gradient(145deg,#ffffff,#f1f5f9)!important;
border:1px solid rgba(255,255,255,.7)!important;
border-radius:24px!important;
padding:24px!important;
position:relative!important;
overflow:hidden!important;
box-shadow:0 20px 45px rgba(15,23,42,.12)!important;
backdrop-filter:blur(10px)!important;
}
.health-pro::before{
content:"";
position:absolute;
top:-60px;
right:-60px;
width:180px;
height:180px;
background:radial-gradient(circle,#22c55e33,transparent 70%);
border-radius:50%;
}
.health-pro h2{
margin:0 0 18px!important;
font-size:28px!important;
font-weight:900!important;
color:#0f172a!important;
letter-spacing:-0.5px!important;
}
.health-item{
display:flex!important;
justify-content:space-between!important;
align-items:center!important;
padding:14px 0!important;
border-bottom:1px solid rgba(148,163,184,.18)!important;
font-size:15px!important;
}
.health-item:last-child{border-bottom:none!important;}
.health-label{
color:#64748b!important;
font-weight:700!important;
}
.health-value{
font-weight:900!important;
color:#0f172a!important;
}
.health-online{
background:linear-gradient(135deg,#dcfce7,#bbf7d0)!important;
color:#047857!important;
padding:8px 16px!important;
border-radius:999px!important;
font-size:12px!important;
font-weight:900!important;
box-shadow:0 6px 18px rgba(34,197,94,.2)!important;
}


/* PRO REVENUE CARD */
.revenue-pro{background:linear-gradient(145deg,#ffffff,#f8fafc)!important}
.rev-sub{color:#64748b;margin-top:-8px;margin-bottom:18px}
.revenue-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.rev-card{
    padding:18px;
    border-radius:18px;
    color:#0f172a;
    box-shadow:0 12px 28px rgba(15,23,42,.08);
    position:relative;
    overflow:hidden;
    min-height:125px;
}
.rev-card:after{
    content:"";
    position:absolute;
    right:-35px;
    top:-35px;
    width:110px;
    height:110px;
    border-radius:50%;
    background:rgba(255,255,255,.35);
}
.rev-card span{font-weight:900;color:#475569;font-size:13px}
.rev-card h2{font-size:26px;margin:12px 0 6px}
.rev-card small{color:#64748b;font-weight:700}
.rev-card.today{background:linear-gradient(135deg,#dcfce7,#bbf7d0)}
.rev-card.week{background:linear-gradient(135deg,#dbeafe,#bfdbfe)}
.rev-card.month{background:linear-gradient(135deg,#fef3c7,#fde68a)}
.rev-card.total{background:linear-gradient(135deg,#ede9fe,#ddd6fe)}
.revenue-mini{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:14px}
.revenue-mini div{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:13px;display:flex;justify-content:space-between}
.revenue-mini span{font-weight:900;color:#16a34a}
@media(max-width:900px){.revenue-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.revenue-grid,.revenue-mini{grid-template-columns:1fr}}


/* PRO REVENUE ANALYTICS */
.revenue-chart-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.revenue-chart-pro:before{
    content:"";
    position:absolute;
    right:-80px;
    top:-80px;
    width:220px;
    height:220px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(59,130,246,.18),transparent 70%);
}
.chart-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:14px;
    position:relative;
    z-index:2;
}
.chart-head h2{
    margin:0;
    font-size:25px;
}
.chart-head p{
    margin:6px 0 0;
    color:#64748b;
}
.chart-pill{
    background:#dcfce7;
    color:#047857;
    padding:8px 14px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.chart-summary{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
    margin-bottom:16px;
    position:relative;
    z-index:2;
}
.chart-summary div{
    background:white;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:14px;
    box-shadow:0 10px 24px rgba(15,23,42,.06);
}
.chart-summary b{
    display:block;
    font-size:20px;
    color:#0f172a;
}
.chart-summary span{
    color:#64748b;
    font-size:12px;
    font-weight:800;
}
.revenue-chart-pro canvas{
    position:relative;
    z-index:2;
    max-height:300px!important;
}
@media(max-width:700px){
    .chart-summary{grid-template-columns:1fr}
}


/* PRO VOUCHER STATUS */
.voucher-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.voucher-pro:before{
    content:"";
    position:absolute;
    right:-80px;
    top:-80px;
    width:220px;
    height:220px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(236,72,153,.18),transparent 70%);
}
.voucher-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:15px;
    position:relative;
    z-index:2;
}
.voucher-head h2{
    margin:0;
    font-size:25px;
}
.voucher-head p{
    margin:6px 0 0;
    color:#64748b;
}
.voucher-live{
    background:#dcfce7;
    color:#047857;
    padding:8px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:900;
}
.voucher-layout{
    display:grid;
    grid-template-columns:1fr .9fr;
    gap:18px;
    align-items:center;
    position:relative;
    z-index:2;
}
.voucher-chart{
    min-height:260px;
    display:flex;
    align-items:center;
    justify-content:center;
}
.voucher-chart canvas{
    max-height:260px!important;
}
.voucher-stats{
    display:grid;
    gap:12px;
}
.voucher-stat{
    padding:16px;
    border-radius:18px;
    box-shadow:0 10px 24px rgba(15,23,42,.06);
}
.voucher-stat span{
    display:block;
    font-size:13px;
    font-weight:900;
    color:#475569;
}
.voucher-stat b{
    display:block;
    font-size:28px;
    margin-top:8px;
}
.voucher-stat.blue{background:linear-gradient(135deg,#dbeafe,#bfdbfe)}
.voucher-stat.pink{background:linear-gradient(135deg,#fce7f3,#f9a8d4)}
.voucher-stat.dark{background:linear-gradient(135deg,#e2e8f0,#cbd5e1)}
@media(max-width:800px){
    .voucher-layout{grid-template-columns:1fr}
}


/* PRO HOTSPOT USERS */
.hotspot-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.hotspot-pro:before{
    content:"";
    position:absolute;
    right:-80px;
    top:-80px;
    width:220px;
    height:220px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(34,197,94,.18),transparent 70%);
}
.hotspot-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:16px;
    position:relative;
    z-index:2;
}
.hotspot-head h2{
    margin:0;
    font-size:25px;
}
.hotspot-head p{
    margin:6px 0 0;
    color:#64748b;
}
.hotspot-count{
    background:#dcfce7;
    color:#047857;
    padding:9px 14px;
    border-radius:999px;
    font-weight:900;
    font-size:13px;
    white-space:nowrap;
}
.hotspot-tools{
    display:flex;
    gap:10px;
    margin-bottom:14px;
    position:relative;
    z-index:2;
}
.hotspot-search{
    margin-bottom:0!important;
}
.refresh-btn{
    border:none;
    background:#16a34a;
    color:white;
    padding:0 18px;
    border-radius:12px;
    font-weight:900;
    cursor:pointer;
}
.refresh-btn:hover{
    background:#15803d;
}
.pro-table th{
    background:#020617!important;
    color:white!important;
}
.pro-table td{
    vertical-align:middle;
}
.user-pill{
    display:inline-block;
    background:#e0f2fe;
    color:#0369a1;
    font-weight:900;
    padding:6px 11px;
    border-radius:999px;
}
.online-pill{
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.empty-row{
    text-align:center;
    color:#64748b;
    padding:22px!important;
    font-weight:700;
}
@media(max-width:700px){
    .hotspot-head,.hotspot-tools{
        flex-direction:column;
    }
    .refresh-btn{
        height:42px;
    }
}


/* PRO PPPOE USERS */
.pppoe-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.pppoe-pro:before{
    content:"";
    position:absolute;
    right:-80px;
    top:-80px;
    width:220px;
    height:220px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(59,130,246,.18),transparent 70%);
}
.pppoe-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:16px;
    position:relative;
    z-index:2;
}
.pppoe-head h2{
    margin:0;
    font-size:25px;
}
.pppoe-head p{
    margin:6px 0 0;
    color:#64748b;
}
.pppoe-count{
    background:#dbeafe;
    color:#1d4ed8;
    padding:9px 14px;
    border-radius:999px;
    font-weight:900;
    font-size:13px;
    white-space:nowrap;
}
.pppoe-tools{
    display:flex;
    gap:10px;
    margin-bottom:14px;
    position:relative;
    z-index:2;
}
.pppoe-search{
    margin-bottom:0!important;
}
.pppoe-refresh{
    border:none;
    background:#2563eb;
    color:white;
    padding:0 18px;
    border-radius:12px;
    font-weight:900;
    cursor:pointer;
}
.pppoe-refresh:hover{
    background:#1d4ed8;
}
.pppoe-table th{
    background:#020617!important;
    color:white!important;
}
.pppoe-user-pill{
    display:inline-block;
    background:#dbeafe;
    color:#1d4ed8;
    font-weight:900;
    padding:6px 11px;
    border-radius:999px;
}
.pppoe-online-pill{
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.pppoe-empty{
    text-align:center;
    color:#64748b;
    padding:22px!important;
    font-weight:700;
}
@media(max-width:700px){
    .pppoe-head,.pppoe-tools{
        flex-direction:column;
    }
    .pppoe-refresh{
        height:42px;
    }
}


/* PRO SIMPLE QUEUES */
.queues-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.queues-pro:before{
    content:"";
    position:absolute;
    right:-80px;
    top:-80px;
    width:220px;
    height:220px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(245,158,11,.20),transparent 70%);
}
.queues-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:16px;
    position:relative;
    z-index:2;
}
.queues-head h2{
    margin:0;
    font-size:25px;
}
.queues-head p{
    margin:6px 0 0;
    color:#64748b;
}
.queues-count{
    background:#fef3c7;
    color:#92400e;
    padding:9px 14px;
    border-radius:999px;
    font-weight:900;
    font-size:13px;
    white-space:nowrap;
}
.queues-tools{
    display:flex;
    gap:10px;
    margin-bottom:14px;
    position:relative;
    z-index:2;
}
.queues-search{margin-bottom:0!important}
.queues-refresh{
    border:none;
    background:#f59e0b;
    color:white;
    padding:0 18px;
    border-radius:12px;
    font-weight:900;
    cursor:pointer;
}
.queues-refresh:hover{background:#d97706}
.queue-name-pill{
    display:inline-block;
    background:#fef3c7;
    color:#92400e;
    font-weight:900;
    padding:6px 11px;
    border-radius:999px;
}
.speed-pill{
    display:inline-block;
    background:#dbeafe;
    color:#1d4ed8;
    font-weight:900;
    padding:6px 11px;
    border-radius:999px;
}
.queue-active-pill{
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.queue-disabled-pill{
    background:#fee2e2;
    color:#991b1b;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
@media(max-width:700px){
    .queues-head,.queues-tools{flex-direction:column}
    .queues-refresh{height:42px}
}


/* PRO DHCP CLIENTS */
.dhcp-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.dhcp-pro:before{
    content:"";
    position:absolute;
    right:-80px;
    top:-80px;
    width:220px;
    height:220px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(14,165,233,.18),transparent 70%);
}
.dhcp-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:16px;
    position:relative;
    z-index:2;
}
.dhcp-head h2{
    margin:0;
    font-size:25px;
}
.dhcp-head p{
    margin:6px 0 0;
    color:#64748b;
}
.dhcp-count{
    background:#dbeafe;
    color:#1d4ed8;
    padding:10px 15px;
    border-radius:999px;
    font-size:13px;
    font-weight:900;
}
.dhcp-tools{
    display:flex;
    gap:10px;
    margin-bottom:14px;
}
.dhcp-search{
    margin-bottom:0!important;
}
.dhcp-refresh{
    border:none;
    background:#0ea5e9;
    color:white;
    padding:0 18px;
    border-radius:12px;
    font-weight:900;
    cursor:pointer;
}
.dhcp-refresh:hover{
    background:#0284c7;
}
.dhcp-table th{
    background:#020617!important;
    color:white!important;
}
.ip-pill{
    display:inline-block;
    background:#dbeafe;
    color:#1d4ed8;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
}
.mac-pill{
    display:inline-block;
    background:#f1f5f9;
    color:#334155;
    padding:6px 11px;
    border-radius:999px;
    font-weight:800;
}
.host-pill{
    display:inline-block;
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:800;
}
.bound-pill{
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.network-pill{
    background:#fef3c7;
    color:#92400e;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
@media(max-width:700px){
    .dhcp-head,.dhcp-tools{
        flex-direction:column;
    }
    .dhcp-refresh{
        height:42px;
    }
}


/* PRO ROUTER INTERFACES */
.interfaces-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.interfaces-pro:before{
    content:"";
    position:absolute;
    right:-80px;
    top:-80px;
    width:220px;
    height:220px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(20,184,166,.18),transparent 70%);
}
.interfaces-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:16px;
    position:relative;
    z-index:2;
}
.interfaces-head h2{
    margin:0;
    font-size:25px;
}
.interfaces-head p{
    margin:6px 0 0;
    color:#64748b;
}
.interfaces-count{
    background:#ccfbf1;
    color:#0f766e;
    padding:10px 15px;
    border-radius:999px;
    font-size:13px;
    font-weight:900;
    white-space:nowrap;
}
.interfaces-tools{
    display:flex;
    gap:10px;
    margin-bottom:14px;
}
.iface-search{
    margin-bottom:0!important;
}
.iface-refresh{
    border:none;
    background:#0f766e;
    color:white;
    padding:0 18px;
    border-radius:12px;
    font-weight:900;
    cursor:pointer;
}
.iface-refresh:hover{
    background:#115e59;
}
.iface-table th{
    background:#020617!important;
    color:white!important;
}
.iface-name-pill{
    display:inline-block;
    background:#ccfbf1;
    color:#0f766e;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
}
.iface-type-pill{
    display:inline-block;
    background:#f1f5f9;
    color:#334155;
    padding:6px 11px;
    border-radius:999px;
    font-weight:800;
}
.iface-running-pill{
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.iface-down-pill{
    background:#fee2e2;
    color:#991b1b;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.iface-enabled-pill{
    background:#dbeafe;
    color:#1d4ed8;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.iface-disabled-pill{
    background:#fef3c7;
    color:#92400e;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.iface-health-good{
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.iface-health-warn{
    background:#fff7ed;
    color:#c2410c;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
@media(max-width:700px){
    .interfaces-head,.interfaces-tools{
        flex-direction:column;
    }
    .iface-refresh{
        height:42px;
    }
}


/* INTERFACE ACTION BUTTONS */
.port-btn{
    display:inline-block;
    text-decoration:none;
    padding:7px 10px;
    border-radius:10px;
    font-size:11px;
    font-weight:900;
    margin:2px;
    white-space:nowrap;
}
.port-btn.view{
    background:#dbeafe;
    color:#1d4ed8;
}
.port-btn.config{
    background:#fef3c7;
    color:#92400e;
}
.port-btn.health{
    background:#dcfce7;
    color:#166534;
}
.port-btn:hover{
    transform:translateY(-1px);
    filter:brightness(.97);
}


/* PROFESSIONAL FOOTER FINAL */
.pro-footer-final{
    background:linear-gradient(135deg,#020617,#052e2b,#064e3b)!important;
    border:1px solid rgba(255,255,255,.08);
    border-radius:24px!important;
    padding:26px!important;
    color:white!important;
    box-shadow:0 22px 55px rgba(0,0,0,.28);
    position:relative;
    overflow:hidden;
}
.pro-footer-final:before{
    content:"";
    position:absolute;
    right:-120px;
    top:-120px;
    width:280px;
    height:280px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(34,197,94,.28),transparent 70%);
}
.footer-main{
    display:grid;
    grid-template-columns:1.1fr 2.1fr 1fr;
    gap:22px;
    position:relative;
    z-index:2;
}
.footer-brand h2{
    margin:0 0 10px;
    font-size:26px;
}
.footer-brand p,.footer-support p{
    color:#cbd5e1;
    line-height:1.4;
}
.footer-status{
    display:inline-block;
    margin-top:10px;
    background:#dcfce7;
    color:#047857;
    padding:8px 13px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}
.footer-actions{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
}
.footer-actions a{
    text-decoration:none;
    color:white;
    background:rgba(255,255,255,.08);
    padding:13px;
    border-radius:14px;
    font-weight:900;
    font-size:13px;
    transition:.25s;
}
.footer-actions a:hover{
    background:#16a34a;
    transform:translateY(-2px);
}
.footer-actions a.logout:hover{
    background:#dc2626;
}
.footer-support{
    background:rgba(255,255,255,.08);
    padding:18px;
    border-radius:18px;
}
.footer-support h3{
    margin:0 0 10px;
}
.support-btn{
    display:inline-block;
    margin-top:10px;
    color:white;
    text-decoration:none;
    background:#16a34a;
    padding:11px 14px;
    border-radius:12px;
    font-weight:900;
}
.footer-bottom-pro{
    position:relative;
    z-index:2;
    margin-top:22px;
    padding-top:16px;
    border-top:1px solid rgba(255,255,255,.12);
    display:flex;
    justify-content:space-between;
    color:#cbd5e1;
    font-size:13px;
}
@media(max-width:1000px){
    .footer-main{grid-template-columns:1fr}
    .footer-actions{grid-template-columns:repeat(2,1fr)}
    .footer-bottom-pro{display:block}
}
@media(max-width:600px){
    .footer-actions{grid-template-columns:1fr}
}


.top-actions{
    display:flex;
    align-items:center;
    gap:12px;
}
.profile-chip{
    display:flex;
    align-items:center;
    gap:10px;
    background:rgba(255,255,255,.12);
    color:white;
    text-decoration:none;
    padding:8px 12px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.18);
}
.profile-chip:hover{
    background:#16a34a;
}
.profile-avatar{overflow:hidden;overflow:hidden;overflow:hidden;
    width:38px;
    height:38px;
    border-radius:50%;
    background:white;
    color:#064e3b;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}
.profile-chip b{
    display:block;
    font-size:13px;
}
.profile-chip small{
    display:block;
    color:#d1fae5;
    font-size:11px;
}


.profile-avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.profile-avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.profile-avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.profile-avatar{
    width:48px;
    height:48px;
    border-radius:50%;
    overflow:hidden;
    background:white;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 4px 15px rgba(0,0,0,.15);
}
.profile-avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}
.profile-avatar span{
    font-size:22px;
}


.action-btn{
    border:none;
    padding:7px 11px;
    border-radius:10px;
    font-weight:900;
    font-size:12px;
    cursor:pointer;
    margin:2px;
}
.action-btn.danger{background:#fee2e2;color:#991b1b}
.action-btn.warning{background:#fef3c7;color:#92400e}
.action-btn.success{background:#dcfce7;color:#166534}
.action-btn:hover{filter:brightness(.95)}


/* PRO BANDWIDTH LEADERBOARD */
.bandwidth-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
    position:relative;
    overflow:hidden;
}
.bandwidth-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:16px;
}
.bandwidth-head h2{margin:0;font-size:25px}
.bandwidth-head p{margin:6px 0 0;color:#64748b}
.bandwidth-pill{
    background:#dcfce7;
    color:#047857;
    padding:10px 15px;
    border-radius:999px;
    font-size:13px;
    font-weight:900;
}
.bandwidth-cards{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-bottom:16px;
}
.bw-card{
    background:linear-gradient(135deg,#ecfdf5,#f8fafc);
    border:1px solid #d1fae5;
    border-radius:16px;
    padding:16px;
}
.bw-card span{
    color:#64748b;
    font-weight:900;
    font-size:13px;
}
.bw-card b{
    display:block;
    margin-top:8px;
    font-size:24px;
    color:#064e3b;
}
@media(max-width:800px){
    .bandwidth-head{flex-direction:column}
    .bandwidth-cards{grid-template-columns:1fr}
}


.bypass-pro{
    background:linear-gradient(145deg,#ffffff,#f8fafc)!important;
    border:1px solid rgba(226,232,240,.9);
}
.bypass-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:16px;
}
.bypass-head h2{margin:0;font-size:25px}
.bypass-head p{margin:6px 0 0;color:#64748b}
.bypass-pill{
    background:#dcfce7;
    color:#047857;
    padding:10px 15px;
    border-radius:999px;
    font-size:13px;
    font-weight:900;
}
.bypass-status{
    background:#dcfce7;
    color:#166534;
    padding:6px 11px;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
}

</style>
</head>
<body>

<aside class="sidebar">
<div class="logo">📊 M.Hakim</div>
<div class="sub">Hakim Networks Pro NOC</div>
<div class="nav">
<a class="active" href="noc_final_clean.php">📡 Live NOC</a>
<a href="noc_final_clean.php">📊 Dashboard</a>
<a href="noc.php">🖥 NOC Center</a>
<a href="routers.php">🛰 Routers</a>
<a href="clients.php">👥 Clients</a>
<a href="packages.php">📦 Packages</a>
<a href="vouchers.php">🎫 Vouchers</a>
<a href="payments.php">💳 Payments</a>
<a href="pppoe.php">🌐 PPPoE</a>
<a href="router_wizard.php">🧙 Router Wizard</a>
<a href="router_health.php">🩺 Health Check</a>
<a href="reports.php">📈 Reports</a>
<a href="analytics.php">📊 Analytics</a>
<a href="users.php">👤 Users</a>
<a href="settings.php">⚙️ Settings</a>
<a href="logout.php">🚪 Logout</a>
</div>
</aside>

<main class="main">
<div class="header">
<div><h1>Live NOC Dashboard</h1><p>Clean real-time MikroTik, clients, queues, revenue and health monitoring.</p></div>
<div class="top-actions">
<a href="settings.php" class="profile-chip" title="Open Admin Settings">
    <div class="profile-avatar">
<?php if(!empty($adminSettings["logo_path"])): ?>
<img src="<?php echo htmlspecialchars($adminSettings["logo_path"]); ?>?v=<?php echo time(); ?>" alt="Logo">
<?php else: ?>
<span>👤</span>
<?php endif; ?>
</div>
    <div>
        <b>Admin Profile</b>
        <small>Settings</small>
    </div>
</a>
<span id="status" class="badge online">ONLINE</span> <b id="clock"></b></div>
</div>

<div class="cards">
<div class="card"><h4>Router Identity</h4><h2 id="identity">-</h2><small id="board">-</small></div>
<div class="card"><h4>CPU Load</h4><h2 id="cpu">0%</h2></div>
<div class="card"><h4>Download RX</h4><h2 id="rx">0 Mbps</h2></div>
<div class="card"><h4>Upload TX</h4><h2 id="tx">0 Mbps</h2></div>
<div class="card"><h4>Hotspot Online</h4><h2 id="hotspotCount">0</h2></div>
<div class="card"><h4>PPPoE Online</h4><h2 id="pppoeCount">0</h2></div>
<div class="card"><h4>Simple Queues</h4><h2 id="queueCount">0</h2></div>
<div class="card"><h4>Router Uptime</h4><h2 id="uptime">-</h2></div>
</div>

<div class="grid3">
<div class="section"><h2>Bandwidth Monitor</h2><canvas id="trafficChart"></canvas></div>
<div class="section"><h2>Client Distribution</h2><canvas id="clientPie"></canvas></div>
<div class="section health-pro">
    <h2>⚡ MikroTik Health</h2>

    <div class="health-item">
        <span class="health-label">CPU Load</span>
        <span class="health-value" id="healthCpu">0%</span>
    </div>

    <div class="health-item">
        <span class="health-label">Memory Free</span>
        <span class="health-value" id="memory">-</span>
    </div>

    <div class="health-item">
        <span class="health-label">RouterOS Version</span>
        <span class="health-value" id="version">-</span>
    </div>

    <div class="health-item">
        <span class="health-label">System Status</span>
        <span id="healthStatus" class="health-online">ONLINE</span>
    </div>
</div>
</div>

<div class="grid">

<div class="section revenue-pro">
<h2>💰 Revenue Performance</h2>
<p class="rev-sub">Live payment summary from your billing system.</p>

<div class="revenue-grid">
    <div class="rev-card today">
        <span>Today Revenue</span>
        <h2 id="revToday">Ksh 0</h2>
        <small>Collected today</small>
    </div>

    <div class="rev-card week">
        <span>This Week</span>
        <h2 id="revWeek">Ksh 0</h2>
        <small>Current week total</small>
    </div>

    <div class="rev-card month">
        <span>This Month</span>
        <h2 id="revMonth">Ksh 0</h2>
        <small>Monthly revenue</small>
    </div>

    <div class="rev-card total">
        <span>Total Revenue</span>
        <h2 id="bizRevenue">Ksh 0</h2>
        <small>All-time collections</small>
    </div>
</div>

<div class="revenue-mini">
    <div><b>Total Clients</b><span id="bizClients">0</span></div>
    <div><b>Packages</b><span id="bizPackages">0</span></div>
    <div><b>Vouchers</b><span id="bizVouchers">0</span></div>
</div>
</div>

<div class="section"><h2>Quick Actions</h2>
<div class="quick">
<a href="clients.php">👥 Clients</a><a href="packages.php">📦 Packages</a>
<a href="vouchers.php">🎫 Vouchers</a><a href="payments.php">💳 Payments</a>
<a href="routers.php">🛰 Routers</a><a href="reports.php">📈 Reports</a>
</div>
</div>
</div>

<div class="grid">
<div class="section revenue-chart-pro">
    <div class="chart-head">
        <div>
            <h2>📈 Revenue Analytics</h2>
            <p>Professional revenue trend and collection overview.</p>
        </div>
        <span class="chart-pill">Live</span>
    </div>
    <div class="chart-summary">
        <div><b id="revChartTotal">Ksh 0</b><span>Total Revenue</span></div>
        <div><b id="revChartMonth">Ksh 0</b><span>This Month</span></div>
        <div><b id="revChartToday">Ksh 0</b><span>Today</span></div>
    </div>
    <canvas id="revenueChart"></canvas>
</div>
<div class="section voucher-pro">
    <div class="voucher-head">
        <div>
            <h2>🎫 Voucher Status</h2>
            <p>Track used and unused vouchers in real time.</p>
        </div>
        <span class="voucher-live">Live</span>
    </div>

    <div class="voucher-layout">
        <div class="voucher-chart">
            <canvas id="voucherPie"></canvas>
        </div>

        <div class="voucher-stats">
            <div class="voucher-stat blue">
                <span>Unused Vouchers</span>
                <b id="voucherUnused">0</b>
            </div>
            <div class="voucher-stat pink">
                <span>Used Vouchers</span>
                <b id="voucherUsed">0</b>
            </div>
            <div class="voucher-stat dark">
                <span>Total Vouchers</span>
                <b id="voucherTotal">0</b>
            </div>
        </div>
    </div>
</div>
</div>


<div class="section bypass-pro">
    <div class="bypass-head">
        <div>
            <h2>✅ Online Bypassed Users</h2>
            <p>Clients activated through STK or voucher bypass.</p>
        </div>
        <span class="bypass-pill"><b id="bypassCount">0</b> Online</span>
    </div>

    <div class="tablebox">
        <table id="bypassTable">
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>MAC</th>
                    <th>Activation</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="section hotspot-pro">
    <div class="hotspot-head">
        <div>
            <h2>📶 Online Hotspot Users</h2>
            <p>Live hotspot sessions connected through MikroTik.</p>
        </div>
        <span class="hotspot-count"><b id="hotspotBadgeCount">0</b> Online</span>
    </div>

    <div class="hotspot-tools">
        <input class="search hotspot-search" placeholder="Search by user, IP, MAC or uptime..." onkeyup="filterHotspotTable(this.value)">
        <button onclick="live()" class="refresh-btn">↻ Refresh</button>
    </div>

    <div class="tablebox">
        <table id="hotspotTable" class="pro-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>IP Address</th>
                    <th>MAC Address</th>
                    <th>Uptime</th>
                    <th>Download</th>
                    <th>Upload</th>
                    <th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="section pppoe-pro">
    <div class="pppoe-head">
        <div>
            <h2>🌐 Online PPPoE Users</h2>
            <p>Live PPPoE sessions authenticated on MikroTik.</p>
        </div>
        <span class="pppoe-count"><b id="pppoeBadgeCount">0</b> Online</span>
    </div>

    <div class="pppoe-tools">
        <input class="search pppoe-search" placeholder="Search by user, IP, caller ID or service..." onkeyup="filterPppoeTable(this.value)">
        <button onclick="live()" class="pppoe-refresh">↻ Refresh</button>
    </div>

    <div class="tablebox">
        <table id="pppoeTable" class="pppoe-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>IP Address</th>
                    <th>Caller ID</th>
                    <th>Uptime</th>
                    <th>Service</th>
                    <th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="section queues-pro">
    <div class="queues-head">
        <div>
            <h2>⚡ Active Simple Queues</h2>
            <p>Live bandwidth control rules synced from MikroTik.</p>
        </div>
        <span class="queues-count"><b id="queueBadgeCount">0</b> Active</span>
    </div>

    <div class="queues-tools">
        <input class="search queues-search" placeholder="Search queue name, target IP or speed..." onkeyup="filterQueueTable(this.value)">
        <button onclick="live()" class="queues-refresh">↻ Refresh</button>
    </div>

    <div class="tablebox">
        <table id="queueTable" class="queues-table">
            <thead>
                <tr>
                    <th>Queue Name</th>
                    <th>Target IP</th>
                    <th>Speed Limit</th>
                    <th>Usage</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="section dhcp-pro">
    <div class="dhcp-head">
        <div>
            <h2>🖧 Known DHCP Clients</h2>
            <p>Devices currently discovered and managed by DHCP leases.</p>
        </div>

        <span class="dhcp-count">
            <b id="dhcpCount">0</b> Devices
        </span>
    </div>

    <div class="dhcp-tools">
        <input class="search dhcp-search"
               placeholder="Search IP, MAC, hostname or status..."
               onkeyup="filterDhcpTable(this.value)">

        <button onclick="live()" class="dhcp-refresh">
            ↻ Refresh
        </button>
    </div>

    <div class="tablebox">
        <table id="leaseTable" class="dhcp-table">
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>MAC Address</th>
                    <th>Host Device</th>
                    <th>Status</th>
                    <th>Network</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="section interfaces-pro">
    <div class="interfaces-head">
        <div>
            <h2>🛰 Router Interfaces</h2>
            <p>Live interface state from MikroTik ports, bridge, loopback and wireless.</p>
        </div>
        <span class="interfaces-count"><b id="ifaceCount">0</b> Interfaces</span>
    </div>

    <div class="interfaces-tools">
        <input class="search iface-search"
               placeholder="Search interface name, type, running or disabled..."
               onkeyup="filterIfaceTable(this.value)">
        <button onclick="live()" class="iface-refresh">↻ Refresh</button>
    </div>

    <div class="tablebox">
        <table id="ifaceTable" class="iface-table">
            <thead>
                <tr>
                    <th>Interface</th>
                    <th>Type</th>
                    <th>Running Status</th>
                    <th>Admin State</th>
                    <th>Health</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


<div class="footer pro-footer-final">
    <div class="footer-main">
        <div class="footer-brand">
            <h2>📡 Hakim Networks</h2>
            <p>Professional ISP NOC, MikroTik monitoring, hotspot control and billing operations.</p>
            <span class="footer-status">● System Online</span>
        </div>

        <div class="footer-actions">
            <a href="users.php">👤 Profile</a>
<a href="settings.php">⚙️ Settings</a>
            <a href="settings.php">🔐 Password</a>
            <a href="settings.php">⚙️ Settings</a>
            <a href="settings.php">🏢 Company Logo</a>
            <a href="router_wizard.php">🧙 Router Wizard</a>
            <a href="router_health.php">🩺 Health Check</a>
            <a href="reports.php">📈 Reports</a>
            <a href="analytics.php">📊 Analytics</a>
            <a href="backups.php">🛡 Backups</a>
            <a href="payments.php">💳 Payments</a>
            <a href="clients.php">👥 Clients</a>
            <a href="logout.php" class="logout">🚪 Logout</a>
        </div>

        <div class="footer-support">
            <h3>Support Center</h3>
            <p>Use Router Wizard for setup, Health Check for diagnosis, Reports for tracking, and Backups for safety.</p>
            <a href="router_wizard.php" class="support-btn">Open Support Tools</a>
        </div>
    </div>

    <div class="footer-bottom-pro">
        <span>© 2026 Hakim Networks. All rights reserved.</span>
        <span>Live NOC • Secure Admin • MikroTik API Connected</span>
    </div>
</div>

</main>

<script>
let labels=[],rxData=[],txData=[];
const trafficChart=new Chart(trafficChartEl=document.getElementById("trafficChart"),{type:"line",data:{labels,datasets:[{label:"Download",data:rxData,borderWidth:3,tension:.4},{label:"Upload",data:txData,borderWidth:3,tension:.4}]}});
const clientPie=new Chart(document.getElementById("clientPie"),{type:"doughnut",data:{labels:["Hotspot","PPPoE","DHCP"],datasets:[{data:[0,0,0]}]}});
const revenueChart=new Chart(document.getElementById("revenueChart"),{
    type:"bar",
    data:{
        labels:["Today","Week","Month","Total"],
        datasets:[
            {type:"bar",label:"Revenue Ksh",data:[0,0,0,0],borderWidth:1},
            {type:"line",label:"Trend",data:[0,0,0,0],borderWidth:3,tension:.4}
        ]
    },
    options:{
        responsive:true,
        plugins:{legend:{position:"top"}},
        scales:{y:{beginAtZero:true}}
    }
});
const voucherPie=new Chart(document.getElementById("voucherPie"),{type:"doughnut",data:{labels:["Unused","Used"],datasets:[{data:[0,0]}]}});

function esc(v){return String(v??"-")}
function bytes(v){v=Number(v||0);if(v>=1073741824)return(v/1073741824).toFixed(2)+" GB";if(v>=1048576)return(v/1048576).toFixed(2)+" MB";if(v>=1024)return(v/1024).toFixed(2)+" KB";return v+" B"}
function rows(id,html){document.querySelector("#"+id+" tbody").innerHTML=html||'<tr><td colspan="10" class="empty-row">No live hotspot users connected right now</td></tr>'}
setInterval(()=>clock.textContent=new Date().toLocaleTimeString(),1000);

async function live(){
const r=await fetch("live_noc_pro.php?api=1&_="+Date.now());
const d=await r.json();

document.getElementById("status").textContent=d.status.toUpperCase(); document.getElementById("status").className="badge "+d.status;
document.getElementById("healthStatus").textContent=d.status.toUpperCase(); document.getElementById("healthStatus").className="badge "+d.status;

identity.textContent=d.identity; board.textContent=d.board; cpu.textContent=d.cpu+"%";
rx.textContent=d.rx+" Mbps"; tx.textContent=d.tx+" Mbps"; uptime.textContent=d.uptime;
hotspotCount.textContent=d.hotspot.length; pppoeCount.textContent=d.pppoe.length; queueCount.textContent=d.queues.length;
healthCpu.textContent=d.cpu+"%"; memory.textContent=bytes(d.free_memory); version.textContent=d.version;

bizRevenue.textContent="Ksh "+d.business.revenue;
bizClients.textContent=d.business.clients;
bizPackages.textContent=d.business.packages;
bizVouchers.textContent=d.business.vouchers;

labels.push(d.updated);rxData.push(d.rx);txData.push(d.tx);if(labels.length>12){labels.shift();rxData.shift();txData.shift()}trafficChart.update();
clientPie.data.datasets[0].data=[d.hotspot.length,d.pppoe.length,d.leases.length];clientPie.update();
revenueChart.data.datasets[0].data=[d.business.revenue];revenueChart.update();
voucherPie.data.datasets[0].data=[d.business.unused_vouchers,d.business.used_vouchers];
voucherPie.update();

if(document.getElementById("voucherUnused")){
    voucherUnused.textContent = d.business.unused_vouchers;
    voucherUsed.textContent = d.business.used_vouchers;
    voucherTotal.textContent = d.business.vouchers;
}

if(document.getElementById("hotspotBadgeCount")){
    hotspotBadgeCount.textContent = d.hotspot.length;
}

rows("hotspotTable",d.hotspot.map(u=>`
<tr>
    <td><span class="user-pill">${esc(u.user)}</span></td>
    <td>${esc(u.address)}</td>
    <td>${esc(u["mac-address"])}</td>
    <td>${esc(u.uptime)}</td>
    <td>${bytes(u["bytes-out"])}</td>
    <td>${bytes(u["bytes-in"])}</td>
    <td><span class="online-pill">Online</span></td>
<td>
    <button class="action-btn danger" onclick="controlClient('kick_hotspot','${esc(u.user)}','${esc(u.address)}')">Kick</button>
</td>
</tr>`).join(""));
if(document.getElementById("pppoeBadgeCount")){
    pppoeBadgeCount.textContent = d.pppoe.length;
}

rows("pppoeTable",d.pppoe.map(u=>`
<tr>
    <td><span class="pppoe-user-pill">${esc(u.name)}</span></td>
    <td>${esc(u.address)}</td>
    <td>${esc(u["caller-id"])}</td>
    <td>${esc(u.uptime)}</td>
    <td>${esc(u.service)}</td>
    <td><span class="pppoe-online-pill">Online</span></td>
</tr>`).join(""));
if(document.getElementById("queueBadgeCount")){
    queueBadgeCount.textContent = d.queues.length;
}

rows("queueTable",d.queues.map(q=>`
<tr>
    <td><span class="queue-name-pill">${esc(q.name)}</span></td>
    <td>${esc(q.target)}</td>
    <td>
<span class="speed-pill">
${
(() => {
    let lim = q["max-limit"] || "0/0";

    function fmt(v){
        v = parseInt(v || 0);

        if(v >= 1000000){
            return (v / 1000000).toFixed(v % 1000000 === 0 ? 0 : 1) + "M";
        }

        if(v >= 1000){
            return (v / 1000).toFixed(v % 1000 === 0 ? 0 : 1) + "K";
        }

        return v;
    }

    let parts = lim.split("/");
    return fmt(parts[0]) + "/" + fmt(parts[1]);
})()
}
</span>
</td>
    <td>${esc(q.bytes)}</td>
    <td>${q.disabled==="true"
        ? `<span class="queue-disabled-pill">Disabled</span>`
        : `<span class="queue-active-pill">Active</span>`}
    </td>
<td>
    ${q.disabled==="true"
        ? `<button class="action-btn success" onclick="controlClient('enable_queue','${esc(q.name)}','${esc(q.target)}')">Resume</button>`
        : `<button class="action-btn warning" onclick="controlClient('disable_queue','${esc(q.name)}','${esc(q.target)}')">Pause</button>`}
</td>
</tr>`).join(""));
rows("leaseTable",d.leases.map(l=>`<tr><td>${esc(l.address)}</td><td>${esc(l["mac-address"])}</td><td>${esc(l["host-name"])}</td><td>${esc(l.status)}</td></tr>`).join(""));
if(document.getElementById("ifaceCount")){
    ifaceCount.textContent = d.interfaces.length;
}

rows("ifaceTable",d.interfaces.map(i=>{
    const running = i.running === "true";
    const disabled = i.disabled === "true";

    return `
<tr>
    <td><span class="iface-name-pill">${esc(i.name)}</span></td>
    <td><span class="iface-type-pill">${esc(i.type)}</span></td>
    <td>${running
        ? `<span class="iface-running-pill">Running</span>`
        : `<span class="iface-down-pill">Down</span>`}
    </td>
    <td>${disabled
        ? `<span class="iface-disabled-pill">Disabled</span>`
        : `<span class="iface-enabled-pill">Enabled</span>`}
    </td>
    <td>${running && !disabled
        ? `<span class="iface-health-good">Healthy</span>`
        : `<span class="iface-health-warn">Check</span>`}
    </td>
    <td>
        <a class="port-btn view" href="http://192.168.88.1/webfig/#Interfaces" target="_blank">🌐 Interfaces</a>
        <a class="port-btn config" href="http://192.168.88.1/webfig/#Interfaces" target="_blank">⚙️ Configure</a>
        <a class="port-btn health" href="http://192.168.88.1/webfig/#IP:Hotspot:Active" target="_blank">👥 Users</a>
    </td>
</tr>`;
}).join(""));
}
live();setInterval(live,5000);

async function loadRevenueSummary(){
    try{
        const r = await fetch("revenue_summary_api.php?_=" + Date.now());
        const x = await r.json();
        revToday.textContent = "Ksh " + Number(x.today).toLocaleString();
        revWeek.textContent = "Ksh " + Number(x.week).toLocaleString();
        revMonth.textContent = "Ksh " + Number(x.month).toLocaleString();
        bizRevenue.textContent = "Ksh " + Number(x.total).toLocaleString();
    }catch(e){ console.log(e); }
}
loadRevenueSummary();
setInterval(loadRevenueSummary, 10000);


async function loadRevenueChartPro(){
    try{
        const r = await fetch("revenue_summary_api.php?_=" + Date.now());
        const x = await r.json();

        revChartToday.textContent = "Ksh " + Number(x.today).toLocaleString();
        revChartMonth.textContent = "Ksh " + Number(x.month).toLocaleString();
        revChartTotal.textContent = "Ksh " + Number(x.total).toLocaleString();

        revenueChart.data.datasets[0].data = [x.today, x.week, x.month, x.total];
        revenueChart.data.datasets[1].data = [x.today, x.week, x.month, x.total];
        revenueChart.update();
    }catch(e){ console.log(e); }
}
loadRevenueChartPro();
setInterval(loadRevenueChartPro, 10000);

function filterHotspotTable(q){
    q = String(q || "").toLowerCase();
    document.querySelectorAll("#hotspotTable tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}
function filterPppoeTable(q){
    q = String(q || "").toLowerCase();
    document.querySelectorAll("#pppoeTable tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}
function filterQueueTable(q){
    q = String(q || "").toLowerCase();
    document.querySelectorAll("#queueTable tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(q) ? "" : "none";
    });
}
function filterDhcpTable(q){
    q = String(q || "").toLowerCase();

    document.querySelectorAll("#leaseTable tbody tr").forEach(row=>{
        row.style.display =
            row.innerText.toLowerCase().includes(q)
            ? ""
            : "none";
    });
}
function filterIfaceTable(q){
    q = String(q || "").toLowerCase();

    document.querySelectorAll("#ifaceTable tbody tr").forEach(row=>{
        row.style.display =
            row.innerText.toLowerCase().includes(q)
            ? ""
            : "none";
    });
}

async function controlClient(action,user,ip){
    if(!confirm("Confirm action: " + action + " for " + user + "?")) return;

    const form = new FormData();
    form.append("action", action);
    form.append("user", user);
    form.append("ip", ip);

    const r = await fetch("client_control_api.php", {
        method:"POST",
        body:form
    });

    const d = await r.json();
    alert(d.message);
    live();
}


async function loadBandwidthLeaderboard(){
    try{
        const r = await fetch("bandwidth_api.php?_=" + Date.now());
        const d = await r.json();

        bwUsers.textContent = d.total.users || 0;
        bwRx.textContent = bytes(d.total.total_rx || 0);
        bwTx.textContent = bytes(d.total.total_tx || 0);
        bwTop.textContent = d.rows.length ? d.rows[0].username : "-";

        document.querySelector("#bandwidthTable tbody").innerHTML = d.rows.length
            ? d.rows.map(x => `
                <tr>
                    <td><span class="user-pill">${esc(x.username)}</span></td>
                    <td>${esc(x.ip_address)}</td>
                    <td>${esc(x.rx_rate)}</td>
                    <td>${esc(x.tx_rate)}</td>
                    <td>${esc(x.updated_at)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="5">No bandwidth records yet</td></tr>`;
    }catch(e){
        console.log(e);
    }
}
loadBandwidthLeaderboard();
setInterval(loadBandwidthLeaderboard, 5000);


async function loadBypassUsers(){
    try{
        const r = await fetch("bypass_users_api.php?_=" + Date.now());
        const d = await r.json();

        if(document.getElementById("bypassCount")){
            bypassCount.textContent = d.rows.length;
        }

        const body = document.querySelector("#bypassTable tbody");
        if(!body) return;

        body.innerHTML = d.rows.length
            ? d.rows.map(x => `
                <tr>
                    <td><span class="ip-pill">${esc(x.ip)}</span></td>
                    <td>${esc(x.mac)}</td>
                    <td>${esc(x.comment)}</td>
                    <td><span class="bypass-status">${esc(x.status)}</span></td>
                </tr>
            `).join("")
            : `<tr><td colspan="4">No bypassed users online</td></tr>`;
    }catch(e){console.log(e);}
}
loadBypassUsers();
setInterval(loadBypassUsers,5000);

</script>

<script>
fetch("profile_logo_api.php?_=" + Date.now())
.then(r => r.json())
.then(d => {
    if(d.logo){
        document.querySelectorAll(".profile-avatar").forEach(el => {
            el.innerHTML = '<img src="' + d.logo + '?v=' + Date.now() + '" alt="Admin Profile">';
        });
    }
});

async function controlClient(action,user,ip){
    if(!confirm("Confirm action: " + action + " for " + user + "?")) return;

    const form = new FormData();
    form.append("action", action);
    form.append("user", user);
    form.append("ip", ip);

    const r = await fetch("client_control_api.php", {
        method:"POST",
        body:form
    });

    const d = await r.json();
    alert(d.message);
    live();
}


async function loadBandwidthLeaderboard(){
    try{
        const r = await fetch("bandwidth_api.php?_=" + Date.now());
        const d = await r.json();

        bwUsers.textContent = d.total.users || 0;
        bwRx.textContent = bytes(d.total.total_rx || 0);
        bwTx.textContent = bytes(d.total.total_tx || 0);
        bwTop.textContent = d.rows.length ? d.rows[0].username : "-";

        document.querySelector("#bandwidthTable tbody").innerHTML = d.rows.length
            ? d.rows.map(x => `
                <tr>
                    <td><span class="user-pill">${esc(x.username)}</span></td>
                    <td>${esc(x.ip_address)}</td>
                    <td>${esc(x.rx_rate)}</td>
                    <td>${esc(x.tx_rate)}</td>
                    <td>${esc(x.updated_at)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="5">No bandwidth records yet</td></tr>`;
    }catch(e){
        console.log(e);
    }
}
loadBandwidthLeaderboard();
setInterval(loadBandwidthLeaderboard, 5000);


async function loadBypassUsers(){
    try{
        const r = await fetch("bypass_users_api.php?_=" + Date.now());
        const d = await r.json();

        if(document.getElementById("bypassCount")){
            bypassCount.textContent = d.rows.length;
        }

        const body = document.querySelector("#bypassTable tbody");
        if(!body) return;

        body.innerHTML = d.rows.length
            ? d.rows.map(x => `
                <tr>
                    <td><span class="ip-pill">${esc(x.ip)}</span></td>
                    <td>${esc(x.mac)}</td>
                    <td>${esc(x.comment)}</td>
                    <td><span class="bypass-status">${esc(x.status)}</span></td>
                </tr>
            `).join("")
            : `<tr><td colspan="4">No bypassed users online</td></tr>`;
    }catch(e){console.log(e);}
}
loadBypassUsers();
setInterval(loadBypassUsers,5000);

</script>


<script>
async function loadNocApi(){
  try{
    const r = await fetch("live_noc_api.php?t=" + Date.now(), {cache:"no-store"});
    const d = await r.json();

    const text = document.body.innerHTML;

    if(d.status === "online"){
      document.body.innerHTML = document.body.innerHTML
        .replaceAll("OFFLINE","ONLINE")
        .replaceAll("Offline","Online")
        .replaceAll("offline","online")
        .replace(/Router Identity[\s\S]*?<\\/div>/i, match => match.replace("-", d.identity || "MikroTik"));

      document.querySelectorAll("*").forEach(el=>{
        if(el.innerText && el.innerText.trim()==="0%" && d.cpu !== undefined){
          el.innerText = d.cpu + "%";
        }
        if(el.innerText && el.innerText.trim()==="0 B" && d.memory_free !== undefined){
          el.innerText = d.memory_free + " B";
        }
        if(el.innerText && el.innerText.trim()==="-"){
          if(el.previousElementSibling && el.previousElementSibling.innerText.includes("RouterOS")){
            el.innerText = d.version || "-";
          }
        }
      });
    }
  }catch(e){
    console.log("Live NOC API error", e);
  }
}
loadNocApi();
setInterval(loadNocApi,5000);
</script>

<script>
async function syncLiveNoc(){
try{
const r=await fetch("live_noc_api.php?t="+Date.now(),{cache:"no-store"});
const d=await r.json();
function setByLabel(label,value){
document.querySelectorAll(".card, .stat, div").forEach(card=>{
if(card.innerText && card.innerText.includes(label)){
let els=card.querySelectorAll("h2,h3,.big,.value,strong,b,span");
if(els.length){ els[els.length-1].innerText=value; }
}
});
}
setByLabel("Router Identity", d.identity || "-");
setByLabel("CPU Load", (d.cpu || 0)+"%");
setByLabel("Hotspot Online", d.hotspot_online || 0);
setByLabel("PPPoE Online", d.pppoe_online || 0);
setByLabel("Simple Queues", d.queues || 0);
setByLabel("Router Uptime", d.uptime || "-");
setByLabel("Memory Free", d.memory_free || "-");
setByLabel("RouterOS Version", d.version || "-");
setByLabel("System Status", d.status==="online" ? "ONLINE" : "OFFLINE");
document.body.innerHTML=document.body.innerHTML.replaceAll("OFFLINE", d.status==="online"?"ONLINE":"OFFLINE");
}catch(e){console.log(e)}
}
syncLiveNoc();
setInterval(syncLiveNoc,5000);
</script>
</body>
</html>
