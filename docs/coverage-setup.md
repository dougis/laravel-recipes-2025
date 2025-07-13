# Coverage Reporting Setup Guide

This document provides comprehensive instructions for setting up and using code coverage reporting with Codacy integration for the Laravel Recipes 2025 project.

## Overview

The project uses **Codacy** for automated code quality analysis and test coverage reporting. Coverage data is collected from both backend (PHP/Laravel) and frontend (JavaScript/Vue.js) test suites and automatically uploaded to Codacy during CI/CD pipeline execution.

## Features

- ✅ **Automated Coverage Collection** - PHPUnit and Jest generate coverage reports
- ✅ **Multi-Format Support** - Clover XML for PHP, LCOV for JavaScript
- ✅ **CI/CD Integration** - Automatic upload during GitHub Actions workflow
- ✅ **Coverage Thresholds** - Enforce minimum coverage requirements
- ✅ **Quality Gates** - Block PRs with insufficient coverage
- ✅ **Combined Reporting** - Unified coverage metrics across languages

## Prerequisites

### Required Tools

- **PHP 8.3+** with Xdebug extension for coverage collection
- **Node.js 20.x** with npm for frontend dependencies
- **Codacy Account** with project configured
- **GitHub Secrets** configured with Codacy project token

### Environment Setup

```bash
# Ensure Xdebug is installed and enabled for PHP coverage
php -m | grep -i xdebug

# Install project dependencies
composer install
npm install
```

## Backend Coverage (PHP/Laravel)

### PHPUnit Configuration

The project is configured with comprehensive coverage settings in `phpunit.xml`:

```xml
<coverage>
    <report>
        <clover outputFile="coverage.xml"/>
        <html outputDirectory="coverage-html"/>
        <text outputFile="php://stdout"/>
    </report>
</coverage>
```

### Running Backend Coverage

```bash
# Run all tests with coverage
php artisan test --coverage

# Run specific test suites with coverage
php artisan test --testsuite=Unit --coverage-clover=coverage-unit.xml
php artisan test --testsuite=Feature --coverage-clover=coverage-feature.xml

# View coverage summary
cat coverage-summary.txt
```

### Coverage Files Generated

- `coverage-unit.xml` - Unit test coverage in Clover format
- `coverage-feature.xml` - Feature test coverage in Clover format  
- `coverage.xml` - Combined coverage report
- `coverage-html/` - HTML coverage report for local viewing
- `coverage-summary.txt` - Coverage percentage summary

## Frontend Coverage (JavaScript/Vue.js)

### Jest Configuration

Frontend coverage is configured in `jest.config.js`:

```javascript
{
  collectCoverageFrom: [
    'resources/js/**/*.{js,vue}',
    '!resources/js/app.js',
    '!resources/js/bootstrap.js',
    '!**/node_modules/**'
  ],
  coverageDirectory: 'coverage',
  coverageReporters: ['lcov', 'text', 'html'],
  coverageThreshold: {
    global: {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    }
  }
}
```

### Running Frontend Coverage

```bash
# Run frontend tests with coverage
npm run test:coverage

# View coverage report in browser
open coverage/index.html
```

### Coverage Files Generated

- `coverage/lcov.info` - LCOV format for Codacy upload
- `coverage/index.html` - Interactive HTML report
- `coverage/coverage-final.json` - JSON format coverage data

## Codacy Integration

### Automatic Upload Script

The project includes a comprehensive coverage upload script at `scripts/upload-coverage.sh`:

```bash
# Upload all coverage files to Codacy
./scripts/upload-coverage.sh upload

# Check environment and coverage files
./scripts/upload-coverage.sh check

# Install Codacy coverage reporter only
./scripts/upload-coverage.sh install
```

### Manual Upload

```bash
# Set your Codacy project token
export CODACY_PROJECT_TOKEN="your_project_token_here"

# Upload backend coverage
bash <(curl -Ls https://coverage.codacy.com/get.sh) report -l PHP -r coverage.xml

# Upload frontend coverage  
bash <(curl -Ls https://coverage.codacy.com/get.sh) report -l JavaScript -r coverage/lcov.info

# Finalize coverage report
bash <(curl -Ls https://coverage.codacy.com/get.sh) final
```

## CI/CD Pipeline Integration

### GitHub Actions Workflow

The CI/CD pipeline automatically handles coverage reporting:

**Backend Coverage Upload**

```yaml
- name: Upload coverage to Codacy
  working-directory: src
  env:
    CODACY_PROJECT_TOKEN: ${{ secrets.CODACY_PROJECT_TOKEN }}
  run: |
    if [[ -n "$CODACY_PROJECT_TOKEN" ]]; then
      chmod +x scripts/upload-coverage.sh
      ./scripts/upload-coverage.sh upload
    else
      echo "Skipping Codacy upload: CODACY_PROJECT_TOKEN not set"
    fi
  continue-on-error: true
```

