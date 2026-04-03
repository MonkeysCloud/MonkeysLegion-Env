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
}
