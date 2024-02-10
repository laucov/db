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

namespace Laucov\Db\Setup;

use Laucov\Db\Data\Connection;
use Laucov\Db\Statement\AlterTableStatement;
use Laucov\Db\Statement\ColumnDefinition as ColumnDef;
use Laucov\Db\Statement\CreateTableStatement;
use Laucov\Db\Statement\DropTableStatement;

/**
 * Manipulates database structures.
 */
class Schema
{
    /**
     * Database connection.
     */
    protected Connection $connection;

    /**
     * Create the schema instance.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Change a column.
     * 
     * This method will drop the column and re-create it.
     * 
     * All data will be preserved following the new column types.
     */
    public function alterColumn(
        string $table_name,
        string $column_name,
        ColumnDef $column,
    ): static {
        // Set a temporary name.
        $temp_name = uniqid("{$column_name}_alter_");

        // Save the original name and create the column.
        $final_name = $column->name;
        $column->name = $temp_name;
        $this->createColumn($table_name, $column);

        // Copy data into the new column.
        $this->connection->query(<<<SQL
            UPDATE {$table_name} SET {$temp_name} = {$column_name}
            SQL);
        
        // Replace the old column.
        $this->dropColumn($table_name, $column_name);
        $this->renameColumn($table_name, $temp_name, $final_name);

        return $this;
    }

    /**
     * Add a column to a table.
     */
    public function createColumn(string $table_name, ColumnDef $column): static
    {
        $stmt = new AlterTableStatement($table_name);
        $stmt->addColumn($column);
        $this->connection->query($stmt);

        return $this;
    }

    /**
     * Create a table.
     */
    public function createTable(string $name, ColumnDef ...$columns): static
    {
        $stmt = new CreateTableStatement($name);
        $stmt->addColumns(...$columns);
        $this->connection->query($stmt);

        return $this;
    }

    /**
     * Drop a column from the given table.
     */
    public function dropColumn(string $table_name, string $column_name): static
    {
        $stmt = new AlterTableStatement($table_name);
        $stmt->dropColumn($column_name);
        $this->connection->query($stmt);

        return $this;
    }

    /**
     * Drop a table.
     */
    public function dropTable(string $name, bool $if_exists = false): static
    {
        $stmt = new DropTableStatement($name, $if_exists);
        $this->connection->query($stmt);

        return $this;
    }

    /**
     * Get the columns of a table.
     * 
     * @var array<string>
     */
    public function getColumns(string $table_name): array
    {
        $template = $this->connection->getDriver()->columnGetterStatements;
        $stmts = str_replace('{table_name}', $table_name, $template);
        foreach ($stmts as $stmt) {
            $this->connection->query($stmt);
        }

        return array_column($this->connection->listNum(), 0);
    }

    /**
     * Get all tables stored in this database.
     * 
     * @var array<string>
     */
    public function getTables(): array
    {
        $stmts = $this->connection->getDriver()->tableGetterStatements;
        foreach ($stmts as $stmt) {
            $this->connection->query($stmt);
        }

        return array_column($this->connection->listNum(), 0);
    }

    /**
     * Rename a column from the given table.
     */
    public function renameColumn(
        string $table_name,
        string $column_name,
        string $new_name,
    ): static {
        $stmt = new AlterTableStatement($table_name);
        $stmt->renameColumn($column_name, $new_name);
        $this->connection->query($stmt);

        return $this;
    }

    /**
     * Rename a table.
     */
    public function renameTable(string $name, string $new_name): static
    {
        $stmt = new AlterTableStatement($name);
        $stmt->renameTable($new_name);
        $this->connection->query($stmt);

        return $this;
    }
}
