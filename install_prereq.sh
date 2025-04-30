#!/bin/bash

# Determine the directory of this script
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

echo "Starting installation of prerequisites..."

# Update package list
sudo apt update; sudo apt upgrade -y

# Install PHP and dependencies
echo "Installing PHP and required modules..."
sudo apt install -y php php-cli php-curl php-mbstring php-xml unzip

# Install Composer (PHP package manager)
if ! command -v composer &> /dev/null
then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
else
    echo "Composer is already installed!"
fi

# Ask the user to set a password for the PHP WebSocket server
echo "Please enter a password for the PHP WebSocket server (leave blank for default: securepassword):"
read -s USER_PASSWORD

# Use a default password if none is provided
if [ -z "$USER_PASSWORD" ]; then
    USER_PASSWORD="securepassword"
fi

# Update the password in server.php
SERVER_FILE="$SCRIPT_DIR/server.php"
if [ -f "$SERVER_FILE" ]; then
    echo "Setting password in server.php..."
    sed -i "s/private \$password = .*/private \$password = \"$USER_PASSWORD\";/" "$SERVER_FILE"
else
    echo "Warning: server.php not found. Ensure the password is set manually."
fi

# Install Ratchet for WebSockets
echo "Setting up Ratchet WebSocket library..."

# Navigate to the script's directory
cd "$SCRIPT_DIR"

# Initialize Composer project in silent mode with default values
if [ ! -f "composer.json" ]; then
    echo "Initializing Composer project with default values..."
    composer init --name=default/UploadServer --description="" --author="TalkL3ss" --type="" --license="MIT" -n
fi

# Require cboden/ratchet library
echo "Adding Ratchet WebSocket library as a dependency..."
composer require cboden/ratchet --quiet

# Install dependencies
echo "Installing dependencies..."
composer install --quiet

# Create uploads directory if it doesn't exist
UPLOADS_DIR="$SCRIPT_DIR/uploads"
if [ ! -d "$UPLOADS_DIR" ]; then
    echo "Creating uploads directory..."
    mkdir -p "$UPLOADS_DIR"
    chmod 777 "$UPLOADS_DIR"
fi

# Install Python (if missing)
echo "Checking Python installation..."
if ! command -v python3 &> /dev/null
then
    echo "Installing Python..."
    sudo apt install -y python3
else
    echo "Python is already installed!"
fi

echo "All prerequisites installed!"
