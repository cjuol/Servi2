#!/bin/bash

# Script helper para ejecutar comandos artisan dentro del contenedor Docker
# Uso: ./artisan.sh <comando>
# Ejemplo: ./artisan.sh migrate

cd "$(dirname "$0")"

docker compose -f enviroment/docker-compose.yml exec web php artisan "$@"
