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

use Laucov\Db\Statement\AlterTableStatement;
use Laucov\Db\Statement\ColumnDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Db\Statement\AlterTableStatement
 */ 
class AlterTableStatementTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::addColumn
     * @covers ::dropColumn
     * @covers ::renameColumn
     * @covers ::renameTable
     * @uses Laucov\Db\Statement\ColumnDefinition::__construct
     * @uses Laucov\Db\Statement\ColumnDefinition::__toString
     */
    public function testCanBuildAQuery(): void
    {
        // Create statement.
        $stmt = new AlterTableStatement('foobar');

        // Test renaming the table.
        $expected_a = <<<SQL
            ALTER TABLE foobar
            RENAME TO bazbaz
            SQL;
        $this->assertSame($expected_a, (string) $stmt->renameTable('bazbaz'));
        
        // Test renaming a column.
        $expected_b = <<<SQL
            ALTER TABLE foobar
            RENAME COLUMN creation_date TO created_at
            SQL;
        $this->assertSame(
            $expected_b,
            (string) $stmt->renameColumn('creation_date', 'created_at'),
        );

        // Test dropping a column.
        $expected_c = <<<SQL
            ALTER TABLE foobar
            DROP COLUMN useless_column
            SQL;
        $this->assertSame(
            $expected_c,
            (string) $stmt->dropColumn('useless_column'),
        );

        // Test adding a column.
        $expected_d = <<<SQL
            ALTER TABLE foobar
            ADD COLUMN city VARCHAR(64) NOT NULL
            SQL;
        $column_def = new ColumnDefinition('city', 'VARCHAR', 64, false);
        $this->assertSame(
            $expected_d,
            (string) $stmt->addColumn($column_def),
        );
    }
}
