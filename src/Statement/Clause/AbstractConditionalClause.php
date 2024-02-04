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

namespace Laucov\Db\Statement\Clause;

/**
 * Provides an interface to build a SQL conditional clause.
 */
class AbstractConditionalClause
{
    /**
     * Registered constraints.
     * 
     * @var array<Constraint>
     */
    protected array $constraints = [];

    /**
     * Current logical operator.
     */
    protected LogicalOperator $logicalOperator = LogicalOperator::AND;

    /**
     * Add a constraint to this clause.
     */
    public function addConstraint(
        string $expression_a,
        null|string $operator = null,
        null|int|string|array $expression_b = null,
    ): static {
        // Set comparison operator.
        $comparison_operator = $operator !== null
            ? ComparisonOperator::from($operator)
            : null;

        // Set logical operator.
        $logical_operator = count($this->constraints) > 0
            ? $this->logicalOperator
            : null;
        
        // Add the constraint object.
        $this->constraints[] = new Constraint(
            $logical_operator,
            $expression_a,
            $comparison_operator,
            $expression_b,
        );

        return $this;
    }

    /**
     * Set the logical operator to join the next constraints.
     */
    public function setLogicalOperator(string $operator): static
    {
        $this->logicalOperator = LogicalOperator::from($operator);
        return $this;
    }
}
