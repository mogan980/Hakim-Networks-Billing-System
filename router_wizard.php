<?php
require_once __DIR__ . '/access_guard.php';
require_once "auth.php";

require_once "config/database.php";

$history = $pdo->query("
    SELECT *
    FROM router_history
    ORDER BY id DESC
    LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);

function safe($v){
    return htmlspecialchars($v ?? "-", ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Router Setup Wizard</title>
<link rel="stylesheet" href="assets/pro-sidebar.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#020617;
    color:#e5e7eb;
}

.main{
    margin-left:280px;
    padding:32px;
}

.hero{
    background:linear-gradient(135deg,#052e2b,#0f172a);
    border:1px solid #1e293b;
    border-radius:26px;
    padding:28px;
    margin-bottom:22px;
    box-shadow:0 25px 70px rgba(0,0,0,.35);
}

.hero h1{
    margin:0;
    font-size:34px;
}

.hero p{
    color:#cbd5e1;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.card{
    background:#0f172a;
    border:1px solid #1e293b;
    border-radius:22px;
    padding:24px;
    margin-bottom:22px;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
}

.video-container{
    position:relative;
    height:320px;
    border-radius:24px;
    overflow:hidden;
    border:1px solid rgba(34,197,94,.25);
    background:#020617;
    box-shadow:0 25px 60px rgba(0,0,0,.35);
}

.bg-animation{
    position:absolute;
    inset:-20%;
    background:
        radial-gradient(circle at 20% 20%, rgba(34,197,94,.35), transparent 30%),
        radial-gradient(circle at 80% 40%, rgba(59,130,246,.30), transparent 30%),
        radial-gradient(circle at 50% 80%, rgba(34,197,94,.25), transparent 35%),
        linear-gradient(135deg,#052e2b,#020617,#0f172a);

    animation:moveBg 14s ease-in-out infinite alternate;
    transform:scale(1.1);
}

@keyframes moveBg{
    0%{
        transform:scale(1.1) translate(0,0);
    }

    100%{
        transform:scale(1.2) translate(-20px,-15px);
    }
}

.video-overlay{
    position:absolute;
    inset:0;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding:20px;
    backdrop-filter:blur(3px);
}

.video-overlay h2{
    color:#22c55e;
    font-size:34px;
    margin-bottom:10px;
}

.video-overlay p{
    color:#e2e8f0;
    font-size:16px;
    margin-bottom:22px;
}

.play-circle{
    width:90px;
    height:90px;
    border-radius:50%;
    background:rgba(34,197,94,.15);
    border:2px solid rgba(34,197,94,.55);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    color:#22c55e;
    margin-bottom:22px;
    animation:pulse 2s infinite;
}

@keyframes pulse{
    0%{
        transform:scale(1);
        box-shadow:0 0 0 0 rgba(34,197,94,.5);
    }

    70%{
        transform:scale(1.06);
        box-shadow:0 0 0 18px rgba(34,197,94,0);
    }

    100%{
        transform:scale(1);
    }
}

.mini-steps{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    justify-content:center;
}

.mini-steps span{
    background:rgba(15,23,42,.75);
    border:1px solid rgba(34,197,94,.25);
    padding:10px 14px;
    border-radius:999px;
    color:#bbf7d0;
    font-size:13px;
}

.video h2{
    color:#22c55e;
}

input,textarea{
    width:100%;
    padding:13px;
    border-radius:12px;
    border:1px solid #334155;
    background:#020617;
    color:white;
    margin:8px 0 14px;
}

button{
    padding:13px 18px;
    border:0;
    border-radius:12px;
    background:#22c55e;
    color:#052e16;
    font-weight:900;
    cursor:pointer;
}

button.blue{
    background:#3b82f6;
    color:white;
}

button.red{
    background:#ef4444;
    color:white;
}

.btns{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.code{
    background:#020617;
    border:1px solid #334155;
    border-radius:14px;
    padding:16px;
    white-space:pre-wrap;
    color:#bbf7d0;
    font-family:monospace;
    min-height:240px;
    overflow:auto;
}

.status{
    margin-top:15px;
    padding:13px;
    border-radius:13px;
    display:none;
}

.table-wrap{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    text-align:left;
    padding:12px;
    border-bottom:1px solid #1e293b;
    font-size:14px;
}

th{
    color:#cbd5e1;
}

.badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:bold;
}

.online{
    background:#052e16;
    color:#22c55e;
}

.offline{
    background:#450a0a;
    color:#fecaca;
}

.unknown{
    background:#334155;
    color:#cbd5e1;
}

.steps{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-top:18px;
}

.step{
    background:rgba(15,23,42,.8);
    border:1px solid #1e293b;
    border-radius:16px;
    padding:14px;
}

.step strong{
    color:#22c55e;
}

@media(max-width:900px){
    .main{margin-left:0;padding:18px}
    .grid{grid-template-columns:1fr}
    .steps{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div class="sidebar">
<h2>Hakim Networks</h2>
<a href="noc_final_clean.php"><span class="icon">📊</span>Dashboard</a>
<a href="analytics.php"><span class="icon">📈</span>Analytics</a>
<a href="noc.php"><span class="icon">🖥️</span>NOC Center</a>
<a href="routers.php"><span class="icon">🛰️</span>Routers</a>
<a class="active" href="router_wizard.php"><span class="icon">🧙</span>Router Wizard</a>
<a href="health_check.php"><span class="icon">🩺</span>Health Check</a>
<a href="logout.php"><span class="icon">🚪</span>Logout</a>
</div>

<div class="main">

<div class="hero">
    <h1>Router Setup Wizard</h1>
    <p>Connect MikroTik routers to Hakim Networks Billing using a guided one-run setup script.</p>

    <div class="steps">
        <div class="step"><strong>1.</strong><br>Generate Script</div>
        <div class="step"><strong>2.</strong><br>Run in MikroTik</div>
        <div class="step"><strong>3.</strong><br>Test API</div>
        <div class="step"><strong>4.</strong><br>Go Live</div>
    </div>
</div>

<div class="grid">

<div class="card">
    <h2>Setup Video</h2>
    <div class="video-container">

    <div class="bg-animation"></div>

    <div class="video-overlay">

        <div class="play-circle" onclick="playDemo()">▶</div>

        <h2>🎥 MikroTik Linking Guide</h2>

        <p>
            Connect, Manage & Monitor MikroTik Routers
        </p>

        <div class="mini-steps">
            <span>Generate Script</span>
            <span>Run In MikroTik</span>
            <span>Test Connection</span>
            <span>Go Live</span>
        </div>

    </div>

</div>

    <p style="color:#94a3b8;margin-top:14px;">
        Later you can replace this box with a YouTube iframe or local MP4 tutorial.
    </p>
</div>

<div class="card">
    <h2>Router Connection</h2>

    <label>Billing Server IP / Domain</label>
    <input id="server" value="<?php echo safe($_SERVER['HTTP_HOST']); ?>">

    <label>Router IP Address</label>
    <input id="routerIp" value="192.168.88.1">

    <label>API Port</label>
    <input id="apiPort" value="8728">

    <label>API Username</label>
    <input id="apiUser" value="mhakimapi">

    <label>API Password</label>
    <input id="apiPass" placeholder="Enter API password">

    <label>Hotspot DNS Name</label>
    <input id="dnsName" value="login.hakim">

    <div class="btns">
        <button onclick="generateScript()">Generate Script</button>
        <button class="blue" onclick="testRouter()">Test & Save Router</button>
        <button class="red" onclick="clearStatus()">Clear</button>
    </div>

    <div id="routerStatus" class="status"></div>
</div>

</div>

<div class="card">
    <h2>One-Run MikroTik Script</h2>
    <p style="color:#94a3b8;">Copy this and run it inside MikroTik Terminal, not Linux terminal.</p>

    <div class="code" id="scriptBox">Fill router details then click Generate Script.</div>

    <br>

    <div class="btns">
        <button onclick="copyScript()">Copy Script</button>
        <button class="blue" onclick="downloadScript()">Download .rsc File</button>
    </div>
</div>

<div class="card">
    <h2>Connected Router History</h2>

    <div class="table-wrap">
        <table>
            <tr>
                <th>ID</th>
                <th>Router</th>
                <th>IP</th>
                <th>User</th>
                <th>Port</th>
                <th>Status</th>
                <th>Action</th>
                <th>Message</th>
                <th>Date</th>
            </tr>

            <?php foreach($history as $h): ?>
                <tr>
                    <td><?php echo (int)$h["id"]; ?></td>
                    <td><?php echo safe($h["router_name"]); ?></td>
                    <td><?php echo safe($h["router_ip"]); ?></td>
                    <td><?php echo safe($h["username"]); ?></td>
                    <td><?php echo safe($h["api_port"]); ?></td>
                    <td>
                        <span class="badge <?php echo strtolower($h["status"] ?: "unknown"); ?>">
                            <?php echo safe($h["status"]); ?>
                        </span>
                    </td>
                    <td><?php echo safe($h["action"]); ?></td>
                    <td><?php echo safe($h["message"]); ?></td>
                    <td><?php echo safe($h["created_at"]); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

</div>

<script>
function generateScript(){
    const server = document.getElementById("server").value.trim();
    const user = document.getElementById("apiUser").value.trim();
    const pass = document.getElementById("apiPass").value.trim();
    const dns = document.getElementById("dnsName").value.trim();

    if(!pass){
        alert("Enter API password first.");
        return;
    }

    const script = `
# Hakim Networks MikroTik Setup Script
# IMPORTANT: Run this inside MikroTik Terminal, not Linux terminal.

/ip service set api disabled=no port=8728
/ip service set www disabled=no

/user group add name=hakim-billing policy=read,write,api,test,sensitive
/user add name="${user}" password="${pass}" group=hakim-billing comment="Hakim Networks Billing API User"

/ip hotspot profile set [find] dns-name="${dns}" login-by=http-chap,cookie
/ip dns set allow-remote-requests=yes

/system note set note="Linked to Hakim Networks Billing Server: ${server}"

:put "Hakim Networks router setup completed successfully."
`;

    document.getElementById("scriptBox").innerText = script.trim();
}

function copyScript(){
    const text = document.getElementById("scriptBox").innerText;
    navigator.clipboard.writeText(text);
    alert("Script copied.");
}

function downloadScript(){
    const text = document.getElementById("scriptBox").innerText;
    const blob = new Blob([text], {type:"text/plain"});
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "hakim-router-setup.rsc";
    a.click();
}

async function testRouter(){
    const routerIp = document.getElementById("routerIp").value.trim();
    const username = document.getElementById("apiUser").value.trim();
    const password = document.getElementById("apiPass").value.trim();
    const apiPort = document.getElementById("apiPort").value.trim();

    const status = document.getElementById("routerStatus");

    if(!routerIp || !username || !password){
        alert("Fill router IP, API username, and password.");
        return;
    }

    status.style.display = "block";
    status.style.background = "#172554";
    status.style.color = "#bfdbfe";
    status.innerHTML = "Testing router connection...";

    try{
        const fd = new FormData();
        fd.append("router_ip", routerIp);
        fd.append("username", username);
        fd.append("password", password);
        fd.append("api_port", apiPort);

        const res = await fetch("test_router_connection.php", {
            method:"POST",
            body:fd
        });

        const data = await res.json();

        if(data.success){
            status.style.background = "#052e16";
            status.style.color = "#bbf7d0";
            status.innerHTML = "✅ " + data.message + "<br>Router saved successfully.";

            setTimeout(() => location.reload(), 1400);
        }else{
            status.style.background = "#450a0a";
            status.style.color = "#fecaca";
            status.innerHTML = "❌ " + data.message;
        }

    }catch(e){
        status.style.background = "#450a0a";
        status.style.color = "#fecaca";
        status.innerHTML = "❌ Connection test failed.";
    }
}

function clearStatus(){
    document.getElementById("routerStatus").style.display = "none";
}
function playDemo(){
    alert("Router setup demo: Generate Script → Run in MikroTik → Test Connection → Go Live");
}
</script>

</body>
</html>
