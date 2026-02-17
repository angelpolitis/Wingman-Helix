<?php
    /*/
     * Project Name:    Wingman — Helix — Access Modifier
     * Created by:      Angel Politis
     * Creation Date:   Feb 17 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix.Enums namespace.
    namespace Wingman\Helix\Enums;

    /**
     * Represents the access modifiers for a method, property or constant.
     * @package Wingman\Helix\Enums
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    enum AccessModifier : string {
        /**
         * The private access modifier.
         * A method, property, or constant with this modifier is accessible only within the class itself.
         * Example: A private method that handles internal logic not meant to be exposed outside the class.
         * @var string
         */
        case Private = "private";

        /**
         * The protected access modifier.
         * A method, property, or constant with this modifier is accessible within the class itself and by inheriting classes.
         * Example: A protected method that can be used by subclasses but not by external code.
         * @var string
         */
        case Protected = "protected";

        /**
         * The public access modifier.
         * A method, property, or constant with this modifier is accessible from anywhere.
         * Example: A public method that can be called from any code outside the class.
         * @var string
         */
        case Public = "public";

        /**
         * Resolves an access modifier from a string or returns the existing instance.
         * @param static|string $modifier The access modifier to resolve.
         * @return static The resolved access modifier.
         */
        public static function resolve (self|string $modifier) : static {
            return $modifier instanceof static ? $modifier : static::from(strtolower($modifier));
        }
    }
?>