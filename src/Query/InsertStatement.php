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

/**
 * Provides an interface to build a SQL INSERT query.
 */
class InsertStatement implements \Stringable
{
    /**
     * Columns to fill in this insertion.
     * 
     * @var array<string>
     */
    protected array $columns = [];

    /**
     * SELECT statement used to insert values.
     */
    protected null|SelectStatement $selectStatement = null;

    /**
     * Values to insert.
     * 
     * @var array<string[]>
     */
    protected array $values = [];

    /**
     * Create the INSERT statement instance.
     */
    public function __construct(
        /**
         * Table to insert.
         */
        protected string $tableName,

        /**
         * Table alias.
         */
        protected null|string $tableAlias = null,
    ) {}

    /**
     * Get the INSERT statement string representation.
     */
    public function __toString(): string
    {
        // Initialize statement.
        $statement = $this->tableAlias !== null
            ? "INSERT INTO {$this->tableName} AS {$this->tableAlias}"
            : "INSERT INTO {$this->tableName}";
        
        // Define columns.
        if (count($this->columns) > 0) {
            $statement .= ' (' . implode(', ', $this->columns) . ')';
        }

        // Add values.
        if (count($this->values) > 0) {
            // Format.
            $values = array_map(
                fn ($v) => '(' . implode(', ', $v) . ')',
                $this->values,
            );
            // Add values.
            $statement .= "\nVALUES\n" . implode(",\n", $values);
        }

        // Add SELECT statement.
        if ($this->selectStatement !== null) {
            $statement .= "\n({$this->selectStatement})";
        }

        return $statement;
    }

    /**
     * Add a row to insert.
     * 
     * This will remove any SELECT statement used to set the row values.
     */
    public function addRowValues(string ...$values): static
    {
        // Remove any SELECT statement in use.
        if ($this->selectStatement !== null) {
            $this->selectStatement = null;
        }

        // Add values.
        $this->values[] = $values;

        return $this;
    }

    /**
     * Set the columns that will receive values in this insertion.
     */
    public function setColumns(string ...$columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Set a SELECT statement to
     * 
     * This will remove any previously set row values.
     */
    public function setSelectStatement(SelectStatement $statement): static
    {
        // Remove previously set values.
        if (count($this->values) > 0) {
            $this->values = [];
        }

        // Set statement.
        $this->selectStatement = $statement;

        return $this;
    }
}
