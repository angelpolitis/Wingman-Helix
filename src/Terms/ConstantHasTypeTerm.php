<?php
    /**
     * Project Name:    Wingman Helix - Constant Has Type Term
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
    use Wingman\Helix\Constant;
    use Wingman\Helix\Inspector;

    /**
     * Checks that a constant type matches an expected type.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ConstantHasTypeTerm extends ConstantContractTerm {
        /**
         * The expected type of a constant, or `null` to just check for any type.
         * @var string|null
         */
        protected ?string $type;

        /**
         * Creates a new term.
         * @param string|null $type The expected type of the constant, or `null` to just check for any type.
         */
        public function __construct (?string $type = null) {
            $this->type = $type ?: "";
        }

        /**
         * Evaluates a contract term against a given object or class.
         * @param object|string $objOrClass The object instance or class name to evaluate.
         * @return bool Whether the constant has the required type.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Constant::exists($objOrClass, $this->constant->getName())) {
                return false;
            }
            $reflection = Inspector::getInstance()->getConstantReflection($objOrClass, $this->constant);
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
            return "Constant '{$this->constant->getName()}' does not have the required type '{$this->type}'.";
        }
    }
?>