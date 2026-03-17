<?php
    /**
     * Project Name:    Wingman Helix - Inspector Interface
     * Created by:      Angel Politis
     * Creation Date:   Mar 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    # Use the Helix.Interfaces namespace.
    namespace Wingman\Helix\Interfaces;

    # Import the following classes to the current scope.
    use ReflectionClass;
    use ReflectionClassConstant;
    use ReflectionMethod;
    use ReflectionProperty;
    use Wingman\Helix\Constant;
    use Wingman\Helix\Contract;
    use Wingman\Helix\Exceptions\ContractViolationException;
    use Wingman\Helix\Method;
    use Wingman\Helix\Property;

    /**
     * Defines the public API for reflecting on and enforcing contracts against classes and objects.
     * @package Wingman\Helix\Interfaces
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    interface InspectorInterface {
        /**
         * Clears all cached reflections.
         * This can be useful to free memory or reset state between inspections.
         */
        public function clearCache () : void;

        /**
         * Checks if an object or class name complies with a contract without throwing exceptions.
         * @param object|string $target The object instance or FQCN to check.
         * @param Contract $contract The contract to check against.
         * @return bool Whether the target complies with the contract.
         */
        public function complies (object|string $target, Contract $contract) : bool;

        /**
         * Enforces a contract against an object or a class name.
         * @param object|string $target The object instance or FQCN to check.
         * @param Contract $contract The contract to enforce.
         * @param bool $allErrors Whether to collect all violations or stop at the first one.
         * @throws ContractViolationException If the target violates any term of the contract.
         */
        public function enforce (object|string $target, Contract $contract, bool $allErrors = false) : void;

        /**
         * Gets a cached reflection for a class or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @return ReflectionClass The reflection of the specified class.
         */
        public function getClassReflection (object|string $target) : ReflectionClass;

        /**
         * Gets a cached reflection for a constant or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Constant $constant The constant name or Constant object to reflect.
         * @return ReflectionClassConstant The reflection of the specified constant.
         */
        public function getConstantReflection (object|string $target, string|Constant $constant) : ReflectionClassConstant;

        /**
         * Gets a cached reflection for a method or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Method $method The method name or Method object to reflect.
         * @return ReflectionMethod The reflection of the specified method.
         */
        public function getMethodReflection (object|string $target, string|Method $method) : ReflectionMethod;

        /**
         * Gets a cached reflection for a property or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Property $property The property name or Property object to reflect.
         * @return ReflectionProperty The reflection of the specified property.
         */
        public function getPropertyReflection (object|string $target, string|Property $property) : ReflectionProperty;
    }
?>