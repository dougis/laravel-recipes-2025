#!/bin/bash

# Laravel Recipes 2025 - Staging Deployment Script
# This script deploys to staging environment for testing

set -e

# Configuration
APP_NAME="laravel-recipes-2025"
DEPLOY_USER="deploy"
DEPLOY_PATH="/opt/$APP_NAME-staging"
LOG_FILE="/var/log/$APP_NAME-staging-deploy.log"

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

log "🧪 Starting staging deployment for $APP_NAME"

# Step 1: Pull latest changes from develop branch
log "📥 Pulling latest code from develop branch..."
cd "$DEPLOY_PATH"
git fetch origin
git checkout develop
git pull origin develop || error "Failed to pull latest changes"

# Step 2: Create staging environment file
log "⚙️ Setting up staging environment..."
if [ ! -f ".env.staging" ]; then
    cp .env.dev .env.staging
    log "Created .env.staging from template"
fi
cp .env.staging .env

# Step 3: Build Docker images
log "🏗️ Building Docker images for staging..."
docker-compose -f docker-compose.dev.yml build --no-cache || error "Failed to build images"

# Step 4: Start services
log "🚀 Starting staging services..."
docker-compose -f docker-compose.dev.yml up -d || error "Failed to start services"

# Wait for services to be ready
log "⏳ Waiting for services to be ready..."
sleep 30

# Step 5: Install dependencies
log "📦 Installing dependencies..."
docker-compose -f docker-compose.dev.yml exec -T app composer install --no-interaction || error "Failed to install Composer dependencies"
docker-compose -f docker-compose.dev.yml exec -T node npm ci || error "Failed to install NPM dependencies"

# Step 6: Generate application key if needed
if grep -q "GENERATE_NEW_KEY_HERE" .env; then
    log "🔑 Generating application key..."
    docker-compose -f docker-compose.dev.yml exec -T app php artisan key:generate || error "Failed to generate app key"
fi

# Step 7: Run database migrations
log "🗄️ Running database migrations..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan migrate:fresh --force || error "Database migration failed"

# Step 8: Seed database with test data
log "🌱 Seeding database with test data..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan db:seed --force || error "Database seeding failed"

# Step 9: Build frontend assets
log "🎨 Building frontend assets..."
docker-compose -f docker-compose.dev.yml exec -T node npm run build || error "Failed to build frontend assets"

# Step 10: Clear caches
log "🧹 Clearing application caches..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan config:clear || error "Failed to clear config cache"
docker-compose -f docker-compose.dev.yml exec -T app php artisan cache:clear || error "Failed to clear cache"
docker-compose -f docker-compose.dev.yml exec -T app php artisan view:clear || error "Failed to clear view cache"

# Step 11: Run tests
log "🧪 Running test suite..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan test || warning "Some tests failed in staging"

# Step 12: Perform smoke tests
log "🔍 Running smoke tests..."

# Test API endpoints
if curl -f -s http://localhost:8080/api/v1/recipes/public > /dev/null; then
    success "✅ Public recipes API is responding"
else
    error "❌ Public recipes API is not responding"
fi

if curl -f -s http://localhost:8080/api/v1/cookbooks/public > /dev/null; then
    success "✅ Public cookbooks API is responding"
else
    error "❌ Public cookbooks API is not responding"
fi

# Test database connectivity
docker-compose -f docker-compose.dev.yml exec -T app php artisan tinker --execute="
DB::connection()->getMongoDB()->listCollections();
echo 'Database connection OK';
" || error "Database connectivity test failed"

# Step 13: Load test data for manual testing
log "📊 Loading test data for manual testing..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan tinker --execute="
use App\\Models\\User;
use App\\Models\\Recipe;
use App\\Models\\Cookbook;

// Create test users
\$freeUser = User::factory()->create([
    'name' => 'Free User',
    'email' => 'free@staging.test',
    'subscription_tier' => 0
]);

\$tier1User = User::factory()->create([
    'name' => 'Tier 1 User', 
    'email' => 'tier1@staging.test',
    'subscription_tier' => 1
]);

\$tier2User = User::factory()->create([
    'name' => 'Tier 2 User',
    'email' => 'tier2@staging.test', 
    'subscription_tier' => 2
]);

