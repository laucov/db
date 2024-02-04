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
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Query\JoinClause
 */
class JoinClauseTest extends TestCase
{
    /**
     * @covers ::__toString
     * @covers ::addConstraint
     * @covers ::setLogicalOperator
     * @covers ::setOn
     * @uses Laucov\Db\Query\Constraint::__construct
     * @uses Laucov\Db\Query\Constraint::__toString
     */
    public function testCanCreateAndStringify(): void
    {
        // Test simple JOIN.
        $clause_a = new JoinClause();
        $clause_a
            ->setOn('LEFT', 'customers')
            ->addConstraint('customers.id', '=', 'cars.customer_id');
        $this->assertSame(<<<SQL
            LEFT JOIN customers
            ON customers.id = cars.customer_id
            SQL, (string) $clause_a);
        
        // Test joining with alias.
        $clause_b = new JoinClause();
        $clause_b
            ->setOn('INNER', 'customers', 'clients')
            ->addConstraint('clients.id', '=', 'cars.customer_id');
        $this->assertSame(<<<SQL
            INNER JOIN customers AS clients
            ON clients.id = cars.customer_id
            SQL, (string) $clause_b);
    }
}
