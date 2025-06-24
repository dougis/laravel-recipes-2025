# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel Recipes 2025 is a subscription-based recipe and cookbook management platform built with Laravel 11, Vue 3, and MongoDB. The application features clean architecture with repository patterns, service layers, and versioned APIs. The project is currently **85% complete** with comprehensive test infrastructure recently implemented.

**Key Features:**

- Recipe management with ingredients, instructions, and nutritional information
- Cookbook creation and organization with recipe collections
- Subscription-based tier system (Free, Tier 1, Tier 2, Admin)
- Privacy controls for recipe sharing (Tier 2+ feature)
- PDF export functionality for recipes and cookbooks
- Advanced search and filtering capabilities
- Admin dashboard for user and content management

## Technology Stack

- **Backend:** Laravel 11 with MongoDB integration, Repository pattern
- **Frontend:** Vue 3 with Composition API, Tailwind CSS, Heroicons
- **Database:** MongoDB 7.x with optimized indexes for search
- **Authentication:** Laravel Sanctum with JWT tokens
- **Payment:** Stripe integration for subscription management
- **Deployment:** Docker containers with Nginx, PHP-FPM, Redis
- **Testing:** PHPUnit with MongoDB/Redis test services

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
use codacy mcp server to examine the code and fix any issues it finds

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
use codacy mcp server to examine the code and fix any issues it finds
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

## Git Workflow

### Branching Strategy

This project follows a **GitHub Flow** branching strategy optimized for continuous deployment:

- **`main`** - Production-ready code, protected branch
- **Feature branches** - `feature/issue-{number}-{short-description}` (e.g., `feature/issue-15-recipe-search`)
- **Bugfix branches** - `bugfix/issue-{number}-{short-description}` (e.g., `bugfix/issue-23-login-validation`)
- **Hotfix branches** - `hotfix/critical-{description}` (for urgent production fixes)

### Development Workflow

1. **Start New Work**

   ```bash
   # Create and switch to new feature branch from main
   git checkout main
   git pull origin main
   git checkout -b feature/issue-{number}-{description}

   # Push branch to remote immediately
   git push -u origin feature/issue-{number}-{description}
   ```

2. **Development Process**

   ```bash
   # Make changes and commit frequently with descriptive messages
   git add .
   git commit -m "feat(recipes): add advanced search functionality

   - Implement ingredient-based filtering
   - Add nutritional information search
   - Update API endpoints for search parameters
   - Add comprehensive test coverage

   Relates to #15"

   # Push changes regularly
   git push origin feature/issue-{number}-{description}
   ```

3. **Quality Checks Before PR**

   ```bash
   # Run all quality checks locally
   cd src
   php artisan test             # All backend tests must pass
   composer audit               # Security check
   npm run lint                 # Fix any linting issues
   npm run build                # Verify production build
   npm audit --audit-level=high # Frontend security check
   use codacy mcp server to examine the code and fix any issues it finds
   ```

4. **Create Pull Request**

   ```bash
   # Create PR using GitHub CLI (preferred)
   gh pr create --title "feat(recipes): add advanced search functionality" \
                --body "Implements advanced search capabilities as specified in #15"

   # Wait for checks to complete and handle results automatically
   sleep 60  # Wait for CI/CD checks to start and potentially complete
   
   # Check PR status and handle accordingly
   PR_STATUS=$(gh pr view --json statusCheckRollup --jq '.statusCheckRollup[].state' | sort | uniq)
   
   if [[ "$PR_STATUS" == "SUCCESS" ]]; then
     # All checks passed - merge automatically
     gh pr merge --auto --squash
     echo "✅ PR merged automatically - all checks passed"
   elif [[ "$PR_STATUS" =~ "PENDING" ]]; then
     # Some checks still running - wait longer
     echo "⏳ Checks still running, waiting additional 60 seconds..."
     sleep 60
     # Re-check and merge if all pass, otherwise address issues
   else
     echo "❌ Some checks failed - address issues before merging"
     gh pr view --json statusCheckRollup --jq '.statusCheckRollup[] | select(.state != "SUCCESS")'
   fi
   ```

5. **Automated Code Review Process**
   - **Automatic Merging**: PRs are automatically merged when all checks pass
   - **Required Checks**: Build, tests, linting, security audits, Codacy quality gates
   - **Manual Review Override**: Can be disabled for critical changes requiring human review
   - **Check Monitoring**: System waits for checks to complete before making merge decisions
   - **Failure Handling**: Failed checks must be addressed before re-attempting merge

