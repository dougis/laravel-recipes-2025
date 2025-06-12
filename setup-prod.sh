#!/bin/bash

# Laravel Recipes 2025 - Production Setup Script
# This script sets up the production environment using Docker

set -e

echo "🍳 Laravel Recipes 2025 - Production Setup"
echo "========================================="

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
    echo "📄 Creating .env file from production template..."
    cp .env.docker .env
    echo "✅ .env file created."
    echo "⚠️  IMPORTANT: Please update the .env file with your production settings before continuing!"
    echo "   - Generate a new APP_KEY"
    echo "   - Set secure database passwords"
    echo "   - Configure Stripe keys"
    echo "   - Set your domain in APP_URL"
    read -p "Press Enter when you have updated the .env file..."
else
    echo "✅ .env file already exists."
fi

# Validate critical environment variables
if grep -q "GENERATE_NEW_KEY_HERE" .env; then
    echo "❌ Please generate a new APP_KEY in your .env file"
    exit 1
fi

if grep -q "secure_admin_password_here" .env; then
    echo "❌ Please set a secure MongoDB admin password in your .env file"
    exit 1
fi

if grep -q "secure_redis_password_here" .env; then
    echo "❌ Please set a secure Redis password in your .env file"
    exit 1
fi

echo "🏗️  Building and starting production containers..."
docker-compose up -d --build

echo "⏳ Waiting for services to be ready..."
sleep 15

# Check if MongoDB is ready
echo "🗄️  Checking MongoDB connection..."
until docker-compose exec mongodb mongo --eval "print('MongoDB is ready')" > /dev/null 2>&1; do
    echo "   Waiting for MongoDB..."
    sleep 5
done
echo "✅ MongoDB is ready!"

# Check if Redis is ready
echo "🔄 Checking Redis connection..."
until docker-compose exec redis redis-cli ping > /dev/null 2>&1; do
    echo "   Waiting for Redis..."
    sleep 2
done
echo "✅ Redis is ready!"

# Run database migrations
echo "🗄️  Running database migrations..."
docker-compose exec app php artisan migrate --force

# Run database seeders (only basic data for production)
echo "🌱 Seeding database with essential data..."
docker-compose exec app php artisan db:seed --class=MetadataSeeder --force
docker-compose exec app php artisan db:seed --class=SubscriptionSeeder --force

# Create storage link
echo "🔗 Creating storage link..."
docker-compose exec app php artisan storage:link

# Optimize application for production
echo "⚡ Optimizing application for production..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan event:cache

# Install and build frontend assets
echo "🎨 Building production assets..."
docker-compose exec app npm ci --only=production
docker-compose exec app npm run build

echo ""
echo "🎉 Production environment setup complete!"
echo ""
echo "🌐 Your application is available at:"
echo "   • Main App: http://localhost (or your configured domain)"
echo ""
echo "🔒 Security Checklist:"
echo "   ✅ Set strong passwords for MongoDB and Redis"
echo "   ✅ Configure SSL certificates for HTTPS"
echo "   ✅ Set up firewall rules"
echo "   ✅ Configure backup strategy"
echo "   ✅ Set up monitoring and logging"
echo ""
echo "🔧 Useful commands:"
echo "   • View logs: docker-compose logs -f"
echo "   • Laravel shell: docker-compose exec app bash"
echo "   • Monitor queues: docker-compose exec app php artisan queue:monitor"
echo "   • Stop services: docker-compose down"
echo ""
echo "🚀 Your Laravel Recipes 2025 application is now running in production!"
