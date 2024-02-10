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

use Laucov\Db\Data\Connection;
use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\Db\Query\Table;
use Laucov\Db\Statement\Clause\WhereClause;
use Laucov\Db\Statement\SelectStatement;
use PHPUnit\Framework\TestCase;
 
/**
 * @coversDefaultClass \Laucov\Db\Query\Table
 */
final class TableTest extends TestCase
{
    protected Connection $conn;

    protected array $records;

    protected Table $table;

    /**
     * @covers ::__construct
     * @covers ::applyWhereClause
     * @covers ::average
     * @covers ::closeGroup
     * @covers ::constrain
     * @covers ::constrainArray
     * @covers ::count
     * @covers ::countRecords
     * @covers ::createPlaceholderName
     * @covers ::deleteRecords
     * @covers ::filter
     * @covers ::findMax
     * @covers ::findMin
     * @covers ::group
     * @covers ::insertRecord
     * @covers ::insertRecords
     * @covers ::join
     * @covers ::limit
     * @covers ::offset
     * @covers ::openGroup
     * @covers ::on
     * @covers ::or
     * @covers ::pick
     * @covers ::resetTemporaryProperties
     * @covers ::selectColumn
     * @covers ::selectRecords
     * @covers ::set
     * @covers ::sort
     * @covers ::subquery
     * @covers ::sum
     * @covers ::updateRecords
     * @uses Laucov\Db\Data\Connection::__construct
     * @uses Laucov\Db\Data\Connection::getLastId
     * @uses Laucov\Db\Data\Connection::getStatement
     * @uses Laucov\Db\Data\Connection::listAssoc
     * @uses Laucov\Db\Data\Connection::query
     * @uses Laucov\Db\Data\Driver\DriverFactory::createDriver
     * @uses Laucov\Db\Query\Table::insertRecord
     * @uses Laucov\Db\Statement\AbstractJoinableStatement::compileFromClause
     * @uses Laucov\Db\Statement\AbstractJoinableStatement::setFromClause
     * @uses Laucov\Db\Statement\AbstractConditionalStatement::setWhereClause
     * @uses Laucov\Db\Statement\AbstractJoinableStatement::addJoinClause
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::addConstraint
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::beginGroup
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::endGroup
     * @uses Laucov\Db\Statement\Clause\AbstractConditionalClause::setLogicalOperator
     * @uses Laucov\Db\Statement\Clause\Constraint::__construct
     * @uses Laucov\Db\Statement\Clause\Constraint::__toString
     * @uses Laucov\Db\Statement\Clause\RowOrder::__toString
     * @uses Laucov\Db\Statement\Clause\RowOrder::__construct
     * @uses Laucov\Db\Statement\Clause\JoinClause::__toString
     * @uses Laucov\Db\Statement\Clause\JoinClause::setOn
     * @uses Laucov\Db\Statement\Clause\WhereClause::__toString
     * @uses Laucov\Db\Statement\DeleteStatement::__construct
     * @uses Laucov\Db\Statement\DeleteStatement::__toString
     * @uses Laucov\Db\Statement\InsertStatement::__construct
     * @uses Laucov\Db\Statement\InsertStatement::__toString
     * @uses Laucov\Db\Statement\InsertStatement::addRowValues
     * @uses Laucov\Db\Statement\InsertStatement::setColumns
     * @uses Laucov\Db\Statement\ResultColumn::__construct
     * @uses Laucov\Db\Statement\ResultColumn::__toString
     * @uses Laucov\Db\Statement\SelectStatement::__toString
     * @uses Laucov\Db\Statement\SelectStatement::addResultColumn
     * @uses Laucov\Db\Statement\SelectStatement::groupRows
     * @uses Laucov\Db\Statement\SelectStatement::orderRows
     * @uses Laucov\Db\Statement\SelectStatement::setLimit
     * @uses Laucov\Db\Statement\SelectStatement::setOffset
     * @uses Laucov\Db\Statement\UpdateStatement::__construct
     * @uses Laucov\Db\Statement\UpdateStatement::__toString
     * @uses Laucov\Db\Statement\UpdateStatement::setValue
     */
    public function testCanReadAndWrite(): void
    {
        // Select all records.
        $expected_a = $this->getRecords([1, 2, 3]);
        $actual_a = $this->table->selectRecords();
        $this->assertArrayIsLike($expected_a, $actual_a);

        // Select specific columns.
        $expected_b = $this->getRecords([1, 2, 3], ['name', 'tin']);
        $actual_b = $this->table
            ->pick('name')
            ->pick('tin')
            ->selectRecords();
        $this->assertArrayIsLike($expected_b, $actual_b);

        // Test aliases and select a column as a list.
        $expected_c = ['j.doe', 'm.scott', 'm.poppins'];
        $actual_c = $this->table
            ->pick('login', 'login_alias')
            ->selectColumn('login_alias');
        $this->assertArrayIsLike($expected_c, $actual_c);

        // Test calculation columns.
        $expected_d = [[
            'count' => 3,
            'average' => 143/3,
            'sum' => 143,
            'max' => 74,
            'min' => 25,
        ]];
        $actual_d = $this->table
            ->count('id', 'count')
            ->average('score', 'average')
            ->sum('score', 'sum')
            ->findMax('score', 'max')
            ->findMin('score', 'min')
            ->selectRecords();
        $this->assertArrayIsLike($expected_d, $actual_d);

        // Test counting records.
        $actual_e = $this->table->countRecords('id');
        $this->assertSame(3, $actual_e);
        $actual_f = $this->table->countRecords('tin');
        $this->assertSame(2, $actual_f);

        // Test grouping.
        $expected_g = [
            ['gender' => 'f', 'total_score' => 74],
            ['gender' => 'm', 'total_score' => 69],
        ];
        $actual_g = $this->table
            ->pick('gender')
            ->sum('score', 'total_score')
            ->group('gender')
            ->selectRecords();
        $this->assertArrayIsLike($expected_g, $actual_g);

        // Test sorting.
        $actual_h = $this->table
            ->pick('id')
            ->sort('gender')
            ->sort('name', true)
            ->selectColumn('id');
        $this->assertArrayIsLike([3, 2, 1], $actual_h);

        // Test limit and offset.
        $actual_i = $this->table
            ->pick('id')
            ->offset(1)
            ->limit(1)
            ->selectColumn('id');
        $this->assertArrayIsLike([2], $actual_i);

        // Test retrieving a subquery.
        $expected_j = ['John Doe', 'Michael Scott', 'Mary Poppins'];
        $stmt = new SelectStatement();
        $stmt
            ->addResultColumn('u.name')
            ->setFromClause('users', 'u')
            ->setWhereClause(function (WhereClause $clause): void {
                $clause->addConstraint('u.id', '=', 'users.id');
            });
        $actual_j = $this->table
            ->subquery($stmt, 'subquery_name')
            ->selectColumn('subquery_name');
        $this->assertArrayIsLike($expected_j, $actual_j);

        // Test simple filtering.
        $filter_tests = [
            [['login', '=', 'm.scott'], [2]],
            [['name', '!=', 'John Doe'], [2, 3]],
            [['birth', '>', '1970-01-01'], [1, 3]],
            [['birth', '>=', '1988-02-14'], [1]],
            [['score', '<', 44], [2]],
            [['score', '<=', 44], [1, 2]],
            [['name', '^=', 'M'], [2, 3]],
            [['name', '$=', 's'], [3]],
            [['name', '*=', 'n'], [1, 3]],
            [['login', '!^=', 'm'], [1]],
            [['login', '!$=', '.poppins'], [1, 2]],
            [['login', '!*=', 's'], [1]],
            [['tin', '=', null], [2]],
            [['tin', '!=', null], [1, 3]],
            [['login', '=', ['j.doe', 'm.poppins']], [1, 3]],
            [['login', '!=', ['j.doe', 'm.poppins']], [2]],
            [['name', '$=', ['ott', 'ins']], [2, 3]],
            [['name', '!$=', ['oe', 'ns']], [2]],
        ];
        foreach ($filter_tests as $i => $filter_test) {
            $actual_k = $this->table
                ->pick('id')
                ->filter(...$filter_test[0])
                ->selectColumn('id');
            $this->assertArrayIsLike(
                $filter_test[1],
                $actual_k,
                "Filter test #{$i}",
            );
        }

        // Test filtering with OR operator.
        $actual_l = $this->table
            ->pick('id')
            ->filter('name', '=', 'Michael Scott')
            ->or()->filter('login', '^=', ['j.d', 'm.p'])
            ->filter('gender', '=', 'f')
            ->selectColumn('id');
        $this->assertArrayIsLike([2, 3], $actual_l);

        // Test filtering with grouping.
        $actual_m = $this->table
            ->pick('id')
            ->filter('score', '>', 0)
            ->openGroup()
                ->filter('name', '^=', 'Michael')
                ->or()->filter('name', '$=', 'Poppins')
            ->closeGroup()
            ->filter('tin', '!=', null)
            ->selectColumn('id');
        $this->assertArrayIsLike([3], $actual_m);

        // Test joining.
        $actual_n = $this->table
            ->pick('users.id')
            ->count('l.id', 'attempts')
            ->join('logins', 'l')
                ->on('l.user_id', '=', 'users.id')
            ->group('users.id')
            ->sort('users.id')
            ->selectColumn('attempts');
        $this->assertArrayIsLike([2, 4, 3], $actual_n);

        // Test inserting a single record.
        $this->assertSame('4', $this->table->insertRecord([
            'name' => 'Kevin Malone',
            'login' => 'kev',
            'birth' => '1968-06-01',
            'gender' => 'm',
            'tin' => null,
            'score' => 99,
        ]));

        // Test inserting multiple records.
        $this->assertSame('6', $this->table->insertRecords(
            [
                'name' => 'Willy Wonka',
                'login' => 'wonka',
                'birth' => '1970-01-01',
                'gender' => 'm',
                'tin' => null,
                'score' => 50,
            ],
            [
                'name' => 'Elizabeth Bennet',
                'login' => 'lizzy',
                'birth' => '1800-09-19',
                'gender' => 'f',
                'tin' => null,
                'score' => 82,
            ],
        ));

        // Test simple update.
        $this->table
            ->filter('gender', '=', 'f')
            ->updateRecords(['score' => 91]);
        $actual_o = $this->table
            ->pick('score')
            ->filter('gender', '=', 'f')
            ->selectColumn('score');
        $this->assertArrayIsLike([91, 91], $actual_o);

        // Test setting update values beforehand.
        $this->conn
            ->query(<<<SQL
                ALTER TABLE users
                ADD COLUMN score_copy INT(11)
                SQL);
        $this->table
            ->set('tin', null)
            ->set('score_copy', 'score', true)
            ->updateRecords();
        $actual_p = $this->table
            ->pick('score_copy')
            ->selectColumn('score_copy');
        $this->assertArrayIsLike([44, 25, 91, 99, 50, 91], $actual_p);

        // Test deleting values.
        $this->table
            ->filter('birth', '>=', '1970-01-01')
            ->filter('birth', '<=', '1975-01-01')
            ->deleteRecords();
        $actual_q = $this->table
            ->pick('id')
            ->selectColumn('id');
        $this->assertArrayIsLike([1, 2, 4, 6], $actual_q);

        // Test joining subqueries.
        $subquery = (new SelectStatement())
            ->setFromClause('logins')
            ->addResultColumn('logins.user_id')
            ->addResultColumn('COUNT(logins.attempted_at)', 'attempts')
            ->setWhereClause(function (WhereClause $clause): void {
                $values = ["'2024-02-06 00:00:00'", "'2024-02-06 23:59:59'"];
                $clause->addConstraint('attempted_at', 'BETWEEN', $values);
            })
            ->groupRows('logins.user_id');
        $actual_r = $this->table
            ->pick('users.id')
            ->join($subquery, 'l', 'INNER')
                ->on('l.user_id', '=', 'users.id')
            ->selectColumn('id');
        $this->assertArrayIsLike([2], $actual_r);
    }

