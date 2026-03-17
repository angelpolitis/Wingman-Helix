<?php
    /**
     * Project Name:    Wingman Helix - Property Is Public Term
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    # Use the Helix namespace.
    namespace Wingman\Helix\Terms;

    # Import the following classes to the current scope.
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Property;

    /**
     * Checks that a property is public.
     * @package Wingman\Helix\Terms
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class PropertyIsPublicTerm extends PropertyContractTerm {
        /**
         * Evaluates whether a property is public on the given object or class.
         * @param object|string $objOrClass The object or class name to check.
         * @return bool Whether the property is public.
         */
        public function evaluate (object|string $objOrClass) : bool {
            if (!Property::exists($objOrClass, $this->property->getName())) {
                return false;
            }

            return Inspector::getInstance()->getPropertyReflection($objOrClass, $this->property->getName())->isPublic();
        }

        /**
         * Gets the error message for when a property is not public.
         * @return string The error message.
         */
        public function getErrorMessage () : string {
            return "Property '{$this->property->getName()}' is not public.";
        }
    }
?>