\$admin = User::factory()->create([
    'name' => 'Admin User',
    'email' => 'admin@staging.test',
    'subscription_tier' => 2,
    'admin_override' => true
]);

echo 'Test users created successfully';

// Create test recipes
Recipe::factory()->count(50)->create();
Cookbook::factory()->count(20)->create();

echo 'Test recipes and cookbooks created';
" || warning "Failed to create test data"

# Step 14: Generate deployment report
log "📝 Generating deployment report..."

DEPLOYMENT_REPORT="staging-deployment-$(date +%Y%m%d_%H%M%S).txt"

cat > "$DEPLOYMENT_REPORT" << EOF
Laravel Recipes 2025 - Staging Deployment Report
================================================

Deployment Date: $(date)
Git Branch: develop
Git Commit: $(git rev-parse HEAD)
Git Commit Message: $(git log -1 --pretty=%B)

Services Status:
$(docker-compose -f docker-compose.dev.yml ps)

Test Users Created:
- Free User: free@staging.test (password)
- Tier 1 User: tier1@staging.test (password) 
- Tier 2 User: tier2@staging.test (password)
- Admin User: admin@staging.test (password)

Access URLs:
- Main Application: http://localhost:8080
- MongoDB Admin: http://localhost:8081 (admin/admin)
- Redis Admin: http://localhost:8082
- API Documentation: http://localhost:8080/api/documentation

Test Data:
- 50 sample recipes created
- 20 sample cookbooks created
- Users across all subscription tiers

Manual Testing Checklist:
□ User registration and authentication
□ Recipe CRUD operations for each user tier
□ Cookbook CRUD operations for each user tier
□ Subscription tier enforcement
□ Privacy controls (Tier 2 users)
□ Admin functionality
□ Search functionality
□ PDF generation
□ Mobile responsiveness
□ Payment integration (test mode)

Next Steps:
1. Perform manual testing using the test accounts
2. Verify all features work as expected
3. Test subscription tier limitations
4. Validate UI/UX improvements
5. Run performance tests if needed
6. When ready, merge develop -> main for production
EOF

log "📄 Deployment report saved to: $DEPLOYMENT_REPORT"

# Step 15: Send notifications
if [ -n "$SLACK_WEBHOOK_URL" ]; then
    log "📢 Sending staging deployment notification..."
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"🧪 $APP_NAME deployed to staging - Ready for testing\n\n**Access URLs:**\n- App: http://staging.laravel-recipes.com\n- MongoDB: http://staging.laravel-recipes.com:8081\n- Redis: http://staging.laravel-recipes.com:8082\n\n**Test Users:**\n- free@staging.test\n- tier1@staging.test\n- tier2@staging.test\n- admin@staging.test\n\nAll passwords: \`password\`\"}" \
        "$SLACK_WEBHOOK_URL" || warning "Failed to send Slack notification"
fi

# Final success message
success "🎉 Staging deployment completed successfully!"

log "📊 Staging Environment Summary:"
log "   - Environment: Staging"
log "   - Branch: develop"
log "   - Commit: $(git rev-parse HEAD)"
log "   - Services: $(docker-compose -f docker-compose.dev.yml ps --services | wc -l) containers running"
log "   - Test Users: 4 users created with different subscription tiers"

log "🔗 Access Information:"
log "   - Main App: http://localhost:8080"
log "   - MongoDB Admin: http://localhost:8081 (admin/admin)"
log "   - Redis Admin: http://localhost:8082"

log "🧪 Testing Instructions:"
log "   - Use the test accounts to verify functionality"
log "   - Test subscription tier enforcement"
log "   - Verify privacy controls work correctly"
log "   - Test mobile responsiveness"
log "   - Validate payment flows (test mode)"

log "🔧 Useful Commands:"
log "   - View logs: docker-compose -f docker-compose.dev.yml logs -f"
log "   - Laravel shell: docker-compose -f docker-compose.dev.yml exec app bash"
log "   - Run tests: docker-compose -f docker-compose.dev.yml exec app php artisan test"
log "   - Stop services: docker-compose -f docker-compose.dev.yml down"

log "✨ Staging environment is ready for testing!"
