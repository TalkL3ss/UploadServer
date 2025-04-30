#!/bin/bash

echo "Starting installation of prerequisites..."

# Update package list
sudo apt update

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

# Install Ratchet for WebSockets
echo "Setting up Ratchet WebSocket library..."
mkdir -p /opt/myphp
cd /opt/myphp
composer require cboden/ratchet

# Create uploads directory
mkdir -p /opt/myphp/uploads
chmod 777 /opt/myphp/uploads

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

