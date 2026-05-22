function styleQuickActions(){
  document.querySelectorAll("h2,h3").forEach(h=>{
    if(!h.innerText.includes("Quick Actions")) return;
    const card = h.closest("div");
    if(card) card.classList.add("quick-actions-pro");
  });
}

styleQuickActions();
setInterval(styleQuickActions,3000);