**Frontend Coverage Upload**

```yaml
- name: Upload frontend coverage to Codacy
  working-directory: src
  env:
    CODACY_PROJECT_TOKEN: ${{ secrets.CODACY_PROJECT_TOKEN }}
  run: |
    if [[ -n "$CODACY_PROJECT_TOKEN" && -f "coverage/lcov.info" ]]; then
      chmod +x scripts/upload-coverage.sh
      ./scripts/upload-coverage.sh upload
    else
      echo "Skipping frontend Codacy upload: CODACY_PROJECT_TOKEN not set or coverage file missing"
    fi
  continue-on-error: true
```

### Environment Variables

Configure the following in your GitHub repository secrets:

- `CODACY_PROJECT_TOKEN` - Your Codacy project token for coverage uploads

## Coverage Thresholds

### Backend Thresholds

- **Unit Tests**: 80% minimum coverage required
- **Feature Tests**: 80% minimum coverage required
- **Combined Coverage**: 80% minimum for overall project

### Frontend Thresholds

- **Statements**: 80% minimum coverage
- **Branches**: 80% minimum coverage
- **Functions**: 80% minimum coverage
- **Lines**: 80% minimum coverage

## Troubleshooting

### Common Issues

**Coverage Not Generated**

```bash
# Check if Xdebug is enabled
php -m | grep -i xdebug

# Verify PHPUnit configuration
cat phpunit.xml | grep -A 10 "<coverage>"

# Run tests with verbose output
php artisan test --coverage --verbose
```

**Upload Failures**

```bash
# Check environment variable
echo $CODACY_PROJECT_TOKEN

# Verify coverage files exist
ls -la coverage*.xml coverage/lcov.info

# Test upload script manually
./scripts/upload-coverage.sh check
```

**Permission Issues**

```bash
# Make upload script executable
chmod +x scripts/upload-coverage.sh

# Check file permissions
ls -la scripts/upload-coverage.sh
```

### Debug Mode

Enable debug output for coverage upload:

```bash
# Set debug environment variable
export CODACY_DEBUG=true

# Run upload with debug output
./scripts/upload-coverage.sh upload
```

## Manual Setup Steps

### 1. Codacy Project Configuration

1. **Login to Codacy Dashboard**
   - Go to <https://app.codacy.com>
   - Connect your GitHub repository

2. **Generate Project Token**
   - Navigate to project settings
   - Go to "Integrations" → "Coverage"
   - Copy the project token

3. **Configure Coverage Settings**
   - Set coverage threshold to 80%
   - Enable coverage diff for pull requests
   - Configure notification preferences

### 2. GitHub Repository Configuration

1. **Add Repository Secret**
   - Go to repository Settings → Secrets and variables → Actions
   - Add new secret: `CODACY_PROJECT_TOKEN`
   - Paste your Codacy project token

2. **Enable Branch Protection**
   - Go to Settings → Branches
   - Add rule for main/develop branches
   - Require status checks: Codacy coverage checks

### 3. Local Development Setup

```bash
# Set up local environment variable (optional)
echo 'export CODACY_PROJECT_TOKEN="your_token_here"' >> ~/.bashrc
source ~/.bashrc

# Test local coverage generation
php artisan test --coverage
npm run test:coverage

# Test coverage upload (optional)
./scripts/upload-coverage.sh upload
```

## Monitoring and Maintenance

### Regular Checks

- Monitor coverage trends in Codacy dashboard
- Review coverage reports for uncovered code
- Update coverage thresholds as project matures
- Maintain Jest and PHPUnit configurations

### Coverage Reports

- **Daily**: Automatic uploads via CI/CD
- **Weekly**: Review coverage trends and quality gates
- **Monthly**: Analyze uncovered code and update tests

## Best Practices

1. **Write Tests First** - Follow TDD approach for better coverage
2. **Monitor Trends** - Track coverage changes over time
3. **Quality Over Quantity** - Focus on meaningful test coverage
4. **Regular Reviews** - Review uncovered code periodically
5. **Threshold Management** - Adjust thresholds as project evolves

## Additional Resources

- [Codacy Documentation](https://docs.codacy.com/)
- [PHPUnit Coverage Documentation](https://phpunit.readthedocs.io/en/9.5/code-coverage-analysis.html)
- [Jest Coverage Documentation](https://jestjs.io/docs/code-coverage)
- [Laravel Testing Guide](https://laravel.com/docs/testing)

---

**Note**: This setup ensures comprehensive code coverage analysis and maintains high code quality standards throughout the development lifecycle.
