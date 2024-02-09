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
 * Provides an interface to build a SQL ALTER TABLE query.
 */
class AlterTableStatement implements StatementInterface
{
    /**
     * Selected ALTER TABLE change.
     */
    protected null|TableChange $change = null;

    /**
     * Column to rename or drop.
     */
    protected null|string $column = null;

    /**
     * Column to add.
     */
    protected null|ColumnDefinition $columnDefinition = null;

    /**
     * Column to add.
     */
    protected null|string $newColumnName = null;

    /**
     * New table name.
     */
    protected null|string $newTableName = null;

    /**
     * Create the ALTER TABLE statement instance.
     */
    public function __construct(
        /**
         * Table name.
         */
        protected string $tableName,
    ) {
    }

    /**
     * Get the ALTER TABLE statement string representation.
     */
    public function __toString(): string
    {
        // Initialize statement.
        $statement = "ALTER TABLE {$this->tableName}";

        // Add table change.
        $change = match ($this->change) {
            TableChange::ADD_COLUMN
                => "ADD COLUMN {$this->columnDefinition}",
            TableChange::DROP_COLUMN
                => "DROP COLUMN {$this->column}",
            TableChange::RENAME_COLUMN
                => "RENAME COLUMN {$this->column} TO {$this->newColumnName}",
            TableChange::RENAME_TABLE
                => "RENAME TO {$this->newTableName}",
            default => null,
        };
        if ($change !== null) {
            $statement .= "\n" . $change;
        }

        return $statement;
    }

    /**
     * Add a new column.
     */
    public function addColumn(ColumnDefinition $column_definition): static
    {
        $this->change = TableChange::ADD_COLUMN;
        $this->columnDefinition = $column_definition;

        return $this;
    }

    /**
     * Drop an existing column.
     */
    public function dropColumn(string $column_name): static
    {
        $this->change = TableChange::DROP_COLUMN;
        $this->column = $column_name;

        return $this;
    }

    /**
     * Rename an existing column.
     */
    public function renameColumn(string $column_name, string $new_name): static
    {
        $this->change = TableChange::RENAME_COLUMN;;
        $this->column = $column_name;
        $this->newColumnName = $new_name;

        return $this;
    }

    /**
     * Rename the table.
     */
    public function renameTable(string $new_name): static
    {
        $this->change = TableChange::RENAME_TABLE;
        $this->newTableName = $new_name;

        return $this;
    }
}