6. **Merge and Cleanup**
   ```bash
   # After successful merge (automatic or manual), clean up locally
   git checkout main
   git pull origin main
   git branch -d feature/issue-{number}-{description}
   git remote prune origin
   ```

### Commit Message Standards

Follow **Conventional Commits** for consistent commit history:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Types:**

- `feat:` - New feature (recipes, cookbooks, search, etc.)
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks
- `perf:` - Performance improvements

**Examples:**

```bash
feat(recipes): add PDF export functionality
fix(auth): resolve JWT token expiration handling
docs: update API documentation for cookbook endpoints
test(recipes): add comprehensive search validation tests
perf(database): optimize MongoDB indexes for faster search
```

### Branch Protection Rules

**Main Branch Protection** (configured via GitHub settings):

- ✅ Require pull request reviews (minimum 1 approval)
- ✅ Dismiss stale reviews when new commits are pushed
- ✅ Require status checks to pass before merging
- ✅ Require branches to be up to date before merging
- ✅ Restrict pushes that create files larger than 100MB
- ✅ Do not allow force pushes
- ✅ Do not allow deletions

**Required Status Checks:**

- ✅ Backend tests pass (`php artisan test`)
- ✅ Frontend build succeeds (`npm run build`)
- ✅ Code quality checks pass (PHPStan, ESLint)
- ✅ Security audits pass (Composer, NPM)
- ✅ Codacy quality gate passes
- ✅ Performance tests pass (Lighthouse CI, Load Testing)

### Pull Request Guidelines

Use the provided PR template (`.github/pull_request_template.md`) which includes:

- **Summary** - Clear description of changes
- **Related Issue** - Link to GitHub issue
- **Type of Change** - Bug fix, feature, breaking change, etc.
- **Testing** - How changes were tested
- **Checklist** - Quality assurance items

### Emergency Procedures

**Hotfix Process:**

```bash
# For critical production issues
git checkout main
git pull origin main
git checkout -b hotfix/critical-{description}

# Make minimal fix
git commit -m "hotfix: resolve critical payment processing issue"
git push -u origin hotfix/critical-{description}

# Create emergency PR with expedited review
gh pr create --title "HOTFIX: Critical payment processing issue" --label "hotfix"
```

**Rollback Process:**

```bash
# If needed, revert to previous stable commit
git revert {commit-hash}
git push origin main
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

### Test Structure (Recently Implemented - 95% Complete)
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

### Testing Strategy

- Unit tests for all service layer business logic
- Feature tests for all API endpoints
- Model tests for validation and relationships
- Integration tests for Stripe payment flows
- Performance tests for search and pagination

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
1. **Code Quality**: PHP CS, PHPStan, ESLint, security audits, Codacy scanning
2. **Backend Tests**: Unit and feature tests with MongoDB/Redis services
3. **Frontend Tests**: Build verification and asset optimization
4. **Integration Tests**: Full Docker stack testing
5. **Security Scanning**: Trivy vulnerability scanning
6. **Performance Tests**: Lighthouse CI, Load Testing, Database Performance
7. **Deployment**: Automatic staging/production deployment on branch pushes

## Key Business Context

### Subscription Tiers

- **Free (Tier 0):** 25 recipes, 1 cookbook, public content only
- **Tier 1 ($9.99/month):** Unlimited public recipes, 10 cookbooks
- **Tier 2 ($19.99/month):** Privacy controls, unlimited recipes/cookbooks
- **Admin (Tier 100):** Full system access, user management

### Competitive Advantages

- Clean, modern Laravel 11 architecture with repository patterns
- Vue 3 SPA with optimized user experience
- MongoDB performance optimization for recipe search
- Comprehensive PDF export functionality
- Mobile-responsive design optimized for kitchen use

## Development Workflow

### Code Conventions

- Use PHP 8.3+ features and strict typing
- Follow Laravel conventions and best practices
- Implement proper error handling and validation
- Use Vue 3 Composition API for frontend components
- Follow MongoDB best practices for document design

### Quality Gates

- All new features must include comprehensive tests
- Code coverage requirements for critical business logic
- Performance validation for search and database operations
- Mobile responsiveness validation
- Security audit compliance

## Current Status

- ✅ **Core Platform Complete:** Recipe and cookbook management fully implemented
- ✅ **Test Infrastructure:** Comprehensive test suite recently completed
- ✅ **Performance Monitoring:** GitHub Actions performance tests operational
- 🔄 **Documentation Updates:** API documentation and deployment guides in progress
- 📋 **Remaining Work:** Minor UI polish and advanced search features

**Project Completion:** ~85% complete with production-ready foundation

**Next Actions:** Focus on remaining UI enhancements and performance optimizations