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
 * Provides an interface to build a SQL DELETE query.
 */
class DeleteStatement extends AbstractConditionalStatement
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
     * Create the DELETE statement instance.
     */
    public function __construct(
        string $table_name,
        null|string $table_alias = null,
    ) {
        $this->from = $table_name;
        $this->fromAlias = $table_alias;
    }

    /**
     * Get the DELETE statement string representation.
     */
    public function __toString(): string
    {
        // Initialize statement.
        $statement = "DELETE ";
        $statement .= $this->fromAlias !== null
            ? "FROM {$this->from} AS {$this->fromAlias}"
            : "FROM {$this->from}";

        // Add WHERE clause.
        if ($this->whereClause !== null) {
            $statement .= "\n{$this->whereClause}";
        }
        
        return $statement;
    }
}
