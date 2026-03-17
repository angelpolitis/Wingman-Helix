<?php
    /**
     * Project Name:    Wingman Helix - Inspector
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use ReflectionClass;
    use ReflectionClassConstant;
    use ReflectionMethod;
    use ReflectionProperty;
    use WeakMap;
    use Wingman\Helix\Exceptions\ContractViolationException;
    use Wingman\Helix\Interfaces\InspectorInterface;

    /**
     * Enforces contracts against objects and provides compliance checks.
     * Implements InspectorInterface and exposes a static getInstance()/setInstance() seam
     * so a custom implementation can be injected globally (e.g. for testing or decoration).
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Inspector implements InspectorInterface {
        /**
         * The maximum number of distinct class names that may be held in the static-target cache.
         * Prevents unbounded memory growth in long-running processes.
         * @var int
         */
        protected const int MAX_STATIC_CACHE_SIZE = 500;

        /**
         * The shared singleton instance, swappable via setInstance().
         * @var InspectorInterface|null
         */
        protected static ?InspectorInterface $instance = null;

        /**
         * A cache for object-specific member reflections to optimise repeated inspections.
         * @var WeakMap<object, array<string, object>>|null
         */
        protected ?WeakMap $objectCache = null;

        /**
         * A cache for static member reflections to optimise repeated inspections.
         * @var array<string, array<string, object>>|null
         */
        protected ?array $staticCache = null;

        /**
         * Returns the shared Inspector instance, creating it on first call.
         * @return static The shared inspector instance.
         */
        public static function getInstance () : static {
            return self::$instance ??= new static();
        }

        /**
         * Replaces the shared inspector with a custom implementation.
         * @param InspectorInterface $inspector The implementation to use globally.
         */
        public static function setInstance (InspectorInterface $inspector) : void {
            self::$instance = $inspector;
        }

        /**
         * Resolves a reflection object, either from cache or by creating a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Member $member The member name or Member object to reflect.
         * @param string $class The reflection class to instantiate.
         * @return object The resolved reflection object.
         */
        protected function resolve (object|string $target, string|Member|null $member, string $class) : object {
            $memberName = $member instanceof Member ? $member->getName() : ($member ?? "");
            $key = $class . ':' . $memberName;

            if (is_object($target)) {
                $this->objectCache ??= new WeakMap();
                $store = $this->objectCache[$target] ?? [];

                if (!isset($store[$key])) {
                    $store[$key] = ($class === ReflectionClass::class)
                        ? new $class($target)
                        : new $class($target, $memberName);
                    $this->objectCache[$target] = $store;
                }

                return $store[$key];
            }

            if (!isset($this->staticCache[$target])) {
                if (count($this->staticCache ?? []) >= static::MAX_STATIC_CACHE_SIZE) {
                    reset($this->staticCache);
                    unset($this->staticCache[key($this->staticCache)]);
                }

                $this->staticCache[$target] = [];
            }

            if (!isset($this->staticCache[$target][$key])) {
                $this->staticCache[$target][$key] = ($class === ReflectionClass::class)
                    ? new $class($target)
                    : new $class($target, $memberName);
            }

            return $this->staticCache[$target][$key];
        }

        /**
         * Enforces a contract against an object or a class name.
         * Delegates to Contract::validate() to guarantee consistent error formatting and behaviour.
         * @param object|string $target The object instance or FQCN to check.
         * @param Contract $contract The contract to enforce.
         * @param bool $allErrors Whether to collect all violations or stop at the first one.
         * @throws ContractViolationException If the target violates any term of the contract.
         */
        public function enforce (object|string $target, Contract $contract, bool $allErrors = false) : void {
            $contract->validate($target, $allErrors);
        }

        /**
         * Checks if an object or class name complies with a contract without throwing exceptions.
         * @param object|string $target The object instance or FQCN to check.
         * @param Contract $contract The contract to check against.
         * @return bool Whether the target complies with the contract.
         */
        public function complies (object|string $target, Contract $contract) : bool {
            return $contract->isSatisfiedBy($target);
        }

        /**
         * Clears all cached reflections from the inspector.
         * This can be useful to free memory or reset state between inspections.
         */
        public function clearCache () : void {
            $this->objectCache = null;
            $this->staticCache = null;
        }

        /**
         * Gets a cached reflection for a class or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @return ReflectionClass The reflection of the specified class.
         */
        public function getClassReflection (object|string $target) : ReflectionClass {
            return $this->resolve($target, null, ReflectionClass::class);
        }

        /**
         * Gets a cached reflection for a constant or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Constant $constant The constant name or Constant object to reflect.
         * @return ReflectionClassConstant The reflection of the specified constant.
         */
        public function getConstantReflection (object|string $target, string|Constant $constant) : ReflectionClassConstant {
            return $this->resolve($target, $constant, ReflectionClassConstant::class);
        }

        /**
         * Gets a cached reflection for a method or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Method $method The method name or Method object to reflect.
         * @return ReflectionMethod The reflection of the specified method.
         */
        public function getMethodReflection (object|string $target, string|Method $method) : ReflectionMethod {
            return $this->resolve($target, $method, ReflectionMethod::class);
        }

        /**
         * Gets a cached reflection for a property or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Property $property The property name or Property object to reflect.
         * @return ReflectionProperty The reflection of the specified property.
         */
        public function getPropertyReflection (object|string $target, string|Property $property) : ReflectionProperty {
            return $this->resolve($target, $property, ReflectionProperty::class);
        }
    }
?>