    /**
     * Asserts that two arrays contain the same content.
     */
    protected function assertArrayIsLike(
        array $expected,
        mixed $actual,
        string $message = '',
    ): void {
        $this->assertIsArray($actual, $message);
        $this->assertSameSize($expected, $actual, $message);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $actual, $message);
            $this->assertSame($value, $actual[$key], $message);
        }
    }

    /**
     * Get records for comparison.
     */
    protected function getRecords(array $ids, array $columns = []): array
    {
        $result = [];
        foreach ($this->records as $record) {
            if (count($columns) < 1) {
                $result[] = $record;
                continue;
            }
            $values = [];
            foreach ($columns as $column) {
                $values[$column] = $record[$column];
            }
            $result[] = $values;
        }

        return $result;
    }

    protected function setUp(): void
    {
        // Create connection instance.
        $factory = new DriverFactory();
        $this->conn = new Connection($factory, 'sqlite::memory:');

        // Configure database.
        $this->conn
            ->query(<<<SQL
                CREATE TABLE "users" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                    "name" VARCHAR(128),
                    "login" VARCHAR(64),
                    "birth" DATETIME,
                    "gender" VARCHAR(1),
                    "tin" VARCHAR(32),
                    "score" INT(11)
                )
                SQL)
            ->query(<<<SQL
                CREATE TABLE "logins" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                    "user_id" INT(11),
                    "is_successful" INT(1),
                    "attempted_at" DATETIME
                )
                SQL)
            ->query(<<<SQL
                INSERT INTO "users" ("name", "login", "birth", "gender", "tin", "score")
                VALUES
                ('John Doe', 'j.doe', '1988-02-14', 'm', '123456789', 44),
                ('Michael Scott', 'm.scott', '1965-03-15', 'm', NULL, 25),
                ('Mary Poppins', 'm.poppins', '1972-12-20', 'f', '987654321', 74)
                SQL)
            ->query(<<<SQL
                INSERT INTO "logins" ("user_id", "is_successful", "attempted_at")
                VALUES
                (1, 1, '2024-02-04 10:41:20'),
                (3, 1, '2024-02-04 22:12:07'),
                (3, 1, '2024-02-05 06:57:41'),
                (1, 1, '2024-02-05 18:10:59'),
                (3, 1, '2024-02-05 19:00:35'),
                (2, 0, '2024-02-06 04:08:14'),
                (2, 0, '2024-02-06 04:08:58'),
                (2, 0, '2024-02-06 04:09:23'),
                (2, 1, '2024-02-06 09:59:10')
                SQL);

        // Set records.
        $this->records = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'login' => 'j.doe',
                'birth' => '1988-02-14',
                'gender' => 'm',
                'tin' => '123456789',
                'score' => 44,
            ],
            [
                'id' => 2,
                'name' => 'Michael Scott',
                'login' => 'm.scott',
                'birth' => '1965-03-15',
                'gender' => 'm',
                'tin' => null,
                'score' => 25,
            ],
            [
                'id' => 3,
                'name' => 'Mary Poppins',
                'login' => 'm.poppins',
                'birth' => '1972-12-20',
                'gender' => 'f',
                'tin' => '987654321',
                'score' => 74,
            ],
        ];

        // Create table instance.
        $this->table = new Table($this->conn, 'users');
    }
}