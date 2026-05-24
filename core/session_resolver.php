<?php
function hn_get_best_session(PDO $pdo, string $ip): ?array {
    $pdo->exec("SET time_zone = '+03:00'");

    $sessions = [];

    $v = $pdo->prepare("
        SELECT id, voucher_code AS client, used_at AS started_at, expires_at
        FROM smart_vouchers
        WHERE used_by=?
        AND status='used'
        AND expires_at IS NOT NULL
        AND expires_at > NOW()
    ");
    $v->execute([$ip]);

    foreach($v->fetchAll(PDO::FETCH_ASSOC) as $r){
        $sessions[] = [
            "source"=>"voucher",
            "id"=>$r["id"],
            "client"=>$r["client"],
            "username"=>$r["client"],
            "password"=>$r["client"],
            "started_at"=>$r["started_at"],
            "expires_at"=>$r["expires_at"]
        ];
    }

    $s = $pdo->prepare("
        SELECT id, phone AS client, created_at AS started_at, expires_at
        FROM payments
        WHERE client_ip=?
        AND status='paid'
        AND expires_at IS NOT NULL
        AND expires_at > NOW()
    ");
    $s->execute([$ip]);

    foreach($s->fetchAll(PDO::FETCH_ASSOC) as $r){
        $phone = preg_replace('/\D/','',$r["client"]);
        $sessions[] = [
            "source"=>"stk",
            "id"=>$r["id"],
            "client"=>$r["client"],
            "username"=>"stk_".$phone,
            "password"=>"stk".$r["id"],
            "started_at"=>$r["started_at"],
            "expires_at"=>$r["expires_at"]
        ];
    }

    if(!$sessions) return null;

    usort($sessions, function($a,$b){
        return strtotime($b["started_at"] ?? "1970-01-01") <=> strtotime($a["started_at"] ?? "1970-01-01");
    });

    return $sessions[0];
}
