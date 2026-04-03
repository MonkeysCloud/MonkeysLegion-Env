<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Tests\Unit;

use MonkeysLegion\Env\EnvManager;
use MonkeysLegion\Env\Contracts\EnvLoaderInterface;
use MonkeysLegion\Env\Contracts\EnvRepositoryInterface;
use PHPUnit\Framework\TestCase;

class EnvManagerTest extends TestCase
{
    public function testBootLoadsEnvironmentVariables(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $testData = ['APP_NAME' => 'TestApp', 'DEBUG' => 'true'];

        $loader->expects($this->once())
            ->method('load')
            ->with('/test/path')
            ->willReturn($testData);

        $repository->expects($this->once())
            ->method('setMany')
            ->with($testData);

        $manager = new EnvManager($loader, $repository);
        $manager->boot('/test/path');

        $this->assertTrue($manager->isBooted());
    }

    public function testBootOnlyRunsOnce(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $loader->expects($this->once())
            ->method('load')
            ->willReturn([]);

        $repository->expects($this->once())
            ->method('setMany');

        $manager = new EnvManager($loader, $repository);
        $manager->boot('/test/path');
        $manager->boot('/test/path'); // Second call should be ignored

        $this->assertTrue($manager->isBooted());
    }

    public function testGetRepositoryReturnsRepositoryInstance(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $manager = new EnvManager($loader, $repository);

        $this->assertSame($repository, $manager->getRepository());
    }

    public function testIsBootedReturnsFalseInitially(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $manager = new EnvManager($loader, $repository);

        $this->assertFalse($manager->isBooted());
    }

    // ========================================
    // Proxy Method Tests
    // ========================================

    public function testGetProxiesToRepository(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('get')
            ->with('TEST_KEY', 'default')
            ->willReturn('test_value');

        $manager = new EnvManager($loader, $repository);
        $result = $manager->get('TEST_KEY', 'default');

        $this->assertSame('test_value', $result);
    }

    public function testGetBoolProxiesToRepository(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('getBool')
            ->with('DEBUG', false)
            ->willReturn(true);

        $manager = new EnvManager($loader, $repository);
        $result = $manager->getBool('DEBUG', false);

        $this->assertTrue($result);
    }

    public function testGetIntProxiesToRepository(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('getInt')
            ->with('PORT', 8080)
            ->willReturn(3000);

        $manager = new EnvManager($loader, $repository);
        $result = $manager->getInt('PORT', 8080);

        $this->assertSame(3000, $result);
    }

    public function testGetFloatProxiesToRepository(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('getFloat')
            ->with('TIMEOUT', 30.0)
            ->willReturn(60.5);

        $manager = new EnvManager($loader, $repository);
        $result = $manager->getFloat('TIMEOUT', 30.0);

        $this->assertSame(60.5, $result);
    }

    public function testSetProxiesToRepository(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('set')
            ->with('TEST_KEY', 'test_value');

        $manager = new EnvManager($loader, $repository);
        $manager->set('TEST_KEY', 'test_value');
    }

    public function testSetManyProxiesToRepository(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $variables = ['KEY1' => 'value1', 'KEY2' => 'value2'];

        $repository->expects($this->once())
            ->method('setMany')
            ->with($variables);

        $manager = new EnvManager($loader, $repository);
        $manager->setMany($variables);
    }

    public function testHasProxiesToRepository(): void
    {
        $loader = $this->createMock(EnvLoaderInterface::class);
        $repository = $this->createMock(EnvRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('has')
            ->with('TEST_KEY')
            ->willReturn(true);

        $manager = new EnvManager($loader, $repository);
        $result = $manager->has('TEST_KEY');

        $this->assertTrue($result);
    }
}
