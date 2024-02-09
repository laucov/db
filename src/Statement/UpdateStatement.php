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

namespace Laucov\Db\Statement;

/**
 * Provides an interface to build a SQL SELECT query.
 */
class UpdateStatement extends AbstractConditionalStatement
{
    /**
     * Values to update.
     * 
     * @var array<string, string>
     */
    protected array $values = [];

    /**
     * Create the UPDATE statement instance.
     */
    public function __construct(
        /**
         * Table to update.
         */
        protected string $tableName,

        /**
         * Table alias.
         */
        protected null|string $tableAlias = null,
    ) {}

    /**
     * Get the UPDATE statement string representation.
     */
    public function __toString(): string
    {
        // Initialize statement.
        $statement = $this->tableAlias !== null
            ? "UPDATE {$this->tableName} AS {$this->tableAlias}"
            : "UPDATE {$this->tableName}";

        // Add updated values.
        if (count($this->values) > 0) {
            $values = array_map(
                fn (string $v, string $k) => "{$k} = {$v}",
                $this->values,
                array_keys($this->values),
            );
            $statement .= "\nSET " . implode(', ', $values);
        }

        // Add WHERE clause.
        if ($this->whereClause !== null) {
            $statement .= "\n{$this->whereClause}";
        }

        return $statement;
    }

    /**
     * Set a value to update.
     */
    public function setValue(string $column, string $expression): static
    {
        $this->values[$column] = $expression;
        return $this;
    }
}
