# Laravel Recipes 2025 - Technical Specification

## System Architecture

### Overview

Laravel Recipes 2025 is a web application built on the Laravel framework with MongoDB as the persistence layer. The application follows a model-view-controller (MVC) architecture with additional service layers for business logic and repository patterns for data access.

### Technology Stack

- **Backend Framework**: Laravel 11.x
- **Database**: MongoDB 7.x
- **Frontend**:
  - HTML5, CSS3, JavaScript (ES2022+)
  - Vue.js 3.x for reactive components
  - Tailwind CSS 4.x for responsive styling
- **Authentication**: Laravel Sanctum with JWT
- **Payment Processing**: Stripe API
- **PDF Generation**: Laravel Dompdf
- **Caching**: Redis
- **Search**: Laravel Scout with MongoDB driver
- **Testing**: PHPUnit, Laravel Dusk

### System Components

#### Backend Components

1. **Models**: MongoDB document models for all entities
2. **Controllers**: Handle HTTP requests and responses
3. **Services**: Encapsulate business logic
4. **Repositories**: Abstract data access layer
5. **Middleware**: Request/response modification and filtering
6. **Jobs**: Background processing for tasks like PDF generation
7. **Events & Listeners**: Handle asynchronous activities
8. **Mail**: Email notifications and verification
9. **API Versions**: Separate namespaces for each API version

#### Frontend Components

1. **Layouts**: Base templates for consistent UI
2. **Views**: Blade templates for server-rendered pages
3. **Components**: Vue.js components for interactive elements
4. **Assets**: CSS, JavaScript, images, and fonts
5. **Service Workers**: Offline functionality support

#### Infrastructure Components

1. **Web Server**: Nginx
2. **Application Server**: PHP-FPM
3. **Database**: MongoDB
4. **Cache**: Redis
5. **Queue**: Redis or Laravel Horizon
6. **Storage**: Local file system with cloud backup

## Database Schema

### MongoDB Collections

#### Users Collection

```javascript
{
  _id: ObjectId,
  name: String,
  email: String,
  password: String, // Hashed
  email_verified_at: Date,
  remember_token: String,
  subscription_tier: Number, // 0=Free, 1=Paid Tier 1, 2=Paid Tier 2
  subscription_status: String, // 'active', 'canceled', 'trial', etc.
  subscription_expires_at: Date,
  admin_override: Boolean,
  stripe_customer_id: String,
  stripe_subscription_id: String,
  created_at: Date,
  updated_at: Date
}
```

#### Recipes Collection

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,
  name: String,
  ingredients: String,
  instructions: String,
  notes: String,
  servings: Number,
  source_id: ObjectId,
  classification_id: ObjectId,
  date_added: Date,
  calories: Number,
  fat: Number,
  cholesterol: Number,
  sodium: Number,
  protein: Number,
  marked: Boolean,
  tags: [String],
  meal_ids: [ObjectId],
  preparation_ids: [ObjectId],
  course_ids: [ObjectId],
  is_private: Boolean,  // Only used by Tier 2 and Admin users
  created_at: Date,
  updated_at: Date
}
```

#### Cookbooks Collection

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,
  name: String,
  description: String,
  cover_image: String, // Path to image
  recipe_ids: [
    {
      recipe_id: ObjectId,
      order: Number
    }
  ],
  is_private: Boolean,  // Only used by Tier 2 and Admin users
  created_at: Date,
  updated_at: Date
}
```

#### Classifications Collection

```javascript
{
  _id: ObjectId,
  name: String,
  created_at: Date,
  updated_at: Date
}
```

#### Sources Collection

```javascript
{
  _id: ObjectId,
  name: String,
  created_at: Date,
  updated_at: Date
}
```

#### Meals Collection

```javascript
{
  _id: ObjectId,
  name: String,
  created_at: Date,
  updated_at: Date
}
```

#### Courses Collection

```javascript
{
  _id: ObjectId,
  name: String,
  created_at: Date,
  updated_at: Date
}
```

#### Preparations Collection

