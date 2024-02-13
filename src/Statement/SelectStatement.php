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

use Laucov\Db\Statement\Clause\OrderDirection;
use Laucov\Db\Statement\Clause\RowOrder;

/**
 * Provides an interface to build a SQL SELECT query.
 */
class SelectStatement extends AbstractJoinableStatement
{
    /**
     * Columns used to group rows.
     * 
     * @var array<string>
     */
    protected array $groupColumns = [];

    /**
     * Maximum number of rows to retrieve.
     */
    protected null|int $limit = null;

    /**
     * Number of rows to ignore.
     */
    protected null|int $offset = null;

    /**
     * Registered ordering conditions.
     * 
     * @var array<RowOrder>
     */
    protected array $ordering = [];

    /**
     * Selected columns stored as key-value pairs.
     * 
     * Keys are aliases while values are the column name or definition.
     * 
     * @var array<ResultColumn>
     */
    protected array $resultColumns = [];

    /**
     * Get the SELECT statement string representation.
     */
    public function __toString(): string
    {
        // Add columns.
        $columns = count($this->resultColumns) > 0
            ? implode(",\n", $this->resultColumns)
            : '*';
        $statement = "SELECT {$columns}";

        // Add FROM clause.
        $from = $this->compileFromClause();
        if ($from !== null) {
            $statement .= "\n{$from}";
        }

        // Add JOIN and WHERE clauses.
        if (count($this->joinClauses) > 0) {
            $statement .= "\n" . implode("\n", $this->joinClauses);
        }
        if ($this->whereClause !== null) {
            $statement .= "\n{$this->whereClause}";
        }

        // Add GROUP BY and ORDER BY clauses.
        if (count($this->groupColumns) > 0) {
            $statement .= "\nGROUP BY " . implode(', ', $this->groupColumns);
        }
        if (count($this->ordering) > 0) {
            $statement .= "\nORDER BY " . implode(', ', $this->ordering);
        }

        // Add LIMIT and OFFSET clauses.
        if ($this->limit !== null) {
            $statement .= "\nLIMIT " . abs($this->limit);
        }
        if ($this->offset !== null) {
            $statement .= "\nOFFSET " . abs($this->offset);
        }

        return $statement;
    }

    /**
     * Select a column.
     * 
     * @param $def Column definition (field name, expression, etc.).
     * @param $alias Column alias.
     */
    public function addResultColumn(
        string $expression,
        null|string $alias = null,
    ): static {
        $this->resultColumns[] = new ResultColumn($expression, $alias);
        return $this;
    }

    /**
     * Group rows by the given column.
     */
    public function groupRows(string $column_name): static
    {
        $this->groupColumns[] = $column_name;
        return $this;
    }

    /**
     * Order rows by the given column.
     */
    public function orderRows(
        string $column_name,
        string $order = 'ASC',
    ): static {
        $order = OrderDirection::from($order);
        $this->ordering[] = new RowOrder($column_name, $order);

        return $this;
    }

    /**
     * Set the maximum number of records to retrieve.
     * 
     * @var positive-int $limit
     */
    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the number of rows to skip when retrieving.
     */
    public function setOffset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }
}
