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

namespace Tests\Statement\Clause;

use Laucov\Db\Statement\Clause\WhereClause;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Statement\Clause\WhereClause
 */
class WhereClauseTest extends TestCase
{
    /**
     * @covers ::__toString
     * @covers ::addConstraint
     * @covers ::setLogicalOperator
     * @uses Laucov\Db\Statement\Clause\Constraint::__construct
     * @uses Laucov\Db\Statement\Clause\Constraint::__toString
     */
    public function testCanCreateAndStringify(): void
    {
        // Test empty WHERE.
        $this->assertSame('WHERE 1', (string) new WhereClause());

        // Test with constraints.
        $clause_a = new WhereClause();
        $clause_a
            ->addConstraint('first_name', 'LIKE', "'John%'")
            ->addConstraint('age', '>=', 18)
            ->setLogicalOperator('OR')
            ->addConstraint('age', '>=', 16)
            ->setLogicalOperator('AND')
            ->addConstraint('is_emancipated', '=', 1);
        $this->assertSame(<<<SQL
            WHERE first_name LIKE 'John%'
            AND age >= 18
            OR age >= 16
            AND is_emancipated = 1
            SQL, (string) $clause_a);
        
        // Test manual "WHERE 1".
        $clause_b = new WhereClause();
        $clause_b->addConstraint('1');
        $this->assertSame('WHERE 1', (string) $clause_b);
    }
}
