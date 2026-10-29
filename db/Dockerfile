# Image de la base de données : MySQL 8.4 (version LTS officielle)
FROM mysql:8.4

LABEL org.opencontainers.image.title="projet-docker-db" \
      org.opencontainers.image.description="Base MySQL de l'application articles" \
      org.opencontainers.image.source="https://github.com/Spitchy/Projet_Final_Docker"

# Paramètres par défaut, sans aucun mot de passe : ceux-ci sont fournis
# au démarrage via des secrets (MYSQL_ROOT_PASSWORD_FILE, MYSQL_PASSWORD_FILE).
ENV MYSQL_DATABASE=test \
    MYSQL_USER=test

# Configuration serveur (encodage, désactivation du chargement de fichiers locaux)
COPY --chown=root:root --chmod=0644 conf.d/ /etc/mysql/conf.d/

# Script d'initialisation exécuté au premier démarrage (volume vide)
COPY --chown=root:root --chmod=0644 init/ /docker-entrypoint-initdb.d/

# Le processus mysqld tourne déjà sous l'utilisateur non-root "mysql"
# (l'entrypoint officiel abandonne les privilèges root au démarrage).
EXPOSE 3306

HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=5 \
  CMD mysqladmin ping -h 127.0.0.1 --silent || exit 1
