// MongoDB Initialization Script for Laravel Recipes 2025
// This script sets up the database and creates necessary indexes

// Switch to the Laravel Recipes database
db = db.getSiblingDB('laravel_recipes');

// Create application user with read/write permissions
db.createUser({
  user: 'laravel_user',
  pwd: 'laravel_password',
  roles: [
    {
      role: 'readWrite',
      db: 'laravel_recipes'
    }
  ]
});

// Create collections with proper schema validation
db.createCollection('users', {
  validator: {
    $jsonSchema: {
      bsonType: 'object',
      required: ['name', 'email', 'password'],
      properties: {
        name: {
          bsonType: 'string',
          description: 'User name is required and must be a string'
        },
        email: {
          bsonType: 'string',
          pattern: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$',
          description: 'Email must be a valid email address'
        },
        password: {
          bsonType: 'string',
          description: 'Password is required'
        },
        subscription_tier: {
          bsonType: 'int',
          minimum: 0,
          maximum: 2,
          description: 'Subscription tier must be 0, 1, or 2'
        }
      }
    }
  }
});

db.createCollection('recipes', {
  validator: {
    $jsonSchema: {
      bsonType: 'object',
      required: ['name', 'ingredients', 'instructions', 'user_id'],
      properties: {
        name: {
          bsonType: 'string',
          description: 'Recipe name is required'
        },
        ingredients: {
          bsonType: 'string',
          description: 'Ingredients are required'
        },
        instructions: {
          bsonType: 'string',
          description: 'Instructions are required'
        },
        servings: {
          bsonType: 'int',
          minimum: 1,
          description: 'Servings must be a positive integer'
        },
        is_private: {
          bsonType: 'bool',
          description: 'Privacy flag must be boolean'
        }
      }
    }
  }
});

db.createCollection('cookbooks');
db.createCollection('classifications');
db.createCollection('sources');
db.createCollection('meals');
db.createCollection('courses');
db.createCollection('preparations');
db.createCollection('subscriptions');

// Create indexes for performance

// Users indexes
db.users.createIndex({ email: 1 }, { unique: true });
db.users.createIndex({ subscription_tier: 1 });
db.users.createIndex({ created_at: 1 });

// Recipes indexes
db.recipes.createIndex({ user_id: 1 });
db.recipes.createIndex({ name: 1 });
db.recipes.createIndex({ is_private: 1 });
db.recipes.createIndex({ created_at: 1 });
db.recipes.createIndex({ user_id: 1, is_private: 1 });

// Text index for recipe search
db.recipes.createIndex(
  {
    name: 'text',
    ingredients: 'text',
    instructions: 'text',
    tags: 'text'
  },
  {
    weights: {
      name: 10,
      ingredients: 5,
      tags: 3,
      instructions: 1
    },
    name: 'recipe_search_index'
  }
);

// Cookbooks indexes
db.cookbooks.createIndex({ user_id: 1 });
db.cookbooks.createIndex({ name: 1 });
db.cookbooks.createIndex({ is_private: 1 });
db.cookbooks.createIndex({ created_at: 1 });
db.cookbooks.createIndex({ user_id: 1, is_private: 1 });

// Metadata collections indexes
db.classifications.createIndex({ name: 1 }, { unique: true });
db.sources.createIndex({ name: 1 }, { unique: true });
db.meals.createIndex({ name: 1 }, { unique: true });
db.courses.createIndex({ name: 1 }, { unique: true });
db.preparations.createIndex({ name: 1 }, { unique: true });

// Subscriptions indexes
db.subscriptions.createIndex({ tier: 1 }, { unique: true });
db.subscriptions.createIndex({ name: 1 }, { unique: true });

print('MongoDB initialization completed successfully!');
print('Collections created with schema validation');
print('Indexes created for optimal performance');
print('Application user created with appropriate permissions');
