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
 
/**
 * @coversDefaultClass \Laucov\Db\Setup\Schema
 */
class SchemaTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::alterColumn
     * @covers ::createColumn
     * @covers ::createTable
     * @covers ::dropColumn
     * @covers ::dropTable
     * @covers ::getColumns
     * @covers ::renameColumn
     * @covers ::renameTable
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Connection::getDriver
     * @uses Laucov\Db\Data\Connection::listNum
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
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
        // Create queries.
        $query_a = <<<SQL
            CREATE TABLE sales
            (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_id INT(11),
            amount DECIMAL(16,2),
            discount DECIMAL(16,2)
            )
            SQL;
        $query_b = <<<SQL
            DROP TABLE IF EXISTS inexistent_table
            SQL;
        $query_c = <<<SQL
            ALTER TABLE sales
            RENAME TO orders
            SQL;
        $query_d = <<<SQL
            ALTER TABLE orders
            ADD COLUMN employee_id INT(11)
            SQL;
        $query_e = <<<SQL
            ALTER TABLE orders
            DROP COLUMN discount
            SQL;
        $query_f = <<<SQL
            ALTER TABLE orders
            RENAME COLUMN amount TO total_amount
            SQL;
        $query_g = <<<SQL
            SELECT "name"
            FROM pragma_table_info('orders')
            SQL;
        $query_h = <<<SQL
            ALTER TABLE orders
            ADD COLUMN total_amount_alter_0 DECIMAL(20,4)
            SQL;
        $query_i = <<<SQL
            UPDATE orders SET total_amount_alter_0 = total_amount
            SQL;
        $query_j = <<<SQL
            ALTER TABLE orders
            DROP COLUMN total_amount
            SQL;
        $query_k = <<<SQL
            ALTER TABLE orders
            RENAME COLUMN total_amount_alter_0 TO total_amount
            SQL;
                
        // Mock connection.
        $conn = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs([new DriverFactory(), 'sqlite::memory:'])
            ->onlyMethods(['getStatement', 'query'])
            ->getMock();
        $conn
            ->expects($this->exactly(11))
            ->method('query')
            ->withConsecutive(
                [$this->equalTo($query_a)],
                [$this->equalTo($query_b)],
                [$this->equalTo($query_c)],
                [$this->equalTo($query_d)],
                [$this->equalTo($query_e)],
                [$this->equalTo($query_f)],
                [$this->equalTo($query_g)],
                [$this->equalTo($query_h)],
                [$this->equalTo($query_i)],
                [$this->equalTo($query_j)],
                [$this->equalTo($query_k)],
            );
        
        // Instanciate schema.
        $schema = new Schema($conn);

        // Test all operations.
        $schema
            // Create table.
            ->createTable(
                'sales',
                new ColumnDefinition('id', 'INTEGER', isPk: true, isAi: true),
                new ColumnDefinition('customer_id', 'INT', 11),
                new ColumnDefinition('amount', 'DECIMAL', 16, decimals: 2),
                new ColumnDefinition('discount', 'DECIMAL', 16, decimals: 2),
            )
            // Drop table.
            ->dropTable('inexistent_table', true)
            // Rename table.
            ->renameTable('sales', 'orders')
            ->createColumn(
                'orders',
                new ColumnDefinition('employee_id', 'INT', 11)
            )
            ->dropColumn('orders', 'discount')
            ->renameColumn('orders', 'amount', 'total_amount')
            ->alterColumn(
                'orders',
                'total_amount',
                new ColumnDefinition('total_amount', 'DECIMAL', 20, decimals: 4),
            );
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
        $this->assertIsArray($actual);
        $this->assertCount(5, $actual);
        foreach ($actual as $k => $v) {
            $this->assertArrayHasKey($k, $expected);
            $this->assertSame($expected[$k], $v);
        }
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
