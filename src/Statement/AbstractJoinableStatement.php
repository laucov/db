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

use Laucov\Db\Statement\Clause\JoinClause;

/**
 * Provides methods for manipulating the statement's JOIN clauses.
 */
abstract class AbstractJoinableStatement extends AbstractConditionalStatement
{
    /**
     * Source table or subquery.
     */
    protected null|string $from = null;

    /**
     * Source alias.
     */
    protected null|string $fromAlias = null;

    /**
     * Registered JOIN clauses.
     * 
     * @var array<JoinClause>
     */
    protected array $joinClauses = [];

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
     * Get the FROM clause string representation.
     */
    protected function compileFromClause(): null|string
    {
        if ($this->from === null) {
            return null;
        }

        return $this->fromAlias !== null
            ? "FROM {$this->from} AS {$this->fromAlias}"
            : "FROM {$this->from}";
    }
}
