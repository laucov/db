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

namespace Laucov\Db\Statement\Clause;

/**
 * Represents a SQL ordering condition.
 */
class RowOrder implements \Stringable
{
    /**
     * Create the row order instance.
     */
    public function __construct(
        /**
         * Column name.
         */
        public readonly string $columnName,

        /**
         * Order option.
         */
        public readonly OrderDirection $direction,
    ) {
    }

    /**
     * Get the row order string representation.
     */
    public function __toString(): string
    {
        return "{$this->columnName} {$this->direction->value}";
    }
}
