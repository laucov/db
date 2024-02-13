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

use Laucov\Db\Data\Connection;
use Laucov\Db\Statement\AbstractConditionalStatement;
use Laucov\Db\Statement\Clause\JoinClause;
use Laucov\Db\Statement\Clause\WhereClause;
use Laucov\Db\Statement\DeleteStatement;
use Laucov\Db\Statement\InsertStatement;
use Laucov\Db\Statement\SelectStatement;
use Laucov\Db\Statement\UpdateStatement;

/**
 * Manipules table records.
 */
class Table
{
    /**
     * Current array to push the next clause calls data.
     * 
     * @var array<array{string, string[]}>
     */
    protected array $clauseCalls;

    /**
     * Column name used to group the next query's rows.
     */
    protected null|string $groupingColumnName = null;

    /**
     * Registered JOIN clauses.
     * 
     * @var array<array{string, string, string, array{string, string[]}}>
     */
    protected array $joinClauses = [];

    /**
     * Maximum number of records to retrieve in the next SELECT query.
     */
    protected null|int $limit = null;
    
    /**
     * Number of records to skip in the next SELECT query.
     */
    protected null|int $offset = null;

    /**
     * Ordering criteria to add in the next SELECT query.
     * 
     * @var array<string[]>
     */
    protected array $ordering = [];

    /**
     * Parameters to use in the next query.
     * 
     * @var array<string, string>
     */
    protected array $parameters = [];

    /**
     * Whether to reset the logical operator to AND after the next constraint.
     */
    protected bool $resetLogicalOperator = false;

    /**
     * Result columns to add in the next SELECT query.
     * 
     * @var array<string[]>
     */
    protected array $resultColumns = [];

    /**
     * Values to set without preparation in an UPDATE statement.
     * 
     * @var array<string, string>
     */
    protected array $values = [];

    /**
     * Method calls for the next `WhereClause` object in use.
     * 
     * @var array<array{string, string[]}>
     */
    protected array $whereClauseCalls = [];

    /**
     * Create the table instance.
     */
    public function __construct(
        /**
         * Configured connection.
         */
        protected Connection $connection,

        /**
         * Table name.
         */
        protected string $tableName,
    ) {
        $this->clauseCalls = &$this->whereClauseCalls;
    }

    /**
     * Calculate the average value of a column.
     */
    public function average(string $column_name, string $alias): static
    {
        // Quote identifiers.
        $column_name = $this->connection->quoteIdentifier($column_name);
        $alias = $this->connection->quoteIdentifier($alias);

        // Add column.
        $this->resultColumns[] = ["AVG({$column_name})", $alias];

        return $this;
    }

    /**
     * Close the last open constraint group.
     */
    public function closeGroup(): static
    {
        $this->clauseCalls[] = ['endGroup', []];
        return $this;
    }

    /**
     * Count the values of a column that are not NULL.
     */
    public function count(string $column_name, string $alias): static
    {
        // Quote identifiers.
        $column_name = $this->connection->quoteIdentifier($column_name);
        $alias = $this->connection->quoteIdentifier($alias);

        // Add column.
        $this->resultColumns[] = ["COUNT({$column_name})", $alias];

        return $this;
    }

    /**
     * Run a SELECT query with a count column and return its value.
     * 
     * Mind - when choosing the column name - that NULL values are not counted.
     */
    public function countRecords(string $column_name): int
    {
        $alias = uniqid('count_');
        return $this
            ->count($column_name, $alias)
            ->selectRecords()[0][$alias];
    }

    /**
     * 
     */
    public function deleteRecords(): void
    {
        // Initialize statement.
        $table_name = $this->connection->quoteIdentifier($this->tableName);
        $stmt = new DeleteStatement($table_name);

        // Add conditional clauses.
        $this->applyWhereClause($stmt);

        // Execute the statement.
        $this->connection->query($stmt, $this->parameters);
        $this->resetTemporaryProperties();
    }

    /**
     * Filter the results.
     */
    public function filter(
        string $column_name,
        string $operator,
        null|int|float|string|array $value,
    ): static {
        $this->clauseCalls = &$this->whereClauseCalls;
        $this->constrain($column_name, $operator, $value, false);

        return $this;
    }

    /**
     * Find the greatest value from a column.
     */
    public function findMax(string $column_name, string $alias): static
    {
        // Quote identifiers.
        $column_name = $this->connection->quoteIdentifier($column_name);
        $alias = $this->connection->quoteIdentifier($alias);

        // Add column.
        $this->resultColumns[] = ["MAX({$column_name})", $alias];

        return $this;
    }

    /**
     * Find the smallest value from a column.
     */
    public function findMin(string $column_name, string $alias): static
    {
        // Quote identifiers.
        $column_name = $this->connection->quoteIdentifier($column_name);
        $alias = $this->connection->quoteIdentifier($alias);

        // Add column.
        $this->resultColumns[] = ["MIN({$column_name})", $alias];

        return $this;
    }

