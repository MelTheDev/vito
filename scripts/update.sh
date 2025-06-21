#!/bin/bash

echo "Updating Vito..."

cd /home/vito/vito

echo "Pulling changes..."
git fetch --all

# Parse release type argument
INCLUDE_PRE_RELEASES=""

if [[ "$1" == "--alpha" ]]; then
    INCLUDE_PRE_RELEASES="alpha|beta|rc"
elif [[ "$1" == "--beta" ]]; then
    INCLUDE_PRE_RELEASES="beta|rc"
fi

echo "Checking out the latest tag..."

if [[ -n "$INCLUDE_PRE_RELEASES" ]]; then
    NEW_RELEASE=$(git tag -l "3.*" | grep -E "$INCLUDE_PRE_RELEASES|^[0-9]+\.[0-9]+\.[0-9]+$" | sort -Vr | head -n 1)
else
    NEW_RELEASE=$(git tag -l "3.*" | grep -E '^[0-9]+\.[0-9]+\.[0-9]+$' | sort -Vr | head -n 1)
fi

if [[ -z "$NEW_RELEASE" ]]; then
    echo "❌ No matching tag found."
    exit 1
fi

echo "Switching to tag: $NEW_RELEASE"
git checkout "$NEW_RELEASE"
git pull origin "$NEW_RELEASE"

echo "Installing composer dependencies..."
composer install --no-dev

echo "Installing npm packages..."
npm install
npm run build

echo "Running migrations..."
php artisan migrate --force

echo "Optimizing..."
php artisan optimize:clear
php artisan optimize

echo "Restarting workers..."
sudo supervisorctl restart worker:*

bash scripts/post-update.sh

echo "✅ Vito updated successfully to $NEW_RELEASE! 🎉"
