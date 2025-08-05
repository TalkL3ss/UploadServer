#!/bin/bash

# Get the directory of this script
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

echo "Starting servers from $SCRIPT_DIR..."

# Start the PHP WebSocket server
PHP_SERVER="$SCRIPT_DIR/server.php"
if [ -f "$PHP_SERVER" ]; then
    echo "Starting PHP WebSocket server..."
    php "$PHP_SERVER" > "$SCRIPT_DIR/php_server.log" 2>&1 &
    echo $! > "$SCRIPT_DIR/php_server.pid"
else
    echo "Error: server.php not found in $SCRIPT_DIR."
    exit 1
fi

# Start the Python HTTP server
UPLOADS_DIR="$SCRIPT_DIR/uploads"
if [ ! -d "$UPLOADS_DIR" ]; then
    echo "Creating uploads directory..."
    mkdir -p "$UPLOADS_DIR"
    chmod 777 "$UPLOADS_DIR"
fi

echo "Starting Python HTTP server..."
cd "$SCRIPT_DIR/myhtml/"
python3 -m http.server > "$SCRIPT_DIR/myhtml/python_http.log" 2>&1 &
echo $! > "$SCRIPT_DIR/python_http.pid"

echo "Servers started successfully!"
echo "Logs:"
echo "  - PHP WebSocket server: $SCRIPT_DIR/php_server.log"
echo "  - Python HTTP server: $SCRIPT_DIR/python_http.log"
