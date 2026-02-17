<?php
    /*/
     * Project Name:    Wingman — Helix — Constant Matches Signature Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 17 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Constant;
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\TypeComparator;

    /**
     * Checks that a constant matches a specified signature.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com
     * @since 1.0
     */
    class ConstantMatchesSignatureTerm extends ConstantContractTerm {
        /**
         * Indicates whether the constant has been defined on the target class or object.
         * @var bool
         */
        protected bool $constantDefined;

        /**
         * Evaluates whether the constant signature on the target matches the blueprint.
         * @param object|string $objOrClass
         * @return bool
         */
        public function evaluate (object|string $objOrClass) : bool {
            $blueprint = $this->constant;
            $name = $blueprint->getName();

            # 1. Existence Check
            if (!Constant::exists($objOrClass, $name)) {
                $this->constantDefined = false;
                return $blueprint->isOptional();
            }

            $this->constantDefined = true;

            $reflection = Inspector::getConstantReflection($objOrClass, $name);

            # 2. Access Modifier Check
            if (($expectedMod = $blueprint->getAccessModifier()) !== null) {
                $actualMod = match (true) {
                    $reflection->isPublic() => AccessModifier::Public,
                    $reflection->isProtected() => AccessModifier::Protected,
                    $reflection->isPrivate() => AccessModifier::Private,
                    default => null
                };
                if ($expectedMod !== $actualMod) return false;
            }

            # 3. Type Check (PHP 8.3+)
            if ($blueprint->getType() !== null) {
                if (method_exists($reflection, "hasType") && $reflection->hasType()) {
                    if (!TypeComparator::matchType($blueprint->getType(), $reflection->getType(), $objOrClass)) {
                        return false;
                    }
                }
            }

            # 4. Value Check
            if ($blueprint->hasValue()) {
                if ($reflection->getValue() !== $blueprint->getValue()) {
                    return false;
                }
            }

            return true;
        }

        /**
         * Gets the error message for when a constant does not match the required signature.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            if (!$this->constantDefined) {
                return "Constant '{$this->constant->getName()}' does not exist.";
            }
            return "Constant '{$this->constant->getName()}' does not match the required signature: {$this->constant->getSignature()}";
        }
    }
?>