    /**
     * Group the records by the given column name.
     */
    public function group(string $column_name): static
    {
        $column_name = $this->connection->quoteIdentifier($column_name);
        $this->groupingColumnName = $column_name;

        return $this;
    }

    /**
     * Insert a record using an associative array.
     */
    public function insertRecord(array $values): string
    {
        // Get placeholders.
        $columns = array_keys($values);
        $placeholders = array_map(fn (string $k) => ":{$k}", $columns);

        // Quote identifiers.
        $table_name = $this->connection->quoteIdentifier($this->tableName);
        $columns = array_map([$this->connection, 'quoteIdentifier'], $columns);

        // Create statement.
        $stmt = new InsertStatement($table_name);
        $stmt
            ->setColumns(...$columns)
            ->addRowValues(...$placeholders);
        
        // Set parameters.
        $parameters = array_combine($placeholders, array_values($values));

        return $this->connection
            ->query($stmt, $parameters)
            ->getLastId();
    }

    /**
     * Insert one or more records using a list of associative arrays.
     */
    public function insertRecords(array ...$list): string
    {
        // Get identifiers.
        $table_name = $this->connection->quoteIdentifier($this->tableName);
        $columns = array_map(
            [$this->connection, 'quoteIdentifier'],
            array_keys($list[0]),
        );

        // Create statement.
        $stmt = new InsertStatement($table_name);
        $stmt->setColumns(...$columns);
        
        // Set rows.
        $parameters = [];
        foreach ($list as $i => $values) {
            // Add parameters.
            $keys = array_map(fn ($k) => "{$k}_{$i}", array_keys($values));
            $values = array_combine($keys, array_values($values));
            $parameters = array_merge($parameters, $values);
            // Add values to statement.
            $placeholders = array_map(fn (string $k) => ":{$k}", $keys);
            $stmt->addRowValues(...$placeholders);
        }

        return $this->connection
            ->query($stmt, $parameters)
            ->getLastId();
    }

    /**
     * Join another table.
     */
    public function join(
        string|SelectStatement $table_name,
        null|string $alias = null,
        string $operator = 'LEFT',
    ): static {
        // Quote table/statement.
        if (is_string($table_name)) {
            $table_name = $this->connection->quoteIdentifier($table_name);
        } else {
            $table_name = "({$table_name})";
        }

        // Add JOIN clause.
        $calls = [];
        $this->joinClauses[] = [$operator, $table_name, $alias, &$calls];

        // Set the current clause as the active one.
        $this->clauseCalls = &$calls;

        return $this;
    }

    /**
     * Set the maximum number of records to retrieve.
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Skip the given number of records.
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Set a constraint for the last JOIN operation.
     */
    public function on(
        string $column_name,
        string $operator,
        null|int|float|string|array $value,
        bool $value_is_column = true,
    ): static {
        // Get last JOIN clause argument list.
        $last_key = array_key_last($this->joinClauses);
        $this->clauseCalls = &$this->joinClauses[$last_key][3];

        // Add constraint.
        $this->constrain($column_name, $operator, $value, $value_is_column);

        return $this;
    }

    /**
     * Open a constraint group.
     */
    public function openGroup(): static
    {
        $this->clauseCalls[] = ['beginGroup', []];
        return $this;
    }

    /**
     * Use OR as the next constraint logical operator.
     */
    public function or(): static
    {
        $this->clauseCalls[] = ['setLogicalOperator', ['OR']];
        $this->resetLogicalOperator = true;

        return $this;
    }

    /**
     * Select an existing column.
     */
    public function pick(
        string $column_name,
        null|string $alias = null,
    ): static {
        // Quote identifiers.
        $column_name = $this->connection->quoteIdentifier($column_name);
        $alias = $alias !== null
            ? $this->connection->quoteIdentifier($alias)
            : null;

        // Add column.
        $this->resultColumns[] = [$column_name, $alias];

        return $this;
    }

    /**
     * Run a SELECT query and get the values of a specific column.
     */
    public function selectColumn(string $column_name): array
    {
        return array_column($this->selectRecords(), $column_name);
    }

