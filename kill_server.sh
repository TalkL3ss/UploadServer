#!/bin/bash

echo "Stopping both servers..."

# Kill PHP WebSocket server
pkill -f server.php

# Kill Python HTTP server
pkill -f "python3 -m http.server"

echo "Servers have been stopped!"

