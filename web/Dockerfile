# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.2

# ---------------------------------------------------------------
# Étape 1 : compilation des extensions PHP
# Les outils de compilation restent dans cette étape et ne sont
# pas embarqués dans l'image finale.
# ---------------------------------------------------------------
FROM php:${PHP_VERSION}-apache AS builder
RUN docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli

# ---------------------------------------------------------------
# Étape 2 : tests (php -l sur tout le code)
# Utilisée par la CI : docker build --target test
# ---------------------------------------------------------------
FROM builder AS test
COPY src/ /app/
RUN find /app -name '*.php' -print0 | xargs -0 -n1 php -l

# ---------------------------------------------------------------
# Étape 3 : image finale d'exécution
# ---------------------------------------------------------------
FROM php:${PHP_VERSION}-apache AS runtime

LABEL org.opencontainers.image.title="projet-docker-web" \
      org.opencontainers.image.description="Application PHP articles (Apache + PHP 8.2)" \
      org.opencontainers.image.source="https://github.com/Spitchy/Projet_Final_Docker"

# Mises à jour de sécurité + curl (healthcheck) et ping (tests réseau).
# Les compilateurs fournis par l'image de base ($PHPIZE_DEPS) ne servent
# qu'à compiler les extensions (étape builder) : on les supprime ici,
# ce qui retire aussi linux-libc-dev (CVE critique signalée par Trivy).
# Le cache apt est nettoyé dans la même couche.
RUN apt-get update \
 && apt-get upgrade -y \
 && apt-get install -y --no-install-recommends curl iputils-ping \
 && apt-get purge -y --auto-remove $PHPIZE_DEPS libc6-dev linux-libc-dev \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Extensions compilées à l'étape builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini \
                    /usr/local/etc/php/conf.d/docker-php-ext-mysqli.ini \
                    /usr/local/etc/php/conf.d/

# Configuration PHP de production + surcharges
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --chmod=0644 web/php/app.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

# Configuration Apache : vhost, en-têtes de sécurité, modules inutiles désactivés
COPY --chmod=0644 web/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --chmod=0644 web/apache/security.conf /etc/apache2/conf-available/zz-security.conf
RUN a2enmod headers \
 && a2enconf zz-security \
 && a2dismod -f autoindex status

# Code source : propriété root, lecture seule pour www-data.
# Le DocumentRoot de l'image de base est en 1777, on le restreint.
COPY --chown=root:www-data --chmod=0640 src/ /var/www/html/
RUN chown root:www-data /var/www/html && chmod 0750 /var/www/html

# Exécution sans privilèges root
USER www-data

EXPOSE 80

HEALTHCHECK --interval=15s --timeout=5s --start-period=20s --retries=3 \
  CMD curl -fsS http://127.0.0.1/healthz.php || exit 1