```javascript
{
  _id: ObjectId,
  name: String,
  created_at: Date,
  updated_at: Date
}
```

#### Subscriptions Collection

```javascript
{
  _id: ObjectId,
  name: String,
  description: String,
  price: Number,
  tier: Number, // 0=Free, 1=Paid Tier 1, 2=Paid Tier 2
  features: [String],
  created_at: Date,
  updated_at: Date
}
```

### Indexes

- Users: email (unique)
- Recipes: user_id, name, tags (text index)
- Cookbooks: user_id, name
- All collections: created_at

## API Versioning Implementation

### Version Management Strategy

- **Versioning Approach**: URI Path Versioning (e.g., `/api/v1/recipes`)
- **Initial Version**: v1 for all endpoints
- **Version Lifecycle**:
  - Development (active development)
  - Stable (current production version)
  - Deprecated (scheduled for removal)
  - Sunset (no longer available)

### Directory Structure

- Controllers organized by version:

  ```
  app/Http/Controllers/Api/V1/
  app/Http/Controllers/Api/V2/ (future)
  ```

- Separate route files for each version:

  ```
  routes/api_v1.php
  routes/api_v2.php (future)
  ```

- Version-specific request validation:

  ```
  app/Http/Requests/Api/V1/
  ```

### Implementation Details

- **Route Registration**:

  ```php
  // In routes/api.php
  Route::prefix('v1')
       ->middleware(['api'])
       ->namespace('App\\Http\\Controllers\\Api\\V1')
       ->group(base_path('routes/api_v1.php'));
  ```

- **Version Headers**:
  - All API responses include version information in headers:

    ```
    API-Version: v1
    ```

  - Deprecated versions include sunset warning headers:

    ```
    API-Deprecated: true
    API-Sunset-Date: 2026-06-30
    API-Successor-Version: v2
    ```

### Version Transition Strategy

- Minimum 6-month overlap period for major version changes
- Documentation of breaking changes between versions
- Migration guides for transitioning from v1 to future versions
- API client SDK updates to support multiple versions

### Version Control Guidelines

- **When to Create a New Version**:
  - Changes to endpoint URL structure
  - Removal of response fields
  - Changes to response format
  - Modification of resource representations
  - Changes to authentication mechanisms

- **Non-Breaking Changes** (no version change required):
  - Adding new endpoints
  - Adding optional request parameters
  - Adding response fields (maintaining backward compatibility)
  - Performance improvements
  - Bug fixes

### Backward Compatibility

- All deprecated fields marked in API documentation
- Optional backward compatibility middleware for critical transitions
- Legacy support adapters for important integrations

## Authentication and Authorization

### Authentication

- JWT-based authentication using Laravel Sanctum
- Email verification upon registration
- Password reset functionality
- "Remember me" functionality
- Session management with timeout

### Authorization

- Role-based access control:
  - Guest: Public recipes and cookbooks only (read-only)
  - Free User: Access to own recipes and cookbooks (edit), all recipes are public
  - Paid Tier 1: Access to own recipes and cookbooks (edit), all recipes are public
  - Paid Tier 2: Access to own recipes and cookbooks (edit), can make recipes private or public
  - Admin: Full system access, can access all recipes/cookbooks, can override privacy settings
- Policy-based authorization for resources
- Feature access based on subscription tier
- Admin override capability
- Privacy enforcement based on user tier and ownership

## API Endpoints

### API Versioning Strategy

- All API endpoints use versioning prefix (starting with v1)
- Version is specified in the URL path (`/api/v1/...`)
- New versions (v2, v3, etc.) will be created for breaking changes
- Multiple API versions can run concurrently during transition periods
- Deprecation notices will be provided before removing older versions
- API version documentation will be maintained for all supported versions

### Authentication Endpoints

