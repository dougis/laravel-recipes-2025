# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Application Overview

Laravel Recipes 2025 is a subscription-based recipe and cookbook management platform (85% complete) built with Laravel 11, Vue 3, and MongoDB. The application features clean architecture with repository patterns, service layers, and versioned APIs.

## Development Commands

### Backend Development
```bash
# Working directory is always: /src
cd src

# Core Laravel commands
php artisan serve                    # Start development server (http://localhost:8000)
php artisan migrate                  # Run database migrations
php artisan db:seed                  # Seed database with test data
php artisan key:generate             # Generate application key

# Testing
php artisan test                     # Run all tests
php artisan test --testsuite=Unit    # Run unit tests only
php artisan test --testsuite=Feature # Run feature tests only
php artisan test --coverage          # Run with coverage report

# Code quality
composer require --dev squizlabs/php_codesniffer && ./vendor/bin/phpcs --standard=PSR12 app/
composer require --dev phpstan/phpstan larastan/larastan && ./vendor/bin/phpstan analyse --memory-limit=2G
composer audit                       # Security audit

# Cache management
php artisan config:cache             # Cache configuration
php artisan route:cache              # Cache routes
php artisan view:cache               # Cache views
php artisan optimize                 # Optimize for production
```

### Frontend Development
```bash
# Frontend asset management
npm install                          # Install dependencies
npm run dev                          # Development with hot reload
npm run build                        # Production build
npm run preview                      # Preview production build

# Code quality
npm run lint                         # Run ESLint (requires configuration)
npm audit --audit-level=high         # Security audit
```

### Development Setup
```bash
# Initial setup sequence
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run dev  # In separate terminal
php artisan serve
```

## Architecture Overview

### Repository Pattern Implementation
- **Interfaces**: All repositories implement contracts in `app/Repositories/`
- **MongoDB Implementation**: Located in `app/Repositories/MongoDB/`
- **Service Binding**: Registered in `RepositoryServiceProvider`
- **Usage**: Controllers → Services → Repositories → Models

### API Structure
- **Base Path**: `/api/v1/` (versioned for future compatibility)
- **Authentication**: Laravel Sanctum with JWT tokens
- **Response Format**: Consistent JSON with success/data/message/meta structure
- **Route Files**: `routes/api_v1.php` for v1 endpoints

### Database Design (MongoDB)
- **Collections**: Users, Recipes, Cookbooks, Classifications, Sources, Meals, Courses, Preparations
- **Relationships**: ObjectId references between documents
- **Indexes**: Optimized for search (text indexes on name, ingredients, tags)
- **Privacy**: Boolean fields with subscription tier enforcement

### Subscription System
- **Tiers**: Free (0), Tier 1 (1), Tier 2 (2), Admin (100)
- **Limits**: Recipe counts, cookbook counts, privacy features by tier
- **Payment**: Stripe integration for subscription management

### Service Layer Organization
```
app/Services/
├── RecipeService.php      # Recipe CRUD and business logic
├── CookbookService.php    # Cookbook management
├── PDFService.php         # PDF generation
└── SubscriptionService.php # Stripe and tier management
```

### Frontend Architecture (Vue 3)
- **Components**: Modular Vue 3 with Composition API
- **State**: Pinia stores for auth, recipes, cookbooks
- **Styling**: Tailwind CSS utility-first approach
- **Icons**: Heroicons Vue integration
- **Routing**: Vue Router 4.x SPA navigation

## Testing Approach

### Test Structure (Setup Required - 0% Complete)
```
tests/
├── Unit/
│   ├── Models/           # Model validation and relationships
│   └── Services/         # Business logic testing
└── Feature/
    └── Api/V1/          # API endpoint testing
```

### Default Test Users (After db:seed)
- **Admin**: admin@example.com / password (full access)
- **Free**: free@example.com / password (25 recipes, 1 cookbook)
- **Tier 1**: tier1@example.com / password (unlimited public, 10 cookbooks)
- **Tier 2**: tier2@example.com / password (privacy controls, unlimited)

## Important Development Patterns

### Controller Structure
All API controllers in `app/Http/Controllers/Api/V1/` follow:
1. Request validation via Form Request classes
2. Service layer delegation for business logic
3. Consistent JSON response formatting
4. Authentication/authorization via middleware

### Repository Pattern Usage
```php
// Always inject interface, not implementation
public function __construct(RecipeRepositoryInterface $recipeRepository) {}

// Service layer handles business logic
// Repository handles data access only
```

### Authentication Flow
1. Login via `/api/v1/auth/login` returns JWT token
2. Include in requests: `Authorization: Bearer {token}`
3. Middleware handles user resolution and subscription checks

### Privacy Implementation
- Public/private toggle available for Tier 2+ users
- Admin override capability for all content access
- Privacy checked at repository level for consistent enforcement

## Environment Requirements

### Required Extensions
- PHP 8.3+ with: mongodb, redis, gd, curl, mbstring, openssl, pdo, tokenizer, xml
- Node.js 20.x with npm 10.x
- MongoDB 7.x for primary database
- Redis 7.x for caching and sessions (optional but recommended)

### Key Configuration
- MongoDB connection via `DB_CONNECTION=mongodb`
- Redis for cache/session performance
- Stripe keys for subscription features
- SMTP for email verification

## Docker Development

The project includes comprehensive Docker setup:
- `docker-compose.yml` for production
- `docker-compose.dev.yml` for development
- Services: Nginx, PHP-FPM, MongoDB, Redis, Queue Worker, Scheduler

## CI/CD Pipeline

GitHub Actions workflow includes:
1. **Code Quality**: PHP CS, PHPStan, ESLint, security audits
2. **Backend Tests**: Unit and feature tests with MongoDB/Redis services
3. **Frontend Tests**: Build verification and asset optimization
4. **Integration Tests**: Full Docker stack testing
5. **Security Scanning**: Trivy vulnerability scanning
6. **Deployment**: Automatic staging/production deployment on branch pushes

The pipeline expects tests to exist but they are not yet implemented (0% complete).