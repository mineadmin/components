<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
require_once __DIR__ . '/../Entity/User.php';
require_once __DIR__ . '/../Entity/Post.php';
require_once __DIR__ . '/../Entity/Comment.php';
require_once __DIR__ . '/../Entity/Profile.php';
require_once __DIR__ . '/../Entity/Tag.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\MemoryDriver;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\SafeCaller;
use Mine\Doctrine\Config;
use Mine\Doctrine\EntityManagerFactory;
use Mine\Doctrine\ORMSetupFactory;
use Mine\Doctrine\Pool\ConnectionFactory;
use Mine\Doctrine\Pool\PoolFactory;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

describe('Entity Association Integration', function () {
    beforeEach(function () {
        // Create Mine Doctrine components for testing
        $container = ApplicationContext::getContainer();

        // Mock ConfigInterface for testing
        $configInterface = Mockery::mock(ConfigInterface::class);
        $configInterface->allows('get')->with('doctrine')->andReturn([
            'paths' => [__DIR__ . '/../Entity'],
            'isDevMode' => true,
            'cache' => 'memory',
            'proxy_dir' => sys_get_temp_dir() . '/doctrine_proxies',
            'database' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'path' => ':memory:',
                    'name' => 'default',
                    'option' => [
                        'min_connections' => 1,
                        'max_connections' => 1,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'maxIdleTime' => 60,
                    ],
                ],
            ],
        ]);

        // Set up individual config queries
        $configInterface->allows('get')->with('doctrine.paths')->andReturn([__DIR__ . '/../Entity']);
        $configInterface->allows('get')->with('doctrine.isDevMode')->andReturn(true);
        $configInterface->allows('get')->with('doctrine.cache')->andReturn('memory');
        $configInterface->allows('get')->with('doctrine.proxy_dir')->andReturn(sys_get_temp_dir() . '/doctrine_proxies');
        $configInterface->allows('get')->with('doctrine.database')->andReturn([
            'default' => [
                'driver' => 'pdo_sqlite',
                'path' => ':memory:',
                'name' => 'default',
                'option' => [
                    'min_connections' => 1,
                    'max_connections' => 1,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'maxIdleTime' => 60,
                ],
            ],
        ]);
        $configInterface->allows('get')->with('doctrine.database.default')->andReturn([
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'name' => 'default',
            'option' => [
                'min_connections' => 1,
                'max_connections' => 1,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'maxIdleTime' => 60,
            ],
        ]);
        $configInterface->allows('get')->with('doctrine.database.default.option')->andReturn([
            'min_connections' => 1,
            'max_connections' => 1,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'maxIdleTime' => 60,
        ]);
        $configInterface->allows('has')->andReturn(true);
        $configInterface->allows('set')->andReturnTrue();

        // Create Config with mock
        $doctrineConfig = new Config($configInterface);
        $container->set(Config::class, $doctrineConfig);

        // Mock CacheManager
        $cacheDriver = new MemoryDriver($container, []);
        $cacheInterface = Mockery::mock(CacheInterface::class);
        $cacheInterface->allows('get')->andReturn(null);
        $cacheInterface->allows('set')->andReturn(true);
        $cacheInterface->allows('delete')->andReturn(true);
        $cacheInterface->allows('clear')->andReturn(true);
        $cacheInterface->allows('getMultiple')->andReturn([]);
        $cacheInterface->allows('setMultiple')->andReturn(true);
        $cacheInterface->allows('deleteMultiple')->andReturn(true);
        $cacheInterface->allows('has')->andReturn(false);

        $cacheManager = Mockery::mock(CacheManager::class);
        $cacheManager->allows('getDriver')->with('memory')->andReturn($cacheDriver);
        $container->set(CacheManager::class, $cacheManager);

        // Mock SafeCaller
        $safeCaller = Mockery::mock(SafeCaller::class);
        $safeCaller->allows('call')->andReturnUsing(function ($callback) {
            return $callback();
        });
        $container->set(SafeCaller::class, $safeCaller);

        // Mock Logger
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->allows('info')->andReturnTrue();
        $logger->allows('warning')->andReturnTrue();
        $logger->allows('error')->andReturnTrue();
        $container->set(LoggerInterface::class, $logger);

        // Create ConnectionFactory
        $connectionFactory = new ConnectionFactory($safeCaller);
        $container->set(ConnectionFactory::class, $connectionFactory);

        // Create ORMSetupFactory
        $ormSetupFactory = new ORMSetupFactory($doctrineConfig, $cacheManager);
        $container->set(ORMSetupFactory::class, $ormSetupFactory);

        // Create PoolFactory
        $poolFactory = new PoolFactory($doctrineConfig, $container);
        $container->set(PoolFactory::class, $poolFactory);

        // Create EntityManagerFactory
        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);
        $container->set(EntityManagerFactory::class, $entityManagerFactory);

        // Create EntityManager through our Factory
        $this->entityManager = $entityManagerFactory->create('default');

        // Create database schema
        $classes = [
            $this->entityManager->getClassMetadata(User::class),
            $this->entityManager->getClassMetadata(Post::class),
            $this->entityManager->getClassMetadata(Comment::class),
            $this->entityManager->getClassMetadata(Profile::class),
            $this->entityManager->getClassMetadata(Tag::class),
        ];

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema($classes);
    });

    afterEach(function () {
        $this->entityManager->close();
        Mockery::close();
    });

    it('creates user with profile (one-to-one)', function () {
        // Create User
        $user = new User();
        $user->setName('John Doe');
        $user->setEmail('john@example.com');

        // Create Profile
        $profile = new Profile();
        $profile->setBio('Software Developer');
        $profile->setWebsite('https://johndoe.dev');
        $profile->setLocation('New York');

        // Set the bidirectional relationship
        $user->setProfile($profile);

        // Persist entities
        $this->entityManager->persist($user);
        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        // Verify one-to-one relationship
        expect($user->getProfile())->toBe($profile);
        expect($profile->getUser())->toBe($user);

        // Clear and reload from database
        $this->entityManager->clear();

        $loadedUser = $this->entityManager->find(User::class, $user->getId());
        expect($loadedUser->getProfile())->toBeInstanceOf(Profile::class);
        expect($loadedUser->getProfile()->getBio())->toBe('Software Developer');
        expect($loadedUser->getProfile()->getUser())->toBe($loadedUser);
    });

    it('creates user with posts (one-to-many)', function () {
        // Create User
        $user = new User();
        $user->setName('Jane Smith');
        $user->setEmail('jane@example.com');

        // Create Posts
        $post1 = new Post();
        $post1->setTitle('First Post');
        $post1->setContent('This is my first blog post.');
        $post1->setAuthor($user);
        $post1->setPublished(true);

        $post2 = new Post();
        $post2->setTitle('Second Post');
        $post2->setContent('This is my second blog post.');
        $post2->setAuthor($user);
        $post2->setPublished(false);

        // Add posts to user
        $user->addPost($post1);
        $user->addPost($post2);

        // Persist entities
        $this->entityManager->persist($user);
        $this->entityManager->persist($post1);
        $this->entityManager->persist($post2);
        $this->entityManager->flush();

        // Verify one-to-many relationship
        expect($user->getPosts())->toHaveCount(2);
        expect($post1->getAuthor())->toBe($user);
        expect($post2->getAuthor())->toBe($user);

        // Clear and reload from database
        $this->entityManager->clear();

        $loadedUser = $this->entityManager->find(User::class, $user->getId());
        expect($loadedUser->getPosts())->toHaveCount(2);

        $loadedPost1 = $this->entityManager->find(Post::class, $post1->getId());
        expect($loadedPost1->getAuthor()->getId())->toBe($loadedUser->getId());
    });

    it('creates post with comments (one-to-many)', function () {
        // Create User
        $author = new User();
        $author->setName('Alice');
        $author->setEmail('alice@example.com');

        $commenter = new User();
        $commenter->setName('Bob');
        $commenter->setEmail('bob@example.com');

        // Create Post
        $post = new Post();
        $post->setTitle('Interesting Article');
        $post->setContent('This article discusses various topics.');
        $post->setAuthor($author);
        $post->setPublished(true);

        // Create Comments
        $comment1 = new Comment();
        $comment1->setContent('Great article!');
        $comment1->setAuthor($commenter);
        $comment1->setPost($post);
        $comment1->setApproved(true);

        $comment2 = new Comment();
        $comment2->setContent('I disagree with some points.');
        $comment2->setAuthor($commenter);
        $comment2->setPost($post);
        $comment2->setApproved(false);

        // Add comments to post
        $post->addComment($comment1);
        $post->addComment($comment2);

        // Persist entities
        $this->entityManager->persist($author);
        $this->entityManager->persist($commenter);
        $this->entityManager->persist($post);
        $this->entityManager->persist($comment1);
        $this->entityManager->persist($comment2);
        $this->entityManager->flush();

        // Verify relationships
        expect($post->getComments())->toHaveCount(2);
        expect($comment1->getPost())->toBe($post);
        expect($comment1->getAuthor())->toBe($commenter);

        // Clear and reload from database
        $this->entityManager->clear();

        $loadedPost = $this->entityManager->find(Post::class, $post->getId());
        expect($loadedPost->getComments())->toHaveCount(2);
    });

    it('creates posts with tags (many-to-many)', function () {
        // Create User
        $user = new User();
        $user->setName('Charlie');
        $user->setEmail('charlie@example.com');

        // Create Tags
        $tag1 = new Tag();
        $tag1->setName('PHP');
        $tag1->setSlug('php');
        $tag1->setColor('#8993BE');

        $tag2 = new Tag();
        $tag2->setName('Doctrine');
        $tag2->setSlug('doctrine');
        $tag2->setColor('#FA2E2E');

        $tag3 = new Tag();
        $tag3->setName('Testing');
        $tag3->setSlug('testing');
        $tag3->setColor('#4CAF50');

        // Create Posts
        $post1 = new Post();
        $post1->setTitle('PHP Best Practices');
        $post1->setContent('Learn PHP best practices.');
        $post1->setAuthor($user);
        $post1->setPublished(true);

        $post2 = new Post();
        $post2->setTitle('Doctrine ORM Tutorial');
        $post2->setContent('Complete guide to Doctrine ORM.');
        $post2->setAuthor($user);
        $post2->setPublished(true);

        // Add tags to posts
        $post1->addTag($tag1);
        $post1->addTag($tag3);

        $post2->addTag($tag1);
        $post2->addTag($tag2);
        $post2->addTag($tag3);

        // Persist entities
        $this->entityManager->persist($user);
        $this->entityManager->persist($tag1);
        $this->entityManager->persist($tag2);
        $this->entityManager->persist($tag3);
        $this->entityManager->persist($post1);
        $this->entityManager->persist($post2);
        $this->entityManager->flush();

        // Verify many-to-many relationships
        expect($post1->getTags())->toHaveCount(2);
        expect($post2->getTags())->toHaveCount(3);
        expect($tag1->getPosts())->toHaveCount(2);
        expect($tag2->getPosts())->toHaveCount(1);
        expect($tag3->getPosts())->toHaveCount(2);

        // Clear and reload from database
        $this->entityManager->clear();

        $loadedPost1 = $this->entityManager->find(Post::class, $post1->getId());
        $loadedTag1 = $this->entityManager->find(Tag::class, $tag1->getId());

        expect($loadedPost1->getTags())->toHaveCount(2);
        expect($loadedTag1->getPosts())->toHaveCount(2);
    });

    it('handles complex entity relationships', function () {
        // Create User with Profile
        $user = new User();
        $user->setName('David Wilson');
        $user->setEmail('david@example.com');

        $profile = new Profile();
        $profile->setBio('Full-stack developer passionate about clean code');
        $profile->setWebsite('https://davidwilson.dev');
        $profile->setLocation('San Francisco');

        // Set the bidirectional relationship
        $user->setProfile($profile);

        // Create Tags
        $phpTag = new Tag();
        $phpTag->setName('PHP');
        $phpTag->setSlug('php');

        $jsTag = new Tag();
        $jsTag->setName('JavaScript');
        $jsTag->setSlug('javascript');

        // Create Post with Tags and Comments
        $post = new Post();
        $post->setTitle('Building Modern Web Applications');
        $post->setContent('A comprehensive guide to modern web development.');
        $post->setAuthor($user);
        $post->setPublished(true);
        $post->addTag($phpTag);
        $post->addTag($jsTag);

        // Add post to user collection
        $user->addPost($post);

        // Create Comments
        $comment1 = new Comment();
        $comment1->setContent('Excellent tutorial!');
        $comment1->setAuthor($user);
        $comment1->setPost($post);
        $comment1->setApproved(true);

        $comment2 = new Comment();
        $comment2->setContent('Very helpful, thank you!');
        $comment2->setAuthor($user);
        $comment2->setPost($post);
        $comment2->setApproved(true);

        // Add comments to user and post collections
        $user->addComment($comment1);
        $user->addComment($comment2);
        $post->addComment($comment1);
        $post->addComment($comment2);

        // Persist all entities
        $this->entityManager->persist($user);
        $this->entityManager->persist($profile);
        $this->entityManager->persist($phpTag);
        $this->entityManager->persist($jsTag);
        $this->entityManager->persist($post);
        $this->entityManager->persist($comment1);
        $this->entityManager->persist($comment2);
        $this->entityManager->flush();

        // Verify all relationships work together
        expect($user->getProfile())->toBe($profile);
        expect($user->getPosts())->toHaveCount(1);
        expect($user->getComments())->toHaveCount(2);
        expect($post->getTags())->toHaveCount(2);
        expect($post->getComments())->toHaveCount(2);
        expect($post->getAuthor())->toBe($user);

        // Clear and reload to test persistence
        $this->entityManager->clear();

        $loadedUser = $this->entityManager->find(User::class, $user->getId());
        expect($loadedUser->getProfile())->toBeInstanceOf(Profile::class);
        expect($loadedUser->getPosts())->toHaveCount(1);
        expect($loadedUser->getComments())->toHaveCount(2);

        $loadedPost = $loadedUser->getPosts()->first();
        expect($loadedPost->getTags())->toHaveCount(2);
        expect($loadedPost->getComments())->toHaveCount(2);
    });

    it('handles association removal correctly', function () {
        // Create entities
        $user = new User();
        $user->setName('Emma Thompson');
        $user->setEmail('emma@example.com');

        $post = new Post();
        $post->setTitle('Test Post');
        $post->setContent('Content of the test post.');
        $post->setAuthor($user);

        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setSlug('test-tag');

        $post->addTag($tag);
        $user->addPost($post);

        // Persist entities
        $this->entityManager->persist($user);
        $this->entityManager->persist($post);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        // Verify initial state
        expect($user->getPosts())->toHaveCount(1);
        expect($post->getTags())->toHaveCount(1);
        expect($tag->getPosts())->toHaveCount(1);

        // Get post ID before removal
        $postId = $post->getId();

        // Remove associations
        $user->removePost($post);
        $post->removeTag($tag);

        // Remove the post entirely since author_id is NOT NULL
        $this->entityManager->remove($post);

        $this->entityManager->flush();

        // Verify removal
        expect($user->getPosts())->toHaveCount(0);
        expect($tag->getPosts())->toHaveCount(0);

        // Verify the post was actually removed from database
        $removedPost = $this->entityManager->find(Post::class, $postId);
        expect($removedPost)->toBeNull();
    });

    it('queries related entities using DQL', function () {
        // Create test data
        $user1 = new User();
        $user1->setName('Publisher One');
        $user1->setEmail('publisher1@example.com');

        $user2 = new User();
        $user2->setName('Publisher Two');
        $user2->setEmail('publisher2@example.com');

        $phpTag = new Tag();
        $phpTag->setName('PHP');
        $phpTag->setSlug('php');

        // Create posts
        $post1 = new Post();
        $post1->setTitle('PHP Tutorial');
        $post1->setContent('Learn PHP basics.');
        $post1->setAuthor($user1);
        $post1->setPublished(true);
        $post1->addTag($phpTag);

        $post2 = new Post();
        $post2->setTitle('Advanced PHP');
        $post2->setContent('Advanced PHP concepts.');
        $post2->setAuthor($user2);
        $post2->setPublished(true);
        $post2->addTag($phpTag);

        $post3 = new Post();
        $post3->setTitle('Draft Post');
        $post3->setContent('This is a draft.');
        $post3->setAuthor($user1);
        $post3->setPublished(false);

        // Persist entities
        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($phpTag);
        $this->entityManager->persist($post1);
        $this->entityManager->persist($post2);
        $this->entityManager->persist($post3);
        $this->entityManager->flush();

        // Query published posts
        $dql = 'SELECT p FROM Post p WHERE p.published = :published';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('published', true);
        $publishedPosts = $query->getResult();

        expect($publishedPosts)->toHaveCount(2);

        // Query posts by tag
        $dql = 'SELECT p FROM Post p JOIN p.tags t WHERE t.slug = :slug';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('slug', 'php');
        $phpPosts = $query->getResult();

        expect($phpPosts)->toHaveCount(2);

        // Query users with their posts count
        $dql = 'SELECT u, COUNT(p.id) as postCount FROM User u LEFT JOIN u.posts p GROUP BY u.id';
        $query = $this->entityManager->createQuery($dql);
        $usersWithPostCount = $query->getResult();

        expect($usersWithPostCount)->toHaveCount(2);
    });
});
