async function hakimLiveSync(){
  try{
    const res = await fetch("live_noc_api.php?t=" + Date.now(), {cache:"no-store"});
    const d = await res.json();

    function updateCard(label, value){
      document.querySelectorAll("div").forEach(card=>{
        if(card.innerText && card.innerText.includes(label)){
          const targets = card.querySelectorAll("h1,h2,h3,strong,b,span,div");
          for(let i=targets.length-1;i>=0;i--){
            let t = targets[i];
            if(t.innerText && !t.innerText.includes(label)){
              t.innerText = value;
              break;
            }
          }
        }
      });
    }

    updateCard("Router Identity", d.identity || "-");
    updateCard("CPU Load", (d.cpu || 0) + "%");
    updateCard("Hotspot Online", d.hotspot_online || 0);
    updateCard("PPPoE Online", d.pppoe_online || 0);
    updateCard("Simple Queues", d.queues || 0);
    updateCard("Router Uptime", d.uptime || "-");
    updateCard("Memory Free", d.memory_free ? d.memory_free + " B" : "-");
    updateCard("RouterOS Version", d.version || "-");
    updateCard("System Status", d.status === "online" ? "ONLINE" : "OFFLINE");

    document.querySelectorAll("*").forEach(el=>{
      if(el.innerText && el.innerText.trim()==="OFFLINE" && d.status==="online"){
        el.innerText="ONLINE";
        el.style.color="#16a34a";
      }
    });

  }catch(e){
    console.log("Hakim Live Sync Error:", e);
  }
}

hakimLiveSync();
setInterval(hakimLiveSync, 5000);
