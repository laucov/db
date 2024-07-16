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

use Laucov\Db\Data\Connection;
use Laucov\Db\Data\ConnectionFactory;
use Laucov\Db\Data\Driver\AbstractDriver;
use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\Db\Query\Schema;
use Laucov\Db\Query\Table;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Db\Data\ConnectionFactory
 */
class ConnectionFactoryTest extends TestCase
{
    /**
     * Driver factory.
     */
    protected DriverFactory&MockObject $drivers;

    /**
     * Connection factory.
     */
    protected ConnectionFactory $factory;

    /**
     * @covers ::__construct
     * @covers ::getConnection
     * @covers ::getDefaultConnection
     * @covers ::setConnection
     * @covers ::setDefaultConnection
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Connection::getDriver
     */
    public function testCanSetAndGetConnections(): void
    {
        // Register connections.
        $dsn = 'sqlite::memory:';
        $user = null;
        $pass = null;
        $opts = [];
        $this->factory
            ->setConnection('conn_1', $dsn)
            ->setConnection('conn_2', $dsn, $user, $pass, $opts);
        
        // Get the default connection name.
        $actual = $this->factory->getDefaultConnection();
        $this->assertSame('conn_1', $actual);
        $actual = $this->factory
            ->setDefaultConnection('conn_2')
            ->getDefaultConnection();
        $this->assertSame('conn_2', $actual);

        // Mock driver factory methods.
        $driver_1 = $this->createMock(AbstractDriver::class);
        $driver_2 = $this->createMock(AbstractDriver::class);
        $this->drivers
            ->method('createDriver')
            ->willReturn($driver_1, $driver_2);

        // Test caching.
        $this->factory->setDefaultConnection('conn_1');
        $conn_a = $this->factory->getConnection();
        $this->assertInstanceOf(Connection::class, $conn_a);
        $this->assertSame($driver_1, $conn_a->getDriver());
        $conn_b = $this->factory->getConnection();
        $this->assertSame($conn_a, $conn_b);
        $conn_c = $this->factory->getConnection('conn_1');
        $this->assertSame($conn_a, $conn_c);
        $conn_d = $this->factory->getConnection('conn_2');
        $this->assertNotSame($conn_a, $conn_d);
        $this->assertInstanceOf(Connection::class, $conn_d);
        $this->assertSame($driver_2, $conn_d->getDriver());
    }

    /**
     * @covers ::getSchema
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\ConnectionFactory::__construct
     * @uses Laucov\Db\Data\ConnectionFactory::getConnection
     * @uses Laucov\Db\Data\ConnectionFactory::setConnection
     * @uses Laucov\Db\Query\Schema::__construct
     */
    public function testCanCreateSchemaObjects(): void
    {
        // Register connections.
        $this->factory
            ->setConnection('conn_1', 'sqlite::memory:')
            ->setConnection('conn_2', 'sqlite::memory:');
        
        // Create schema objects.
        $schema_1 = $this->factory->getSchema();
        $this->assertInstanceOf(Schema::class, $schema_1);
        $this->assertConnection('conn_1', $schema_1);
        $schema_2 = $this->factory->getSchema('conn_1');
        $this->assertNotSame($schema_1, $schema_2);
        $this->assertInstanceOf(Schema::class, $schema_2);
        $this->assertConnection('conn_1', $schema_2);
        $schema_3 = $this->factory->getSchema('conn_2');
        $this->assertNotSame($schema_1, $schema_3);
        $this->assertNotSame($schema_2, $schema_3);
        $this->assertInstanceOf(Schema::class, $schema_3);
        $this->assertConnection('conn_2', $schema_3);
    }

    /**
     * @covers ::getTable
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\ConnectionFactory::__construct
     * @uses Laucov\Db\Data\ConnectionFactory::getConnection
     * @uses Laucov\Db\Data\ConnectionFactory::setConnection
     * @uses Laucov\Db\Query\Table::__construct
     */
    public function testCanCreateTableObjects(): void
    {
        // Register connections.
        $this->factory
            ->setConnection('conn_1', 'sqlite::memory:')
            ->setConnection('conn_2', 'sqlite::memory:');
        
        // Create instances.
        $table_1 = $this->factory->getTable('users');
        $this->assertInstanceOf(Table::class, $table_1);
        $this->assertTableName('users', $table_1);
        $this->assertConnection('conn_1', $table_1);
        $table_2 = $this->factory->getTable('users');
        $this->assertNotSame($table_1, $table_2);
        $this->assertTableName('users', $table_2);
        $this->assertConnection('conn_1', $table_2);
        $table_3 = $this->factory->getTable('users', 'conn_2');
        $this->assertNotSame($table_1, $table_3);
        $this->assertNotSame($table_2, $table_3);
        $this->assertTableName('users', $table_3);
        $this->assertConnection('conn_2', $table_3);
        $table_4 = $this->factory->getTable('roles');
        $this->assertNotSame($table_1, $table_4);
        $this->assertNotSame($table_2, $table_4);
        $this->assertNotSame($table_3, $table_4);
        $this->assertTableName('roles', $table_4);
        $this->assertConnection('conn_1', $table_4);
    }

    /**
     * Assert that a table object has the expected connection.
     */
    protected function assertConnection(
        string $expected,
        Schema|Table $subject,
    ): void {
        $expected = $this->factory->getConnection($expected);
        $property = new \ReflectionProperty($subject, 'connection');
        $actual = $property->getValue($subject);
        $this->assertSame($expected, $actual);
    }

    /**
     * Assert that a table object has the expected table name.
     */
    protected function assertTableName(string $expected, Table $table): void
    {
        $property = new \ReflectionProperty($table, 'tableName');
        $actual = $property->getValue($table);
        $this->assertSame($expected, $actual);
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->drivers = $this->createMock(DriverFactory::class);
        $this->factory = new ConnectionFactory($this->drivers);
    }
}
