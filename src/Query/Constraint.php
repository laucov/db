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
 * Represents a SQL constraint.
 */
class Constraint implements \Stringable
{
    /**
     * Create the constraint instance.
     */
    public function __construct(
        /**
         * Logical operator.
         */
        public readonly null|LogicalOperator $logicalOperator,

        /**
         * First or only expression.
         */
        public readonly string $expressionA,

        /**
         * Logical operator.
         */
        public readonly null|ComparisonOperator $comparisonOperator = null,

        /**
         * Second expression to compare with the first one.
         */
        public readonly null|int|string|array $expressionB = null,
    ) {}

    /**
     * Get the constraint string representation.
     */
    public function __toString(): string
    {
        // Initialize constraint.
        $constraint = $this->logicalOperator
            ? ($this->logicalOperator->value . ' ')
            : '';
        $constraint .= $this->expressionA;

        if (
            $this->comparisonOperator === ComparisonOperator::IS_NOT_NULL
            || $this->comparisonOperator === ComparisonOperator::IS_NULL
        ) {
            // Just add IS NULL or IS NOT NULL.
            $constraint .= ' ' . $this->comparisonOperator->value;
        } elseif (
            is_array($this->expressionB)
            && $this->comparisonOperator === ComparisonOperator::NOT_BETWEEN
            || $this->comparisonOperator === ComparisonOperator::BETWEEN
        ) {
            // Add the operator and mininum/maximum values.
            $operator = $this->comparisonOperator->value;
            $min = $this->expressionB[0];
            $max = $this->expressionB[1];
            $constraint .= " {$operator} {$min} AND {$max}";
        } elseif (
            $this->comparisonOperator !== null
            && $this->expressionB !== null
        ) {
            // Add a simple comparison and its single/array value.
            if (is_array($this->expressionB)) {
                $expression = '(' . implode(', ', $this->expressionB) . ')';
            } else {
                $expression = (string) $this->expressionB;
            }
            $constraint .= " {$this->comparisonOperator->value} {$expression}";
        }

        return $constraint;
    }
}
