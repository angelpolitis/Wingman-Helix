<?php
    /*/
     * Project Name:    Wingman — Helix — Property Value Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 17 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Inspector;

    /**
     * Checks that a property has a specific value.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyValueTerm extends PropertyContractTerm {
        /**
         * Evaluates whether a property has the expected value on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the property has the expected value.
         */
        public function evaluate (object|string $objOrClass) : bool {
            $blueprint = $this->property;

            # 1. Skip if no specific live value is required.
            if (!$blueprint->hasValue()) {
                return true;
            }

            $expected = $blueprint->getValue();
            $reflection = Inspector::getPropertyReflection($objOrClass, $blueprint);

            # 2. Static Context (Class String)
            if (is_string($objOrClass)) {
                # We can only check the blueprint's default value here.
                # If the property isn't static, we can't possibly know a "live" value.
                if (!$reflection->isStatic()) {
                    return true; 
                }
                return $reflection->hasDefaultValue() && $reflection->getDefaultValue() === $expected;
            }

            # 3. Instance Context (Object)
            # isInitialized() is vital for typed properties to avoid Fatal Errors.
            if (!$reflection->isInitialized($objOrClass)) {
                return false;
            }

            # Handle visibility for older PHP versions (8.1+ does this automatically).
            if (method_exists($reflection, "setAccessible")) {
                $reflection->setAccessible(true);
            }
            
            # Check the live memory of the object.
            $actualValue = $reflection->getValue($objOrClass);
            
            return $actualValue === $expected;
        }

        /**
         * Gets the error message for when a property does not have the expected value.
         * @return string The error message.
         */
        public function getErrorMessage(): string {
            $expected = var_export($this->property->getValue(), true);
            return "Property '\${$this->property->getName()}' does not match the required instance value: {$expected}";
        }
    }
?>