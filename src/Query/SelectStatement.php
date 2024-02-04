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
 * Provides an interface to build a SQL SELECT query.
 */
class SelectStatement implements \Stringable
{
    use ExpressionCompilerTrait;

    /**
     * Source table or subquery.
     */
    protected null|string $from = null;

    /**
     * Source alias.
     */
    protected null|string $fromAlias = null;

    /**
     * Columns used to group rows.
     * 
     * @var array<string>
     */
    protected array $groupColumns = [];

    /**
     * Registered JOIN clauses.
     * 
     * @var array<JoinClause>
     */
    protected array $joinClauses = [];

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
     * Registered WHERE clause.
     */
    protected null|WhereClause $whereClause = null;

    /**
     * Get the SELECT statement string representation.
     */
    public function __toString(): string
    {
        // Add columns.
        // @todo Use astherisk if columns are not defined.
        $columns = implode(', ', $this->resultColumns);
        $statement = "SELECT {$columns}";

        // Add FROM clause.
        if ($this->from !== null) {
            $from = $this->compileExpression($this->from, $this->fromAlias);
            $statement .= "\nFROM {$from}";
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
     * Start a JOIN clause.
     */
    public function addJoinClause(callable $callback): static
    {
        $clause = new JoinClause();
        $this->joinClauses[] = $clause;
        call_user_func($callback, $clause);

        return $this;
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
     * Set the source table or subquery of this statement.
     */
    public function setFromClause(
        string $table_or_subquery,
        null|string $alias = null,
    ): static {
        $this->from = $table_or_subquery;
        $this->fromAlias = $alias;

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

    /**
     * Set the WHERE clause.
     */
    public function setWhereClause(callable $callback): static
    {
        $this->whereClause = new WhereClause();
        call_user_func($callback, $this->whereClause);

        return $this;
    }
}
