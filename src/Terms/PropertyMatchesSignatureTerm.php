<?php
    /*/
     * Project Name:    Wingman — Helix — Property Matches Signature Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 17 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Enums\AccessModifier;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Property;
    use Wingman\Helix\TypeComparator;

    /**
     * Checks that a property matches a specified signature.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyMatchesSignatureTerm extends PropertyContractTerm {
        /**
         * Indicates whether the property has been defined on the target class or object.
         * @var bool
         */
        protected bool $propertyDefined;

        /**
         * Evaluates whether the property signature on the target matches the blueprint.
         * @param object|string $objOrClass
         * @return bool
         */
        public function evaluate (object|string $objOrClass) : bool {
            $blueprint = $this->property;
            $name = $blueprint->getName();

            # 1. Check if the property even exists.
            if (!Property::exists($objOrClass, $name)) {
                $this->propertyDefined = false;
                return $blueprint->isOptional();
            }

            $this->propertyDefined = true;

            $reflection = Inspector::getPropertyReflection($objOrClass, $blueprint);

            # 2. Access Modifier Check.
            if (($expectedModifier = $blueprint->getAccessModifier()) !== null) {
                $actualModifier = match(true) {
                    $reflection->isPublic() => AccessModifier::Public,
                    $reflection->isProtected() => AccessModifier::Protected,
                    $reflection->isPrivate() => AccessModifier::Private,
                    default => null
                };

                if ($expectedModifier !== $actualModifier) return false;
            }

            # 3. Static Modifier Check.
            if ($blueprint->isStatic() !== null) {
                if ($blueprint->isStatic() !== $reflection->isStatic()) return false;
            }

            # 4. Readonly Modifier Check (PHP 8.1+).
            if ($blueprint->isReadonly() !== null) {
                $isActualReadonly = method_exists($reflection, "isReadOnly") && $reflection->isReadOnly();
                if ($blueprint->isReadonly() !== $isActualReadonly) return false;
            }

            # 5. Type Hint Check.
            if (($expectedType = $blueprint->getType()) !== null) {
                if (!$reflection->hasType()) return false;
                
                if (!TypeComparator::matchType($expectedType, $reflection->getType(), $objOrClass)) {
                    return false;
                }
            }

            return true;
        }

        /**
         * Gets the error message for a signature mismatch.
         * @return string
         */
        public function getErrorMessage () : string {
            if (!$this->propertyDefined) {
                return "Property '\${$this->property->getName()}' does not exist.";
            }
            return "Property '\${$this->property->getName()}' does not match the required signature: {$this->property->getSignature()}";
        }
    }
?>