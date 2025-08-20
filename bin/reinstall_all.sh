#!/bin/bash
set -e

docker compose -f docker/docker-compose.yml down --volumes --remove-orphans
docker compose -f docker/docker-compose.yml build
docker compose -f docker/docker-compose.yml up --detach
