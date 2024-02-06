<?php

/**
 * This file is part of Laucov's Database Library project.
 * 
 * Copyright 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package db
 * 
 * @author Rafael Covaleski Pereira <rafael.covaleski@laucov.com>
 * 
 * @license <http://www.apache.org/licenses/LICENSE-2.0> Apache License 2.0
 * 
 * @copyright © 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 */

declare(strict_types=1);

namespace Tests\Data;

use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\Db\Data\Driver\AbstractDriver;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Data\Driver\DriverFactory
 */
class DriverFactoryTest extends TestCase
{
    protected DriverFactory $factory;

    /**
     * @covers ::createDriver
     * @covers ::registerDriver
     */
    public function testCanGetAndSetDrivers(): void
    {
        // Register and get a new driver.
        $driver = $this->factory
            ->registerDriver('example', ExampleDriver::class)
            ->createDriver('example');
        $this->assertInstanceOf(ExampleDriver::class, $driver);
    }

    /**
     * @covers ::createDriver
     */
    public function testMustGetAnExistentDriver(): void
    {
        // Get inexistent driver.
        $this->expectException(\RuntimeException::class);
        $this->factory->createDriver('foobar_db');
    }

    /**
     * @covers ::registerDriver
     */
    public function testMustRegisterValidDrivers(): void
    {
        // Register invalid driver.
        $this->expectException(\InvalidArgumentException::class);
        $this->factory->registerDriver('pdo', \PDO::class);
    }

    protected function setUp(): void
    {
        $this->factory = new DriverFactory();
    }
}

/**
 * Example driver.
 */
class ExampleDriver extends AbstractDriver
{
    public array $columnGetterStatements = [];
    public string $leftIdentifierDelimiter = '«';
    public string $rightIdentifierDelimiter = '»';
    public array $tableGetterStatements = [];
}
