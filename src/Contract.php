<?php
    /*/
     * Project Name:    Wingman — Helix — Contract
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 19 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use InvalidArgumentException;
    use Wingman\Helix\Exceptions\ContractViolationException;

    /**
     * Defines a contract made up of method and property requirements.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class Contract {
        /**
         * The name of a contract.
         * @var string
         */
        protected string $name;

        /**
         * The members (methods, properties, constants) that make up a contract.
         * @var Member[]
         */
        protected array $members = [];

        /**
         * Creates a new contract with a name and optional members.
         * @param string $name The name of the contract.
         * @param Member[] $members An array of members that make up the contract.
         */
        public function __construct (string $name, array $members = []) {
            $this->name = $name;
            $this->members = $members;
        }

        /**
         * Converts the contract to a string representation.
         * @return string A string representation of the contract.
         */
        public function __toString () : string {
            return "Contract '{$this->name}' with " . count($this->members) . " members";
        }

        /**
         * Creates a new contract with a name and members defined by a callback.
         * @param string $name The name of the contract.
         * @param callable $callback A callback that defines the contract's members.
         * @return static The contract.
         */
        public static function create (string $name, callable $callback) : static {
            $contract = new static($name);
            $callback($contract);
            return $contract;
        }

        /**
         * Defines a constant requirement for a contract.
         * @param string|Constant $nameOrObj The name or instance of the constant.
         * @return Constant The created constant member for chaining.
         */
        public function forConstant (string|Constant $nameOrObj) : Constant {
            $constant = $this->members[] = $nameOrObj instanceof Constant ? clone $nameOrObj : new Constant($nameOrObj);
            $constant->bindToContract($this);
            return $constant;
        }

        /**
         * Defines a method requirement for a contract.
         * @param string|Method $nameOrObj The name or instance of the method.
         * @return Method The created method member for chaining.
         */
        public function forMethod (string|Method $nameOrObj) : Method {
            $method = $this->members[] = $nameOrObj instanceof Method ? clone $nameOrObj : new Method($nameOrObj);
            $method->bindToContract($this);
            return $method;
        }

        /**
         * Defines a property requirement for a contract.
         * @param string|Property $nameOrObj The name or instance of the property.
         * @return Property The created property member for chaining.
         */
        public function forProperty (string|Property $nameOrObj) : Property {
            $property = $this->members[] = $nameOrObj instanceof Property ? clone $nameOrObj : new Property($nameOrObj);
            $property->bindToContract($this);
            return $property;
        }

        /**
         * Creates a contract based on an existing interface, using its methods, properties, and constants as terms.
         * @param string $interface The name of the interface to create the contract from.
         * @return static The created contract based on the interface.
         * @throws InvalidArgumentException If the specified interface does not exist.
         */
        public static function fromInterface (string $interface) : static {
            if (!interface_exists($interface)) {
                throw new InvalidArgumentException("Interface '$interface' does not exist.");
            }

            $reflection = Inspector::getClassReflection($interface);
            $contract = new static($interface);

            foreach ($reflection->getConstants() as $name => $value) {
                $contract->forConstant($name)
                    ->expectValue($value)
                    ->require();
            }

            foreach ($reflection->getMethods() as $method) {
                $m = $contract->forMethod($method->getName())->require();

                if ($method->hasReturnType()) {
                    $m->expectReturnType(TypeComparator::stringifyType($method->getReturnType()));
                }
                
                foreach ($method->getParameters() as $param) {
                    $p = new Parameter(
                        $param->getName(),
                        $param->hasType() ? TypeComparator::stringifyType($param->getType()) : null,
                        $param->isOptional(),
                        $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                        $param->isVariadic(),
                        $param->isPassedByReference()
                    );

                    $m->expectParameter($p);
                }
            }

            foreach ($reflection->getProperties() as $property) {
                $p = $contract->forProperty($property->getName())->require();
                
                if ($property->hasType()) {
                    $p->expectType(TypeComparator::stringifyType($property->getType()));
                }
            }

            return $contract;
        }

        /**
         * Gets all members of a contract.
         * @return Member[] An array of members that make up the contract.
         */
        public function getMembers () : array {
            return $this->members;
        }

        /**
         * Gets the name of a contract.
         * @return string The name of the contract.
         */
        public function getName () : string {
            return $this->name;
        }

        /**
         * Gets all terms of a contract.
         * @return Term[] An array of terms that make up the contract.
         */
        public function getTerms () : array {
            $allTerms = [];
            foreach ($this->members as $member) {
                foreach ($member->getTerms() as $term) {
                    $allTerms[] = $term;
                }
            }
            return $allTerms;
        }

        /**
         * Checks if an object or class name satisfies all terms of a contract.
         * @param object|string $objOrClass The object instance or class name to check.
         * @return bool Whether the object or class satisfies all terms of the contract.
         */
        public function isSatisfiedBy (object|string $objOrClass) : bool {
            foreach ($this->getTerms() as $term) {
                if (!$term->evaluate($objOrClass)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Validates an object or class against a contract, throwing an exception if any terms are violated.
         * @param object|string $objOrClass The object instance or class name to validate.
         * @param bool $allErrors Whether to collect all errors or stop at the first violation.
         * @throws ContractViolationException If any contract terms are violated.
         */
        public function validate (object|string $objOrClass, bool $allErrors = false) : void {
            $errors = [];
            foreach ($this->getTerms() as $term) {
                if ($term->evaluate($objOrClass)) continue;
                $errors[] = $term->getErrorMessage();
                if (!$allErrors) break;
            }
            if (!empty($errors)) {
                if (is_object($objOrClass)) {
                    $ref = Inspector::getClassReflection($objOrClass);

                    if ($ref->isAnonymous()) {
                        $file = $ref->getFileName();
                        $line = $ref->getStartLine();
                        $name = "anonymous-class@{$file}:{$line}";
                    }
                    else $name = $ref->getName();
                }
                else $name = $objOrClass;

                $errorString = $allErrors ? PHP_EOL . implode(PHP_EOL, $errors) . PHP_EOL : ' ' . $errors[0];

                throw new ContractViolationException(
                    $this,
                    "Target '{$name}' violates contract '{$this->name}':$errorString"
                );
            }
        }
    }
?>