<?php
    /**
     * Project Name:    Wingman Helix - Property Value Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 17 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    # Use the Helix.Terms namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Inspector;

    /**
     * Checks that a property has a specific value.
     * When the target is a class string, only static properties can be checked;
     * attempting to assert the value of a non-static property against a class string will always fail.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyValueTerm extends PropertyContractTerm {
        /**
         * Tracks whether the last evaluation failed because an object instance was required.
         * @var bool
         */
        protected bool $instanceRequired = false;
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
            $reflection = Inspector::getInstance()->getPropertyReflection($objOrClass, $blueprint);

            # 2. Static Context (Class String)
            if (is_string($objOrClass)) {
                if (!$reflection->isStatic()) {
                    $this->instanceRequired = true;
                    return false;
                }

                $this->instanceRequired = false;
                return $reflection->hasDefaultValue() && $reflection->getDefaultValue() === $expected;
            }

            $this->instanceRequired = false;

            # 3. Instance Context (Object)
            # isInitialized() is vital for typed properties to avoid Fatal Errors.
            if (!$reflection->isInitialized($objOrClass)) {
                return false;
            }

            # Handle visibility for older PHP versions (8.1+ does this automatically).
            if (method_exists($reflection, "setAccessible")) {
                /** @disregard 1007 */
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
        public function getErrorMessage () : string {
            if ($this->instanceRequired) {
                return "Property '\${$this->property->getName()}' requires an object instance to verify its value; a class name was given.";
            }

            $expected = var_export($this->property->getValue(), true);
            return "Property '\${$this->property->getName()}' does not match the required value: {$expected}";
        }
    }
?>