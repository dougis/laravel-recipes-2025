#!/bin/bash

# Laravel Recipes 2025 - Development Setup Script
# This script sets up the development environment using Docker

set -e

echo "🍳 Laravel Recipes 2025 - Development Setup"
echo "=========================================="

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Check if .env file exists, if not create from template
if [ ! -f ".env" ]; then
    echo "📄 Creating .env file from development template..."
    cp .env.dev .env
    echo "✅ .env file created. Please update the APP_KEY and other settings as needed."
else
    echo "✅ .env file already exists."
fi

# Generate Laravel application key if not set
if grep -q "APP_KEY=base64:GENERATE_NEW_KEY_HERE" .env; then
    echo "🔑 Generating Laravel application key..."
    # We'll generate this after the container is running
    echo "⚠️  Remember to generate a new APP_KEY after setup is complete."
fi

echo "🏗️  Building and starting development containers..."
docker-compose -f docker-compose.dev.yml up -d --build

echo "⏳ Waiting for services to be ready..."
sleep 10

# Check if MongoDB is ready
echo "🗄️  Checking MongoDB connection..."
until docker-compose -f docker-compose.dev.yml exec mongodb mongo --eval "print('MongoDB is ready')" > /dev/null 2>&1; do
    echo "   Waiting for MongoDB..."
    sleep 5
done
echo "✅ MongoDB is ready!"

# Check if Redis is ready
echo "🔄 Checking Redis connection..."
until docker-compose -f docker-compose.dev.yml exec redis redis-cli ping > /dev/null 2>&1; do
    echo "   Waiting for Redis..."
    sleep 2
done
echo "✅ Redis is ready!"

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
docker-compose -f docker-compose.dev.yml exec app composer install

# Generate application key if needed
if grep -q "APP_KEY=base64:GENERATE_NEW_KEY_HERE" .env; then
    echo "🔑 Generating Laravel application key..."
    docker-compose -f docker-compose.dev.yml exec app php artisan key:generate
fi

# Run database migrations
echo "🗄️  Running database migrations..."
docker-compose -f docker-compose.dev.yml exec app php artisan migrate --force

# Run database seeders
echo "🌱 Seeding database with initial data..."
docker-compose -f docker-compose.dev.yml exec app php artisan db:seed --force

# Install NPM dependencies and build assets
echo "🎨 Installing NPM dependencies and building assets..."
docker-compose -f docker-compose.dev.yml exec node npm install
docker-compose -f docker-compose.dev.yml exec node npm run build

# Create storage link
echo "🔗 Creating storage link..."
docker-compose -f docker-compose.dev.yml exec app php artisan storage:link

# Clear and cache configuration
echo "🧹 Clearing and caching configuration..."
docker-compose -f docker-compose.dev.yml exec app php artisan config:clear
docker-compose -f docker-compose.dev.yml exec app php artisan cache:clear
docker-compose -f docker-compose.dev.yml exec app php artisan view:clear

echo ""
echo "🎉 Development environment setup complete!"
echo ""
echo "🌐 Your application is available at:"
echo "   • Main App: http://localhost:8080"
echo "   • MongoDB Admin: http://localhost:8081 (admin/admin)"
echo "   • Redis Admin: http://localhost:8082"
echo "   • Vite Dev Server: http://localhost:5173"
echo ""
echo "📊 Database Access:"
echo "   • MongoDB: localhost:27018"
echo "   • Redis: localhost:6380"
echo ""
echo "🔧 Useful commands:"
echo "   • View logs: docker-compose -f docker-compose.dev.yml logs -f"
echo "   • Laravel shell: docker-compose -f docker-compose.dev.yml exec app bash"
echo "   • Run tests: docker-compose -f docker-compose.dev.yml exec app php artisan test"
echo "   • Stop services: docker-compose -f docker-compose.dev.yml down"
echo ""
echo "Happy coding! 🚀"
