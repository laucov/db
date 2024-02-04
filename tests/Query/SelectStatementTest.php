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

use Laucov\Db\Query\JoinClause;
use Laucov\Db\Query\SelectStatement;
use Laucov\Db\Query\WhereClause;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Query\SelectStatement
 */
class SelectStatementTest extends TestCase
{
    /**
     * @covers ::__toString
     * @covers ::addJoinClause
     * @covers ::addResultColumn
     * @covers ::groupRows
     * @covers ::orderRows
     * @covers ::setFromClause
     * @covers ::setLimit
     * @covers ::setOffset
     * @covers ::setWhereClause
     * @uses Laucov\Db\Query\AbstractConditionalClause::addConstraint
     * @uses Laucov\Db\Query\AbstractConditionalClause::setLogicalOperator
     * @uses Laucov\Db\Query\Constraint::__construct
     * @uses Laucov\Db\Query\Constraint::__toString
     * @uses Laucov\Db\Query\JoinClause::__toString
     * @uses Laucov\Db\Query\JoinClause::setOn
     * @uses Laucov\Db\Query\ResultColumn::__construct
     * @uses Laucov\Db\Query\ResultColumn::__toString
     * @uses Laucov\Db\Query\RowOrder::__construct
     * @uses Laucov\Db\Query\RowOrder::__toString
     * @uses Laucov\Db\Query\Traits\ExpressionCompilerTrait::compileExpression
     * @uses Laucov\Db\Query\WhereClause::__toString
     * 
     * @todo Laucov\Db\Query\JoinClause
     * @todo Laucov\Db\Query\OrderDirection
     * @todo Laucov\Db\Query\ResultColumn
     * @todo Laucov\Db\Query\RowOrder
     * @todo Laucov\Db\Query\WhereClause
     */
    public function testCanBuildAQuery(): void
    {
        // Create expected query.
        $expected = <<<SQL
            SELECT model, cars.brand_name AS brand, drivers.name AS driver
            FROM cars
            LEFT JOIN customers AS drivers
            ON drivers.id = cars.customer_id
            WHERE cars.color = 'blue'
            AND cars.registration IS NOT NULL
            OR cars.is_registering = 1
            GROUP BY vin
            ORDER BY drivers.name ASC, cars.id DESC
            LIMIT 100
            OFFSET 200
            SQL;
        
        // Build actual query.
        $actual = (string) (new SelectStatement())
            ->addResultColumn('model')
            ->addResultColumn('cars.brand_name', 'brand')
            ->addResultColumn('drivers.name', 'driver')
            ->setFromClause('cars')
            ->addJoinClause(function (JoinClause $clause): void {
                $clause
                    ->setOn('LEFT', 'customers', 'drivers')
                    ->addConstraint('drivers.id', '=', 'cars.customer_id');
            })
            ->setWhereClause(function (WhereClause $clause): void {
                $clause
                    ->addConstraint('cars.color', '=', "'blue'")
                    ->addConstraint('cars.registration', 'IS NOT NULL')
                    ->setLogicalOperator('OR')
                    ->addConstraint('cars.is_registering', '=', 1);
            })
            ->groupRows('vin')
            ->orderRows('drivers.name')
            ->orderRows('cars.id', 'DESC')
            ->setLimit(100)
            ->setOffset(200);
        
        // Compare queries.
        $this->assertSame($expected, $actual);
    }
}
