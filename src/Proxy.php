<?php
    /**
     * Project Name:    Wingman Helix - Proxy
     * Created by:      Angel Politis
     * Creation Date:   Feb 22 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use Closure;
    use RuntimeException;
    use ReflectionMethod;

    /**
     * A high-performance dynamic proxy that translates interface calls to target object methods, regardless of visibility.
     *
     * SECURITY NOTE: This class intentionally bypasses PHP's visibility model by binding closures
     * to the target's instance and class scope via Closure::bind(). This grants access to
     * private and protected members of the proxied object.
     * Only use this class with objects from trusted, first-party code. Do NOT proxy
     * user-supplied or third-party objects in a multi-tenant or sandboxed environment,
     * as doing so would expose their internal state and behaviour.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Proxy {
        /**
         * The foreign object being proxied.
         * @var object
         */
        protected object $target;

        /**
         * Mapping of interface methods to target methods.
         * @var array<string, string>
         */
        protected array $map;

        /**
         * Cache of bound closures for performance.
         * @var array<string, Closure>
         */
        protected array $cache = [];

        /**
         * Creates a new proxy.
         * @param object $target The object to wrap.
         * @param array $map The method translation map.
         */
        public function __construct (object $target, array $map = []) {
            $this->target = $target;
            $this->map = $map;
        }

        /**
         * Intercepts method calls, translates them using the map, and invokes the corresponding method on the target object.
         * This method also caches the bound closures for subsequent calls to improve performance.
         * @param string $method The method name being called on the proxy.
         * @param array $arguments The arguments passed to the method.
         * @return mixed The result of the method call on the target object.
         * @throws RuntimeException If the target method does not exist.
         */
        public function __call (string $method, array $arguments) : mixed {
            # 1. Check the cache first for the highest performance.
            if (isset($this->cache[$method])) {
                return $this->cache[$method](...$arguments);
            }

            # 2. Determine the target method name.
            $targetMethod = $this->map[$method] ?? $method;

            # 3. Validate existence.
            if (!method_exists($this->target, $targetMethod)) {
                throw new RuntimeException(
                    sprintf("Proxy Error: Method '%s' (mapped to '%s') does not exist on %s.", 
                    $method, $targetMethod, get_class($this->target))
                );
            }

            # 4. Bind the method.
            # We create a closure that calls the method on the target.
            # Binding it to the target's instance and class scope allows access to private/protected.
            $this->cache[$method] = Closure::bind(
                fn (...$args) => $this->{$targetMethod}(...$args),
                $this->target,
                $this->target
            );

            return $this->cache[$method](...$arguments);
        }

        /**
         * Clears the method cache, forcing re-binding on the next call.
         * This can be useful if the target's class has changed (e.g., via a decorator pattern) and we need to refresh access.
         * @return static The proxy.
         */
        public function clearCache () : static {
            $this->cache = [];
            return $this;
        }

        /**
         * Creates a proxy for a given target and optional method map.
         * @param object $target The object to wrap.
         * @param array $map The method translation map.
         * @return static The created proxy.
         */
        public static function from (object $target, array $map = []) : static {
            return new static($target, $map);
        }

        /**
         * Allows dynamic remapping of interface methods to target methods after the proxy has been created.
         * This can be useful for changing behavior at runtime without needing to create a new proxy instance.
         * @param string $interfaceMethod The method name as called on the proxy.
         * @param string $targetMethod The method name on the target object to map to.
         * @return static The proxy.
         */
        public function map (string $interfaceMethod, string $targetMethod) : static {
            $this->map[$interfaceMethod] = $targetMethod;
            unset($this->cache[$interfaceMethod]);
            return $this;
        }

        /**
         * Gets the signature of a method on the target object, considering the mapping.
         * @param string $method The method name as called on the proxy.
         * @return string The method signature.
         * @throws RuntimeException If the method does not exist on the target object.
         */
        public function getMethodSignature (string $method) : string {
            $targetMethod = $this->map[$method] ?? $method;
            if (!method_exists($this->target, $targetMethod)) {
                throw new RuntimeException(
                    sprintf("Proxy Error: Method '%s' (mapped to '%s') does not exist on %s.", 
                    $method, $targetMethod, get_class($this->target))
                );
            }
            $reflection = new ReflectionMethod($this->target, $targetMethod);
            return (string) $reflection;
        }

        /**
         * Expose the underlying target if absolutely necessary.
         * @return object The underlying target object.
         */
        public function getTarget () : object {
            return $this->target;
        }

        /**
         * Checks if a method exists on the target object, considering the mapping.
         * @param string $method The method name as called on the proxy.
         * @return bool Whether the method exists on the target object.
         */
        public function hasMethod (string $method) : bool {
            $targetMethod = $this->map[$method] ?? $method;
            return method_exists($this->target, $targetMethod);
        }
    }
?>