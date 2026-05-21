async function loadMikroTikCards(){
  try{
    const r = await fetch("mikrotik_cards_api.php?t="+Date.now(), {cache:"no-store"});
    const d = await r.json();
    if(!d.ok) return;

    function badge(title,count){
      document.querySelectorAll("h2,h3").forEach(h=>{
        if(h.innerText.includes(title)){
          const box=h.closest(".card,section,div");
          if(!box) return;
          const pill=box.querySelector(".badge,.pill,span");
          if(pill && pill.innerText.includes("Online")) pill.innerText=count+" Online";
          if(pill && pill.innerText.includes("Active")) pill.innerText=count+" Active";
          if(pill && pill.innerText.includes("Devices")) pill.innerText=count+" Devices";
          if(pill && pill.innerText.includes("Interfaces")) pill.innerText=count+" Interfaces";
        }
      });
    }

    function fillTable(title, rows, mapper, emptyText){
      document.querySelectorAll("h2,h3").forEach(h=>{
        if(!h.innerText.includes(title)) return;
        const box=h.closest(".card,section,div");
        if(!box) return;
        const tbody=box.querySelector("tbody");
        const table=box.querySelector("table");
        if(!table) return;

        let body = tbody || table;
        const html = rows.length ? rows.slice(0,20).map(mapper).join("") :
          `<tr><td colspan="7" style="text-align:center;color:#64748b;padding:18px">${emptyText}</td></tr>`;

        if(tbody) tbody.innerHTML=html;
        else {
          const headers=table.querySelector("tr");
          table.innerHTML=headers.outerHTML+html;
        }
      });
    }

    badge("Online Bypassed Users", d.counts.bindings);
    badge("Online Hotspot Users", d.counts.hotspot);
    badge("Online PPPoE Users", d.counts.pppoe);
    badge("Active Simple Queues", d.counts.queues);
    badge("Known DHCP Clients", d.counts.leases);
    badge("Router Interfaces", d.counts.interfaces);

    fillTable("Online Hotspot Users", d.hotspot, x=>`
      <tr>
        <td>${x.user||"-"}</td><td>${x.address||"-"}</td><td>${x["mac-address"]||"-"}</td>
        <td>${x.uptime||"-"}</td><td>${x["bytes-in"]||"0"}</td><td>${x["bytes-out"]||"0"}</td><td>Online</td>
      </tr>`, "No live hotspot users connected right now");

    fillTable("Online PPPoE Users", d.pppoe, x=>`
      <tr>
        <td>${x.name||"-"}</td><td>${x.address||"-"}</td><td>${x["caller-id"]||"-"}</td>
        <td>${x.uptime||"-"}</td><td>${x.service||"-"}</td><td>Online</td>
      </tr>`, "No live PPPoE users connected right now");

    fillTable("Active Simple Queues", d.queues, x=>`
      <tr>
        <td>${x.name||"-"}</td><td>${x.target||"-"}</td><td>${x["max-limit"]||"-"}</td>
        <td>${x.disabled==="true"?"Disabled":"Active"}</td>
      </tr>`, "No simple queues found");

    fillTable("Known DHCP Clients", d.leases, x=>`
      <tr>
        <td>${x.address||"-"}</td><td>${x["mac-address"]||"-"}</td><td>${x["host-name"]||"-"}</td>
        <td>${x.status||"-"}</td><td>${x.server||"-"}</td>
      </tr>`, "No DHCP clients found");

    fillTable("Router Interfaces", d.interfaces, x=>`
      <tr>
        <td>${x.name||"-"}</td><td>${x.type||"-"}</td><td>${x.running==="true"?"Running":"Down"}</td>
        <td>${x.disabled==="true"?"Disabled":"Enabled"}</td><td>${x.running==="true"?"Healthy":"Check"}</td>
      </tr>`, "No interfaces found");

  }catch(e){ console.log("cards live error",e); }
}

loadMikroTikCards();
setInterval(loadMikroTikCards,5000);
