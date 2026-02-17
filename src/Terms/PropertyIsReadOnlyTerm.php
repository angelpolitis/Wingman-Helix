<?php
    /*/
     * Project Name:    Wingman — Helix — Property Is Read Only Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Property;

    /**
     * Checks that a property is read-only.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyIsReadOnlyTerm extends PropertyContractTerm {
        /**
         * Evaluates whether a property is read-only on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the property is read-only.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Property::exists($objOrClass, $this->property->getName())) {
                return false;
            }

            return Inspector::getPropertyReflection($objOrClass, $this->property->getName())->isReadOnly();
        }

        /**
         * Gets the error message for when a property is not read-only.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Property '{$this->property->getName()}' is not read-only.";
        }
    }
?>