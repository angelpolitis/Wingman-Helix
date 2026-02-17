<?php
    /*/
     * Project Name:    Wingman — Helix — Method Matches Signature Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use ReflectionMethod;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Method;
    use Wingman\Helix\Parameter;
    use Wingman\Helix\TypeComparator;

    /**
     * Checks that a method matches an expected parameter signature.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodMatchesSignatureTerm extends MethodContractTerm {
        /**
         * Indicates whether the method has been defined on the target class or object.
         * @var bool
         */
        protected bool $methodDefined;
        
        /**
         * Checks that the parameters of a method match the expected signature.
         * @param ReflectionMethod $reflection The reflection of the method to check.
         * @param Parameter[] $expectedParams The expected parameters defined in the contract.
         * @param object|string $objOrClass The object or class name for resolving types.
         * @return bool Whether the parameters match the expected signature.
         */
        protected function checkParameters (ReflectionMethod $reflection, array $expectedParams, object|string $objOrClass) : bool {
            $actualParams = $reflection->getParameters();

            if (count($actualParams) < count($expectedParams)) {
                return false;
            }

            foreach ($expectedParams as $index => $expected) {
                $actual = $actualParams[$index] ?? null;

                # 1. Exact Name Check
                # We only check the name if the contract explicitly requires it.
                if ($expected->isExactNameRequired()) {
                    if ($actual->getName() !== $expected->getName()) {
                        return false;
                    }
                }

                # 2. Type Check
                if (($expectedType = $expected->getType()) !== null) {
                    if (!$actual->hasType()) return false;
                    if (!TypeComparator::matchType($expectedType, $actual->getType(), $objOrClass)) return false;
                }

                # 3. Modifiers (Reference / Variadic)
                if ($expected->isPassedByReference() !== null && $expected->isPassedByReference() !== $actual->isPassedByReference()) return false;
                if ($expected->isVariadic() !== null && $expected->isVariadic() !== $actual->isVariadic()) return false;

                # 4. Optionality & Default Values
                if ($expected->isOptional() && !$actual->isOptional()) return false;
                
                if ($expected->hasDefaultValue()) {
                    if (!$actual->isDefaultValueAvailable()) return false;
                    if ($actual->getDefaultValue() !== $expected->getDefaultValue()) return false;
                }
            }

            return true;
        }

        /**
         * Evaluates whether a method matches the expected parameter signature on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the method matches the expected parameter signature.
         */
        public function evaluate (object|string $objOrClass) : bool {
            $blueprint = $this->method;
            $name = $blueprint->getName();

            if (!Method::exists($objOrClass, $name)) {
                $this->methodDefined = false;
                return $blueprint->isOptional();
            }

            $this->methodDefined = true;

            $reflection = Inspector::getMethodReflection($objOrClass, $blueprint);

            if (($expectedModifier = $blueprint->getAccessModifier()) !== null) {
                $actual = match(true) {
                    $reflection->isPublic() => AccessModifier::Public,
                    $reflection->isProtected() => AccessModifier::Protected,
                    $reflection->isPrivate() => AccessModifier::Private,
                    default => null
                };

                if ($expectedModifier !== $actual) {
                    return false;
                }
            }

            if ($blueprint->isStatic() !== null) {
                if ($blueprint->isStatic() !== $reflection->isStatic()) return false;
            }
            if ($blueprint->isFinal() !== null) {
                if ($blueprint->isFinal() !== $reflection->isFinal()) return false;
            }
            if ($blueprint->isAbstract() !== null) {
                if ($blueprint->isAbstract() !== $reflection->isAbstract()) return false;
            }

            if (($type = $blueprint->getType()) !== null) {
                if (!$reflection->hasReturnType()) return false;
                if (!TypeComparator::matchType($type, $reflection->getReturnType(), $objOrClass)) {
                    return false;
                }
            }

            return $this->checkParameters($reflection, $blueprint->getParameters(), $objOrClass);
        }

        /**
         * Gets the error message for when a method does not match the expected parameter signature.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            if (!$this->methodDefined) {
                return "Method '{$this->method->getName()}' does not exist.";
            }
            return "Method '{$this->method->getName()}' does not match the defined signature: " . $this->method->getSignature();
        }
    }
?>