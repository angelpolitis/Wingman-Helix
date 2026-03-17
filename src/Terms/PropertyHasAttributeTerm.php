<?php
    /**
     * Project Name:    Wingman Helix - Property Has Attribute Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
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

            $attributes = Inspector::getInstance()->getPropertyReflection($objOrClass, $this->property)->getAttributes($this->attribute);

            if (empty($attributes)) {
                return false;
            }

            $attributeValue = $this->args[0] ?? null;

            if ($attributeValue === null) {
                return true;
            }

            foreach ($attributes as $attribute) {
                $arguments = $attribute->getArguments();

                if (in_array($attributeValue, $arguments, true) || $arguments === $attributeValue) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Gets the error message for when a property does not have the required attribute.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            $baseMessage = "Property '{$this->property->getName()}' does not have the required attribute '{$this->attribute}'";

            if (isset($this->args[0])) {
                return "{$baseMessage} with value '{$this->args[0]}'.";
            }

            return "{$baseMessage}.";
        }
    }
?>