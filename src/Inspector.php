<?php
    /*/
     * Project Name:    Wingman — Helix — Inspector
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use ReflectionClass;
    use ReflectionClassConstant;
    use ReflectionMethod;
    use ReflectionProperty;
    use WeakMap;
    use Wingman\Helix\Exceptions\ContractViolationException;

    /**
     * Enforces contracts against objects and provides compliance checks.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Inspector {
        /**
         * A cache for object-specific member reflections to optimise repeated inspections.
         * @var WeakMap<object, array<string, object>>|null
         */
        protected static ?WeakMap $objectCache = null;

        /**
         * A cache for static member reflections to optimise repeated inspections.
         * @var array<string, array<string, object>>|null
         */
        protected static ?array $staticCache = null;

        /**
         * Resolves a reflection object, either from cache or by creating a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Member $member The member name or Member object to reflect.
         * @param string $class The reflection class to instantiate.
         * @return object The resolved reflection object.
         */
        protected static function resolve (object|string $target, string|Member|null $member, string $class) : object {
            $name = $member instanceof Member ? $member->getName() : ($member ?? "_class_");

            if (is_object($target)) {
                self::$objectCache ??= new WeakMap();
                $store = self::$objectCache[$target] ?? [];

                if (!isset($store[$name])) {
                    $store[$name] = ($class === ReflectionClass::class) 
                        ? new $class($target) 
                        : new $class($target, $name);
                    self::$objectCache[$target] = $store;
                }
                return $store[$name];
            }

            if (!isset(self::$staticCache[$target])) {
                self::$staticCache[$target] = [];
            }

            if (!isset(self::$staticCache[$target][$name])) {
                self::$staticCache[$target][$name] = ($class === ReflectionClass::class)
                    ? new $class($target)
                    : new $class($target, $name);
            }

            return self::$staticCache[$target][$name];
        }

        /**
         * Enforces a contract against an object or a class name.
         * @param object|string $target The object instance or FQCN to check.
         * @param Contract $contract The contract to enforce.
         * @throws ContractViolationException If the target violates any term of the contract.
         */
        public static function enforce (object|string $target, Contract $contract) : void {
            foreach ($contract->getTerms() as $term) {
                if (!$term->evaluate($target)) {
                    $name = is_object($target) ? get_class($target) : $target;
                    throw new ContractViolationException(
                        $contract, 
                        "Target '{$name}' violates contract '{$contract->getName()}': " . $term->getErrorMessage()
                    );
                }
            }
        }

        /**
         * Checks if an object or class name complies with a contract without throwing exceptions.
         * @param object|string $target The object instance or FQCN to check.
         * @param Contract $contract The contract to check against.
         * @return bool Whether the target complies with the contract.
         */
        public static function complies (object|string $target, Contract $contract) : bool {
            try {
                self::enforce($target, $contract);
                return true;
            }
            catch (ContractViolationException) {
                return false;
            }
        }

        /**
         * Clears all cached reflections from the inspector.
         * This can be useful to free memory or reset state between inspections.
         */
        public static function clearCache () : void {
            self::$objectCache = null;
            self::$staticCache = null;
        }

        /**
         * Gets a cached reflection for a class or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @return ReflectionClass The reflection of the specified class.
         */
        public static function getClassReflection (object|string $target) : ReflectionClass {
            return self::resolve($target, null, ReflectionClass::class);
        }

        /**
         * Gets a cached reflection for a constant or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Constant $constant The constant name or Constant object to reflect.
         * @return ReflectionClassConstant The reflection of the specified constant.
         */
        public static function getConstantReflection (object|string $target, string|Constant $constant) : ReflectionClassConstant {
            return self::resolve($target, $constant, ReflectionClassConstant::class);
        }

        /**
         * Gets a cached reflection for a method or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Method $method The method name or Method object to reflect.
         * @return ReflectionMethod The reflection of the specified method.
         */
        public static function getMethodReflection (object|string $target, string|Method $method) : ReflectionMethod {
            return self::resolve($target, $method, ReflectionMethod::class);
        }

        /**
         * Gets a cached reflection for a property or creates a new one.
         * @param object|string $target The object or class name to reflect on.
         * @param string|Property $property The property name or Property object to reflect.
         * @return ReflectionProperty The reflection of the specified property.
         */
        public static function getPropertyReflection (object|string $target, string|Property $property) : ReflectionProperty {
            return self::resolve($target, $property, ReflectionProperty::class);
        }
    }
?>