#!/bin/bash

# Laravel Recipes 2025 - Production Deployment Script
# This script handles zero-downtime production deployments

set -e

# Configuration
APP_NAME="laravel-recipes-2025"
DEPLOY_USER="deploy"
DEPLOY_PATH="/opt/$APP_NAME"
BACKUP_PATH="/opt/backups/$APP_NAME"
LOG_FILE="/var/log/$APP_NAME-deploy.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as correct user
if [ "$USER" != "$DEPLOY_USER" ]; then
    error "This script must be run as the $DEPLOY_USER user"
fi

# Check if required tools are available
command -v docker >/dev/null 2>&1 || error "Docker is required but not installed"
command -v docker-compose >/dev/null 2>&1 || error "Docker Compose is required but not installed"

log "🚀 Starting production deployment for $APP_NAME"

# Step 1: Pre-deployment checks
log "🔍 Running pre-deployment checks..."

# Check disk space
DISK_USAGE=$(df "$DEPLOY_PATH" | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 80 ]; then
    warning "Disk usage is at ${DISK_USAGE}%. Consider cleaning up before deployment."
fi

# Check if services are running
if ! docker-compose -f "$DEPLOY_PATH/docker-compose.yml" ps | grep -q "Up"; then
    error "Application services are not running. Please check the current deployment."
fi

# Step 2: Create backup
log "💾 Creating backup of current deployment..."
BACKUP_DIR="$BACKUP_PATH/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Backup database
log "📦 Backing up MongoDB database..."
docker-compose -f "$DEPLOY_PATH/docker-compose.yml" exec -T mongodb mongodump --out "/backup/$(date +%Y%m%d_%H%M%S)" || error "Database backup failed"

# Backup application files
log "📂 Backing up application files..."
cp -r "$DEPLOY_PATH"/{.env,docker-compose.yml} "$BACKUP_DIR/" || error "File backup failed"

success "✅ Backup completed: $BACKUP_DIR"

# Step 3: Pull latest changes
log "📥 Pulling latest code from repository..."
cd "$DEPLOY_PATH"
git fetch origin
git checkout main
git pull origin main || error "Failed to pull latest changes"

# Step 4: Build new images
log "🏗️ Building new Docker images..."
docker-compose build --no-cache app || error "Failed to build application image"

# Step 5: Update dependencies
log "📦 Updating application dependencies..."
docker-compose run --rm app composer install --no-interaction --optimize-autoloader --no-dev || error "Failed to install Composer dependencies"

# Step 6: Build frontend assets
log "🎨 Building frontend assets..."
docker-compose run --rm app npm ci --only=production || error "Failed to install NPM dependencies"
docker-compose run --rm app npm run build || error "Failed to build frontend assets"

# Step 7: Run database migrations
log "🗄️ Running database migrations..."
docker-compose exec -T app php artisan migrate --force || error "Database migration failed"

# Step 8: Cache optimization
log "⚡ Optimizing application cache..."
docker-compose exec -T app php artisan config:cache || error "Failed to cache config"
docker-compose exec -T app php artisan route:cache || error "Failed to cache routes"
docker-compose exec -T app php artisan view:cache || error "Failed to cache views"
docker-compose exec -T app php artisan event:cache || error "Failed to cache events"

# Step 9: Restart services (zero-downtime)
log "🔄 Performing zero-downtime restart..."

# Start new containers
docker-compose up -d --remove-orphans || error "Failed to start new containers"

# Wait for health checks
log "⏳ Waiting for services to be healthy..."
sleep 30

# Verify application is responding
if ! curl -f -s http://localhost/api/v1/recipes/public > /dev/null; then
    error "Application health check failed. Rolling back..."
fi

# Step 10: Restart queue workers
log "🔄 Restarting queue workers..."
docker-compose exec -T app php artisan queue:restart || warning "Failed to restart queue workers"

# Step 11: Post-deployment verification
log "✅ Running post-deployment verification..."

# Check service status
docker-compose ps

# Check application logs for errors
if docker-compose logs --tail=50 app | grep -i error; then
    warning "Errors found in application logs. Please investigate."
fi

# Check database connectivity
docker-compose exec -T app php artisan tinker --execute="DB::connection()->getMongoDB()->listCollections(); echo 'Database connection OK';" || error "Database connectivity check failed"

# Step 12: Cleanup old images
log "🧹 Cleaning up old Docker images..."
docker image prune -f

# Step 13: Update deployment record
log "📝 Recording deployment information..."
DEPLOYMENT_INFO="Deployment completed at $(date) by $USER from commit $(git rev-parse HEAD)"
echo "$DEPLOYMENT_INFO" >> "$DEPLOY_PATH/deployments.log"

# Step 14: Send notifications (if configured)
if [ -n "$SLACK_WEBHOOK_URL" ]; then
    log "📢 Sending deployment notification..."
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"🚀 $APP_NAME deployed successfully to production\"}" \
        "$SLACK_WEBHOOK_URL" || warning "Failed to send Slack notification"
fi

# Final success message
success "🎉 Production deployment completed successfully!"
log "📊 Deployment Summary:"
log "   - Backup created: $BACKUP_DIR"
log "   - Git commit: $(git rev-parse HEAD)"
log "   - Deployment time: $(date)"
log "   - Services status: $(docker-compose ps --services | wc -l) containers running"

log "🔗 Application URLs:"
log "   - Main App: https://your-domain.com"
log "   - API Health: https://your-domain.com/api/v1/recipes/public"

log "💡 Next steps:"
log "   - Monitor application logs: docker-compose logs -f"
log "   - Check performance metrics"
log "   - Verify user-facing functionality"
log "   - Monitor error rates and response times"

log "✨ Deployment completed successfully!"
