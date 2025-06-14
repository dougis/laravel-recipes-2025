# Docker Configuration for Laravel Recipes 2025

This directory contains Docker configuration files for both development and production environments of the Laravel Recipes 2025 application.

## 🏗️ Architecture Overview

The application uses a multi-container architecture with the following services:

### Production Services
- **nginx** - Web server (Alpine Linux)
- **app** - Laravel application (PHP 8.3 FPM)
- **mongodb** - MongoDB 7.0 database
- **redis** - Redis cache and session store
- **queue** - Laravel queue worker
- **scheduler** - Laravel task scheduler

### Development Additional Services
- **node** - Node.js for frontend build tools
- **mongo-express** - MongoDB web interface
- **redis-commander** - Redis web interface

## 🚀 Quick Start

### Development Environment

1. **Clone the repository**
   ```bash
   git clone https://github.com/dougis/laravel-recipes-2025.git
   cd laravel-recipes-2025
   ```

2. **Run the setup script**
   ```bash
   chmod +x setup-dev.sh
   ./setup-dev.sh
   ```

3. **Access the application**
   - Main App: http://localhost:8080
   - MongoDB Admin: http://localhost:8081
   - Redis Admin: http://localhost:8082

### Production Environment

1. **Prepare environment**
   ```bash
   chmod +x setup-prod.sh
   cp .env.docker .env
   # Edit .env with your production settings
   ```

2. **Run the setup script**
   ```bash
   ./setup-prod.sh
   ```

3. **Access the application**
   - Main App: http://localhost (or your domain)

## 📁 File Structure

```
docker/
├── nginx/
│   ├── nginx.conf          # Production Nginx configuration
│   └── nginx.dev.conf      # Development Nginx configuration
├── php/
│   ├── Dockerfile          # Production PHP container
│   ├── Dockerfile.dev      # Development PHP container
│   ├── local.ini           # PHP configuration
│   ├── opcache.ini         # OPcache configuration (production)
│   ├── xdebug.ini          # Xdebug configuration (development)
│   ├── supervisord.conf    # Supervisor configuration
│   └── crontab             # Laravel scheduler crontab
└── mongodb/
    └── init/
        └── init-mongo.js   # MongoDB initialization script
```

## 🐳 Docker Compose Files

### docker-compose.yml (Production)
- Optimized for production use
- Includes queue workers and scheduler
- Security-focused configuration
- OPcache enabled for performance

### docker-compose.dev.yml (Development)
- Development-friendly configuration
- Includes debugging tools (Xdebug)
- Database admin interfaces
- Hot reload support for frontend

## 🔧 Configuration Details

### PHP Configuration
- **PHP Version**: 8.3 FPM Alpine
- **Extensions**: MongoDB, Redis, GD, ZIP, BCMath, OPcache
- **Memory Limit**: 256M
- **Upload Limit**: 32M
- **Development**: Xdebug enabled with VS Code support

### Nginx Configuration
- **Production**: Security headers, gzip compression, caching
- **Development**: Extended timeouts for debugging
- **SSL**: Ready for HTTPS (certificates needed)

### MongoDB Configuration
- **Version**: 7.0
- **Authentication**: Enabled with custom user
- **Initialization**: Automatic database and user setup
- **Indexes**: Optimized for Laravel Recipes queries

### Redis Configuration
- **Version**: Alpine latest
- **Persistence**: AOF enabled
- **Authentication**: Password protected
- **Usage**: Cache, sessions, and queues

## 🌍 Environment Variables

### Required Variables
```env
APP_KEY=base64:your-app-key-here
DB_DATABASE=laravel_recipes
DB_USERNAME=laravel_user
DB_PASSWORD=your-secure-password
MONGO_ROOT_USERNAME=admin
MONGO_ROOT_PASSWORD=your-admin-password
REDIS_PASSWORD=your-redis-password
```

### Optional Variables
```env
STRIPE_KEY=pk_test_your-stripe-key
STRIPE_SECRET=sk_test_your-stripe-secret
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
```

