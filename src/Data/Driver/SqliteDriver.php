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

namespace Laucov\Db\Data\Driver;

/**
 * Provides specific information for SQLite queries.
 */
class SqliteDriver extends AbstractDriver
{
    /**
     * Statements used to get column names from a table.
     * 
     * @var array<string>
     */
    public array $columnGetterStatements = [
        <<<SQL
            SELECT "name"
            FROM pragma_table_info('{table_name}')
            SQL,
    ];

    /**
     * Delimiter used to start identifiers quotation.
     */
    public string $identifierStartDelimiter = '"';

    /**
     * Delimiter used to end identifiers quotation.
     */
    public string $identifierEndDelimiter = '"';

    /**
     * Statements used to get tables names from the database.
     * 
     * Tables starting with "sqlite_" are ignored. See:
     * 
     * https://www.sqlite.org/lang_createtable.html#the_create_table_command
     * 
     * @var array<string>
     */
    public array $tableGetterStatements = [
        <<<SQL
            SELECT "name" FROM sqlite_schema
            WHERE "type" = 'table'
            AND "name" NOT LIKE 'sqlite_%'
            ORDER BY "name"
            SQL,
    ];
}
