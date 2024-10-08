#!/bin/sh

set -eux

echo "Installing chromium..."
sudo apt-get update
sudo apt-get -y install --no-install-recommends chromium

# Copy the welcome message
sudo cp .devcontainer/welcome-message.txt /usr/local/etc/vscode-dev-containers/first-run-notice.txt