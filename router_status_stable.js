(function(){

let stableState = true;
let failCount = 0;

async function updateStatus(){

    try{

        const r = await fetch(
            "router_status_stable_api.php?_=" + Date.now(),
            {cache:"no-store"}
        );

        const d = await r.json();

        if(d.online){
            stableState = true;
            failCount = 0;
        }else{
            failCount++;
        }

        if(failCount >= 5){
            stableState = false;
        }

    }catch(e){

        failCount++;

        if(failCount >= 5){
            stableState = false;
        }
    }

    document.querySelectorAll("#status,#healthStatus,.badge").forEach(el=>{

        const txt = (el.textContent || "").trim().toUpperCase();

        if(txt === "ONLINE" || txt === "OFFLINE"){

            el.textContent = stableState ? "ONLINE" : "OFFLINE";

            el.classList.remove("online","offline");

            el.classList.add(
                stableState ? "online" : "offline"
            );
        }
    });
}

document.addEventListener("DOMContentLoaded",()=>{

    updateStatus();

    setInterval(updateStatus,15000);
});

})();
