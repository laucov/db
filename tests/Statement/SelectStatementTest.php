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

use Laucov\Db\Statement\Clause\JoinClause;
use Laucov\Db\Statement\Clause\WhereClause;
use Laucov\Db\Statement\SelectStatement;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Statement\SelectStatement
 */
class SelectStatementTest extends TestCase
{
    /**
     * @covers ::__toString
     * @covers ::addJoinClause
     * @covers ::addResultColumn
     * @covers ::compileFromClause
     * @covers ::groupRows
     * @covers ::orderRows
     * @covers ::setFromClause
     * @covers ::setLimit
     * @covers ::setOffset
     * @covers ::setWhereClause
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::addConstraint
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::setLogicalOperator
     * @uses Laucov\Db\Statement\Clause\Constraint::__construct
     * @uses Laucov\Db\Statement\Clause\Constraint::__toString
     * @uses Laucov\Db\Statement\Clause\JoinClause::__toString
     * @uses Laucov\Db\Statement\Clause\JoinClause::setOn
     * @uses Laucov\Db\Statement\Clause\RowOrder::__construct
     * @uses Laucov\Db\Statement\Clause\RowOrder::__toString
     * @uses Laucov\Db\Statement\Clause\WhereClause::__toString
     * @uses Laucov\Db\Statement\ResultColumn::__construct
     * @uses Laucov\Db\Statement\ResultColumn::__toString
     */
    public function testCanBuildAQuery(): void
    {
        // Test a simple query.
        $expected_a = <<<SQL
            SELECT *
            FROM cars
            FULL JOIN customers
            ON customers.id = cars.customer_id
            SQL;
        
        // Build.
        $actual_a =  (string) (new SelectStatement())
            ->setFromClause('cars')
            ->addJoinClause(function (JoinClause $clause): void {
                $clause
                    ->setOn('FULL', 'customers')
                    ->addConstraint('customers.id', '=', 'cars.customer_id');
            });
        
        // Compare.
        $this->assertSame($expected_a, $actual_a);
        
        // Test a complex query.
        $expected_b = <<<SQL
            SELECT model,
            c.brand_name AS brand,
            drivers.name AS driver
            FROM cars AS c
            LEFT JOIN customers AS drivers
            ON drivers.id = c.customer_id
            WHERE c.color = 'blue'
            AND c.registration IS NOT NULL
            OR c.is_registering = 1
            GROUP BY vin
            ORDER BY drivers.name ASC, c.id DESC
            LIMIT 100
            OFFSET 200
            SQL;
        
        // Build.
        $actual_b = (string) (new SelectStatement())
            ->addResultColumn('model')
            ->addResultColumn('c.brand_name', 'brand')
            ->addResultColumn('drivers.name', 'driver')
            ->setFromClause('cars', 'c')
            ->addJoinClause(function (JoinClause $clause): void {
                $clause
                    ->setOn('LEFT', 'customers', 'drivers')
                    ->addConstraint('drivers.id', '=', 'c.customer_id');
            })
            ->setWhereClause(function (WhereClause $clause): void {
                $clause
                    ->addConstraint('c.color', '=', "'blue'")
                    ->addConstraint('c.registration', 'IS NOT NULL')
                    ->setLogicalOperator('OR')
                    ->addConstraint('c.is_registering', '=', 1);
            })
            ->groupRows('vin')
            ->orderRows('drivers.name')
            ->orderRows('c.id', 'DESC')
            ->setLimit(100)
            ->setOffset(200);
        
        // Compare.
        $this->assertSame($expected_b, $actual_b);

        // Test query without FROM clause.
        $expected_c = <<<SQL
            SELECT 'Hello, World!' AS msg
            SQL;
        
        // Build.
        $actual_c = (string) (new SelectStatement())
            ->addResultColumn("'Hello, World!'", 'msg');
        
        // Compare.
        $this->assertSame($expected_c, $actual_c);
    }
}
