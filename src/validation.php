<?php
require 'db-config.php';


function validate(PDO $PDO){
    if(empty($_POST["title"])) {
        return 'Il manque le titre de l\'article';
    }
    if(empty($_POST["author"])) {
        return 'Il manque le nom de l\'auteur de l\'article';
    }
    if(empty($_POST["content"])) {
        return 'Il manque le contenu de l\'article';
    }

    $request = $PDO->prepare("INSERT INTO articles (title, author, content) VALUES (:title, :author, :content)");
    $request->bindValue(":title", $_POST["title"]);
    $request->bindValue(":author", $_POST["author"]);
    $request->bindValue(":content", $_POST["content"]);
    $request->execute();

    return null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$status = 400;
try {
    $PDO = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
    $error = validate($PDO);
} catch (PDOException $pe) {
    error_log('Erreur base de données : ' . $pe->getMessage());
    $status = 500;
    $error = 'Impossible d\'enregistrer l\'article, réessayez plus tard.';
}

if ($error === null) {
    // redirection avant tout affichage, sinon header() échoue
    header('Location: index.php');
    exit;
}

http_response_code($status);
echo '<p style="color: red; font-weight: bold;">' . htmlspecialchars($error) . '</p>';
echo '<p><a href="index.php">accueil</a></p>';