## 🛠️ Common Commands

### Development
```bash
# Start development environment
docker-compose -f docker-compose.dev.yml up -d

# View logs
docker-compose -f docker-compose.dev.yml logs -f

# Laravel commands
docker-compose -f docker-compose.dev.yml exec app php artisan migrate
docker-compose -f docker-compose.dev.yml exec app php artisan test

# Frontend development
docker-compose -f docker-compose.dev.yml exec node npm run dev

# Database access
docker-compose -f docker-compose.dev.yml exec mongodb mongo

# Stop services
docker-compose -f docker-compose.dev.yml down
```

### Production
```bash
# Start production environment
docker-compose up -d

# View logs
docker-compose logs -f

# Laravel commands
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan config:cache

# Monitor queues
docker-compose exec app php artisan queue:monitor

# Stop services
docker-compose down
```

## 🔒 Security Considerations

### Production Security
1. **Change default passwords** in `.env` file
2. **Configure SSL certificates** for HTTPS
3. **Set up firewall rules** to restrict access
4. **Use Docker secrets** for sensitive data
5. **Regular security updates** for base images

### Network Security
- All services communicate through internal Docker network
- Only necessary ports are exposed to host
- MongoDB and Redis are not directly accessible from outside

## 🏥 Health Checks

Health checks are configured for critical services:
- **PHP-FPM**: Checks if PHP process is responding
- **MongoDB**: Verifies database connectivity
- **Redis**: Ensures cache service is available

## 📊 Monitoring and Logging

### Log Locations
- **Nginx**: `/var/log/nginx/`
- **PHP-FPM**: `/var/log/supervisor/`
- **Laravel**: `/var/www/html/storage/logs/`
- **Supervisor**: `/var/log/supervisor/supervisord.log`

### Monitoring
- Use `docker stats` for resource usage
- Monitor logs with `docker-compose logs`
- Queue monitoring via Laravel Horizon (if enabled)

## 🔄 Backup Strategy

### Database Backup
```bash
# MongoDB backup
docker-compose exec mongodb mongodump --out /data/backup

# Copy backup to host
docker cp container_name:/data/backup ./backup
```

### Application Backup
```bash
# Backup uploaded files
docker cp container_name:/var/www/html/storage/app ./storage-backup
```

## 🚨 Troubleshooting

### Common Issues

1. **Permission Issues**
   ```bash
   # Fix file permissions
   docker-compose exec app chown -R www:www /var/www/html
   ```

2. **MongoDB Connection Issues**
   ```bash
   # Check MongoDB logs
   docker-compose logs mongodb
   
   # Test connection
   docker-compose exec app php artisan tinker
   >>> DB::connection()->getMongoDB()->listCollections()
   ```

3. **Redis Connection Issues**
   ```bash
   # Check Redis logs
   docker-compose logs redis
   
   # Test connection
   docker-compose exec redis redis-cli ping
   ```

4. **Frontend Build Issues**
   ```bash
   # Clear Node modules and reinstall
   docker-compose exec node rm -rf node_modules
   docker-compose exec node npm install
   ```

## 🔧 Customization

### Adding New Services
1. Add service definition to `docker-compose.yml`
2. Configure networking and volumes
3. Update environment variables
4. Document in this README

### Modifying PHP Configuration
1. Edit `docker/php/local.ini`
2. Rebuild container: `docker-compose build app`
3. Restart services: `docker-compose up -d`

### Nginx Customization
1. Edit `docker/nginx/nginx.conf`
2. Restart nginx: `docker-compose restart nginx`

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Best Practices](https://laravel.com/docs/deployment#docker)
- [MongoDB Docker Documentation](https://hub.docker.com/_/mongo)
- [Redis Docker Documentation](https://hub.docker.com/_/redis)

## 🤝 Contributing

When contributing Docker-related changes:
1. Test both development and production configurations
2. Update this documentation
3. Ensure security best practices are followed
4. Test on multiple platforms (Linux, macOS, Windows)

---

For questions or issues related to the Docker setup, please open an issue in the main repository.
