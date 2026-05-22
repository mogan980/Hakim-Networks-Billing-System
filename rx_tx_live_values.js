async function updateRxTxValues(){

  try{

    const r = await fetch("bandwidth_live_api.php?t="+Date.now(),{
      cache:"no-store"
    });

    const d = await r.json();

    if(!d.ok) return;

    document.querySelectorAll("div").forEach(card=>{

      const text = card.innerText || "";

      /* DOWNLOAD CARD */

      if(text.includes("Download RX")){

        const vals = [...card.querySelectorAll("*")];

        vals.forEach(el=>{

          if(
            el.innerText &&
            el.innerText.includes("Mbps") &&
            !el.innerText.includes("Download RX") &&
            !el.innerText.includes("Upload TX")
          ){
            el.innerText = d.rx;
            el.style.fontSize = "24px";
            el.style.fontWeight = "900";
            el.style.color = "#020617";
          }

        });

      }

      /* UPLOAD CARD */

      if(text.includes("Upload TX")){

        const vals = [...card.querySelectorAll("*")];

        vals.forEach(el=>{

          if(
            el.innerText &&
            el.innerText.includes("Mbps") &&
            !el.innerText.includes("Download RX") &&
            !el.innerText.includes("Upload TX")
          ){
            el.innerText = d.tx;
            el.style.fontSize = "24px";
            el.style.fontWeight = "900";
            el.style.color = "#020617";
          }

        });

      }

    });

  }catch(e){
    console.log("RX TX update error",e);
  }

}

updateRxTxValues();

setInterval(updateRxTxValues,3000);
