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

namespace Laucov\Db\Data;

use Laucov\Db\Data\Driver\AbstractDriver;
use Laucov\Db\Data\Driver\DriverFactory;

/**
 * Provides an additional interface to PHP Data Objects (PDO).
 */
class Connection
{
    /**
     * Driver in use.
     */
    protected AbstractDriver $driver;

    /**
     * Driver name in use.
     */
    protected string $driverName;

    /**
     * PHP Data Object.
     */
    protected \PDO $pdo;

    /**
     * Current statement object.
     */
    protected null|\PDOStatement $statement = null;

    /**
     * Create the connection instance.
     */
    public function __construct(
        DriverFactory $driver_factory,
        string $dsn,
        null|string $username = null,
        null|string $password = null,
        null|array $options = [],
    ) {
        // Create the PDO.
        $this->pdo = new \PDO($dsn, $username, $password, $options);

        // Set the driver.
        $this->driverName = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $this->driver = $driver_factory->createDriver($this->driverName);
    }

    /**
     * Fetch a row as an associative array.
     * 
     * @return array<string, mixed>
     */
    public function fetchAssoc(): array
    {
        return $this->getStatement()->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a row as an object of the given class.
     * 
     * @template T
     * @param class-string<T> $class_name
     * @return T
     */
    public function fetchClass(string $class_name): mixed
    {
        $statement = $this->getStatement();
        $statement->setFetchMode(\PDO::FETCH_CLASS, $class_name);
        return $statement->fetch();
    }

    /**
     * Fetch a row and fill the given object's properties with the row's data.
     * 
     * @template T
     * @param T $object
     * @return T
     */
    public function fetchInto(mixed $object): mixed
    {
        $statement = $this->getStatement();
        $statement->setFetchMode(\PDO::FETCH_INTO, $object);
        return $statement->fetch();
    }

    /**
     * Fetch a row as a 0-indexed array.
     * 
     * @return array<string, mixed>
     */
    public function fetchNum(): array
    {
        $statement = $this->getStatement();
        return $statement->fetch(\PDO::FETCH_NUM);
    }

    /**
     * Get the database driver information object.
     */
    public function getDriver(): AbstractDriver
    {
        return $this->driver;
    }

    /**
     * Get the database driver name.
     */
    public function getDriverName(): string
    {
        return $this->driverName;
    }

    /**
     * Get the ID of the last inserted row.
     */
    public function getLastId(): string
    {
        $id = $this->pdo->lastInsertId();
        if (!is_string($id) || $id === '0') {
            $message = 'Could not get the last inserted row ID.';
            throw new \RuntimeException($message);
        }

        return $id;
    }

    /**
     * Get the current statement object.
     */
    public function getStatement(): \PDOStatement
    {
        if ($this->statement === null) {
            throw new \RuntimeException('Statement is not set.');
        }

        return $this->statement;
    }

    /**
     * Fetch all rows as associative arrays.
     * 
     * @return array<array<string, mixed>>
     */
    public function listAssoc(): array
    {
        return $this->getStatement()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows as objects of the given class.
     * 
     * @template T
     * @param class-string<T> $class_name
     * @return array<T>
     */
    public function listClass(
        string $class_name,
        ?array $arguments = [],
    ): array {
        return $this->getStatement()->fetchAll(
            \PDO::FETCH_CLASS,
            $class_name,
            $arguments,
        );
    }

    /**
     * Fetch all rows as value lists.
     */
    public function listNum(): array
    {
        return $this->getStatement()->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * Execute a query.
     */
    public function query(string $query, null|array $parameters = null): static
    {
        $statement = $this->pdo->prepare($query);
        $this->statement = $statement;
        $statement->execute($parameters);

        return $this;
    }

    /**
     * Quote an identifier according to driver-specific configuration.
     */
    public function quoteIdentifier(string $identifier): string
    {
        // Split composite identifier.
        if (str_contains($identifier, '.')) {
            $segments = explode('.', $identifier);
            $segments = array_map([$this, 'quoteIdentifier'], $segments);
            return implode('.', $segments);
        }

        // Handle single identifier.
        return $this->driver->identifierStartDelimiter
            . $identifier
            . $this->driver->identifierEndDelimiter;;
    }
}
