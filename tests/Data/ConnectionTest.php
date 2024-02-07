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
use Laucov\Db\Data\Driver\AbstractDriver;
use Laucov\Db\Data\Driver\DriverFactory;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Data\Connection
 */
class ConnectionTest extends TestCase
{
    protected Connection $conn;

    /**
     * @covers ::getDriver
     * @covers ::getDriverName
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     */
    public function testCanGetDriverData(): void
    {
        // Test getting the driver name.
        $this->assertSame('sqlite', $this->conn->getDriverName());

        // Test getting the driver object.
        $this->assertInstanceOf(
            AbstractDriver::class,
            $this->conn->getDriver(),
        );
    }

    /**
     * @covers ::__construct
     * @covers ::countAffectedRows
     * @covers ::fetchAssoc
     * @covers ::fetchClass
     * @covers ::fetchInto
     * @covers ::fetchNum
     * @covers ::getLastId
     * @covers ::getStatement
     * @covers ::listAssoc
     * @covers ::listClass
     * @covers ::listNum
     * @covers ::query
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     */
    public function testCanQueryAndFetch(): void
    {
        // Test basic query.
        $query_a = <<<SQL
            CREATE TABLE testing (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name VARCHAR(128),
                last_name VARCHAR(128)
            )
            SQL;
        $result = $this->conn->query($query_a);
        $this->assertSame($this->conn, $result);

        // Test placeholders, last ID and row count.
        $query_b = <<<SQL
            INSERT INTO testing (first_name, last_name)
            VALUES ('John', 'Doe'),
                ('Vera', 'Fooberg'),
                (:first_name, :last_name)
            SQL;
        $result_b = $this->conn
            ->query($query_b, [
                'first_name' => 'Mary',
                'last_name' => 'Barbaz',
            ])
            ->countAffectedRows();
        $this->assertSame('3', $this->conn->getLastId());
        $this->assertSame(3, $result_b);

        // Test fetching modes - single row.
        $stmt = 'SELECT id, first_name, last_name FROM testing';
        $select = fn (): Connection => $this->conn->query($stmt);
        $this->assertSame('John', $select()->fetchAssoc()['first_name']);
        $this->assertSame('John', $select()->fetchNum()[1]);
        $this->assertSame(
            'John',
            $select()->fetchClass(ExampleEntity::class)->first_name,
        );
        $object = new ExampleEntity();
        $this->assertSame('John', $select()->fetchInto($object)->first_name);

        // Prepare expected values.
        $expected = [
            ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
            ['id' => 2, 'first_name' => 'Vera', 'last_name' => 'Fooberg'],
            ['id' => 3, 'first_name' => 'Mary', 'last_name' => 'Barbaz'],
        ];

        // Test listing as associative arrays.
        $list_a = $select()->listAssoc();
        $this->assertIsArray($list_a);
        foreach ($list_a as $i => $record) {
            $this->assertIsArray($record);
            foreach($record as $key => $value) {
                $this->assertSame($expected[$i][$key], $value);
            }
        }

        // Test listing as objects - without arguments.
        $list_b = $select()->listClass(ExampleEntity::class);
        $this->assertIsArray($list_b);
        foreach ($list_b as $i => $record) {
            $this->assertInstanceOf(ExampleEntity::class, $record);
            $this->assertFalse($record->hasArgs);
            $keys = ['id', 'first_name', 'last_name'];
            foreach($keys as $key) {
                $this->assertSame($expected[$i][$key], $record->$key);
            }
        }

        // Test listing as objects - with arguments.
        $list_c = $select()->listClass(ExampleEntity::class, [true]);
        $this->assertIsArray($list_c);
        foreach ($list_c as $i => $record) {
            $this->assertInstanceOf(ExampleEntity::class, $record);
            $this->assertTrue($record->hasArgs);
            $keys = ['id', 'first_name', 'last_name'];
            foreach($keys as $key) {
                $this->assertSame($expected[$i][$key], $record->$key);
            }
        }

        // Test listing as lists.
        $list_d = $select()->listNum();
        $this->assertIsArray($list_d);
        foreach ($list_d as $i => $record) {
            $this->assertIsArray($record);
            $keys = ['id', 'first_name', 'last_name'];
            foreach($keys as $j => $key) {
                $this->assertSame($expected[$i][$key], $record[$j]);
            }
        }
    }

    /**
     * @covers ::getLastId
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     */
    public function testLastIdMustExistToGetIt(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->conn->getLastId();
    }

    /**
     * @covers ::getStatement
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     */
    public function testStatementMustExistToGetIt(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->conn->getStatement();
    }

    protected function setUp(): void
    {
        // Create the connection object.
        $this->conn = new Connection(new DriverFactory(), 'sqlite::memory:');
    }
}

/**
 * Test class.
 */
class ExampleEntity
{
    public string $first_name;
    public int $id;
    public string $last_name;
    public function __construct(public bool $hasArgs = false)
    {}
}