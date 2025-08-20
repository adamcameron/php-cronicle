#!/bin/bash

# Script to restart the Symfony messenger worker
# Combines: status check -> kill existing worker -> start new worker

echo "Checking messenger status..."
STATUS_OUTPUT=$(docker exec php symfony server:status)
echo "$STATUS_OUTPUT"

PID=$(echo "$STATUS_OUTPUT" | grep -o 'PID [0-9]*' | grep -o '[0-9]*')

if [ -n "$PID" ]; then
    echo "Found messenger worker with PID: $PID"
    echo "Killing worker..."
    docker exec php bash -c "kill $PID"

    # Give it a moment to clean up
    sleep 2

    echo "Worker killed. Starting new messenger worker..."
else
    echo "No messenger worker found running. Starting new worker..."
fi

echo "Starting messenger:consume with schedule watcher..."
docker exec php symfony run -d --watch=/tmp/symfony/schedule-last-updated.dat php bin/console messenger:consume

echo "New messenger worker started. Checking status..."
sleep 1
docker exec php symfony server:status
