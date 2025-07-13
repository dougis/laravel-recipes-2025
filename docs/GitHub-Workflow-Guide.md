# GitHub Issue Implementation Workflow Guide

## Overview

This document outlines the standardized process for implementing GitHub issues in the Laravel Recipes 2025 project. Following this workflow ensures consistent, high-quality development practices and maintainable code.

## Prerequisites

- Git configured with appropriate credentials
- Local development environment set up
- Access to the GitHub repository: `dougis/laravel-recipes-2025`
- Understanding of Laravel, Vue.js, and MongoDB technologies used in this project

## Issue Implementation Workflow

### Step 1: Issue Analysis and Preparation

#### 1.1 Read and Understand the Issue

```bash
# Connect to GitHub and fetch issue details
GET /repos/dougis/laravel-recipes-2025/issues/{issue_number}
```

**Key Information to Extract:**

- Issue title and description
- Acceptance criteria (checklist items)
- Scope and technical requirements
- Files to create/modify
- Labels and priority level

#### 1.2 Verify Issue Status

- Ensure issue is open and not already assigned
- Check for any dependencies on other issues
- Confirm issue is ready for implementation

### Step 2: Branch Creation and Setup

#### 2.1 Prepare Local Repository

```bash
# Ensure we're on main branch and up to date
cd "Z:\dev\Code\laravel-recipes-2025"
git checkout main
git pull origin main
```

#### 2.2 Create Feature Branch

```bash
# Create descriptive branch name following convention
git checkout -b feature/issue-{number}-{short-description}

# Example:
git checkout -b feature/issue-2-service-unit-tests
```

#### 2.3 Push Branch to Remote

```bash
# Push branch to establish remote tracking
git push -u origin feature/issue-{number}-{short-description}
```

**Branch Naming Convention:**

- `feature/issue-{number}-{kebab-case-description}`
- `bugfix/issue-{number}-{kebab-case-description}`
- `enhancement/issue-{number}-{kebab-case-description}`

### Step 3: Implementation Process

#### 3.1 Examine Existing Code Structure

```bash
# Understand current project structure
list_directory("path/to/relevant/code")
read_file("path/to/key/files", length=50)  # Read key files to understand context
```

#### 3.2 Implement Changes Incrementally

**Implementation Principles:**

- **Single Responsibility**: Each commit should address one logical change
- **Test-Driven**: Write tests before or alongside implementation
- **Documentation**: Include comments and documentation for complex logic
- **Error Handling**: Account for edge cases and error conditions

#### 3.3 Regular Commits

```bash
# Commit frequently with descriptive messages
git add [specific-files]
git commit -m "Add [specific functionality]

- [Bullet point describing change 1]
- [Bullet point describing change 2]
- [Additional context if needed]"
```

**Commit Message Format:**

```
[Action] [Component/Feature] with [brief description]

- Specific change 1
- Specific change 2
- Additional context if needed
```

**Example:**

```
Add RecipeServiceTest with comprehensive unit tests

- Test all CRUD operations with mocked repository
- Test privacy controls and subscription tier enforcement
- Test search functionality and access control logic
- Test text generation for export functionality
```

### Step 4: Code Quality Standards

#### 4.1 Testing Requirements

- **Unit Tests**: Mock dependencies, test business logic in isolation
- **Feature Tests**: Test API endpoints with real database interactions
- **Component Tests**: Test Vue components with proper mocking
- **Edge Cases**: Test error conditions and boundary cases

#### 4.2 Code Organization

```php
// PHP/Laravel Standards
- Follow PSR-12 coding standards
- Use proper namespacing
- Implement repository pattern for data access
- Use service classes for business logic
- Proper exception handling
```

```javascript
// Vue.js/Frontend Standards
- Use Composition API consistently
- Proper component organization
- State management with Pinia
- Responsive design with Tailwind CSS
- Accessibility considerations
```

#### 4.3 Documentation Standards

- PHPDoc comments for all public methods
- README updates for new features
- API endpoint documentation
- Code comments for complex business logic

### Step 5: Pull Request Creation

#### 5.1 Final Push

```bash
# Ensure all changes are committed and pushed
git push origin feature/issue-{number}-{short-description}
```

#### 5.2 Create Pull Request

```bash
# Use GitHub API to create PR
POST /repos/dougis/laravel-recipes-2025/pulls
```

**PR Template:**

```markdown
## Summary
Brief description of what this PR implements.

## Changes Made
- ✅ **Component 1**: Description of changes
  - Specific implementation detail 1
  - Specific implementation detail 2

- ✅ **Component 2**: Description of changes
  - Specific implementation detail 1
  - Specific implementation detail 2

## Technical Details
- Implementation approach
- Key architectural decisions
- Any trade-offs or considerations

## Test Coverage
- Types of tests added
- Coverage areas
- Edge cases handled

## Files Added/Modified
- `path/to/file1.php`
- `path/to/file2.vue`
- `path/to/file3.js`

Closes #{issue_number}
```

### Step 6: Review and Merge

#### 6.1 Self-Review Checklist

