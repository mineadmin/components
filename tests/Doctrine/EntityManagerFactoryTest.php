<?php

include __DIR__ . '/Entity/User.php';

use Doctrine\ORM\Tools\SchemaTool;

beforeEach(function () {
    $this->config = Mockery::mock(\Hyperf\Contract\ConfigInterface::class);
    $this->config->allows('get')->andReturn([
        'paths' => [
            __DIR__.'/Entity'
        ],
        'database' =>  [
            'default' => [
                'driver' => 'pdo_sqlite',
                'path' => ':memory:',
                'option'  =>  [
                    'min_connections'   =>  1,
                    'max_connections'   =>  10,
                    'connect_timeout'   =>  10.0,
                    'wait_timeout'      =>  3.0,
                    'heartbeat'         =>  -1,
                    'maxIdleTime'       =>  60,
                    'events'    =>  [
                        \Hyperf\Pool\Event\ReleaseConnection::class
                    ]
                ]
            ],
            'test1' => [
                'driver' => 'pdo_sqlite',
                'path' => ':memory:',
                'option'  =>  [
                    'min_connections'   =>  1,
                    'max_connections'   =>  10,
                    'connect_timeout'   =>  10.0,
                    'wait_timeout'      =>  3.0,
                    'heartbeat'         =>  -1,
                    'maxIdleTime'       =>  60,
                    'events'    =>  [
                        \Hyperf\Pool\Event\ReleaseConnection::class
                    ]
                ]
            ],
        ]
    ]);

    $cacheManager = Mockery::mock(\Hyperf\Cache\CacheManager::class);
    $memoryCache = Mockery::mock(\Hyperf\Cache\Driver\MemoryDriver::class);
    $memoryCache->allows('has')->andReturn(false);
    $memoryCache->allows('getMultiple')->andReturn([]);
    $memoryCache->allows('setMultiple')->andReturnTrue();
    $cacheManager->allows('getDriver')->andReturn($memoryCache);
    $this->eventManagerFactory = new \Mine\Doctrine\EntityManagerFactory($this->config,$cacheManager);
});

it('create table',function (){
    $default = $this->eventManagerFactory->create();
    $schemaTool = new SchemaTool($default);
    $metadatas = $default->getMetadataFactory()->getAllMetadata();
    $result = $schemaTool->getCreateSchemaSql($metadatas);
    expect($result[0])->toBe('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
});

it('test', function () {

    $defaultEventManager = $this->eventManagerFactory->create();
    $testEventManager = $this->eventManagerFactory->create('test1');

    $result = $defaultEventManager->getConnection()->executeQuery('select 33');
    expect($result->fetchOne())
        ->toBe(33);

    $result = $testEventManager->getConnection()->executeQuery('select 33');
    expect($result->fetchOne())
        ->toBe(33);

});


it('test users',function (){
    $default = $this->eventManagerFactory->create();
    $schemaTool = new SchemaTool($default);
    $metadatas = $default->getMetadataFactory()->getAllMetadata();
    $schemaTool->createSchema($metadatas);


    $user = new User();
    $user->setName('test');
    $default->persist($user);
    $default->flush();


    $res = $default->find(User::class,1);

    expect($res->getName())
        ->toBe('test');
});