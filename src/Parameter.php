<?php
    /*/
     * Project Name:    Wingman — Helix — Parameter
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 17 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    /**
     * Describes a method parameter expected by a contract.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Parameter {
        /**
         * A special marker value used to indicate that a parameter characteristic should be waived during contract evaluation.
         * @var string
         */
        private const string UNSET = "[`[`[`[`[`[__HELIX_UNSET__]`]`]`]`]`]";

        /**
         * A list of parameter names that can be waived during contract evaluation.
         * @var string[]
         */
        protected static array $waivableProperties = ["type", "optional", "defaultValue", "passedByReference", "variadic", "exactNameRequired"];

        /**
         * The name of a parameter.
         * @var string
         */
        protected string $name;

        /**
         * The type of a parameter.
         * @var string|null
         */
        protected ?string $type = null;

        /**
         * The default value of a parameter.
         * @var mixed
         */
        protected mixed $defaultValue = self::UNSET;

        /**
         * Whether a parameter is optional.
         * @var bool
         */
        protected bool $optional = false;

        /**
         * Whether a parameter is passed by reference.
         * @var bool|null
         */
        protected ?bool $passedByReference = null;

        /**
         * Whether a parameter is variadic.
         * @var bool|null
         */
        protected ?bool $variadic = null;

        /**
         * Whether an exact name is required for a parameter during contract evaluation.
         * @var bool
         */
        protected bool $exactNameRequired = false;

        /**
         * Creates a new parameter with the given properties.
         * @param string $name The name of the parameter.
         * @param string|null $type The type of the parameter, or null if not set.
         * @param mixed $defaultValue The default value of the parameter, or null if not set.
         * @param bool $optional Whether the parameter is optional.
         * @param bool|null $passedByReference Whether the parameter is passed by reference, or null if not set.
         * @param bool|null $variadic Whether the parameter is variadic, or null if not set.
         * @param bool $exactNameRequired Whether an exact name is required for the parameter.
         */
        public function __construct (
            string $name,
            ?string $type = null,
            bool $optional = false,
            mixed $defaultValue = self::UNSET,
            ?bool $passedByReference = null,
            ?bool $variadic = null,
            bool $exactNameRequired = false
        ) {
            $this->name = $name;
            $this->type = $type;
            $this->optional = $optional;
            $this->defaultValue = $defaultValue;
            $this->passedByReference = $passedByReference;
            $this->variadic = $variadic;
            $this->exactNameRequired = $exactNameRequired;
        }

        /**
         * Expects certain characteristics for a parameter, which will be used for contract evaluation.
         * @param string|null $type The expected type of the parameter, or `null` to not check the type.
         * @param mixed $value The expected default value of the parameter, or `null` to not check the default value.
         * @param mixed $defaultValue The expected default value of the parameter, or `null` to not check the default value.
         * @param bool $optional Whether the parameter should be treated as optional even though it is required.
         * @param bool|null $passedByReference Whether the parameter is expected to be passed by reference, or `null` to not check this characteristic.
         * @param bool|null $variadic Whether the parameter is expected to be variadic, or `null` to not check this characteristic.
         * @param bool $exactNameRequired Whether an exact name is required for the parameter.
         * @return static The parameter.
         */
        public function expect (
            ?string $type = null,
            bool $optional = false,
            mixed $defaultValue = self::UNSET,
            ?bool $passedByReference = null,
            ?bool $variadic = null,
            bool $exactNameRequired = false
        ) : static {
            if ($type !== null) {
                $this->type = $type;
            }
            if ($defaultValue !== self::UNSET) {
                $this->defaultValue = $defaultValue;
            }
            if ($passedByReference !== null) {
                $this->passedByReference = $passedByReference;
            }
            if ($variadic !== null) {
                $this->variadic = $variadic;
            }
            $this->optional = $optional;
            $this->exactNameRequired = $exactNameRequired;
            return $this;
        }

        /**
         * Expects a default value for a parameter.
         * @param mixed $defaultValue The expected default value of the parameter.
         * @return static The parameter.
         */
        public function expectDefaultValue (mixed $defaultValue) : static {
            $this->defaultValue = $defaultValue;
            return $this;
        }

        /**
         * Expects a specific type for a parameter.
         * @param string $type The expected type of the parameter.
         * @return static The parameter.
         */
        public function expectType (string $type) : static {
            $this->type = $type;
            return $this;
        }

        /**
         * Expects whether a parameter is optional.
         * @param bool $optional Whether the parameter should be treated as optional even though it is required.
         * @return static The parameter.
         */
        public function expectOptional (bool $optional = true) : static {
            $this->optional = $optional;
            return $this;
        }

        /**
         * Expects whether a parameter is passed by reference.
         * @param bool $byReference Whether the parameter is expected to be passed by reference.
         * @return static The parameter.
         */
        public function expectPassedByReference (bool $byReference = true) : static {
            $this->passedByReference = $byReference;
            return $this;
        }

        /**
         * Expects whether a parameter is variadic.
         * @param bool $variadic Whether the parameter is expected to be variadic.
         * @return static The parameter.
         */
        public function expectVariadic (bool $variadic = true) : static {
            $this->variadic = $variadic;
            return $this;
        }

        /**
         * Gets the default value of a parameter.
         * @return mixed The default value of the parameter.
         */
        public function getDefaultValue () : mixed {
            return $this->defaultValue;
        }

        /**
         * Gets the name of a parameter.
         * @return string The name of the parameter.
         */
        public function getName () : string {
            return $this->name;
        }

        /**
         * Gets the signature of a parameter as a string.
         * @return string The parameter signature as a string.
         */
        public function getSignature () : string {
            $parts = [];

            if ($this->type) {
                $parts[] = $this->type;
            }

            $namePart = "";
            if ($this->passedByReference === true) {
                $namePart .= '&';
            }

            if ($this->variadic === true) {
                $namePart .= '...';
            }

            $namePart .= '$' . $this->name;
            $parts[] = $namePart;

            $signature = implode(' ', $parts);

            if ($this->hasDefaultValue()) {
                $signature .= " = " . var_export($this->defaultValue, true);
            }

            return $signature;
        }

        /**
         * Gets the type of a parameter.
         * @return string|null The type of the parameter, or null if not set.
         */
        public function getType () : ?string {
            return $this->type;
        }

        /**
         * Checks whether a parameter has an expected default value set.
         * @return bool Whether the parameter has an expected default value set.
         */
        public function hasDefaultValue () : bool {
            return $this->defaultValue !== self::UNSET;
        }

        /**
         * Checks whether an exact name is required for a parameter.
         * @return bool Whether an exact name is required for the parameter.
         */
        public function isExactNameRequired () : bool {
            return $this->exactNameRequired;
        }

        /**
         * Checks whether a parameter is optional.
         * @return bool Whether the parameter is optional.
         */
        public function isOptional () : bool {
            return $this->optional;
        }

        /**
         * Checks whether a parameter is passed by reference.
         * @return bool|null Whether the parameter is passed by reference, or null if not set.
         */
        public function isPassedByReference () : ?bool {
            return $this->passedByReference;
        }

        /**
         * Checks whether a parameter is variadic.
         * @return bool|null Whether the parameter is variadic, or null if not set.
         */
        public function isVariadic () : ?bool {
            return $this->variadic;
        }

        /**
         * Marks a parameter as required for contract evaluation.
         * @param bool $optional Whether the parameter should be treated as optional even though it is required.
         * @return static The parameter.
         */
        public function require (bool $optional = true) : static {
            $this->optional = $optional;
            return $this;
        }

        /**
         * Expects an exact name for a parameter.
         * @param bool $exactNameRequired Whether an exact name is required.
         * @return static The parameter.
         */
        public function requireExactName (bool $exactNameRequired = true) : static {
            $this->exactNameRequired = $exactNameRequired;
            return $this;
        }

        /**
         * Waives specific properties of a parameter during contract evaluation.
         * @param string ...$propertyNames The names of the properties to waive.
         * @return static The parameter.
         */
        public function waive (string ...$propertyNames) : static {
            foreach ($propertyNames as $propertyName) {
                if (in_array($propertyName, static::$waivableProperties, true)) {
                    $this->$propertyName = null;
                }
            }
            return $this;
        }
    }
?>