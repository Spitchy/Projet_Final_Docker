<?php
/**
 * Point de contrôle utilisé par le HEALTHCHECK Docker :
 * renvoie 200 si Apache, PHP et la base de données répondent, 503 sinon.
 */
require 'db-config.php';

header('Content-Type: text/plain');
header('Cache-Control: no-store');

try {
    $PDO = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
    $PDO->query('SELECT 1');
    echo 'OK';
} catch (PDOException $pe) {
    http_response_code(503);
    echo 'DB KO';
}
