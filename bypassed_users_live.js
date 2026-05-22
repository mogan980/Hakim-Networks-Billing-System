async function loadBypassedUsers(){
  try{
    const r = await fetch("mikrotik_cards_api.php?t="+Date.now(), {cache:"no-store"});
    const d = await r.json();
    if(!d.ok) return;

    const rows = (d.bindings || []).filter(x => x.type === "bypassed");

    document.querySelectorAll("h2,h3").forEach(title=>{
      if(!title.innerText.includes("Online Bypassed Users")) return;

      const card = title.closest("div");
      if(!card) return;

      card.style.borderRadius = "22px";
      card.style.boxShadow = "0 18px 45px rgba(15,23,42,.10)";
      card.style.border = "1px solid #e5e7eb";

      const pill = card.querySelector("span,.badge,.pill");
      if(pill){
        pill.innerText = rows.length + " Online";
        pill.style.background = "#dcfce7";
        pill.style.color = "#166534";
      }

      const table = card.querySelector("table");
      if(!table) return;

      const header = table.querySelector("tr").outerHTML;

      table.innerHTML = header + (rows.length ? rows.map(x=>`
        <tr>
          <td>${x.address || "-"}</td>
          <td>${x["mac-address"] || "-"}</td>
          <td>${x.comment || "Manual Bypass"}</td>
          <td><b style="color:#16a34a">Online</b></td>
        </tr>
      `).join("") : `
        <tr>
          <td colspan="4" style="text-align:center;color:#64748b;padding:18px;">
            No bypassed users online
          </td>
        </tr>
      `);
    });
  }catch(e){
    console.log("Bypassed users error", e);
  }
}

loadBypassedUsers();
setInterval(loadBypassedUsers,5000);
