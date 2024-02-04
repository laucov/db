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

namespace Tests\Query;

use Laucov\Db\Query\ResultColumn;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Query\ResultColumn
 */
class ResultColumnTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::compileExpression
     */
    public function testCanCreateAndStringify(): void
    {
        // Test without alias.
        $this->assertSame(
            'cars.model',
            (string) new ResultColumn('cars.model', null),
        );

        // Test with alias.
        $this->assertSame(
            'cars.customer_id AS owner_id',
            (string) new ResultColumn('cars.customer_id', 'owner_id'),
        );
        $this->assertSame(
            '(COUNT(cars.id)) AS car_amount',
            (string) new ResultColumn('(COUNT(cars.id))', 'car_amount'),
        );
    }
}
