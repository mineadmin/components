# Doctrine Final Classes Testing Strategy

## The Problem
Mockery cannot mock `final` classes by default. Our Doctrine components use many final classes:

- `Config` (final)
- `ConfigProvider` (final) 
- `EntityManagerFactory` (final readonly)
- `ConnectionFactory` (final readonly)
- `PoolFactory` (final)
- `Connection` (final)
- `ORMSetupFactory` (final)

## Linus-Style Solution: "Don't Mock What You Don't Need To"

### Strategy 1: Test Through Interfaces
```php
// DON'T mock the final class
$config = Mockery::mock(Config::class); // ❌ Fails

// DO mock its dependencies
$configInterface = Mockery::mock(ConfigInterface::class); // ✅ Works
$config = new Config($configInterface); // ✅ Test real behavior
```

### Strategy 2: Test Boundary Behavior Only
```php
// Instead of mocking everything, test what matters:
it('propagates exceptions from dependencies', function () {
    $poolFactory = Mockery::mock(PoolFactory::class);
    $poolFactory->shouldReceive('getPool')->andThrow(new Exception('Pool error'));
    
    $factory = new EntityManagerFactory($poolFactory, $ormSetup);
    
    expect(fn() => $factory->create())->toThrow(Exception::class, 'Pool error');
});
```

### Strategy 3: Integration Over Unit
For final classes with complex dependencies, use integration tests:

```php
// Instead of 15 unit test mocks, one integration test
it('creates entity manager with real SQLite database', function () {
    // Setup real config pointing to :memory: SQLite
    // Test end-to-end behavior
});
```

### Strategy 4: Value Object Testing
Final classes are often value objects - test their actual behavior:

```php
// Test actual config get/set behavior, not mocks
it('gets configuration values correctly', function () {
    $mockInterface = Mockery::mock(ConfigInterface::class);
    $mockInterface->shouldReceive('get')->with('doctrine.host')->andReturn('localhost');
    
    $config = new Config($mockInterface);
    
    expect($config->get('host'))->toBe('localhost');
});
```

## Key Principles

1. **Mock interfaces, not classes**
2. **Test behavior, not implementation**
3. **Use real objects when dependencies are simple**
4. **Integration tests for complex final class interactions**
5. **Focus on error propagation and boundary conditions**

This approach gives us better, more maintainable tests that actually verify real behavior.