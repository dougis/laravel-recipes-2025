# Laravel Recipes 2025 🍳

A modernized recipe and cookbook management platform built with Laravel 11, Vue 3, and MongoDB. Features subscription tiers, mobile-first design, and comprehensive recipe management capabilities.

![Project Status](https://img.shields.io/badge/Status-85%25%20Complete-green)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green)
![MongoDB](https://img.shields.io/badge/MongoDB-7.x-green)
![License](https://img.shields.io/badge/License-MIT-blue)

## ✨ Features

### Core Functionality
- 🗂️ **Recipe Management** - Create, edit, organize, and search recipes with fuzzy matching
- 📚 **Cookbook Creation** - Compile recipes into custom cookbooks with table of contents
- 🔍 **Advanced Search** - MongoDB-powered search with intelligent ranking
- 📱 **Mobile-First Design** - Responsive design optimized for all devices
- 🔐 **Privacy Controls** - Public/private recipes and cookbooks (Tier 2+ users)
- 📄 **PDF Export** - Professional recipe and cookbook printing with custom templates
- 🏷️ **Smart Categorization** - Organize by classification, meal type, course, and preparation method

### Subscription Tiers
- **Free Tier** - Up to 25 public recipes, 1 public cookbook, basic search
- **Tier 1** - Unlimited public recipes, 10 cookbooks, advanced search, PDF export
- **Tier 2** - Everything + privacy controls, unlimited cookbooks, advanced features

### Technical Features
- 🔒 **Secure Authentication** - Laravel Sanctum with JWT tokens
- 💳 **Stripe Integration** - Subscription management and secure payments
- 🏗️ **Clean Architecture** - Repository pattern, service layers, versioned APIs
- 📊 **MongoDB Integration** - Modern NoSQL database with optimized indexes
- 🎨 **Tailwind CSS** - Modern utility-first CSS framework
- 🔄 **API Versioning** - Future-proof API design starting with v1

## 🚀 Quick Start

### Prerequisites
- **PHP 8.3+** with extensions: mongodb, redis, gd, curl, mbstring, openssl, pdo, tokenizer, xml
- **Composer 2.x** for PHP dependency management
- **Node.js 20.x** and **npm 10.x** for frontend build tools
- **MongoDB 7.x** for data persistence
- **Redis 7.x** (optional, for caching and queues)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/dougis/laravel-recipes-2025.git
   cd laravel-recipes-2025/src
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   # Copy environment file
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

5. **Configure MongoDB** 
   
   Update your `.env` file with MongoDB connection details:
   ```env
   # Database Configuration
   DB_CONNECTION=mongodb
   DB_HOST=127.0.0.1
   DB_PORT=27017
   DB_DATABASE=laravel_recipes
   DB_USERNAME=
   DB_PASSWORD=
   
   # Optional: Redis for caching and queues
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   
   # Mail Configuration (for email verification)
   MAIL_MAILER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   
   # Stripe Configuration (for subscriptions)
   STRIPE_KEY=your_stripe_publishable_key
   STRIPE_SECRET=your_stripe_secret_key
   ```

6. **Database setup**
   ```bash
   # Run MongoDB migrations and create indexes
   php artisan migrate
   
   # Seed database with sample data and test users
   php artisan db:seed
   ```

7. **Build frontend assets**
   ```bash
   # Development build with hot reload
   npm run dev
   
   # Or production build
   npm run build
   ```

8. **Start the development server**
   ```bash
   # Start Laravel development server
   php artisan serve
   
   # In another terminal, start Vite dev server for hot reload
   npm run dev
   ```

9. **Access the application**
   - **Web Interface**: http://localhost:8000
   - **API Base URL**: http://localhost:8000/api/v1

### Default Test Users
After running `php artisan db:seed`, you can login with these test accounts:

- **Admin User**: 
  - Email: `admin@example.com`
  - Password: `password`
  - Access: Full system access, can manage all users and override privacy settings

- **Free Tier User**: 
  - Email: `free@example.com`
  - Password: `password`
  - Limits: 25 public recipes, 1 public cookbook

- **Tier 1 User**: 
  - Email: `tier1@example.com`
  - Password: `password`
  - Features: Unlimited public recipes, 10 cookbooks, PDF export

- **Tier 2 User**: 
  - Email: `tier2@example.com`
  - Password: `password`
  - Features: All features + privacy controls, unlimited cookbooks

## 🏗️ Project Structure

```
laravel-recipes-2025/
├── docs/                           # 📚 Project documentation
│   ├── product_requirements.md     # Complete feature specifications
│   ├── technical_specification.md  # Architecture details
│   └── Laravel Recipes 2025 - Project Status.md
├── src/                           # 🏠 Main Laravel application
│   ├── app/
│   │   ├── Console/               # Artisan commands
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/ # 🎛️ Versioned API controllers
│   │   │   ├── Middleware/        # Custom middleware
│   │   │   └── Requests/          # Form request validation
│   │   ├── Models/                # 🗃️ MongoDB Eloquent models
│   │   ├── Services/              # 🔧 Business logic services
│   │   ├── Repositories/          # 💾 Data access layer
│   │   └── Providers/             # Service providers
│   ├── config/                    # Laravel configuration
│   ├── database/
│   │   ├── migrations/            # MongoDB migrations
│   │   └── seeders/               # Database seeders
│   ├── resources/
│   │   ├── js/                    # ⚡ Vue.js frontend (planned)
│   │   ├── css/                   # 🎨 Tailwind CSS (planned)
│   │   └── views/                 # Blade templates
│   ├── routes/
│   │   ├── api.php                # Main API routing
│   │   ├── api_v1.php             # Version 1 API routes
│   │   └── web.php                # Web routes
│   └── tests/                     # 🧪 Test suite (planned)
├── docker/                        # 🐳 Docker configuration
└── scripts/                       # 🛠️ Development scripts
```

## 🛠️ Technology Stack

### Backend (Fully Implemented)
- **Framework**: Laravel 11.x with PHP 8.3+
- **Database**: MongoDB 7.x with Laravel MongoDB package
- **Authentication**: Laravel Sanctum with JWT tokens
- **Search**: Laravel Scout with MongoDB driver for fuzzy search
- **PDF Generation**: Laravel DomPDF with custom templates
- **Payments**: Stripe API integration for subscriptions
- **Caching**: Redis for application caching and queue management
- **Architecture**: Repository pattern, service layers, dependency injection

### Frontend (Implemented)
- **Framework**: Vue.js 3.x with Composition API
- **Styling**: Tailwind CSS for responsive design
- **Build Tool**: Vite for development and building
- **Icons**: Modern icon system

### Development Tools
- **Local Environment**: Laravel Sail (Docker) or traditional LAMP/LEMP
- **Database Management**: MongoDB Compass
- **API Testing**: Postman, Insomnia, or built-in Laravel tools
- **Code Quality**: PHP CS Fixer, ESLint, Prettier
- **Debugging**: Laravel Telescope, Laravel Debugbar

## 📖 API Documentation

The API follows RESTful principles with version-based routing (`/api/v1/`). All endpoints require proper authentication except for public recipe/cookbook access.

### Authentication Endpoints
```http
POST   /api/v1/auth/register          # User registration with email verification
POST   /api/v1/auth/login             # User login (returns JWT token)
POST   /api/v1/auth/logout            # User logout (invalidates token)
GET    /api/v1/auth/user              # Get authenticated user profile
POST   /api/v1/auth/password/email    # Send password reset email
POST   /api/v1/auth/password/reset    # Reset password with token
```

### Recipe Management
```http
GET    /api/v1/recipes                # List user's recipes
GET    /api/v1/recipes/public         # List all public recipes
POST   /api/v1/recipes                # Create new recipe
GET    /api/v1/recipes/{id}           # Get recipe details
PUT    /api/v1/recipes/{id}           # Update recipe
DELETE /api/v1/recipes/{id}           # Delete recipe
PUT    /api/v1/recipes/{id}/privacy   # Toggle privacy (Tier 2+ only)
GET    /api/v1/recipes/search?q=term  # Fuzzy search recipes
```

### Cookbook Management
```http
GET    /api/v1/cookbooks              # List user's cookbooks
GET    /api/v1/cookbooks/public       # List all public cookbooks
POST   /api/v1/cookbooks              # Create new cookbook
GET    /api/v1/cookbooks/{id}         # Get cookbook with recipes
PUT    /api/v1/cookbooks/{id}         # Update cookbook
DELETE /api/v1/cookbooks/{id}         # Delete cookbook
POST   /api/v1/cookbooks/{id}/recipes # Add recipes to cookbook
DELETE /api/v1/cookbooks/{id}/recipes/{recipe_id} # Remove recipe
PUT    /api/v1/cookbooks/{id}/recipes/order       # Reorder recipes
```

### PDF Export & Printing
```http
GET    /api/v1/recipes/{id}/print     # Generate printable recipe PDF
GET    /api/v1/cookbooks/{id}/print   # Generate cookbook PDF with TOC
GET    /api/v1/recipes/{id}/export/{format}      # Export recipe (pdf, txt)
GET    /api/v1/cookbooks/{id}/export/{format}    # Export cookbook
```

### Metadata & Classification
```http
GET    /api/v1/classifications        # Recipe classifications
GET    /api/v1/sources                # Recipe sources
GET    /api/v1/meals                  # Meal types
GET    /api/v1/courses                # Course types
GET    /api/v1/preparations           # Preparation methods
```

### User & Subscription Management
```http
GET    /api/v1/users/profile          # Get user profile
PUT    /api/v1/users/profile          # Update user profile
GET    /api/v1/users/subscription     # Get subscription details
POST   /api/v1/users/subscription     # Update subscription (Stripe)
```

### Admin Endpoints (Admin users only)
```http
GET    /api/v1/admin/users            # List all users
GET    /api/v1/admin/users/{id}       # Get user details
PUT    /api/v1/admin/users/{id}       # Update user
PUT    /api/v1/admin/users/{id}/override # Toggle admin override
GET    /api/v1/admin/statistics       # System statistics
```

### API Response Format
All API responses follow a consistent JSON format:
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed successfully",
  "meta": {
    "pagination": {...},
    "api_version": "v1"
  }
}
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Structure (Planned)
- **Unit Tests**: Model validation, service logic, repository methods
- **Feature Tests**: API endpoints, authentication flows, subscription management
- **Browser Tests**: End-to-end user journeys with Laravel Dusk

## 🚢 Deployment

### Laravel Forge (Recommended)
1. Connect your server to Laravel Forge
2. Configure MongoDB and Redis
3. Set up SSL certificate
4. Configure environment variables
5. Deploy from GitHub repository

### Manual Deployment
1. **Server Requirements**:
   - Ubuntu 20.04+ or CentOS 8+
   - Nginx or Apache
   - PHP 8.3+ with required extensions
   - MongoDB 7.x
   - Redis 7.x
   - SSL certificate

2. **Deployment Steps**:
   ```bash
   # Clone repository
   git clone https://github.com/dougis/laravel-recipes-2025.git
   cd laravel-recipes-2025/src
   
   # Install dependencies
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   
   # Configure environment
   cp .env.example .env
   # Edit .env with production settings
   
   # Set up database
   php artisan migrate --force
   php artisan db:seed --force
   
   # Optimize for production
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   
   # Set permissions
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

## 🤝 Contributing

1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Follow coding standards**
   - PSR-12 for PHP
   - Vue.js style guide for frontend
   - Write tests for new features
4. **Commit your changes**
   ```bash
   git commit -m 'Add some amazing feature'
   ```
5. **Push to the branch**
   ```bash
   git push origin feature/amazing-feature
   ```
6. **Open a Pull Request**

### Development Guidelines
- Follow Laravel best practices and conventions
- Use the repository pattern for data access
- Write comprehensive tests for new features
- Update documentation for API changes
- Ensure mobile-first responsive design

## 📋 Current Development Status

| Component | Status | Completion |
|-----------|--------|------------|
| **Backend API** | ✅ Complete | 100% |
| **Authentication & Auth** | ✅ Complete | 100% |
| **Recipe Management** | ✅ Complete | 100% |
| **Cookbook Management** | ✅ Complete | 100% |
| **PDF Generation** | ✅ Complete | 100% |
| **Search Implementation** | ✅ Complete | 100% |
| **Admin Features** | ✅ Complete | 100% |
| **Subscription System** | ✅ Complete | 90% |
| **Frontend UI** | ✅ Complete | 95% |
| **Testing Suite** | ⏳ Planned | 0% |
| **Docker Setup** | ⏳ Planned | 0% |

## 🗺️ Development Roadmap

### Phase 1: Testing & Quality Assurance (2-3 weeks)
- [ ] Comprehensive test suite (Unit, Feature, Browser)
- [ ] API documentation with OpenAPI/Swagger
- [ ] Performance optimization and profiling
- [ ] Security audit and code review

### Phase 2: Production Deployment (1-2 weeks)
- [ ] Docker containerization
- [ ] CI/CD pipeline setup
- [ ] Production environment configuration
- [ ] Monitoring and logging setup

### Phase 3: Advanced Features (4-6 weeks)
- [ ] Recipe image uploads and processing
- [ ] Advanced search filters and faceted search
- [ ] Recipe scaling and unit conversion
- [ ] Meal planning and calendar integration

### Future Enhancements
- [ ] Mobile applications (iOS/Android)
- [ ] Social features (sharing, reviews, community)
- [ ] AI-powered recipe recommendations
- [ ] Recipe import from external websites

## 📊 Feature Access by Subscription Tier

| Feature | Free | Tier 1 | Tier 2 | Admin |
|---------|------|--------|--------|-------|
| Recipe Creation | 25 max | Unlimited | Unlimited | Unlimited |
| Recipe Privacy | Public only | Public only | Public/Private | All access |
| Cookbooks | 1 max | 10 max | Unlimited | Unlimited |
| Cookbook Privacy | Public only | Public only | Public/Private | All access |
| PDF Export | Recipes only | Full export | Full export | Full export |
| Advanced Search | Basic | Full | Full | Full |
| Nutritional Info | ❌ | ✅ | ✅ | ✅ |
| Admin Override | ❌ | ❌ | ❌ | ✅ |

## 🔧 Troubleshooting

### Common Issues

**MongoDB Connection Error**
```bash
# Check MongoDB status
mongosh --eval "db.runCommand({connectionStatus: 1})"

# Restart MongoDB
sudo systemctl restart mongod
```

**Composer Install Issues**
```bash
# Clear composer cache
composer clear-cache

# Install with verbose output
composer install -v
```

**Frontend Build Errors**
```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

**Permission Issues**
```bash
# Fix Laravel directory permissions
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Author

**Doug** - [dougis](https://github.com/dougis)

## 🙏 Acknowledgments

- **Laravel Team** - For the robust and elegant PHP framework
- **MongoDB Team** - For the flexible and powerful document database
- **Vue.js Team** - For the progressive and intuitive frontend framework
- **Tailwind CSS** - For the utility-first CSS framework
- **Stripe** - For secure and reliable payment processing
- **Open Source Community** - For the countless packages and tools that make this possible

---

⭐ **Star this repository if you find it helpful!**

🐛 **Found a bug?** [Open an issue](https://github.com/dougis/laravel-recipes-2025/issues)

💡 **Have a feature request?** [Start a discussion](https://github.com/dougis/laravel-recipes-2025/discussions)

📧 **Questions?** Feel free to reach out or check the [documentation](./docs/)
