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

use Laucov\Db\Statement\Interfaces\StatementInterface;

/**
 * Provides an interface to build a SQL CREATE TABLE query.
 */
class CreateTableStatement implements StatementInterface
{
    /**
     * Defined columns.
     * 
     * @var array<ColumnDefinition>
     */
    protected array $columns = [];

    /**
     * Primary key column names.
     * 
     * @var array<string>
     */
    protected array $primaryKey = [];

    /**
     * SELECT statement used to build this table.
     */
    protected null|SelectStatement $selectStatement = null;

    /**
     * Create the CREATE TABLE statement instance.
     */
    public function __construct(
        /**
         * Table name.
         */
        protected string $tableName,

        /**
         * Whether to apply the IF NOT EXISTS constraint.
         */
        protected bool $skipIfExists = false,

        /**
         * Whether the table is temporary.
         */
        protected bool $isTemporary = false,
    ) {
    }

    /**
     * Get the CREATE TABLE statement string representation.
     */
    public function __toString(): string
    {
        // Initialize statement.
        $statement = "CREATE";
        if ($this->isTemporary) {
            $statement .= " TEMPORARY";
        }
        $statement .= " TABLE";
        if ($this->skipIfExists) {
            $statement .= " IF NOT EXISTS";
        }
        $statement .= " " . $this->tableName;

        // Add columns.
        if (count($this->columns) > 0) {
            $columns = implode(",\n", $this->columns);
            $statement .= "\n(\n{$columns}";
            if (count($this->primaryKey) > 0) {
                $pk = implode(', ', $this->primaryKey);
                $statement .= ",\nPRIMARY KEY ({$pk})";
            }
            $statement .= "\n)";
        }

        // Add SELECT statement.
        if ($this->selectStatement !== null) {
            $statement .= "\nAS {$this->selectStatement}";
        }

        return $statement;
    }

    /**
     * Add one or more columns to this table.
     */
    public function addColumns(ColumnDefinition ...$columns): static
    {
        // Remove previosly set SELECT statement.
        if ($this->selectStatement !== null) {
            $this->selectStatement = null;
        }

        // Add columns.
        array_push($this->columns, ...$columns);

        return $this;
    }

    /**
     * Set the table's primary key.
     */
    public function setPrimaryKey(string ...$column_names): static
    {
        $this->primaryKey = $column_names;
        return $this;
    }

    /**
     * Set a SELECT statement to define the table structure and content.
     * 
     * This will remove any previously set columns.
     */
    public function setSelectStatement(SelectStatement $statement): static
    {
        // Remove previously set values.
        if (count($this->columns) > 0) {
            $this->columns = [];
        }

        // Set statement.
        $this->selectStatement = $statement;

        return $this;
    }
}
