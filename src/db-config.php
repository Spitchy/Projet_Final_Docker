<?php
/**
 * Configuration de la connexion à la base de données.
 * Les valeurs proviennent de l'environnement du conteneur (docker-compose.yml)
 * afin de ne rien coder en dur dans l'image.
 */

/**
 * Lit le mot de passe depuis un secret Docker (fichier monté dans /run/secrets)
 * ou, à défaut, depuis une variable d'environnement.
 */
function readSecret(string $name, string $default = ''): string
{
    $file = getenv($name . '_FILE');
    if ($file !== false && is_readable($file)) {
        return trim(file_get_contents($file));
    }
    $value = getenv($name);
    return $value !== false ? $value : $default;
}

$dbHost = getenv('DB_HOST') ?: 'db';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'test';

define('DB_DSN', "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4");
define('DB_USER', getenv('DB_USER') ?: 'test');
define('DB_PASS', readSecret('DB_PASSWORD'));

$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // gérer les erreurs en tant qu'exception
    PDO::ATTR_EMULATE_PREPARES => false, // faire des vrais requêtes préparées et non une émulation
    PDO::ATTR_TIMEOUT => 3 // éviter de bloquer Apache si la base ne répond pas
);