- [ ] All acceptance criteria met
- [ ] Code follows project standards
- [ ] Tests pass and provide good coverage
- [ ] No console errors or warnings
- [ ] Documentation updated as needed
- [ ] No hardcoded values or security issues

#### 6.2 Merge Process

```bash
# Merge the pull request
PUT /repos/dougis/laravel-recipes-2025/pulls/{pull_number}/merge
```

#### 6.3 Issue Closure

```bash
# Close the related issue
PATCH /repos/dougis/laravel-recipes-2025/issues/{issue_number}
# Set state: "closed"
```

#### 6.4 Local Cleanup

```bash
# Switch back to main and pull latest changes
git checkout main
git pull origin main

# Delete local feature branch (optional)
git branch -d feature/issue-{number}-{short-description}
```

## Project-Specific Guidelines

### Laravel Recipes 2025 Standards

#### File Structure

```
src/
├── app/
│   ├── Http/Controllers/Api/V1/     # API controllers (versioned)
│   ├── Models/                      # MongoDB models
│   ├── Services/                    # Business logic
│   ├── Repositories/                # Data access layer
│   └── Http/Requests/Api/V1/        # Form validation
├── tests/
│   ├── Unit/                        # Unit tests
│   ├── Feature/                     # Integration tests
│   └── Browser/                     # End-to-end tests
└── resources/
    ├── js/                          # Vue.js frontend
    └── views/                       # Blade templates
```

#### Technology Stack

- **Backend**: Laravel 11.x with MongoDB
- **Frontend**: Vue 3 with Composition API
- **Styling**: Tailwind CSS with custom design system
- **Testing**: PHPUnit (backend), Vitest (frontend)
- **Database**: MongoDB with jenssegers/mongodb package

#### Subscription Tier Implementation

```php
// Always check subscription access in controllers
if (!$user->canCreateRecipe()) {
    return response()->json([
        'status' => 'error',
        'message' => 'You have reached your recipe limit.'
    ], 403);
}

// Use helper methods in User model
$user->hasTier1Access()    // Tier 1+ features
$user->hasTier2Access()    // Tier 2+ features
$user->isAdmin()           // Admin override
```

#### API Versioning

- All API endpoints use `/api/v1/` prefix
- Controllers organized in `Api/V1/` namespace
- Separate route files for each version
- Maintain backward compatibility

### Common Issue Types

#### Testing Issues

- **Unit Tests**: Focus on mocking dependencies and testing business logic
- **Feature Tests**: Test complete API workflows with database
- **Component Tests**: Test Vue components with proper props/events

#### Feature Implementation

- **CRUD Operations**: Follow repository pattern with service layer
- **API Endpoints**: Include proper validation, authorization, and error handling
- **Frontend Components**: Use Composition API with proper state management

#### Infrastructure Issues

- **Docker**: Multi-container setup with PHP, MongoDB, Redis, Nginx
- **CI/CD**: GitHub Actions with automated testing and deployment
- **Documentation**: OpenAPI/Swagger for API, README for setup

## Troubleshooting

### Common Git Issues

```bash
# If branch push fails due to authentication
# Check GitHub token permissions

# If merge conflicts occur
git checkout main
git pull origin main
git checkout feature/branch-name
git rebase main
# Resolve conflicts, then continue
git rebase --continue
```

### Development Environment Issues

```bash
# If MongoDB connection fails
# Check .env configuration:
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=laravel_recipes

# If tests fail due to missing dependencies
composer install
npm install
```

### Code Quality Issues

- Run `php artisan test` before committing
- Use `npm run lint` for frontend code checking
- Ensure proper PHPDoc comments on all public methods
- Follow PSR-12 standards for PHP code

## Best Practices Summary

1. **Read the Issue Thoroughly**: Understand requirements before coding
2. **Plan Before Implementing**: Consider architecture and dependencies
3. **Commit Frequently**: Small, logical commits with clear messages
4. **Test Comprehensively**: Unit tests, feature tests, edge cases
5. **Document Changes**: Update README, API docs, and code comments
6. **Review Your Work**: Self-review before creating PR
7. **Clean Up**: Merge, close issue, and clean up local branches

## Templates

### Issue Analysis Template

```markdown
## Issue: #{number} - {title}

**Scope:**
- [ ] {requirement 1}
- [ ] {requirement 2}

**Files to Create/Modify:**
- {file 1}
- {file 2}

**Dependencies:**
- {dependency 1}
- {dependency 2}

**Acceptance Criteria:**
- [ ] {criteria 1}
- [ ] {criteria 2}
```

### Commit Message Template

```
{Action} {component} {brief description}

- {Specific change 1}
- {Specific change 2}
- {Additional context if needed}
```

### PR Description Template

```markdown
## Summary
{Brief description of changes}

## Changes Made
- ✅ **{Component}**: {Description}

## Technical Details
- {Key implementation details}

## Files Added/Modified
- {List of files}

Closes #{issue_number}
```

---

*This workflow guide ensures consistent, high-quality implementation of GitHub issues in the Laravel Recipes 2025 project. Follow these steps for reliable, maintainable development practices.*
