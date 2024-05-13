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
 * Provides an interface to build a SQL CREATE TABLE query.
 */
class ColumnDefinition implements \Stringable
{
    /**
     * Create the column definition instance.
     */
    public function __construct(
        /**
         * Column name.
         */
        public string $name,

        /**
         * Type name.
         */
        protected string $type,

        /**
         * Type length.
         */
        protected null|int $length = null,

        /**
         * Whether this column receive NULL values.
         */
        protected bool $isNullable = true,

        /**
         * Column default value.
         */
        protected null|string $defaultValue = null,

        /**
         * Type decimals.
         */
        protected null|int $decimals = null,

        /**
         * Whether this column is a primary key.
         */
        protected bool $isPk = false,

        /**
         * Whether this column increments its value automatically.
         */
        protected bool $isAi = false,
    ) {
    }

    /**
     * Get the column definition string representation.
     */
    public function __toString(): string
    {
        // Initialize definition.
        $def = "{$this->name} {$this->type}";

        // Add type length.
        if ($this->length !== null) {
            $def .= $this->decimals !== null
                ? "({$this->length},{$this->decimals})"
                : "({$this->length})";
        }

        // Add NOT NULL constraint.
        if (!$this->isNullable) {
            $def .= " NOT NULL";
        }

        // Add default value.
        if ($this->defaultValue !== null) {
            $def .= " DEFAULT " . $this->defaultValue;
        }

        // Add primary key constraint.
        if ($this->isPk) {
            $def .= " PRIMARY KEY";
            if ($this->isAi) {
                $def .= " AUTOINCREMENT";
            }
        }

        return $def;
    }
}
