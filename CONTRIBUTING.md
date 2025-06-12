# Contributing to Laravel Recipes 2025

Thank you for your interest in contributing to Laravel Recipes 2025! This document provides guidelines and information for contributors.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contribution Guidelines](#contribution-guidelines)
- [Coding Standards](#coding-standards)
- [Testing Requirements](#testing-requirements)
- [Submitting Changes](#submitting-changes)
- [Review Process](#review-process)
- [Release Process](#release-process)

## 📜 Code of Conduct

By participating in this project, you agree to abide by our Code of Conduct. Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) before contributing.

## 🚀 Getting Started

### Prerequisites

- **PHP 8.3+** with extensions: mongodb, redis, gd, zip, bcmath
- **Node.js 20+** and npm
- **Docker & Docker Compose** (recommended for development)
- **Git** for version control

### Development Setup

1. **Fork and clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/laravel-recipes-2025.git
   cd laravel-recipes-2025
   ```

2. **Set up development environment**
   ```bash
   chmod +x setup-dev.sh
   ./setup-dev.sh
   ```

3. **Access the application**
   - Main App: http://localhost:8080
   - MongoDB Admin: http://localhost:8081
   - Redis Admin: http://localhost:8082

### Alternative Setup (Without Docker)

If you prefer not to use Docker, follow the manual setup instructions in [docs/manual-setup.md](docs/manual-setup.md).

## 🛠️ Contribution Guidelines

### Types of Contributions

We welcome the following types of contributions:

- 🐛 **Bug fixes**
- ✨ **New features**
- 📚 **Documentation improvements**
- 🧪 **Test additions**
- ⚡ **Performance optimizations**
- 🎨 **UI/UX improvements**
- 🔒 **Security enhancements**

### Before You Start

1. **Check existing issues** to avoid duplicate work
2. **Create an issue** for new features or significant changes
3. **Discuss your approach** in the issue before implementing
4. **Follow the project roadmap** and priorities

### Issue Labels

- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Improvements or additions to documentation
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention is needed
- `priority/high` - High priority issues
- `needs-discussion` - Requires team discussion

## 💻 Coding Standards

### PHP (Backend)

- **Standard**: PSR-12 coding style
- **Framework**: Laravel 11.x best practices
- **Database**: MongoDB with Eloquent ODM
- **Testing**: PHPUnit with Feature and Unit tests

#### PHP Code Style

```php
<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Collection;

class RecipeService
{
    public function __construct(
        private RecipeRepository $repository
    ) {}

    public function findPublicRecipes(int $limit = 10): Collection
    {
        return $this->repository
            ->where('is_private', false)
            ->limit($limit)
            ->get();
    }
}
```

#### Key Conventions

- Use **type hints** for all parameters and return types
- Use **constructor property promotion** when appropriate
- Follow **single responsibility principle**
- Use **dependency injection** over facades when possible
- Write **descriptive method and variable names**

### JavaScript/Vue (Frontend)

- **Framework**: Vue 3 with Composition API
- **Style Guide**: Vue.js official style guide
- **Linting**: ESLint with Vue rules
- **Formatting**: Prettier

#### Vue Component Example

```vue
<template>
  <div class="recipe-card">
    <h3 class="recipe-title">{{ recipe.name }}</h3>
    <p class="recipe-description">{{ recipe.description }}</p>
    <button @click="viewRecipe" class="btn btn-primary">
      View Recipe
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  recipe: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['view'])

const viewRecipe = () => {
  emit('view', props.recipe.id)
}
</script>
```

### CSS (Styling)

- **Framework**: Tailwind CSS 3.x
- **Approach**: Utility-first with custom components
- **Responsive**: Mobile-first design
- **Accessibility**: WCAG 2.1 AA compliance

## 🧪 Testing Requirements

### Backend Tests

All backend changes must include appropriate tests:

#### Unit Tests
```php
// tests/Unit/Services/RecipeServiceTest.php
public function test_can_find_public_recipes()
{
    Recipe::factory()->count(5)->create(['is_private' => false]);
    Recipe::factory()->count(3)->create(['is_private' => true]);

    $service = new RecipeService(new RecipeRepository());
    $recipes = $service->findPublicRecipes();

    $this->assertCount(5, $recipes);
    $this->assertTrue($recipes->every(fn($recipe) => !$recipe->is_private));
}
```

#### Feature Tests
```php
// tests/Feature/Api/V1/RecipeControllerTest.php
public function test_authenticated_user_can_create_recipe()
{
    $user = User::factory()->create();
    $recipeData = [
        'name' => 'Test Recipe',
        'ingredients' => 'Test ingredients',
        'instructions' => 'Test instructions',
    ];

    $response = $this->actingAs($user)
                     ->postJson('/api/v1/recipes', $recipeData);

    $response->assertStatus(201)
             ->assertJsonStructure(['data' => ['recipe']]);
}
```

### Frontend Tests

Frontend changes should include:

- **Component tests** using Vue Test Utils
- **Integration tests** for user workflows
- **E2E tests** for critical paths (using Cypress)

### Running Tests

```bash
# Backend tests
docker-compose -f docker-compose.dev.yml exec app php artisan test

# Frontend tests
docker-compose -f docker-compose.dev.yml exec node npm run test

# All tests
npm run test:all
```

## 📝 Submitting Changes

### Branch Naming Convention

- `feature/issue-123-add-recipe-scaling` - New features
- `bugfix/issue-456-fix-search-results` - Bug fixes
- `docs/update-api-documentation` - Documentation updates
- `refactor/optimize-recipe-queries` - Code refactoring
- `test/add-cookbook-tests` - Test additions

### Commit Message Format

```
type(scope): short description

Longer description if needed.

- Bullet point 1
- Bullet point 2

Closes #123
```

**Types**: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

**Examples**:
```
feat(recipes): add recipe scaling functionality

Allow users to scale recipe ingredients based on servings.
Includes validation for positive serving counts and proper
fraction handling for ingredient quantities.

- Add scaling logic to RecipeService
- Update recipe form with serving selector
- Add tests for scaling calculations

Closes #123
```

### Pull Request Process

1. **Create a branch** from `develop` for features, `main` for hotfixes
2. **Make your changes** following coding standards
3. **Add/update tests** to maintain coverage
4. **Update documentation** if needed
5. **Test your changes** thoroughly
6. **Create a pull request** using the template
7. **Address review feedback** promptly

### Pull Request Requirements

- [ ] Descriptive title and summary
- [ ] Links to related issues
- [ ] All tests passing
- [ ] Code coverage maintained
- [ ] Documentation updated
- [ ] Screenshots for UI changes
- [ ] Breaking changes documented

## 👥 Review Process

### Review Criteria

Reviewers will check for:

- **Functionality**: Does the code work as intended?
- **Code Quality**: Is the code clean, readable, and maintainable?
- **Testing**: Are there adequate tests with good coverage?
- **Security**: Are there any security vulnerabilities?
- **Performance**: Are there any performance implications?
- **Documentation**: Is the code properly documented?

### Review Timeline

- **Initial review**: Within 48 hours
- **Follow-up reviews**: Within 24 hours
- **Merge decision**: After all reviewers approve

### Getting Reviews

- **Request specific reviewers** if you know who should review
- **Tag maintainers** if you need urgent review
- **Be patient** - quality reviews take time
- **Be responsive** to feedback and questions

## 🚀 Release Process

### Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR.MINOR.PATCH** (e.g., 2.1.3)
- **MAJOR**: Breaking changes
- **MINOR**: New features (backwards compatible)
- **PATCH**: Bug fixes (backwards compatible)

### Release Schedule

- **Major releases**: Quarterly
- **Minor releases**: Monthly
- **Patch releases**: As needed for critical bugs

### Release Branches

- `main` - Production ready code
- `develop` - Development branch for next release
- `release/v2.1.0` - Release preparation branches

## 🎯 Development Workflow

### Feature Development

1. **Create issue** describing the feature
2. **Create branch** from `develop`
3. **Implement feature** with tests
4. **Create pull request** to `develop`
5. **Address review feedback**
6. **Merge when approved**

### Bug Fixes

1. **Create issue** describing the bug
2. **Create branch** from `develop` (or `main` for hotfixes)
3. **Fix bug** with regression tests
4. **Create pull request**
5. **Fast-track review** for critical bugs

### Hotfixes

1. **Create branch** from `main`
2. **Fix critical issue**
3. **Create pull request** to `main`
4. **Merge after review**
5. **Cherry-pick** to `develop`

## 📚 Documentation

### API Documentation

- Use **OpenAPI/Swagger** for API documentation
- Include **examples** for all endpoints
- Document **error responses**
- Keep documentation **up to date**

### Code Documentation

- Use **PHPDoc** for PHP classes and methods
- Write **JSDoc** for JavaScript functions
- Include **README** files for complex modules
- Add **inline comments** for complex logic

## 💡 Getting Help

### Communication Channels

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions and discussions
- **Email**: security@laravel-recipes.com for security issues

### Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [MongoDB Documentation](https://docs.mongodb.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

## 🏆 Recognition

Contributors will be recognized in:

- **CONTRIBUTORS.md** file
- **Release notes** for significant contributions
- **Hall of Fame** section in README

## 📄 License

By contributing to Laravel Recipes 2025, you agree that your contributions will be licensed under the same [MIT License](LICENSE) that covers the project.

---

Thank you for contributing to Laravel Recipes 2025! 🍳✨
