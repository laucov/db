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

namespace Tests\Statement;

use Laucov\Db\Statement\ColumnDefinition;
use Laucov\Db\Statement\CreateTableStatement;
use Laucov\Db\Statement\SelectStatement;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Statement\CreateTableStatement
 */
class CreateTableStatementTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::addColumns
     * @covers ::setPrimaryKey
     * @covers ::setSelectStatement
     * @uses Laucov\Db\Statement\AbstractConditionalStatement::compileFromClause
     * @uses Laucov\Db\Statement\AbstractConditionalStatement::setFromClause
     * @uses Laucov\Db\Statement\ColumnDefinition::__construct
     * @uses Laucov\Db\Statement\ColumnDefinition::__toString
     * @uses Laucov\Db\Statement\SelectStatement::__toString
     */
    public function testCanBuildAQuery(): void
    {
        // Test a simple query.
        $expected_a = <<<SQL
            CREATE TABLE messages
            (
            id INT(11) PRIMARY KEY AUTOINCREMENT,
            sender_user_id INT(11),
            recipient_user_id INT(11),
            content VARCHAR(128),
            sent_at DATETIME,
            delivered_at DATETIME,
            read_at DATETIME
            )
            SQL;
        
        // Build.
        $actual_a = (string) (new CreateTableStatement('messages'))
            ->addColumns(
                new ColumnDefinition('id', 'INT', 11, isPk: true, isAi: true),
                new ColumnDefinition('sender_user_id', 'INT', 11),
                new ColumnDefinition('recipient_user_id', 'INT', 11),
                new ColumnDefinition('content', 'VARCHAR', 128),
                new ColumnDefinition('sent_at', 'DATETIME'),
                new ColumnDefinition('delivered_at', 'DATETIME'),
                new ColumnDefinition('read_at', 'DATETIME'),
            );

        // Compare.
        $this->assertSame($expected_a, $actual_a);

        // Test with SELECT statement.
        $expected_b = <<<SQL
            CREATE TEMPORARY TABLE IF NOT EXISTS messages_backup
            AS SELECT *
            FROM messages
            SQL;
        
        // Build.
        $select_stmt = (new SelectStatement())->setFromClause('messages');
        $statement_b = new CreateTableStatement('messages_backup', true, true);
        $actual_b = (string) $statement_b->setSelectStatement($select_stmt);

        // Compare.
        $this->assertSame($expected_b, $actual_b);

        // Test replacing the SELECT statement.
        $expected_c = <<<SQL
            CREATE TEMPORARY TABLE IF NOT EXISTS messages_backup
            (
            content VARCHAR(128)
            )
            SQL;
        $actual_c = (string) $statement_b->addColumns(
            new ColumnDefinition('content', 'VARCHAR', 128),
        );
        $this->assertSame($expected_c, $actual_c);
        
        // Test replacing the SELECT statement.
        $expected_d = <<<SQL
            CREATE TEMPORARY TABLE IF NOT EXISTS messages_backup
            AS SELECT *
            FROM messages
            SQL;
        $actual_d = (string) $statement_b->setSelectStatement($select_stmt);
        $this->assertSame($expected_d, $actual_d);

        // Set primary key as table constraint.
        $expected_e = <<<SQL
            CREATE TABLE foobars
            (
            id INT(11),
            id_year INT(11),
            foo_name VARCHAR(128),
            PRIMARY KEY (id, id_year)
            )
            SQL;
        $actual_e = (string) (new CreateTableStatement('foobars'))
            ->addColumns(
                new ColumnDefinition('id', 'INT', 11),
                new ColumnDefinition('id_year', 'INT', 11),
                new ColumnDefinition('foo_name', 'VARCHAR', 128),
            )
            ->setPrimaryKey('id', 'id_year');
        $this->assertSame($expected_e, $actual_e);
    }
}
