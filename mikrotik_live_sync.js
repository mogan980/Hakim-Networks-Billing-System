async function mikrotikLiveSync(){
    try{
        const res = await fetch("mikrotik_live_api.php?t=" + Date.now(), {cache:"no-store"});
        const d = await res.json();

        if(!d) return;

        const map = {
            "Router Identity": d.router?.identity || "-",
            "CPU Load": (d.router?.cpu ?? 0) + "%",
            "Download RX": d.router?.rx || "0 Mbps",
            "Upload TX": d.router?.tx || "0 Mbps",
            "Hotspot Online": d.counts?.hotspot_online ?? 0,
            "PPPoE Online": d.counts?.pppoe_online ?? 0,
            "Simple Queues": d.counts?.queues ?? 0,
            "Known Clients": d.counts?.dhcp_clients ?? 0,
            "Router Uptime": d.router?.uptime || "-",
            "Memory Free": d.router?.memory_free ? d.router.memory_free + " B" : "-",
            "RouterOS Version": d.router?.version || "-",
            "System Status": d.status === "online" ? "ONLINE" : "OFFLINE"
        };

        function updateByLabel(label, value){
            document.querySelectorAll("div,section,article,td").forEach(box=>{
                if(!box.innerText) return;
                if(!box.innerText.includes(label)) return;

                const candidates = box.querySelectorAll("h1,h2,h3,h4,strong,b,span,.value,.big,.stat-value");
                for(let i=candidates.length-1;i>=0;i--){
                    let el = candidates[i];
                    if(!el.innerText.includes(label)){
                        el.innerText = value;
                        if(value === "ONLINE"){
                            el.style.color = "#16a34a";
                        }
                        if(value === "OFFLINE"){
                            el.style.color = "#dc2626";
                        }
                        break;
                    }
                }
            });
        }

        Object.entries(map).forEach(([k,v])=>updateByLabel(k,v));

        document.querySelectorAll("[data-mk]").forEach(el=>{
            const key = el.getAttribute("data-mk");
            const value = key.split(".").reduce((o,k)=>o?.[k], d);
            if(value !== undefined && value !== null){
                el.innerText = value;
            }
        });

        document.querySelectorAll(".status,.badge,.pill").forEach(el=>{
            if(el.innerText.trim().toLowerCase()==="offline" && d.status==="online"){
                el.innerText = "ONLINE";
                el.style.background = "#dcfce7";
                el.style.color = "#166534";
            }
        });

        window.HAKIM_MIKROTIK_LIVE = d;

    }catch(e){
        console.log("MikroTik live sync error:", e);
    }
}

mikrotikLiveSync();
setInterval(mikrotikLiveSync, 5000);
