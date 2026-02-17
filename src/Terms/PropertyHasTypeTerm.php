<?php
    /*/
     * Project Name:    Wingman — Helix — Property Has Type Term
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
     * Checks that a property type matches an expected type.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyHasTypeTerm extends PropertyContractTerm {
        /**
         * The expected type of a property, or `null` to just check for any type.
         * @var string|null
         */
        protected ?string $type;

        /**
         * Creates a new term.
         * @param string|null $type The expected type of the property, or `null` to just check for any type.
         */
        public function __construct (?string $type = null) {
            $this->type = $type ?: "";
        }

        /**
         * Evaluates a contract term against a given object or class.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the property has the required type.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Property::exists($objOrClass, $this->property->getName())) {
                return false;
            }
            $reflection = Inspector::getPropertyReflection($objOrClass, $this->property);
            if ($this->type === "") {
                return $reflection->hasType();
            }
            return $reflection->hasType() && (string) $reflection->getType() === $this->type;
        }

        /**
         * Gets the error message for when a contract term is not satisfied.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Property '{$this->property->getName()}' does not have the required type '{$this->type}'.";
        }
    }
?>