- `POST /api/v1/auth/register` - Register a new user
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/logout` - User logout
- `GET /api/v1/auth/user` - Get authenticated user
- `POST /api/v1/auth/password/email` - Send password reset email
- `POST /api/v1/auth/password/reset` - Reset password
- `POST /api/v1/auth/email/verify/{id}` - Verify email

### User Endpoints

- `GET /api/v1/users/profile` - Get user profile
- `PUT /api/v1/users/profile` - Update user profile
- `GET /api/v1/users/subscription` - Get subscription details
- `POST /api/v1/users/subscription` - Update subscription

### Recipe Endpoints

- `GET /api/v1/recipes` - List user recipes
- `GET /api/v1/recipes/public` - List all public recipes
- `POST /api/v1/recipes` - Create a recipe
- `GET /api/v1/recipes/{id}` - Get a recipe
- `PUT /api/v1/recipes/{id}` - Update a recipe
- `DELETE /api/v1/recipes/{id}` - Delete a recipe
- `PUT /api/v1/recipes/{id}/privacy` - Toggle recipe privacy (Tier 2 and Admin only)
- `GET /api/v1/recipes/search` - Search recipes with fuzzy matching

### Cookbook Endpoints

- `GET /api/v1/cookbooks` - List user cookbooks
- `GET /api/v1/cookbooks/public` - List all public cookbooks
- `POST /api/v1/cookbooks` - Create a cookbook
- `GET /api/v1/cookbooks/{id}` - Get a cookbook
- `PUT /api/v1/cookbooks/{id}` - Update a cookbook
- `DELETE /api/v1/cookbooks/{id}` - Delete a cookbook
- `PUT /api/v1/cookbooks/{id}/privacy` - Toggle cookbook privacy (Tier 2 and Admin only)
- `POST /api/v1/cookbooks/{id}/recipes` - Add recipes to cookbook
- `DELETE /api/v1/cookbooks/{id}/recipes/{recipe_id}` - Remove recipe from cookbook
- `PUT /api/v1/cookbooks/{id}/recipes/order` - Reorder recipes in cookbook

### Print/Export Endpoints

- `GET /api/v1/recipes/{id}/print` - Get printable recipe
- `GET /api/v1/cookbooks/{id}/print` - Get printable cookbook
- `GET /api/v1/recipes/{id}/export/{format}` - Export recipe
- `GET /api/v1/cookbooks/{id}/export/{format}` - Export cookbook

### Admin Endpoints

- `GET /api/v1/admin/users` - List all users
- `GET /api/v1/admin/users/{id}` - Get user details
- `PUT /api/v1/admin/users/{id}` - Update user
- `PUT /api/v1/admin/users/{id}/override` - Toggle admin override
- `GET /api/v1/admin/subscriptions` - List all subscriptions
- `GET /api/v1/admin/statistics` - Get system statistics

### Metadata Endpoints

- `GET /api/v1/classifications` - List classifications
- `GET /api/v1/sources` - List sources
- `GET /api/v1/meals` - List meals
- `GET /api/v1/courses` - List courses
- `GET /api/v1/preparations` - List preparations

## Security Implementation

### Authentication Security

- Password hashing using bcrypt
- CSRF token validation
- Rate limiting for login attempts
- Session expiration and rotation
- Remember token rotation

### Data Security

- Input validation
- MongoDB injection prevention
- XSS prevention
- Content Security Policy
- HTTPS enforcement
- API authentication with Sanctum
- Sensitive data encryption

### Payment Security

- PCI-compliant payment processing via Stripe
- No storage of credit card information
- Secure webhook handling
- Idempotent payment operations

## Subscription and Payment Processing

### Subscription Tiers

- Implementation of three-tier subscription model
- Feature toggling based on subscription level
- Upgrading/downgrading subscription logic
- Subscription expiration handling

### Payment Integration

- Stripe API integration
- Payment webhook handling
- Invoice generation
- Subscription renewal processing
- Refund processing
- Payment failure handling

## Search Implementation

### Fuzzy Search Architecture

- **Search Engine**: MongoDB Atlas Search with fuzzy matching capabilities
- **Alternative Implementation**: Elasticsearch for more advanced search capabilities if needed

### Search Features

- **Fuzzy Matching**: Tolerance for typos and misspellings
  - Edit distance (Levenshtein distance) of 1-2 characters
  - Phonetic matching for similar-sounding words
  - Stemming for different word forms

- **Intelligent Ranking**:
  - Higher weight for matches in recipe names
  - Relevance scoring based on word proximity
  - Boosting for exact phrase matches
  - Contextual ranking based on ingredient lists

- **Advanced Filters**:
  - Filter by recipe classification
  - Filter by meal type, course, and preparation method
  - Filter by nutritional properties (calories, fat, etc.)
  - Filter by ingredient inclusion/exclusion

- **Search Optimization**:
  - Compound MongoDB indexes for query performance
  - Text indexes with weighted fields
  - Caching of frequent searches
  - Incremental index updates

### Search Implementation Details

- **Indexing Strategy**:

  ```javascript
  db.recipes.createIndex({
    name: "text",
    ingredients: "text",
    instructions: "text",
    tags: "text"
  }, {
    weights: {
      name: 10,
      ingredients: 5,
      tags: 3,
      instructions: 1
    },
    default_language: "english",
    language_override: "language",
    name: "recipe_search_index"
  })
  ```

- **Fuzzy Search Implementation**:
  - Utilize MongoDB's `$text` operator with fuzzy matching
  - Implement custom scoring function for result ranking
  - Use aggregation pipeline for complex search logic
  - Apply wildcards for partial word matching

- **Search API Integration**:
  - RESTful API endpoint for search queries
  - Support for advanced query parameters
  - Pagination of search results
  - Highlighting of matched terms in results

- **Performance Considerations**:
  - Asynchronous index updates
  - Search result caching
  - Query optimization for large datasets
  - Monitoring of search performance metrics

## PDF Generation

### Recipe PDF

- Custom template for individual recipes
- Consistent styling with web view
- Header and footer with metadata
- Ingredient and instruction formatting
- Optional image inclusion

### Cookbook PDF

- Cover page with title and description
- Table of contents generation
- Section dividers by classification
- Page break between recipes
- Page numbering
- Header and footer on each page
- Consistent styling throughout

## Mobile-First Implementation

### Responsive Design

- Mobile-first CSS architecture
- Progressive enhancement approach
- Breakpoints for various device sizes
- Touch-friendly UI elements
- Optimized typography for readability
- Responsive images and media

### Performance Optimization

- Lazy loading of images
- Critical CSS loading
- Code splitting for JavaScript
- Asset minification and compression
- Browser caching strategy
- Optimized MongoDB queries
- Content delivery optimization

## MongoDB Migration Strategy

### MySQL to MongoDB Migration Plan

#### 1. Preparation Phase

- **Database Analysis**
  - Map current MySQL schemas and relationships
  - Identify data volumes and complexity
  - Document referential integrity constraints
  - Analyze existing queries for optimization opportunities

- **MongoDB Environment Setup**
  - Set up MongoDB development/staging environment
  - Configure MongoDB with appropriate resources based on data volume
  - Implement security measures for the MongoDB instance
  - Set up monitoring tools for migration progress

- **Migration Tool Selection**
  - Develop custom Laravel Artisan commands for migration
  - Implement logging and error handling framework
  - Create database snapshots for recovery points

#### 2. Schema Design and Mapping

- **Document Model Design**
  - Convert normalized MySQL tables to document collections:
    - Users table → users collection
    - Recipes table → recipes collection
    - Cookbooks table → cookbooks collection
    - Classifications, Sources, Meals, Courses, Preparations → respective collections
  
  - **Relationship Handling**:
    - Many-to-many relationship tables (recipe_meals, recipe_courses, etc.) will be embedded as arrays within the recipe documents
    - cookbook_recipes join table will be transformed into an embedded array in the cookbooks collection

- **Data Type Mapping**
  - MySQL `INT` → MongoDB `NumberInt`/`NumberLong`
  - MySQL `VARCHAR`/`TEXT` → MongoDB `String`
  - MySQL `DATETIME`/`TIMESTAMP` → MongoDB `Date`
  - MySQL `DECIMAL`/`FLOAT` → MongoDB `NumberDecimal`/`Double`
  - MySQL `BOOLEAN`/`TINYINT(1)` → MongoDB `Boolean`
  - MySQL `JSON` → MongoDB native JSON

#### 3. Extraction Process

- **Data Export from MySQL**
  - Develop Artisan command to extract data in batches:

    ```php
    php artisan migration:extract-data --table=recipes --batch-size=1000
    ```

  - Export reference tables first (classifications, sources, etc.)
  - Export main tables with transformed foreign keys
  - Export relationship tables last
  - Store extracted data in JSON format as intermediate step

- **Data Cleaning**
  - Normalize text fields (ingredients, instructions)
  - Remove duplicate entries
  - Fix inconsistent data
  - Handle NULL values appropriately
  - Generate MongoDB-compatible IDs

#### 4. Transformation Process

- **ID Transformation**
  - Map MySQL auto-increment IDs to MongoDB ObjectIDs
  - Create and maintain ID mapping tables for reference
  - Handle UUID conversion if applicable

- **Relationship Transformation**
  - Convert junction tables to embedded arrays
  - Transform foreign keys to MongoDB references
  - Maintain referential integrity where needed

- **Data Restructuring**
  - Embed frequently accessed related data
  - Denormalize when appropriate for performance
  - Create composite fields for search optimization
  - Add metadata fields for migration tracking

#### 5. Loading Process

- **Batch Import to MongoDB**
  - Develop Artisan command for incremental loading:

    ```php
    php artisan migration:load-data --collection=recipes --source=recipes_data.json
    ```

  - Import reference collections first
  - Import main collections with transformed relationships
  - Implement retry logic for failed imports
  - Log import progress and errors

- **Index Creation**
  - Create appropriate indexes for each collection
  - Include text indexes for search functionality
  - Add compound indexes for common query patterns
  - Create unique indexes where needed

#### 6. Validation and Verification

- **Data Integrity Checks**
  - Validate record counts match between MySQL and MongoDB
  - Verify sample records for accurate transformation
  - Check referential integrity in document references
  - Test complex queries on both systems to verify equivalence

- **Application Integration Tests**
  - Run test suite against migrated data
  - Verify all application functions work correctly
  - Check performance metrics for critical operations
  - Test PDF generation with migrated data

- **User Acceptance Testing**
  - Provide access to staging environment with migrated data
  - Collect feedback on any data inconsistencies
  - Address issues before final migration

#### 7. Switchover Strategy

- **Pre-Migration Freeze**
  - Implement temporary read-only mode for MySQL database
  - Perform final data sync before cutover
  - Create final backup of MySQL database

- **Switchover Process**
  - Deploy updated application code with MongoDB adapters
  - Switch database connection to MongoDB
  - Run final validation tests
  - Open system to users

- **Monitoring and Support**
  - Monitor system performance closely after migration
  - Have support team ready for any issues
  - Maintain MySQL database in read-only mode for quick rollback if needed

#### 8. Rollback Plan

- **Criteria for Rollback**
  - Define specific thresholds for triggering rollback
  - Establish decision-making authority for rollback process

- **Rollback Process**
  - Switch application connection string back to MySQL
  - Restore any data created during MongoDB operation
  - Notify users of temporary service disruption
  - Roll back application code changes

#### 9. Post-Migration Tasks

- **Performance Optimization**
  - Fine-tune MongoDB indexes based on actual usage patterns
  - Optimize query performance for frequent operations
  - Implement appropriate caching strategies
  - Monitor and adjust server resources as needed

- **Documentation Update**
  - Update all technical documentation to reflect MongoDB schema
  - Document migration process for future reference
  - Update data dictionary and API documentation

#### 10. MySQL Decommissioning

- **Archival Strategy**
  - Create final archive of MySQL database
  - Store backups according to data retention policy
  - Document archive restoration procedure

- **Resource Reclamation**
  - Schedule MySQL server decommissioning
  - Reallocate resources to other systems
  - Update infrastructure documentation

### MongoDB Optimization

- **Indexing Strategy**
  - Single-field indexes for frequent queries
  - Compound indexes for queries with multiple conditions
  - Text indexes for full-text search capabilities
  - TTL indexes for time-based document expiration
  - Background index creation to minimize downtime

- **Document Structure Optimization**
  - Strategic embedding vs. referencing based on access patterns
  - Limit document size to avoid performance issues
  - Use appropriate data types to minimize storage
  - Consistent field naming convention

- **Query Optimization**
  - Covered queries using proper indexes
  - Projection to limit returned fields
  - Efficient use of aggregation pipelines
  - Appropriate use of read preferences

- **Performance Tuning**
  - Connection pooling configuration
  - Write concern and read preference tuning
  - Appropriate batch size for bulk operations
  - Query plan analysis and optimization

- **High Availability Setup**
  - Replica set configuration for redundancy
  - Appropriate write concern settings
  - Automatic failover configuration
  - Regular backup strategy

## Testing Strategy

### Unit Testing

- Model validation tests
- Service layer logic tests
- Repository pattern tests
- Helper function tests

### Integration Testing

- API endpoint tests
- Authentication flow tests
- Subscription management tests
- PDF generation tests

### End-to-End Testing

- User journey tests with Laravel Dusk
- Mobile and desktop viewport testing
- Cross-browser compatibility testing

### Performance Testing

- Load testing with simulated users
- Database query performance testing
- API response time testing
- PDF generation performance

## Deployment Strategy

### Environment Setup

- Development environment
- Staging environment
- Production environment
- CI/CD pipeline configuration

### Deployment Process

- Version control with Git
- Automated testing before deployment
- Zero-downtime deployment
- Database migration handling
- Monitoring and alerting setup

### Backup and Recovery

- MongoDB backup strategy
- Automated daily backups
- Point-in-time recovery
- Disaster recovery plan

## Monitoring and Maintenance

### Application Monitoring

- Error logging and tracking
- Performance monitoring
- User activity monitoring
- Subscription status monitoring

### System Health Monitoring

- Server resource utilization
- Database performance metrics
- Cache hit/miss rates
- Queue processing metrics

### Maintenance Procedures

- Regular security updates
- Database maintenance
- Cache clearing routines
- Log rotation and archiving

## Appendix

### Technology Dependencies

- PHP 8.3+
- Composer 2.x
- Node.js 20.x
- npm 10.x
- MongoDB 7.x
- Redis 7.x

### Development Tools

- Laravel Sail for local development
- MongoDB Compass for database management
- Laravel Telescope for debugging
- Laravel Horizon for queue monitoring
- PHPStorm or VS Code for development

### Coding Standards

- PSR-12 coding style
- Laravel best practices
- Vue.js style guide
- MongoDB schema design best practices
- Git flow branching strategy

### Documentation

- API documentation with OpenAPI/Swagger
- Separate documentation for each API version
- Code documentation with PHPDoc
- Database schema documentation
- Deployment and maintenance documentation

### Project Structure

```
laravel-recipes-2025/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── RecipeController.php
│   │   │   │   │   ├── CookbookController.php
│   │   │   │   │   └── ...
│   │   │   │   └── ...
│   │   │   └── ...
│   │   ├── Middleware/
│   │   └── Requests/
│   │       ├── Api/
│   │       │   ├── V1/
│   │       │   │   ├── RecipeRequest.php
│   │       │   │   └── ...
│   │       │   └── ...
│   │       └── ...
│   ├── Models/
│   ├── Providers/
│   ├── Repositories/
│   │   ├── Interfaces/
│   │   └── MongoDB/
│   └── Services/
├── config/
├── database/
├── docs/
├── public/
├── resources/
│   ├── js/
│   ├── css/
│   └── views/
├── routes/
│   ├── api.php
│   ├── api_v1.php
│   └── web.php
└── tests/
```
