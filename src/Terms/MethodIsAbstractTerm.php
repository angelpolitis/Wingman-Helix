<?php
    /**
     * Project Name:    Wingman Helix - Method Is Abstract Term
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
    use Wingman\Helix\Method;

    /**
     * Checks that a method is abstract.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class MethodIsAbstractTerm extends MethodContractTerm {
        /**
         * Evaluates a contract term against a given object or class.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the method is abstract.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Method::exists($objOrClass, $this->method->getName())) {
                return false;
            }
            return Inspector::getInstance()->getMethodReflection($objOrClass, $this->method)->isAbstract();
        }

        /**
         * Gets the error message for when a method is not abstract.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Method '{$this->method->getName()}' is not abstract.";
        }
    }
?>