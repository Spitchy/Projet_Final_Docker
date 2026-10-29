-- La base et l'utilisateur applicatif sont créés par l'image officielle MySQL
-- à partir de MYSQL_DATABASE / MYSQL_USER / MYSQL_PASSWORD_FILE
-- (l'utilisateur 'test'@'localhost' d'origine ne pouvait pas se connecter
-- depuis le conteneur web).
-- Ce script n'est exécuté qu'au premier démarrage, quand le volume est vide.

-- le client mysql de l'entrypoint lit le fichier en latin1 par défaut :
-- sans cette ligne, les accents de l'article d'exemple sont corrompus
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    content TEXT NULL,
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO articles (title, author, content) VALUES
    ('Bienvenue', 'Équipe projet', 'Premier article créé automatiquement au démarrage de la base de données.');
