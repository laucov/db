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
use Laucov\Db\Statement\UpdateStatement;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Statement\UpdateStatement
 */
class UpdateStatementTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::compileFromClause
     * @covers ::setFromClause
     * @covers ::setValue
     * @covers ::setWhereClause
     * @uses Laucov\Db\Statement\AbstractJoinableStatement::addJoinClause
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::addConstraint
     * @uses Laucov\Db\Statement\Clause\Constraint::__construct
     * @uses Laucov\Db\Statement\Clause\Constraint::__toString
     * @uses Laucov\Db\Statement\Clause\JoinClause::__toString
     * @uses Laucov\Db\Statement\Clause\JoinClause::setOn
     * @uses Laucov\Db\Statement\Clause\WhereClause::__toString
     */
    public function testCanBuildAQuery(): void
    {
        // Test a simple query.
        $expected_a = <<<SQL
            UPDATE messages AS m
            SET read_at = '2024-03-04 14:48:32'
            SQL;
        
        // Build.
        $actual_a = (string) (new UpdateStatement('messages', 'm'))
            ->setValue('read_at', "'2024-03-04 14:48:32'");

        // Compare.
        $this->assertSame($expected_a, $actual_a);

        // Test a complex query.
        $expected_b = <<<SQL
            UPDATE orders
            SET employee_id = NULL, canceled_at = '2024-02-04 13:19'
            FROM orders AS o
            INNER JOIN employees
            ON employees.id = o.employee_id
            WHERE employees.is_active != 1
            SQL;
        
        // Build.
        $actual_b = (string) (new UpdateStatement('orders'))
            ->setValue('employee_id', 'NULL')
            ->setValue('canceled_at', "'2024-02-04 13:19'")
            ->setFromClause('orders', 'o')
            ->addJoinClause(function (JoinClause $clause): void {
                $clause
                    ->setOn('INNER', 'employees')
                    ->addConstraint('employees.id', '=', 'o.employee_id');
            })
            ->setWhereClause(function (WhereClause $clause): void {
                $clause->addConstraint('employees.is_active', '!=', 1);
            });
        
        // Compare.
        $this->assertSame($expected_b, $actual_b);
    }
}
