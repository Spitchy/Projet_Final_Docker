# syntax=docker/dockerfile:1

# ---------------------------------------------------------------
# Étape 1 : recompilation de gosu avec une version de Go à jour.
# Le gosu fourni par mysql:8.4 est compilé avec go1.24.6, touché par
# la CVE critique CVE-2025-68121 (crypto/tls) signalée par Trivy.
# Compilation croisée sur la plateforme de build (rapide en CI multi-arch).
# ---------------------------------------------------------------
FROM --platform=$BUILDPLATFORM golang:1.27-alpine AS gosu
ARG GOSU_VERSION=1.20
ARG TARGETOS TARGETARCH
# les tags de gosu ("1.20") ne sont pas des versions de module Go : on récupère
# les sources au tag voulu puis on compile
ADD https://github.com/tianon/gosu.git#${GOSU_VERSION} /src
WORKDIR /src
RUN CGO_ENABLED=0 GOOS=$TARGETOS GOARCH=$TARGETARCH \
    go build -trimpath -ldflags="-s -w" -o /gosu .

# ---------------------------------------------------------------
# Étape 2 : image MySQL 8.4 (version LTS officielle)
# ---------------------------------------------------------------
FROM mysql:8.4

LABEL org.opencontainers.image.title="projet-docker-db" \
      org.opencontainers.image.description="Base MySQL de l'application articles" \
      org.opencontainers.image.source="https://github.com/Spitchy/Projet_Final_Docker"

# Paramètres par défaut, sans aucun mot de passe : ceux-ci sont fournis
# au démarrage via des secrets (MYSQL_ROOT_PASSWORD_FILE, MYSQL_PASSWORD_FILE).
ENV MYSQL_DATABASE=test \
    MYSQL_USER=test

# MySQL Shell (client d'administration) n'est pas utile au serveur et
# embarque des bibliothèques Python vulnérables (certifi, cryptography...).
RUN microdnf remove -y mysql-shell \
 && microdnf clean all

# gosu recompilé (utilisé par l'entrypoint pour passer de root à mysql)
COPY --from=gosu --chmod=0755 /gosu /usr/local/bin/gosu

# Configuration serveur (encodage, désactivation du chargement de fichiers locaux)
COPY --chown=root:root --chmod=0644 conf.d/ /etc/mysql/conf.d/

# Script d'initialisation exécuté au premier démarrage (volume vide)
COPY --chown=root:root --chmod=0644 init/ /docker-entrypoint-initdb.d/

# Le processus mysqld tourne déjà sous l'utilisateur non-root "mysql"
# (l'entrypoint officiel abandonne les privilèges root au démarrage).
EXPOSE 3306

HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=5 \
  CMD mysqladmin ping -h 127.0.0.1 --silent || exit 1
