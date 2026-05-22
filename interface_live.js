async function loadRouterInterfaces(){
  try{
    const res = await fetch("interface_live_api.php?t=" + Date.now(), {cache:"no-store"});
    const data = await res.json();
    if(!data.ok) return;

    document.querySelectorAll("h2,h3").forEach(title=>{
      if(!title.innerText.includes("Router Interfaces")) return;

      const card = title.closest("div");
      if(!card) return;

      const pill = card.querySelector("span,.badge,.pill");
      if(pill) pill.innerText = data.count + " Interfaces";

      const table = card.querySelector("table");
      if(!table) return;

      const header = table.querySelector("tr").outerHTML;

      const rows = data.interfaces.map(i => `
        <tr>
          <td>${i.name || "-"}</td>
          <td>${i.type || "-"}</td>
          <td>${i.running === "true" ? "Running" : "Down"}</td>
          <td>${i.disabled === "true" ? "Disabled" : "Enabled"}</td>
          <td>${i.running === "true" ? "Healthy" : "Check"}</td>
          <td>-</td>
        </tr>
      `).join("");

      table.innerHTML = header + rows;
    });

  }catch(e){
    console.log("Interface live error:", e);
  }
}

loadRouterInterfaces();
setInterval(loadRouterInterfaces, 5000);
