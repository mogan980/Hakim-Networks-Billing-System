async function mtAction(action,id,name){
    if(!confirm("Run action: " + action + "?")) return;

    const form = new FormData();
    form.append("action", action);
    form.append("id", id || "");
    form.append("name", name || "");

    const r = await fetch("mikrotik_action_api.php", {
        method: "POST",
        body: form
    });

    const d = await r.json();
    alert(d.message || "Done");

    if(typeof loadModule === "function"){
        loadModule();
    }
}
