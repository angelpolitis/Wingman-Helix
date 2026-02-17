<?php
    /*/
     * Project Name:    Wingman — Helix — Type Comparator
     * Created by:      Angel Politis
     * Creation Date:   Feb 17 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use ReflectionIntersectionType;
    use ReflectionNamedType;
    use ReflectionType;

    /**
     * Checks that a method matches a specified signature.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class TypeComparator {
        /**
         * Compares a blueprint type string with actual type parts from reflection.
         * @param string $blueprint The expected type as defined in the contract (e.g., "int|string|null").
         * @param string[] $actualParts The actual type parts extracted from reflection (e.g., ["int", "null"]).
         * @param object|string $target The target class or object for resolving "self", "parent", etc.
         * @param string $sep The separator used in the blueprint (default: '|').
         * @return bool Whether the actual types match the expected blueprint.
         */
        protected static function compareTypes (string $blueprint, array $actualParts, object|string $target, string $sep = '|') : bool {
            $blueprintParts = explode($sep, $blueprint);
            $className = is_object($target) ? get_class($target) : $target;

            $resolve = function ($type) use ($className) {
                return match(strtolower($type)) {
                    "self", "static" => $className,
                    "parent" => get_parent_class($className) ?: "parent",
                    "boolean" => "bool",
                    "integer" => "int",
                    "double" => "float",
                    default => $type
                };
            };

            $normalisedBlueprint = array_map($resolve, $blueprintParts);
            $normalisedActual = array_map($resolve, $actualParts);

            sort($normalisedBlueprint);
            sort($normalisedActual);

            return $normalisedBlueprint === $normalisedActual;
        }

        /**
         * Matches an expected type string against an actual reflection type.
         * @param string $expectedType The expected type as defined in the contract (e.g., "int|string|null").
         * @param ReflectionType $actualType The actual type obtained from reflection.
         * @param object|string $target The target class or object for resolving "self", "parent", etc.
         * @return bool Whether the actual type matches the expected type.
         */
        public static function matchType (string $expectedType, ReflectionType $actualType, object|string $target) : bool {
            # 1. Handle shorthand nullable (?type).
            if (str_starts_with($expectedType, '?')) {
                $expectedType = substr($expectedType, 1) . "|null";
            }

            # 2. Extract parts from the actual reflection type.
            $actualNames = [];
            if ($actualType instanceof ReflectionNamedType) {
                $actualNames[] = $actualType->getName();
                if ($actualType->allowsNull() && $actualType->getName() !== "mixed") {
                    $actualNames[] = "null";
                }
            }
            else {
                /** @var ReflectionUnionType|ReflectionIntersectionType $actualType */
                /** @var ReflectionNamedType $type */
                foreach ($actualType->getTypes() as $type) {
                    $actualNames[] = $type->getName();
                }
            }

            $separator = ($actualType instanceof ReflectionIntersectionType) ? '&' : '|';
            return static::compareTypes($expectedType, $actualNames, $target, $separator);
        }
    }
?>