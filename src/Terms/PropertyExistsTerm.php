<?php
    /*/
     * Project Name:    Wingman — Helix — Property Exists Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Property;

    /**
     * Checks that a property exists on a class or object.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyExistsTerm extends PropertyContractTerm {
        /**
         * Evaluates whether a property exists on the given class or object.
         * @param object|string $objOrClass The class name or object to check for the property.
         * @return bool Whether the property exists.
         */
        public function evaluate (object|string $objOrClass) : bool {
            return Property::exists($objOrClass, $this->property->getName());
        }

        /**
         * Gets the error message for when a property does not exist.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Property '{$this->property->getName()}' does not exist.";
        }
    }
?>