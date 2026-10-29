#!/usr/bin/env sh
# Génère docs/rapport-technique.pdf (avec les fichiers sources en annexe)
# et README.pdf, via l'image pandoc/latex : aucune installation locale requise.
#   ./docs/build-pdf.sh
set -eu

cd "$(dirname "$0")/.."

IMAGE=pandoc/latex:3.11
TMP=docs/.build
mkdir -p "$TMP"
trap 'rm -rf "$TMP"' EXIT

pandoc() {
  # la sortie de pandoc est filtrée (bruit de l'émulation amd64) sans masquer son code retour
  docker run --rm --platform linux/amd64 -v "$PWD:/data" -w /data "$IMAGE" "$@" \
    > "$TMP/pandoc.log" 2>&1 || { grep -v Ticker "$TMP/pandoc.log"; exit 1; }
}

# --- annexes : contenu réel des fichiers du dépôt ---------------------------
annex() { # annex <fichier> <langage>
  printf '\n## `%s`\n\n```%s\n' "$1" "$2"
  # lignes longues repliées pour tenir dans la largeur de la page
  fold -s -w 98 "$1"
  printf '\n```\n'
}

{
  printf '\n\\clearpage\n\n# Annexes — fichiers sources\n\n'
  printf 'Contenu des fichiers au moment de la génération de ce document (commit `%s`).\n' "$(git rev-parse --short HEAD)"
  annex web/Dockerfile dockerfile
  annex db/Dockerfile dockerfile
  annex docker-compose.yml yaml
  annex .github/workflows/docker.yml yaml
  annex .env.example bash
  annex scripts/init-secrets.sh bash
  annex .dockerignore bash
  annex db/.dockerignore bash
  annex web/apache/000-default.conf apache
  annex web/apache/security.conf apache
  annex web/php/app.ini ini
  annex db/conf.d/app.cnf ini
  annex db/init/01-articles.sql sql
  annex src/db-config.php php
  annex src/healthz.php php
  annex src/validation.php php
} > "$TMP/annexes.md"

cat docs/rapport-technique.md "$TMP/annexes.md" > "$TMP/rapport-complet.md"

# captures placées à l'endroit où elles sont citées (pas de flottants déplacés)
printf '%s\n' '\usepackage{float}' '\floatplacement{figure}{H}' > "$TMP/header.tex"

pandoc "$TMP/rapport-complet.md" -o docs/rapport-technique.pdf \
  --resource-path=docs --pdf-engine=xelatex --syntax-highlighting=tango \
  -H "$TMP/header.tex" -V geometry:margin=2cm -V colorlinks=true

# --- README : arborescence en ASCII (police LaTeX sans caractères de dessin) --
grep -v 'badge.svg' README.md \
  | sed -e 's/├── /|-- /g' -e 's/└── /`-- /g' -e 's/│   /|   /g' > "$TMP/readme.md"

pandoc -f markdown+gfm_auto_identifiers "$TMP/readme.md" -o README.pdf \
  --resource-path=. --pdf-engine=xelatex --syntax-highlighting=tango \
  -V geometry:margin=2cm -V colorlinks=true \
  -V lang=fr-FR --metadata pagetitle=README

echo "PDF générés : docs/rapport-technique.pdf, README.pdf"
