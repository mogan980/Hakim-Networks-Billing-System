async function loadBandwidthCards(){
  try{
    const r = await fetch("bandwidth_live_api.php?t=" + Date.now(), {cache:"no-store"});
    const d = await r.json();
    if(!d.ok) return;

    function fixCard(label, value, icon){
      document.querySelectorAll("div").forEach(card=>{
        if(!card.innerText) return;

        const hasValue = card.innerText.includes(value) || card.innerText.includes("Mbps");
        const hasLabel = card.innerText.includes(label);

        if(!hasLabel && !hasValue) return;

        if(hasLabel){
          const labelEl = Array.from(card.querySelectorAll("*")).find(el =>
            el.innerText && el.innerText.includes(label)
          );

          const valueEl = Array.from(card.querySelectorAll("*")).find(el =>
            el.innerText && el.innerText.includes("Mbps")
          );

          if(labelEl){
            labelEl.innerText = icon + " " + label;
            labelEl.style.color = "#64748b";
            labelEl.style.fontWeight = "800";
            labelEl.style.fontSize = "13px";
          }

          if(valueEl){
            valueEl.innerText = value;
            valueEl.style.fontSize = "24px";
            valueEl.style.fontWeight = "900";
            valueEl.style.color = "#020617";
          }
        }
      });
    }

    fixCard("Download RX", d.rx, "⬇️");
    fixCard("Upload TX", d.tx, "⬆️");

  }catch(e){
    console.log("Bandwidth live error", e);
  }
}

loadBandwidthCards();
setInterval(loadBandwidthCards, 3000);
