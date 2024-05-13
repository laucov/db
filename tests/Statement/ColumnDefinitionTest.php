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

declare(strict_types=1);

namespace Tests\Statement;

use Laucov\Db\Statement\ColumnDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Db\Statement\ColumnDefinition
 */
class ColumnDefinitionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testCanCreateAndStringify(): void
    {
        // Test with length.
        $expected_a = 'code VARCHAR(64)';
        $actual_a = (string) new ColumnDefinition('code', 'VARCHAR', 64);
        $this->assertSame($expected_a, $actual_a);

        // Test with no length.
        $expected_b = 'created_at DATETIME';
        $actual_b = (string) new ColumnDefinition('created_at', 'DATETIME');
        $this->assertSame($expected_b, $actual_b);

        // Test with length, decimals, NOT NULL and default value.
        $expected_c = 'total_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00';
        $actual_c = (string) new ColumnDefinition(
            'total_amount',
            'DECIMAL',
            16,
            false,
            '0.00',
            2,
        );
        $this->assertSame($expected_c, $actual_c);

        // Test with NOT NULL and primary key constraints.
        $expected_d = 'id INT(11) NOT NULL PRIMARY KEY AUTOINCREMENT';
        $actual_d1 = (string) new ColumnDefinition(
            'id',
            'INT',
            11,
            false,
            null,
            null,
            true,
            true,
        );
        $this->assertSame($expected_d, $actual_d1);
        $actual_d2 = (string) new ColumnDefinition(
            'id',
            'INT',
            11,
            false,
            isPk: true,
            isAi: true,
        );
        $this->assertSame($expected_d, $actual_d2);
    }
}
