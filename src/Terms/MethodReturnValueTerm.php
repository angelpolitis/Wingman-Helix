<?php
    /*/
     * Project Name:    Wingman — Helix — Method Return Value Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use ReflectionIntersectionType;
    use ReflectionNamedType;
    use ReflectionType;
    use ReflectionUnionType;
    use Wingman\Helix\Contract;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Method;
    use Wingman\Helix\TypeComparator;

    /**
     * Checks that a method return type satisfies an expected contract.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodReturnValueTerm extends MethodContractTerm {
        /**
         * The expected return type for a method.
         * @var string|null
         */
        protected ?string $type;

        /**
         * An optional nested contract to validate the return type against.
         * @var Contract|null
         */
        protected ?Contract $contract;

        /**
         * Creates a new term.
         * @param string $methodName The name of the method to check.
         * @param string|null $type The expected return type for the method.
         * @param Contract|null $contract An optional nested contract to validate the return type against.
         */
        public function __construct (string $methodName, ?string $type = null, ?Contract $contract = null) {
            parent::__construct($methodName);
            $this->type = $type;
            $this->contract = $contract;
        }

        /**
         * Extracts class/interface names from a ReflectionType, handling named types, unions, and intersections.
         * @param ReflectionType $type The reflection type to extract from.
         * @return string[] An array of class/interface names represented by the type.
         */
        protected function extractNames (ReflectionType $type): array {
            if ($type instanceof ReflectionNamedType) {
                return [$type->getName()];
            }
            $names = [];

            /** @var ReflectionUnionType|ReflectionIntersectionType $compositeType */
            $compositeType = $type;

            /** @var ReflectionNamedType $type */
            foreach ($compositeType->getTypes() as $type) {
                $names[] = $type->getName();
            }
            return $names;
        }

        /**
         * Evaluates whether a method's return type satisfies the expected contract on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the method's return type satisfies the expected contract.
         */
        public function evaluate (object|string $objOrClass) : bool {
            $methodName = $this->method->getName();
            
            if (!Method::exists($objOrClass, $methodName)) {
                return false;
            }

            $reflection = Inspector::getMethodReflection($objOrClass, $this->method);
            
            # 1. If we don't care about the type or nested contract, this term is satisfied.
            if ($this->type === null && $this->contract === null) {
                return true;
            }

            # 2. If it has no return type but we expected one.
            if (!$reflection->hasReturnType()) {
                return $this->type === null;
            }

            $actualReturnType = $reflection->getReturnType();

            # 3. Use TypeComparator for the heavy lifting.
            # This handles unions, intersections, and 'self/static' resolution.
            if ($this->type !== null) {
                if (!TypeComparator::matchType($this->type, $actualReturnType, $objOrClass)) {
                    return false;
                }
            }

            # 4. Recursive Contract Validation.
            # If the return type is a class/interface, validate it against the nested contract.
            if ($this->contract !== null) {
                $typesToValidate = $this->extractNames($actualReturnType);
                
                foreach ($typesToValidate as $className) {
                    # Skip built-in types like 'string', 'int' for nested contracts.
                    if (class_exists($className) || interface_exists($className)) {
                        if (!Inspector::complies($className, $this->contract)) {
                            return false;
                        }
                    }
                }
            }

            return true;
        }

        /**
         * Gets the error message for when a method's return type does not satisfy the expected contract.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            $msg = "Method '{$this->method->getName()}' return type does not satisfy the contract";
            if ($this->type) $msg .= " (Expected: {$this->type})";
            return $msg . ".";
        }
    }
?>