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

namespace Tests\Statement\Clause;

use Laucov\Db\Statement\Clause\ComparisonOperator;
use Laucov\Db\Statement\Clause\Constraint;
use Laucov\Db\Statement\Clause\LogicalOperator;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Statement\Clause\Constraint
 */
class ConstraintTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testCanCreateAndStringify(): void
    {
        // Create test values.
        $tests = [
            [
                'first_name',
                "'John'",
                [
                    ComparisonOperator::EQUAL_TO,
                    ComparisonOperator::NOT_EQUAL_TO,
                    ComparisonOperator::LIKE,
                    ComparisonOperator::NOT_LIKE,
                ],
            ],
            [
                'age',
                "36",
                [
                    ComparisonOperator::GREATER_THAN,
                    ComparisonOperator::GREATER_THAN_OR_EQUAL_TO,
                    ComparisonOperator::LESS_THAN,
                    ComparisonOperator::LESS_THAN_OR_EQUAL_TO,
                ],
            ],
            [
                'pass',
                null,
                [
                    ComparisonOperator::IS_NULL,
                    ComparisonOperator::IS_NOT_NULL,
                ],
            ],
            [
                'job_title',
                ["'Assistant'", "'Manager'", "'Director'"],
                [
                    ComparisonOperator::IN,
                    ComparisonOperator::NOT_IN,
                ],
            ],
            [
                'score',
                [15, 20],
                [
                    ComparisonOperator::BETWEEN,
                    ComparisonOperator::NOT_BETWEEN,
                ],
            ],
        ];

        // Set expecteds values.
        $expected = [
            "first_name = 'John'",
            "first_name != 'John'",
            "first_name LIKE 'John'",
            "first_name NOT LIKE 'John'",
            "age > 36",
            "age >= 36",
            "age < 36",
            "age <= 36",
            "pass IS NULL",
            "pass IS NOT NULL",
            "job_title IN ('Assistant', 'Manager', 'Director')",
            "job_title NOT IN ('Assistant', 'Manager', 'Director')",
            "score BETWEEN 15 AND 20",
            "score NOT BETWEEN 15 AND 20",
        ];

        // Test values.
        $test = 0;
        foreach ($tests as $args) {
            foreach ($args[2] as $comparison_operator) {
                $this->assertSame($expected[$test], (string) new Constraint(
                    null,
                    $args[0],
                    $comparison_operator,
                    $args[1],
                ));
                $test++;
            }
        }

        // Test logical operators.
        $logical_operators = [
            null,
            LogicalOperator::AND,
            LogicalOperator::OR,
        ];
        $expected = [
            'is_active = 1',
            'AND is_active = 1',
            'OR is_active = 1',
        ];
        foreach ($logical_operators as $i => $logical_operator) {
            $this->assertSame($expected[$i], (string) new Constraint(
                $logical_operator,
                'is_active',
                ComparisonOperator::EQUAL_TO,
                1,
            ));
        }

        // Test groups.
        $expected = "(\n(\n(\n(\nname LIKE 'John Doe'\n)\n)";
        $this->assertSame($expected, (string) new Constraint(
            null,
            'name',
            ComparisonOperator::LIKE,
            "'John Doe'",
            4,
            2,
        ));
    }
}
