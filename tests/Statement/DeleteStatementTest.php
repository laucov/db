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

use Laucov\Db\Statement\Clause\WhereClause;
use Laucov\Db\Statement\DeleteStatement;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Statement\DeleteStatement
 */
class DeleteStatementTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::compileFromClause
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::addConstraint
     * @uses Laucov\Db\Statement\Clause\Constraint::__construct
     * @uses Laucov\Db\Statement\Clause\Constraint::__toString
     * @uses Laucov\Db\Statement\Clause\Traits\FromClauseStatementTrait::setFromClause
     * @uses Laucov\Db\Statement\Clause\Traits\FromClauseStatementTrait::setWhereClause
     * @uses Laucov\Db\Statement\Clause\WhereClause::__toString
     */
    public function testCanBuildAQuery(): void
    {
        // Test a simple query.
        $expected_a = <<<SQL
            DELETE FROM messages
            SQL;
        
        // Build.
        $actual_a = (string) new DeleteStatement('messages');

        // Compare.
        $this->assertSame($expected_a, $actual_a);

        // Test another query.
        $expected_b = <<<SQL
            DELETE FROM users AS u
            WHERE u.is_active = 0
            SQL;
        
        // Build.
        $actual_b = (string) (new DeleteStatement('users', 'u'))
            ->setWhereClause(function (WhereClause $clause): void {
                $clause->addConstraint('u.is_active', '=', 0);
            });
        
        // Compare.
        $this->assertSame($expected_b, $actual_b);
    }
}
