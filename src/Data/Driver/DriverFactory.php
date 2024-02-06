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
 * Provides driver configurations from their names.
 */
class DriverFactory
{
    /**
     * Native driver class names.
     * 
     * @var array<string, class-string<AbstractDriver>>
     */
    protected array $drivers = [
        'sqlite' => SqliteDriver::class,
    ];

    /**
     * Create a new driver instance from a driver name.
     */
    public function createDriver(string $name): AbstractDriver
    {
        // Get driver class.
        if (!array_key_exists($name, $this->drivers)) {
            $message = 'No driver is registered under the name "%s".';
            throw new \RuntimeException(sprintf($message, $name));
        }
        $class_name = $this->drivers[$name];

        return new $class_name();
    }

    /**
     * Register a new driver class.
     */
    public function registerDriver(string $name, string $class_name): static
    {
        // Set driver class.
        if (!is_a($class_name, AbstractDriver::class, true)) {
            $message = 'All drivers must extend the AbstractDriver class.';
            throw new \InvalidArgumentException($message);
        }
        $this->drivers[$name] = $class_name;

        return $this;
    }
}
