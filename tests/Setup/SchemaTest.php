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

namespace Tests\Setup;

use Laucov\Db\Data\Connection;
use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\Db\Setup\Schema;
use Laucov\Db\Statement\ColumnDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\AbstractArrayTest;
 
/**
 * @coversDefaultClass \Laucov\Db\Setup\Schema
 */
class SchemaTest extends AbstractArrayTest
{
    /**
     * @covers ::__construct
     * @covers ::alterColumn
     * @covers ::createColumn
     * @covers ::createTable
     * @covers ::dropColumn
     * @covers ::dropTable
     * @covers ::renameColumn
     * @covers ::renameTable
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Connection::getDriver
     * @uses Laucov\Db\Data\Connection::getStatement
     * @uses Laucov\Db\Data\Connection::listNum
     * @uses Laucov\Db\Data\Connection::query
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     * @uses Laucov\Db\Setup\Schema::getColumns
     * @uses Laucov\Db\Setup\Schema::getTables
     * @uses Laucov\Db\Statement\AlterTableStatement::__construct
     * @uses Laucov\Db\Statement\AlterTableStatement::__toString
     * @uses Laucov\Db\Statement\AlterTableStatement::addColumn
     * @uses Laucov\Db\Statement\AlterTableStatement::dropColumn
     * @uses Laucov\Db\Statement\AlterTableStatement::renameColumn
     * @uses Laucov\Db\Statement\AlterTableStatement::renameTable
     * @uses Laucov\Db\Statement\ColumnDefinition::__construct
     * @uses Laucov\Db\Statement\ColumnDefinition::__toString
     * @uses Laucov\Db\Statement\CreateTableStatement::__construct
     * @uses Laucov\Db\Statement\CreateTableStatement::__toString
     * @uses Laucov\Db\Statement\CreateTableStatement::addColumns
     * @uses Laucov\Db\Statement\DropTableStatement::__construct
     * @uses Laucov\Db\Statement\DropTableStatement::__toString
     */
    public function testCanBuildTables(): void
    {
        // Instanciate connection and schema.
        $conn = new Connection(new DriverFactory(), 'sqlite::memory:');
        $schema = new Schema($conn);

        // Test creating table.
        $schema
            ->createTable(
                'sales',
                new ColumnDefinition('id', 'INTEGER', isPk: true, isAi: true),
                new ColumnDefinition('customer_id', 'INT', 11),
                new ColumnDefinition('amount', 'DECIMAL', 16, decimals: 2),
                new ColumnDefinition('discount', 'DECIMAL', 16, decimals: 2),
            )
            ->createTable(
                'useless_table',
                new ColumnDefinition('useless_column', 'VARCHAR', 1),
            );
        
        // Check tables.
        $this->assertArrayIsLike(
            ['sales', 'useless_table'],
            $schema->getTables(),
        );

        // Insert some records.
        $conn->query(<<<SQL
            INSERT INTO sales (customer_id, amount, discount)
            VALUES
                (2, 200.01, 0.00),
                (3, 145.22, 15.00),
                (2, 741.98, 0.00),
                (5, 14.87, 5.00),
                (5, 652.44, 0.00),
                (4, 89.66, 10.00)
            SQL);
        
        // Test inserted records.
        $expected_a = [
            [1, 2, 200.01, 0],
            [2, 3, 145.22, 15],
            [3, 2, 741.98, 0],
            [4, 5, 14.87, 5],
            [5, 5, 652.44, 0],
            [6, 4, 89.66, 10],
        ];
        $actual_a = $conn
            ->query("SELECT * FROM sales")
            ->listNum();
        $this->assertArrayIsLike($expected_a, $actual_a);

        // Drop and rename tables.
        $schema
            ->dropTable('useless_table')
            ->dropTable('inexistent_table', true)
            ->renameTable('sales', 'orders');
        
        // Check tables.
        $this->assertArrayIsLike(
            ['orders'],
            $schema->getTables(),
        );

        // Create a new column.
        $schema->createColumn(
            'orders',
            new ColumnDefinition('employee_id', 'INT', 11)
        );

        // Update records.
        $conn->query(<<<SQL
            UPDATE orders SET employee_id = 2 WHERE 1
            SQL);
        
        // Check updated records.
        $expected_b = [[2], [2], [2], [2], [2], [2]];
        $actual_b = $conn
            ->query("SELECT employee_id FROM orders")
            ->listNum();
        $this->assertArrayIsLike($expected_b, $actual_b);
        
        // Modify columns.
        $schema
            ->dropColumn('orders', 'discount')
            ->dropColumn('orders', 'inexistent_column', true)
            ->renameColumn('orders', 'employee_id', 'person_id')
            ->alterColumn(
                'orders',
                'amount',
                new ColumnDefinition('amount', 'DECIMAL', 20, decimals: 1),
            );
        
        // Check new structure.
        $expected_c = [[
            'id' => 3,
            'customer_id' => 2,
            'amount' => 742,
            'person_id' => 2,
        ]];
        $expected_c = $conn
            ->query("SELECT * FROM orders WHERE id = 3")
            ->listNum();
        $this->assertArrayIsLike($expected_c, $expected_c);
    }

    /**
     * @covers ::getColumns
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Connection::getDriver
     * @uses Laucov\Db\Data\Connection::getStatement
     * @uses Laucov\Db\Data\Connection::listNum
     * @uses Laucov\Db\Data\Connection::query
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     * @uses Laucov\Db\Setup\Schema::__construct
     */
    public function testCanGetTableColumns(): void
    {
        // Create connection.
        $conn = new Connection(new DriverFactory(), 'sqlite::memory:');

        // Add table.
        $conn->query(<<<SQL
            CREATE TABLE users
            (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(128),
            email VARCHAR(128),
            password_hash VARCHAR(256),
            is_active INT(1)
            )
            SQL);
        
        // Get column names.
        $schema = new Schema($conn);
        $expected = ['id', 'name', 'email', 'password_hash', 'is_active'];
        $actual = $schema->getColumns('users');
        $this->assertArrayIsLike($expected, $actual);
    }

    /**
     * @covers ::getTables
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Connection::getDriver
     * @uses Laucov\Db\Data\Connection::getStatement
     * @uses Laucov\Db\Data\Connection::listNum
     * @uses Laucov\Db\Data\Connection::query
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     * @uses Laucov\Db\Setup\Schema::__construct
     */
    public function testCanGetTables(): void
    {
        // Create connection.
        $conn = new Connection(new DriverFactory(), 'sqlite::memory:');

        // Add tables.
        $conn
            ->query(
                <<<SQL
                    CREATE TABLE users
                    (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(128),
                    email VARCHAR(128),
                    password_hash VARCHAR(256),
                    is_active INT(1)
                    )
                    SQL
            )
            ->query(
                <<<SQL
                    CREATE TABLE donations
                    (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INT(11),
                    amount DECIMAL(16,2)
                    )
                    SQL
            );
        
        // Get table names.
        $schema = new Schema($conn);
        $expected = ['donations', 'users'];
        $actual = $schema->getTables();
        $this->assertIsArray($actual);
        $this->assertCount(2, $actual);
        foreach ($actual as $k => $v) {
            $this->assertArrayHasKey($k, $expected);
            $this->assertSame($expected[$k], $v);
        }
    }
}
