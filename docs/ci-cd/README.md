# CI/CD Pipeline Documentation

This document provides comprehensive information about the Continuous Integration and Continuous Deployment (CI/CD) pipeline for Laravel Recipes 2025.

## 📋 Table of Contents

- [Pipeline Overview](#pipeline-overview)
- [Workflow Components](#workflow-components)
- [Environment Setup](#environment-setup)
- [Deployment Process](#deployment-process)
- [Monitoring and Alerting](#monitoring-and-alerting)
- [Troubleshooting](#troubleshooting)
- [Best Practices](#best-practices)

## 🏗️ Pipeline Overview

The CI/CD pipeline is implemented using GitHub Actions and consists of multiple workflows designed to ensure code quality, security, and reliable deployments.

### Pipeline Architecture

```mermaid
graph TB
    A[Code Push] --> B[Code Quality Checks]
    B --> C[Backend Tests]
    B --> D[Frontend Tests]
    C --> E[Integration Tests]
    D --> E
    E --> F[Security Scan]
    F --> G{Branch?}
    G -->|develop| H[Deploy to Staging]
    G -->|main| I[Deploy to Production]
    H --> J[Performance Tests]
    I --> K[Create Release]
    J --> L[Staging Verification]
    K --> M[Production Monitoring]
```

### Key Features

- ✅ **Automated Testing** - Unit, Feature, and Integration tests
- 🔒 **Security Scanning** - Vulnerability detection and code analysis
- 📊 **Performance Monitoring** - Lighthouse CI and load testing
- 🚀 **Zero-Downtime Deployments** - Blue-green deployment strategy
- 📈 **Automated Dependency Updates** - Security patches and updates
- 🔄 **Environment Parity** - Consistent environments across dev/staging/prod

## 🔧 Workflow Components

### 1. Main CI/CD Workflow (`ci-cd.yml`)

**Triggers:**

- Push to `main` or `develop` branches
- Pull requests to `main` or `develop`
- Manual trigger via `workflow_dispatch`

**Jobs:**

#### Code Quality & Security

```yaml
- PHP CodeSniffer (PSR-12 compliance)
- PHPStan (static analysis)
- ESLint (JavaScript linting)
- Security audits (Composer & NPM)
```

#### Backend Testing

```yaml
- Unit tests with coverage
- Feature tests with API validation
- Database integration tests
- Coverage reporting
```

#### Frontend Testing

```yaml
- JavaScript/Vue component tests
- Asset compilation verification
- Browser compatibility checks
```

#### Integration Testing

```yaml
- Docker container testing
- End-to-end API testing
- Database connectivity verification
- Service health checks
```

#### Security Scanning

```yaml
- Trivy vulnerability scanner
- Dependency vulnerability checks
- SARIF report generation
- GitHub Security Advisory integration
```

#### Deployment

```yaml
- Staging deployment (develop branch)
- Production deployment (main branch)
- Zero-downtime deployment strategy
- Automated rollback capabilities
```

### 2. Dependency Updates (`dependencies.yml`)

**Schedule:** Daily at 2 AM UTC

**Features:**

- Automated Composer dependency updates
- NPM package security updates
- Automated pull request creation
- Security vulnerability reporting

### 3. Performance Monitoring (`performance.yml`)

**Schedule:** Every 6 hours

**Components:**

- Lighthouse CI performance testing
- Load testing with Artillery
- Database performance benchmarks
- Performance regression detection

## 🌍 Environment Setup

### Required Secrets

Configure these secrets in your GitHub repository settings:

#### Container Registry

```
REGISTRY_URL=your-registry.com
REGISTRY_USERNAME=deploy-user
REGISTRY_PASSWORD=secure-password
```

#### Staging Environment

```
STAGING_HOST=staging.laravel-recipes.com
STAGING_USERNAME=deploy
STAGING_SSH_KEY=-----BEGIN OPENSSH PRIVATE KEY-----
```

#### Production Environment

```
PRODUCTION_HOST=laravel-recipes.com
PRODUCTION_USERNAME=deploy
PRODUCTION_SSH_KEY=-----BEGIN OPENSSH PRIVATE KEY-----
```

#### Monitoring & Notifications

```
SLACK_WEBHOOK_URL=https://hooks.slack.com/...
LHCI_GITHUB_APP_TOKEN=github_token_here
```

### Environment Variables

Each environment requires specific configuration:

#### Development (.env.dev)

```env
APP_ENV=local
APP_DEBUG=true
DB_DATABASE=laravel_recipes_dev
QUEUE_CONNECTION=sync
```

#### Staging (.env.staging)

```env
APP_ENV=staging
APP_DEBUG=true
DB_DATABASE=laravel_recipes_staging
QUEUE_CONNECTION=redis
```

#### Production (.env.production)

```env
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=laravel_recipes
QUEUE_CONNECTION=redis
```

## 🚀 Deployment Process

### Staging Deployment

**Trigger:** Push to `develop` branch

**Process:**

1. Code quality and security checks
2. Automated testing suite
3. Docker image building
4. Staging environment deployment
5. Database migration and seeding
6. Smoke testing
7. Performance validation
8. Slack notification

**Access:**

- URL: <https://staging.laravel-recipes.com>
- Duration: ~10-15 minutes
- Rollback: Automatic on failure

### Production Deployment

**Trigger:** Push to `main` branch

**Process:**

1. All staging deployment steps
2. Additional security validation
3. Production Docker image building
4. Blue-green deployment strategy
5. Database migration (careful)
6. Cache warming
7. Health checks
8. Release creation
9. Monitoring alerts setup

**Access:**

- URL: <https://laravel-recipes.com>
- Duration: ~15-20 minutes
- Rollback: Manual with automated scripts

### Manual Deployment

For emergency deployments or special cases:

```bash
# Trigger manual deployment
gh workflow run ci-cd.yml --ref main

# Check deployment status
gh run list --workflow=ci-cd.yml

# View deployment logs
gh run view [run-id] --log
```

## 📊 Monitoring and Alerting

### Automated Monitoring

#### Application Health

- API endpoint availability
- Database connectivity
- Redis cache functionality
- Queue worker status

#### Performance Metrics

- Response time monitoring
- Lighthouse performance scores
- Database query performance
- Load testing results

#### Security Monitoring

- Vulnerability scanning
- Dependency security alerts
- SSL certificate expiration
- Access pattern anomalies

### Alert Channels

#### Slack Integration

```json
{
  "deployment_success": "#deployments",
  "deployment_failure": "#alerts",
  "security_issues": "#security",
  "performance_issues": "#performance"
}
```

#### GitHub Issues

- Automatic issue creation for failures
- Performance regression reports
- Security vulnerability reports
- Dependency update summaries

### Health Check Endpoints

#### Application Health

```
GET /health
Response: { "status": "ok", "timestamp": "..." }
```

#### Database Health

```
GET /health/database
Response: { "mongodb": "connected", "redis": "connected" }
```

#### Detailed Health

```
GET /health/detailed
Response: {
  "app": "ok",
  "database": "ok", 
  "cache": "ok",
  "queue": "ok",
  "storage": "ok"
}
```

## 🔧 Troubleshooting

### Common Issues

#### Test Failures

**Symptoms:**

- CI pipeline fails at testing stage
- Specific test cases failing

**Resolution:**

```bash
# Run tests locally
./setup-dev.sh
docker-compose -f docker-compose.dev.yml exec app php artisan test

# Check specific test
docker-compose -f docker-compose.dev.yml exec app php artisan test --filter=TestName

# Debug with verbose output
docker-compose -f docker-compose.dev.yml exec app php artisan test --verbose
```

#### Deployment Failures

**Symptoms:**

- Deployment pipeline fails
- Application not accessible after deployment

**Resolution:**

```bash
# Check deployment logs
gh run view [run-id] --log

# Manual rollback
cd /opt/laravel-recipes
docker-compose down
git checkout previous-working-commit
docker-compose up -d

# Check application health
./scripts/health-check.sh
```

#### Database Migration Issues

**Symptoms:**

- Migration fails during deployment
- Database schema inconsistencies

**Resolution:**

```bash
# Check migration status
docker-compose exec app php artisan migrate:status

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Fix migration and redeploy
git commit -m "fix: migration issue"
git push origin main
```

#### Performance Degradation

**Symptoms:**

- Lighthouse scores below threshold
- Load test failures

**Resolution:**

```bash
# Check current performance
npm run lighthouse

# Profile application
docker-compose exec app php artisan telescope:install
docker-compose exec app php artisan vendor:publish --tag=telescope-assets

# Optimize application
docker-compose exec app php artisan optimize
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
```

### Debug Commands

#### Pipeline Debugging

```bash
# View workflow runs
gh run list

# View specific run details
gh run view [run-id]

# Download run artifacts
gh run download [run-id]

# Cancel running workflow
gh run cancel [run-id]
```

#### Application Debugging

```bash
# Access application container
docker-compose exec app bash

# View application logs
docker-compose logs -f app

# Check database connectivity
docker-compose exec app php artisan tinker
>>> DB::connection()->getMongoDB()->listCollections()

# Check queue status
docker-compose exec app php artisan queue:monitor
```

### Performance Optimization

#### Database Optimization

```bash
# Analyze slow queries
docker-compose exec mongodb mongo --eval "db.setProfilingLevel(2)"

# Check index usage
docker-compose exec app php artisan tinker
>>> DB::connection()->getMongoDB()->explain()

# Optimize indexes
docker-compose exec app php artisan migrate:fresh
```

#### Application Optimization

```bash
# Enable OPcache
docker-compose exec app php -m | grep OPcache

# Cache configuration
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Optimize Composer autoloader
docker-compose exec app composer dump-autoload --optimize
```

## 📚 Best Practices

### Code Quality

#### Pre-commit Checks

```bash
# Install pre-commit hooks
composer require --dev phpstan/phpstan
npm install --save-dev eslint

# Run before committing
composer run phpstan
npm run lint
php artisan test
```

#### Branch Strategy

```
main (production)
├── develop (staging)
├── feature/issue-123-new-feature
├── bugfix/issue-456-critical-fix
└── hotfix/security-patch
```

### Testing Strategy

#### Test Coverage

- Minimum 80% code coverage
- All new features must include tests
- Critical paths require integration tests

#### Test Types

```php
// Unit Tests
tests/Unit/Services/RecipeServiceTest.php

// Feature Tests  
tests/Feature/Api/V1/RecipeControllerTest.php

// Browser Tests
tests/Browser/RecipeManagementTest.php
```

### Deployment Strategy

#### Environment Promotion

```
Development → Staging → Production
```

#### Rollback Strategy

```bash
# Automated rollback triggers
- Health check failures
- Performance degradation
- Error rate spike

# Manual rollback process
./scripts/rollback-production.sh [previous-version]
```

### Security Practices

#### Dependency Management

- Automated security updates
- Regular dependency audits
- Vulnerability scanning in CI

#### Secret Management

```bash
# Use GitHub Secrets for sensitive data
# Never commit secrets to repository
# Rotate secrets regularly
# Use environment-specific secrets
```

### Monitoring Best Practices

#### Key Metrics

- Response time (< 2 seconds)
- Error rate (< 1%)
- Availability (> 99.9%)
- Database performance (< 100ms queries)

#### Alerting Rules

```yaml
Critical: 
  - Application down > 1 minute
  - Error rate > 5%
  - Response time > 5 seconds

Warning:
  - Response time > 2 seconds  
  - Error rate > 1%
  - Disk usage > 80%
```

## 🔄 Continuous Improvement

### Regular Reviews

#### Weekly

- Review failed deployments
- Analyze performance trends
- Update dependencies

#### Monthly

- Review security alerts
- Optimize pipeline performance
- Update documentation

#### Quarterly

- Security audit
- Performance baseline review
- Infrastructure cost analysis
- Tool and process evaluation

### Pipeline Evolution

#### Planned Improvements

- [ ] Advanced blue-green deployment
- [ ] Canary deployment strategy
- [ ] A/B testing integration
- [ ] Advanced monitoring dashboards
- [ ] Automated performance regression detection

---

For questions about the CI/CD pipeline, please:

1. Check this documentation
2. Review workflow logs in GitHub Actions
3. Create an issue with the `ci-cd` label
4. Contact the DevOps team via Slack

*This documentation is maintained by the development team and updated with each pipeline change.*
