#!/bin/bash

# Determine the directory of this script
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

# Get the current IP address
IP_ADDRESS=$(hostname -I | awk '{print $1}')
HTML_FILE="$SCRIPT_DIR/myhtml/index.html"

echo "Detected IP Address: $IP_ADDRESS"

# Check if we have a previous backup of the HTML file
if [ ! -f "$HTML_FILE.bak" ]; then
    echo "Creating a backup of the original HTML file..."
    cp "$HTML_FILE" "$HTML_FILE.bak"
fi

# Replace WebSocket connection with the current IP
sed -i "s/ws:\/\/localhost:8080/ws:\/\/$IP_ADDRESS:8080/g" "$HTML_FILE"

# Start the PHP WebSocket server
echo "Starting PHP WebSocket server on ws://$IP_ADDRESS:8080..."
php "$SCRIPT_DIR/server.php" &

# Start the Python HTTP server
echo "Starting Python HTTP server on http://$IP_ADDRESS:8000..."
cd "$SCRIPT_DIR/myhtml/" && python3 -m http.server 8000 &

echo "Servers are running!"

# Wait for the user to stop the servers (CTRL+C)
trap 'cleanup' INT

cleanup() {
    echo "Restoring HTML file to localhost..."
    cp "$HTML_FILE.bak" "$HTML_FILE"
    echo "Stopping servers..."
    pkill -f server.php
    pkill -f "python3 -m http.server"
    echo "Cleanup done!"
    exit 0
}
