<?php
    /**
     * Project Name:    Wingman Helix - Constant Has Attribute Term
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
    use ReflectionClassConstant;
    use Wingman\Helix\Inspector;

    /**
     * Checks that a constant declares a specific attribute.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantHasAttributeTerm extends ConstantContractTerm {
        /**
         * The name of the attribute to check for.
         * @var string
         */
        protected string $attribute;

        /**
         * Creates a new term.
         * @param string $attribute The name of the attribute to check for.
         */
        public function __construct (string $attribute) {
            $this->attribute = $attribute;
        }

        /**
         * Evaluates whether a constant has the specified attribute.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the constant has the attribute.
         */
        public function evaluate (object|string $objOrClass) : bool {
            $reflection = Inspector::getInstance()->getClassReflection($objOrClass);
            $constant = $reflection->getReflectionConstant($this->constant->getName());

            if (!($constant instanceof ReflectionClassConstant)) {
                return false;
            }

            $attributes = $constant->getAttributes($this->attribute);

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
         * Gets the error message for when a constant does not have the required attribute.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            $baseMessage = "Constant '{$this->constant->getName()}' does not have the required attribute '{$this->attribute}'";

            if (isset($this->args[0])) {
                return "{$baseMessage} with value '{$this->args[0]}'.";
            }

            return "{$baseMessage}.";
        }
    }
?>