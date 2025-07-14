<?php

namespace Tests\Unit\Database;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;
use Tests\TestCase;

class MongoDBCompatibilityTest extends TestCase
{
    /**
     * Test that MongoDB connection works properly
     */
    public function test_mongodb_connection_works(): void
    {
        // Attempt to connect to MongoDB and ping
        $connection = app('db')->connection('mongodb');
        $result = $connection->getMongoDB()->command(['ping' => 1]);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->toArray()[0]['ok']);
    }

    /**
     * Test that MongoDB extension version is compatible
     */
    public function test_mongodb_extension_version_compatibility(): void
    {
        $this->assertTrue(extension_loaded('mongodb'), 'MongoDB extension is not loaded');

        $extensionVersion = phpversion('mongodb');
        $this->assertNotFalse($extensionVersion, 'Could not get MongoDB extension version');

        // The extension should be version 1.15+ or 2.x+ for compatibility
        $versionParts = explode('.', $extensionVersion);
        $majorVersion = (int) $versionParts[0];
        $minorVersion = (int) $versionParts[1];

        $isCompatible =
            ($majorVersion === 1 && $minorVersion >= 15) ||  // 1.15+
            ($majorVersion >= 2);                            // 2.x+

        $this->assertTrue($isCompatible, "MongoDB extension version {$extensionVersion} is not compatible. Need 1.15+ or 2.x+");
    }

    /**
     * Test that Laravel MongoDB package can perform basic operations
     */
    public function test_laravel_mongodb_package_basic_operations(): void
    {
        $userModel = app(User::class);

        // Test Create operation
        $user = $userModel->create([
            'name' => 'MongoDB Test User',
            'email' => 'mongodb.test@example.com',
            'password' => bcrypt('password'),
            'subscription_tier' => 0,
            'admin_override' => false,
        ]);

        $this->assertNotNull($user->_id);
        $this->assertEquals('MongoDB Test User', $user->name);
        $this->assertEquals('mongodb.test@example.com', $user->email);

        // Test Read operation
        $foundUser = $userModel->where('email', 'mongodb.test@example.com')->first();
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->_id, $foundUser->_id);

        // Test Update operation
        $foundUser->name = 'Updated MongoDB Test User';
        $foundUser->save();

        $updatedUser = $userModel->find($foundUser->_id);
        $this->assertEquals('Updated MongoDB Test User', $updatedUser->name);

        // Test Delete operation
        $deletedCount = $userModel->where('_id', $user->_id)->delete();
        $this->assertEquals(1, $deletedCount);

        $deletedUser = $userModel->find($user->_id);
        $this->assertNull($deletedUser);
    }

    /**
     * Test MongoDB aggregation operations work
     */
    public function test_mongodb_aggregation_operations(): void
    {
        $userModel = app(User::class);

        // Create multiple test users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = $userModel->create([
                'name' => "Test User {$i}",
                'email' => "test{$i}@example.com",
                'password' => bcrypt('password'),
                'subscription_tier' => $i % 3,
                'admin_override' => false,
            ]);
        }

        // Test aggregation pipeline
        $pipeline = [
            ['$group' => [
                '_id' => '$subscription_tier',
                'count' => ['$sum' => 1],
            ]],
            ['$sort' => ['_id' => 1]],
        ];

        $result = app('db')->connection('mongodb')
            ->collection('users')
            ->aggregate($pipeline)
            ->toArray();

        $this->assertNotEmpty($result);
        $this->assertGreaterThan(0, count($result));

        // Should have grouped by subscription tiers
        foreach ($result as $group) {
            $this->assertArrayHasKey('_id', $group);
            $this->assertArrayHasKey('count', $group);
            $this->assertGreaterThan(0, $group['count']);
        }
    }

    /**
     * Test MongoDB collection operations
     */
    public function test_mongodb_collection_operations(): void
    {
        $collection = app('db')->connection('mongodb')->collection('test_compatibility');

        // Test insertOne
        $insertResult = $collection->insertOne([
            'test_field' => 'test_value',
            'created_at' => new \MongoDB\BSON\UTCDateTime,
        ]);

        $this->assertNotNull($insertResult->getInsertedId());

        // Test findOne
        $document = $collection->findOne(['test_field' => 'test_value']);
        $this->assertNotNull($document);
        $this->assertEquals('test_value', $document['test_field']);

        // Test updateOne
        $updateResult = $collection->updateOne(
            ['test_field' => 'test_value'],
            ['$set' => ['updated_field' => 'updated_value']]
        );

        $this->assertEquals(1, $updateResult->getModifiedCount());

        // Test deleteOne
        $deleteResult = $collection->deleteOne(['test_field' => 'test_value']);
        $this->assertEquals(1, $deleteResult->getDeletedCount());
    }

    /**
     * Test that Model class methods work properly
     */
    public function test_model_mongodb_methods(): void
    {
        $userModel = app(User::class);

        $user = $userModel->create([
            'name' => 'Model Test User',
            'email' => 'model.test@example.com',
            'password' => bcrypt('password'),
            'subscription_tier' => 1,
            'admin_override' => false,
        ]);

        // Test MongoDB-specific Model methods
        $this->assertInstanceOf(\MongoDB\BSON\ObjectId::class, $user->_id);
        $this->assertTrue(method_exists($user, 'getCollection'));
        $this->assertTrue(method_exists($user, 'newCollection'));

        // Test that the model uses MongoDB connection
        $this->assertEquals('mongodb', $user->getConnectionName());

        // Test raw MongoDB operations through model
        $collection = $user->getCollection();
        $this->assertInstanceOf(\MongoDB\Collection::class, $collection);

        $rawDocument = $collection->findOne(['_id' => $user->_id]);
        $this->assertNotNull($rawDocument);
        $this->assertEquals($user->name, $rawDocument['name']);
    }
}
