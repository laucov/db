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

namespace Tests\Query;

use Laucov\Db\Query\InsertStatement;
use Laucov\Db\Query\SelectStatement;
use Laucov\Db\Query\WhereClause;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Db\Query\InsertStatement
 */
class InsertStatementTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::addRowValues
     * @covers ::setColumns
     * @covers ::setSelectStatement
     * @uses Laucov\Db\Query\AbstractConditionalClause::addConstraint
     * @uses Laucov\Db\Query\Constraint::__construct
     * @uses Laucov\Db\Query\Constraint::__toString
     * @uses Laucov\Db\Query\ResultColumn::__construct
     * @uses Laucov\Db\Query\ResultColumn::__toString
     * @uses Laucov\Db\Query\SelectStatement::__toString
     * @uses Laucov\Db\Query\SelectStatement::addResultColumn
     * @uses Laucov\Db\Query\Traits\JoinClauseStatementTrait::compileFromClause
     * @uses Laucov\Db\Query\Traits\JoinClauseStatementTrait::setFromClause
     * @uses Laucov\Db\Query\Traits\JoinClauseStatementTrait::setWhereClause
     * @uses Laucov\Db\Query\WhereClause::__toString
     */
    public function testCanCreateAndStringify(): void
    {
        // Test simple insertion.
        $expected_a = <<<SQL
            INSERT INTO products AS p (descr, color, price)
            VALUES
            ('Foobar', 'purple', 8.78),
            ('Bazbaz', 'gray', 1.42)
            SQL;
        
        // Build.
        $actual_a = (string) (new InsertStatement('products', 'p'))
            ->setColumns('descr', 'color', 'price')
            ->addRowValues("'Foobar'", "'purple'", '8.78')
            ->addRowValues("'Bazbaz'", "'gray'", '1.42');
        
        // Compare.
        $this->assertSame($expected_a, $actual_a);

        // Test subquery insertion.
        $expected_b = <<<SQL
            INSERT INTO products_backup
            (SELECT descr, color, price
            FROM products
            WHERE 1)
            SQL;
        
        // Build.
        $select_stmt = (new SelectStatement())
            ->addResultColumn('descr')
            ->addResultColumn('color')
            ->addResultColumn('price')
            ->setFromClause('products')
            ->setWhereClause(function (WhereClause $clause): void {
                $clause->addConstraint('1');
            });
        $stmt_b = (new InsertStatement('products_backup'))
            ->setSelectStatement($select_stmt);
        
        // Compare.
        $this->assertSame($expected_b, (string) $stmt_b);

        // Test resetting to row values.
        // Shall not contain the SELECT statement.
        $expected_c = <<<SQL
            INSERT INTO products_backup (descr, color, price)
            VALUES
            ('Foobar', 'purple', 8.78)
            SQL;
        $stmt_b
            ->setColumns('descr', 'color', 'price')
            ->addRowValues("'Foobar'", "'purple'", '8.78');
        $this->assertSame($expected_c, (string) $stmt_b);

        // Test resetting to statement.
        // Shall not contain the value lists.
        $expected_d = <<<SQL
            INSERT INTO products_backup (descr, color, price)
            (SELECT descr, color, price
            FROM products
            WHERE 1)
            SQL;
        $stmt_b->setSelectStatement($select_stmt);
        $this->assertSame($expected_d, (string) $stmt_b);
    }
}
