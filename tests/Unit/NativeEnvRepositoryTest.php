<?php

declare(strict_types=1);

namespace MonkeysLegion\Env\Tests\Unit;

use MonkeysLegion\Env\Repositories\NativeEnvRepository;
use MonkeysLegion\Env\Exceptions\InvalidEnvironmentVariableException;
use PHPUnit\Framework\TestCase;

class NativeEnvRepositoryTest extends TestCase
{
    private NativeEnvRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new NativeEnvRepository();

        // Clean environment before each test
        putenv('TEST_VAR');
        unset($_ENV['TEST_VAR'], $_SERVER['TEST_VAR']);
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        putenv('TEST_VAR');
        putenv('TEST_BOOL');
        putenv('TEST_INT');
        putenv('TEST_FLOAT');
        unset($_ENV['TEST_VAR'], $_SERVER['TEST_VAR']);
        unset($_ENV['TEST_BOOL'], $_SERVER['TEST_BOOL']);
        unset($_ENV['TEST_INT'], $_SERVER['TEST_INT']);
        unset($_ENV['TEST_FLOAT'], $_SERVER['TEST_FLOAT']);
    }

    public function testGetReturnsValueWhenVariableExists(): void
    {
        $this->repository->set('TEST_VAR', 'test_value');

        $result = $this->repository->get('TEST_VAR');

        $this->assertSame('test_value', $result);
    }

    public function testGetReturnsDefaultWhenVariableDoesNotExist(): void
    {
        $result = $this->repository->get('NONEXISTENT_VAR', 'default_value');

        $this->assertSame('default_value', $result);
    }

    public function testGetReturnsEmptyStringAsDefaultWhenNotProvided(): void
    {
        $result = $this->repository->get('NONEXISTENT_VAR');

        $this->assertSame('', $result);
    }

    public function testGetBoolReturnsTrueForTruthyValues(): void
    {
        $truthyValues = ['true', 'True', 'TRUE', '(true)', '1', 'yes', 'YES', 'on', 'ON'];

        foreach ($truthyValues as $value) {
            $this->repository->set('TEST_BOOL', $value);
            $result = $this->repository->getBool('TEST_BOOL');
            $this->assertTrue($result, "Failed for value: $value");
        }
    }

    public function testGetBoolReturnsFalseForFalsyValues(): void
    {
        $falsyValues = ['false', 'False', 'FALSE', '(false)', '0', 'no', 'NO', 'off', 'OFF', ''];

        foreach ($falsyValues as $value) {
            $this->repository->set('TEST_BOOL', $value);
            $result = $this->repository->getBool('TEST_BOOL');
            $this->assertFalse($result, "Failed for value: $value");
        }
    }

    public function testGetBoolReturnsDefaultWhenVariableDoesNotExist(): void
    {
        $result = $this->repository->getBool('NONEXISTENT_VAR', true);

        $this->assertTrue($result);
    }

    public function testGetIntConvertsStringToInteger(): void
    {
        $this->repository->set('TEST_INT', '42');

        $result = $this->repository->getInt('TEST_INT');

        $this->assertSame(42, $result);
    }

    public function testGetIntReturnsDefaultWhenVariableDoesNotExist(): void
    {
        $result = $this->repository->getInt('NONEXISTENT_VAR', 100);

        $this->assertSame(100, $result);
    }

    public function testGetFloatConvertsStringToFloat(): void
    {
        $this->repository->set('TEST_FLOAT', '3.14');

        $result = $this->repository->getFloat('TEST_FLOAT');

        $this->assertSame(3.14, $result);
    }

    public function testGetFloatReturnsDefaultWhenVariableDoesNotExist(): void
    {
        $result = $this->repository->getFloat('NONEXISTENT_VAR', 2.5);

        $this->assertSame(2.5, $result);
    }

    public function testSetUpdatesAllEnvironmentSources(): void
    {
        $this->repository->set('TEST_VAR', 'new_value');

        $this->assertSame('new_value', $_ENV['TEST_VAR']);
        $this->assertSame('new_value', $_SERVER['TEST_VAR']);
        $this->assertSame('new_value', getenv('TEST_VAR'));
    }

    public function testSetManyUpdatesMultipleVariables(): void
    {
        $variables = [
            'VAR1' => 'value1',
            'VAR2' => 'value2',
            'VAR3' => 'value3',
        ];

        $this->repository->setMany($variables);

        $this->assertSame('value1', $this->repository->get('VAR1'));
        $this->assertSame('value2', $this->repository->get('VAR2'));
        $this->assertSame('value3', $this->repository->get('VAR3'));

        // Cleanup
        putenv('VAR1');
        putenv('VAR2');
        putenv('VAR3');
    }

    public function testSetManyThrowsExceptionForNonStringKey(): void
    {
        $this->expectException(InvalidEnvironmentVariableException::class);
        $this->expectExceptionMessage('Environment variable keys must be strings');

        $this->repository->setMany([123 => 'value']);
    }

    public function testSetManyThrowsExceptionForNonStringValue(): void
    {
        $this->expectException(InvalidEnvironmentVariableException::class);
        $this->expectExceptionMessage("Environment variable 'TEST_VAR' must be a string");

        $this->repository->setMany(['TEST_VAR' => ['array']]);
    }

    public function testHasReturnsTrueWhenVariableExists(): void
    {
        $this->repository->set('TEST_VAR', 'value');

        $this->assertTrue($this->repository->has('TEST_VAR'));
    }

    public function testHasReturnsFalseWhenVariableDoesNotExist(): void
    {
        $this->assertFalse($this->repository->has('NONEXISTENT_VAR'));
    }
}
