# Laravel Recipes 2025 🍳

A modernized recipe and cookbook management platform built with Laravel 11, Vue 3, and MongoDB. Features subscription tiers, mobile-first design, and comprehensive recipe management capabilities.

![Project Status](https://img.shields.io/badge/Status-85%25%20Complete-green)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green)
![MongoDB](https://img.shields.io/badge/MongoDB-7.x-green)
![License](https://img.shields.io/badge/License-MIT-blue)

## ✨ Features

### Core Functionality
- 🗂️ **Recipe Management** - Create, edit, organize, and search recipes
- 📚 **Cookbook Creation** - Compile recipes into custom cookbooks
- 🔍 **Advanced Search** - Fuzzy matching and intelligent ranking
- 📱 **Mobile-First Design** - Responsive design optimized for all devices
- 🔐 **Privacy Controls** - Public/private recipes and cookbooks (Tier 2+)
- 📄 **PDF Export** - Professional recipe and cookbook printing

### Subscription Tiers
- **Free Tier** - Up to 25 public recipes, 1 public cookbook
- **Tier 1** - Unlimited public recipes, 10 cookbooks, export features
- **Tier 2** - Everything + privacy controls, unlimited cookbooks, meal planning

### Technical Features
- 🔒 **Secure Authentication** - Laravel Sanctum with JWT
- 💳 **Stripe Integration** - Subscription management and payments
- 🏗️ **Clean Architecture** - Repository pattern, service layers
- 📊 **MongoDB Integration** - Modern NoSQL database
- 🎨 **Custom Design System** - Tailwind CSS with recipe-specific themes
- 🔄 **API Versioning** - Future-proof API design

## 🚀 Quick Start

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 20.x
- MongoDB 7.x
- Redis (optional, for caching)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/dougis/laravel-recipes-2025.git
   cd laravel-recipes-2025/src
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure MongoDB** (Update .env file)
   ```env
   DB_CONNECTION=mongodb
   DB_HOST=127.0.0.1
   DB_PORT=27017
   DB_DATABASE=laravel_recipes
   DB_USERNAME=
   DB_PASSWORD=
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build frontend assets**
   ```bash
   npm run dev
   # or for production
   npm run build
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

### Default Test Users
- **Admin**: admin@example.com / password
- **Free Tier**: free@example.com / password
- **Tier 1**: tier1@example.com / password
- **Tier 2**: tier2@example.com / password

## 🏗️ Project Structure

```
laravel-recipes-2025/
├── docs/                    # 📚 Project documentation
├── src/                     # 🏠 Main Laravel application
│   ├── app/
│   │   ├── Http/Controllers/Api/V1/  # 🎛️ API controllers
│   │   ├── Models/          # 🗃️ MongoDB models
│   │   ├── Services/        # 🔧 Business logic
│   │   └── Repositories/    # 💾 Data access layer
│   ├── resources/
│   │   ├── js/             # ⚡ Vue.js frontend
│   │   └── css/            # 🎨 Tailwind CSS
│   └── routes/             # 🛣️ API and web routes
└── tests/                  # 🧪 Test suite (TODO)
```

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 11.x
- **Database**: MongoDB 7.x
- **Authentication**: Laravel Sanctum
- **Search**: Laravel Scout
- **PDF Generation**: DomPDF
- **Payments**: Stripe

### Frontend
- **Framework**: Vue.js 3.x (Composition API)
- **Styling**: Tailwind CSS 3.x
- **State Management**: Pinia
- **Routing**: Vue Router 4.x
- **Build Tool**: Vite
- **Icons**: Heroicons

## 📖 Documentation

- 📋 [Project Status](./docs/Laravel%20Recipes%202025%20-%20Project%20Status.md) - Current implementation status
- 📝 [Product Requirements](./docs/product_requirements.md) - Complete feature specifications
- 🏗️ [Technical Specification](./docs/technical_specification.md) - Architecture and implementation details

## 🔗 API Endpoints

The API follows RESTful principles with versioning (`/api/v1/`):

### Authentication
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/logout` - User logout

### Recipes
- `GET /api/v1/recipes` - List user recipes
- `POST /api/v1/recipes` - Create recipe
- `GET /api/v1/recipes/{id}` - Get recipe details
- `PUT /api/v1/recipes/{id}` - Update recipe
- `DELETE /api/v1/recipes/{id}` - Delete recipe
- `GET /api/v1/recipes/search` - Search recipes

### Cookbooks
- `GET /api/v1/cookbooks` - List cookbooks
- `POST /api/v1/cookbooks` - Create cookbook
- `GET /api/v1/cookbooks/{id}` - Get cookbook
- `PUT /api/v1/cookbooks/{id}` - Update cookbook

*[Full API documentation available in the docs folder]*

## 🧪 Testing

> **Note**: Test suite implementation is in progress

Planned testing coverage:
- Unit tests for models and services
- Feature tests for API endpoints
- Browser tests with Laravel Dusk
- Vue component tests

## 🚢 Deployment

> **Note**: Docker configuration coming soon

Current deployment options:
- Traditional LAMP/LEMP stack
- Laravel Forge
- Laravel Vapor (serverless)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📋 Development Status

| Component | Status | Progress |
|-----------|--------|----------|
| Backend API | ✅ Complete | 100% |
| Frontend UI | ✅ Complete | 95% |
| Authentication | ✅ Complete | 100% |
| Subscription System | ✅ Complete | 90% |
| PDF Generation | ✅ Complete | 100% |
| Testing Suite | ⏳ Planned | 0% |
| Docker Setup | ⏳ Planned | 0% |

## 🗺️ Roadmap

### v1.0 (Production Ready) - 3-4 weeks
- [ ] Comprehensive testing suite
- [ ] Docker configuration
- [ ] CI/CD pipeline
- [ ] API documentation
- [ ] Performance optimization

### v1.1 (Enhancements) - 1-2 months
- [ ] Recipe image uploads
- [ ] Advanced search filters
- [ ] Recipe scaling functionality
- [ ] Meal planning features
- [ ] Mobile apps (iOS/Android)

### v2.0 (Advanced Features) - 3-6 months
- [ ] Social features (sharing, reviews)
- [ ] AI-powered recipe recommendations
- [ ] Inventory management
- [ ] Grocery list generation
- [ ] Integration with smart kitchen devices

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Author

**Doug** - [dougis](https://github.com/dougis)

## 🙏 Acknowledgments

- Laravel community for the excellent framework
- Vue.js team for the fantastic frontend framework
- Tailwind CSS for the utility-first CSS framework
- MongoDB for the flexible document database

---

⭐ **Star this repository if you find it helpful!**

🐛 **Found a bug?** [Open an issue](https://github.com/dougis/laravel-recipes-2025/issues)

💡 **Have a feature request?** [Start a discussion](https://github.com/dougis/laravel-recipes-2025/discussions)
