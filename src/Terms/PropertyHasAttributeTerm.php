<?php
    /*/
     * Project Name:    Wingman — Helix — Property Has Attribute Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use ReflectionProperty;
    use Wingman\Helix\Property;

    /**
     * Checks that a property declares a specific attribute.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyHasAttributeTerm extends PropertyContractTerm {
        /**
         * The fully qualified name of the attribute to check for.
         * @var class-string
         */
        protected string $attribute;

        /**
         * Creates a new term.
         * @param class-string $attribute The fully qualified name of the attribute to check for.
         */
        public function __construct (string $attribute) {
            $this->attribute = $attribute;
        }

        /**
         * Evaluates whether a property has the specified attribute.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the property has the attribute.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Property::exists($objOrClass, $this->property->getName())) {
                return false;
            }

            $reflection = new ReflectionProperty($objOrClass, $this->property->getName());
            return !empty($reflection->getAttributes($this->attribute));
        }

        /**
         * Gets the error message for when a property does not have the required attribute.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Property '{$this->property->getName()}' does not have the required attribute '{$this->attribute}'.";
        }
    }
?>