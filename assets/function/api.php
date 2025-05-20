<?php
function verifyApiKey($apiKey)
{
    // // Buscar no banco de dados se a chave existe e está ativa
    // $stmt = $pdo->prepare("SELECT user_id FROM api_keys WHERE api_key = ? AND is_active = 1");
    // $stmt->execute([$apiKey]);
    // return $stmt->fetch();
    global $api;
    if ($api["key"] == $apiKey) {
        return true;
    } else {
        return false;
    }
}