    /**
     * Run a SELECT query using the defined conditions.
     * 
     * @todo Allow setting a class name to return objects instead of arrays.
     */
    public function selectRecords(): array
    {
        // Initialize statement.
        $stmt = new SelectStatement();
        $table_name = $this->connection->quoteIdentifier($this->tableName);
        $stmt->setFromClause($table_name);

        // Add result columns.
        foreach ($this->resultColumns as $arguments) {
            $stmt->addResultColumn(...$arguments);
        }

        // Check if any JOIN clauses are set.
        foreach ($this->joinClauses as $j) {
            // Initialize a new clause.
            $stmt->addJoinClause(function (JoinClause $clause) use ($j): void {
                // Apply each registered call for this clause.
                [$operator, $table, $alias, $calls] = $j;
                $clause->setOn($operator, $table, $alias);
                foreach ($calls as [$method, $arguments]) {
                    call_user_func_array([$clause, $method], $arguments);
                }
            });
        }

        // Add WHERE clause.
        $this->applyWhereClause($stmt);

        // Set grouping.
        if ($this->groupingColumnName !== null) {
            $stmt->groupRows($this->groupingColumnName);
        }

        // Set ordering.
        foreach ($this->ordering as $arguments) {
            $stmt->orderRows(...$arguments);
        }

        // Set offset and limit.
        if ($this->limit !== null) {
            $stmt->setLimit($this->limit);
        }
        if ($this->offset !== null) {
            $stmt->setOffset($this->offset);
        }

        // Execute the statement.
        $this->connection->query($stmt, $this->parameters);
        $this->resetTemporaryProperties();

        return $this->connection->listAssoc();
    }

    /**
     * Set a value for the next UPDATE query.
     */
    public function set(
        string $column_name,
        null|int|string $value,
        bool $value_is_column = false,
    ): static {
        // Create value placeholder.
        if ($value_is_column) {
            $value = $this->connection->quoteIdentifier($value);
        } else {
            $this->parameters[$column_name] = $value;
            $value = ':' . $column_name;
        }

        // Store value.
        $column_name = $this->connection->quoteIdentifier($column_name);
        $this->values[$column_name] = $value;

        return $this;
    }

    /**
     * Order the result by the given column name.
     */
    public function sort(string $column_name, bool $descending = false): static
    {
        $column_name = $this->connection->quoteIdentifier($column_name);
        $this->ordering[] = [$column_name, $descending ? 'DESC' : 'ASC'];

        return $this;
    }

    /**
     * Add a subquery column.
     */
    public function subquery(SelectStatement $statement, string $alias): static
    {
        $alias = $this->connection->quoteIdentifier($alias);
        $this->resultColumns[] = ["({$statement})", $alias];
        
        return $this;
    }

    /**
     * Calculate the sum of a column.
     */
    public function sum(string $column_name, string $alias): static
    {
        // Quote identifiers.
        $column_name = $this->connection->quoteIdentifier($column_name);
        $alias = $this->connection->quoteIdentifier($alias);

        // Add column.
        $this->resultColumns[] = ["SUM({$column_name})", $alias];

        return $this;
    }

    /**
     * Update all filtered records.
     */
    public function updateRecords(array $values = []): void
    {
        // Initialize statement.
        $table_name = $this->connection->quoteIdentifier($this->tableName);
        $stmt = new UpdateStatement($table_name);

        // Set values.
        foreach ($this->values as $key => $value) {
            $stmt->setValue($key, $value);
        }
        foreach ($values as $key => $value) {
            $this->parameters[$key] = $value;
            $column_name = $this->connection->quoteIdentifier($key);
            $stmt->setValue($column_name, ':' . $key);
        }

        // Add conditional clauses.
        $this->applyWhereClause($stmt);

        // Execute the statement.
        $this->connection->query($stmt, $this->parameters);
        $this->resetTemporaryProperties();
    }

    /**
     * Apply WHERE clause calls to the given statement.
     */
    protected function applyWhereClause(
        AbstractConditionalStatement $stmt
    ): void {
        // Check if any WHERE clause calls are set.
        if (count($this->whereClauseCalls) > 0) {
            // Initialize clause.
            $stmt->setWhereClause(function (WhereClause $clause): void {
                // Perform each registered call.
                foreach ($this->whereClauseCalls as [$method, $arguments]) {
                    call_user_func_array([$clause, $method], $arguments);
                }
            });
        }
    }

    /**
     * Create a placeholder name using PHP's `uniqid()`.
     */
    protected function createPlaceholderName(string $column_name): string
    {
        $column_name = str_replace('.', '_', $column_name);
        return uniqid($column_name . '_');
    }

