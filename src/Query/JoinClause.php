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

namespace Laucov\Db\Query;

use Laucov\Db\Query\Traits\ExpressionCompilerTrait;

/**
 * Provides an interface to build a SQL JOIN clause.
 */
class JoinClause extends AbstractConditionalClause implements \Stringable
{
    use ExpressionCompilerTrait;

    /**
     * Alias.
     */
    protected null|string $alias;

    /**
     * Operator.
     */
    protected JoinOperator $operator;

    /**
     * Source table or subquery.
     */
    protected string $tableOrSubquery;

    /**
     * Get the JOIN clause string representation.
     */
    public function __toString(): string
    {
        // Prepare expressions.
        $target = $this->compileExpression(
            $this->tableOrSubquery,
            $this->alias,
        );
        $constraints = implode("\n", $this->constraints);

        // Build clause.
        $clause = <<<SQL
            {$this->operator->value} JOIN {$target}
            ON {$constraints}
            SQL;
        
        return $clause;
    }

    /**
     * Set the clause's ON operator.
     */
    public function setOn(
        string $operator,
        string $table_or_subquery,
        null|string $alias = null,
    ): static {
        $this->operator = JoinOperator::from($operator);
        $this->tableOrSubquery = $table_or_subquery;
        $this->alias = $alias;

        return $this;
    }
}