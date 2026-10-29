#!/usr/bin/env sh
# Prépare la configuration locale :
#  - copie .env.example en .env s'il n'existe pas
#  - génère des mots de passe aléatoires dans secrets/ (fichiers non versionnés)
set -eu

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
  cp .env.example .env
  echo ".env créé à partir de .env.example"
fi

mkdir -p secrets
for name in db_password db_root_password; do
  file="secrets/${name}.txt"
  if [ ! -s "$file" ]; then
    # 24 octets aléatoires encodés en base64, sans caractères spéciaux gênants
    head -c 24 /dev/urandom | base64 | tr -d '/+=\n' > "$file"
    echo "secret généré : $file"
  fi
  # lisible par l'utilisateur du conteneur (bind mount), modifiable par personne d'autre
  chmod 644 "$file"
done
