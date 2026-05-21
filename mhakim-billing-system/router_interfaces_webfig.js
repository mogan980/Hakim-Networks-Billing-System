function addRouterInterfaceWebFigButtons(){
  document.querySelectorAll("h2,h3").forEach(title=>{
    if(!title.innerText.includes("Router Interfaces")) return;

    const card = title.closest("div");
    if(!card || card.dataset.webfigReady === "yes") return;
    card.dataset.webfigReady = "yes";

    const top = document.createElement("div");
    top.style.display = "flex";
    top.style.gap = "10px";
    top.style.flexWrap = "wrap";
    top.style.margin = "14px 0";

    top.innerHTML = `
      <a href="http://192.168.88.1/webfig/#Interfaces" target="_blank"
         style="background:#0f766e;color:white;text-decoration:none;padding:11px 16px;border-radius:12px;font-weight:800;">
         🌐 Open MikroTik Interfaces
      </a>

      <a href="http://192.168.88.1/webfig/#IP:Hotspot" target="_blank"
         style="background:#2563eb;color:white;text-decoration:none;padding:11px 16px;border-radius:12px;font-weight:800;">
         📡 Hotspot Settings
      </a>

      <a href="http://192.168.88.1/webfig/#Queues:Simple Queues" target="_blank"
         style="background:#f59e0b;color:white;text-decoration:none;padding:11px 16px;border-radius:12px;font-weight:800;">
         ⚡ Queue Control
      </a>
    `;

    const search = card.querySelector("input");
    if(search){
      search.parentNode.insertBefore(top, search.nextSibling);
    }else{
      title.insertAdjacentElement("afterend", top);
    }

    const note = document.createElement("p");
    note.innerHTML = "Tip: Login with your MikroTik admin/API credentials to configure ports, bridge, wireless, queues and hotspot.";
    note.style.color = "#64748b";
    note.style.fontSize = "13px";
    note.style.marginTop = "8px";
    top.insertAdjacentElement("afterend", note);
  });
}

addRouterInterfaceWebFigButtons();
setInterval(addRouterInterfaceWebFigButtons, 3000);