    /**
     * Add WHERE or JOIN constraint calls.
     */
    protected function constrain(
        string $column_name,
        string $operator,
        null|int|float|string|array $value,
        bool $value_is_column,
    ): void {
        // Find filter operator.
        $operator = FilterOperator::from($operator);

        // Handle array value.
        if (is_array($value)) {
            $this->constrainArray(
                $column_name,
                $operator,
                $value,
                $value_is_column,
            );
            return;
        }

        // Parse operator.
        switch ($operator) {
            // Handle native operators.
            case FilterOperator::EQUAL_TO:
            case FilterOperator::NOT_EQUAL_TO:
            case FilterOperator::GREATER_THAN:
            case FilterOperator::GREATER_THAN_OR_EQUAL_TO:
            case FilterOperator::LESS_THAN:
            case FilterOperator::LESS_THAN_OR_EQUAL_TO:
                // Check if the value is NULL.
                if ($value === null) {
                    // Use NULL operators.
                    $where_operator = match ($operator) {
                        FilterOperator::EQUAL_TO => 'IS NULL',
                        FilterOperator::NOT_EQUAL_TO => 'IS NOT NULL',
                    };
                } else {
                    // Use the operator text.
                    $where_operator = $operator->value;
                }
                break;
            // Handle custom operators.
            case FilterOperator::STARTS_WITH:
            case FilterOperator::DOES_NOT_START_WITH:
            case FilterOperator::ENDS_WITH:
            case FilterOperator::DOES_NOT_END_WITH:
            case FilterOperator::CONTAINS:
            case FilterOperator::DOES_NOT_CONTAIN:
                // Get the correct LIKE operator.
                $where_operator = match ($operator) {
                    FilterOperator::STARTS_WITH,
                    FilterOperator::ENDS_WITH,
                    FilterOperator::CONTAINS => 'LIKE',
                    FilterOperator::DOES_NOT_START_WITH,
                    FilterOperator::DOES_NOT_END_WITH,
                    FilterOperator::DOES_NOT_CONTAIN => 'NOT LIKE',
                };
                // Place the wildcard.
                $value = match ($operator) {
                    FilterOperator::STARTS_WITH,
                    FilterOperator::DOES_NOT_START_WITH => "{$value}%",
                    FilterOperator::ENDS_WITH,
                    FilterOperator::DOES_NOT_END_WITH => "%{$value}",
                    FilterOperator::CONTAINS,
                    FilterOperator::DOES_NOT_CONTAIN => "%{$value}%",
                };
                break;
        }

        // Register placeholder.
        if ($value !== null && !$value_is_column) {
            $placeholder = $this->createPlaceholderName($column_name);
            $this->parameters[$placeholder] = $value;
            $value = ":{$placeholder}";
        }

        // Quote column name.
        $column_name = $this->connection->quoteIdentifier($column_name);
        if (is_string($value) && $value_is_column) {
            $value = $this->connection->quoteIdentifier($value);
        }

        // Add call.
        $this->clauseCalls[] = [
            'addConstraint',
            [$column_name, $where_operator, $value],
        ];
        if ($this->resetLogicalOperator) {
            $this->clauseCalls[] = ['setLogicalOperator', ['AND']];
            $this->resetLogicalOperator = false;
        }
    }

    /**
     * Filter results using an array of values.
     */
    protected function constrainArray(
        string $column_name,
        FilterOperator $operator,
        array $values,
        bool $value_is_column,
    ): void {
        // Get IN/NOT IN operator.
        $in_operator = match ($operator) {
            FilterOperator::EQUAL_TO => 'IN',
            FilterOperator::NOT_EQUAL_TO => 'NOT IN',
            default => null,
        };

        // Handle unconverted operator.
        if ($in_operator === null) {
            // Open constraint group.
            $this->clauseCalls[] = ['beginGroup', []];
            $is_negative = str_starts_with($operator->value, '!');
            // Apply a filter for each value.
            foreach (array_values($values) as $i => $value) {
                // Use AND for negative operators only.
                if ($i === 1 && !$is_negative) {
                    $this->clauseCalls[] = ['setLogicalOperator', ['OR']];
                }
                // Add filter.
                $this->filter($column_name, $operator->value, $value);
            }
            // Close constraint group and reset the logical operator.
            $this->clauseCalls[] = ['endGroup', []];
            if (!$is_negative) {
                $this->clauseCalls[] = ['setLogicalOperator', ['AND']];
            }
            return;
        }

        // Create placeholders.
        if (!$value_is_column) {
            $placeholder_prefix = $this->createPlaceholderName($column_name);
            foreach ($values as $i => $value) {
                $placeholder = "{$placeholder_prefix}_{$i}";
                $this->parameters[$placeholder] = $value;
                $values[$i] = ":{$placeholder}";
            }
        }
        $column_name = $this->connection->quoteIdentifier($column_name);
        if ($value_is_column) {
            $values = array_map(
                [$this->connection, 'quoteIdentifier'],
                $values,
            );
        }
        $values = '(' . implode(', ', $values) . ')';

        // Add call.
        $this->clauseCalls[] = [
            'addConstraint',
            [$column_name, $in_operator, $values],
        ];
    }

    /**
     * Reset temporary properties to build a new query.
     */
    protected function resetTemporaryProperties(): void
    {
        $this->groupingColumnName = null;
        $this->joinClauses = [];
        $this->limit = null;
        $this->offset = null;
        $this->ordering = [];
        $this->parameters = [];
        $this->resultColumns = [];
        $this->values = [];
        $this->whereClauseCalls = [];
    }
}
