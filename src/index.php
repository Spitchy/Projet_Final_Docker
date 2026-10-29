<?php
require 'db-config.php';

// échappement systématique des données affichées (protection XSS)
function e(?string $value): string
{
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function getArticles(PDO $PDO)
{
  $sql = "SELECT * FROM articles ORDER BY id DESC";
  $result = $PDO->query($sql);

  $articles = $result->fetchAll(PDO::FETCH_ASSOC);

  $result->closeCursor();

  return $articles;
}
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Ma propre image Docker !</title>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>

  <nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
    <a class="navbar-brand" href="#">Ma propre image Docker</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item">
          <a class="nav-link" href="#">Accueil</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="#">Articles <span class="sr-only">(current)</span></a>
        </li>

      </ul>
    </div>
  </nav>

  <main role="main" class="container">

    <h1 class="mt-5">Articles</h1>

    <hr>

    <h2 class="mt-5 mb-3">Nouveau article</h2>


    <form action="validation.php" method="post">
      <div class="form-group">
        <label for="title">Titre <span style="color: red; font-weight: bold;">*</span></label>
        <input type="text" class="form-control" id="title" name="title" placeholder="titre de votre article" required="required">
      </div>

      <div class="form-group">
        <label for="author">Nom de l'auteur <span style="color: red; font-weight: bold;">*</span></label>
        <input type="text" class="form-control" id="author" name="author" placeholder="Nom de l'auteur" required="required">
      </div>
      
      <div class="form-group">
        <label for="content">Contenu <span style="color: red; font-weight: bold;">*</span></label>
        <textarea class="form-control" id="content" name="content" rows="3" required="required"></textarea>
      </div>

      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>

    <hr>

    <h2 class="mt-5 mb-5">Liste d'articles</h2>

    <?php
    try {
      $PDO = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
      $articles = getArticles($PDO);
      foreach ($articles as $article) {
        $articleTime = date("d/m/y H:i", strtotime($article["date"]));
        ?>
        <div class="card mt-5">
          <div class="card-header">
            <h2 class="h3"><?= e($article["title"]) ?> <small class="text-muted font-italic"><?= $articleTime ?></small></h2>
          </div>
          <div class="card-body">
            <p class="card-text"><?= nl2br(e($article["content"])) ?></p>
            <footer class="blockquote-footer"><cite title="Auteur"><?= e($article["author"]) ?></cite></footer>
          </div>
        </div>
      <?php
      }
    } catch (PDOException $pe) {
      // le détail technique part dans les logs du conteneur, pas dans la page
      error_log('Erreur base de données : ' . $pe->getMessage());
      echo '<div class="alert alert-danger">Impossible de charger les articles pour le moment.</div>';
    }
    ?>

  </main>

  <footer class="page-footer font-small bg-dark mt-5">
    <div class="footer-copyright text-center py-3 text-white">© Copyright:
      <a href="#"> MonAppDocker</a>
    </div>
  </footer>

</body>

</html>
