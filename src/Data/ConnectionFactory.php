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

use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\Db\Query\Schema;
use Laucov\Db\Query\Table;

/**
 * Registers, caches and provides connections to the database.
 */
class ConnectionFactory
{
    /**
     * Cached connection instances.
     * 
     * @var array<string, Connection>
     */
    protected array $cachedConnections = [];

    /**
     * Registered connection configurations.
     */
    protected array $connections = [];

    /**
     * Default connection name.
     */
    protected string $defaultConnection;

    /**
     * Create the factory instance.
     */
    public function __construct(
        /**
         * Driver factory.
         */
        protected DriverFactory $drivers,
    ) {
    }

    /**
     * Get a connection instance.
     */
    public function getConnection(null|string $name = null): Connection
    {
        $name ??= $this->defaultConnection;
        if (!isset($this->cachedConnections[$name])) {
            $arguments = $this->connections[$name];
            $connection = new Connection($this->drivers, ...$arguments);
            $this->cachedConnections[$name] = $connection;
        }
        return $this->cachedConnections[$name];
    }

    /**
     * Get the default connection name.
     */
    public function getDefaultConnection(): string
    {
        return $this->defaultConnection;
    }

    /**
     * Get a table object.
     */
    public function getSchema(null|string $conn = null): Schema
    {
        return new Schema($this->getConnection($conn));
    }

    /**
     * Get a table object.
     */
    public function getTable(string $name, null|string $conn = null): Table
    {
        return new Table($this->getConnection($conn), $name);
    }

    /**
     * Register a new connection configuration.
     */
    public function setConnection(
        string $name,
        string $dsn,
        null|string $username = null,
        null|string $password = null,
        null|array $options = [],
    ): static {
        $this->defaultConnection ??= $name;
        $this->connections[$name] = [$dsn, $username, $password, $options];
        return $this;
    }

    /**
     * Set the new default connection name.
     */
    public function setDefaultConnection(string $name): static
    {
        $this->defaultConnection = $name;
        return $this;
    }
}
