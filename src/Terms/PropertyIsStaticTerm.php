<?php
    /*/
     * Project Name:    Wingman — Helix — Property Is Static Term
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
     * Checks that a property is static.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyIsStaticTerm extends PropertyContractTerm {
        /**
         * Evaluates whether a property is static on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the property is static.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Property::exists($objOrClass, $this->property->getName())) {
                return false;
            }

            return Inspector::getPropertyReflection($objOrClass, $this->property->getName())->isStatic();
        }

        /**
         * Gets the error message for when a property is not static.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Property '{$this->property->getName()}' is not static.";
        }
    }
?>