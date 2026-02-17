<?php
    /*/
     * Project Name:    Wingman — Helix — Member
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Feb 16 2026
    /*/

    # Use the Helix namespace.
    namespace Wingman\Helix;

    # Import the following classes to the current scope.
    use InvalidArgumentException;
    use Wingman\Helix\Interfaces\ContractTerm;

    /**
     * Represents a base contract member with terms and a type.
     * @package Wingman\Helix
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    abstract class Member {
        /**
         * The contract that a member is bound to for evaluation.
         * @var Contract|null
         */
        protected ?Contract $contract = null;

        /**
         * The name of a member.
         * @var string
         */
        protected string $name;

        /**
         * The type of a member.
         * @var string|null
         */
        protected ?string $type = null;

        /**
         * The contract terms of a member.
         * @var ContractTerm[]
         */
        protected array $terms = [];

        /**
         * Creates a new member with a name and an optional type.
         * @param string $name The name of the member.
         * @param string|null $type The type of the member, or null if not set.
         */
        public function __construct (string $name, ?string $type = null) {
            $this->name = $name;
            $this->type = $type;
            $this->terms = [];
        }

        /**
         * Adds a contract term to a member.
         * @param ContractTerm|string $term The contract term to add, or the class name of the term to instantiate.
         * @param mixed $constructorArgs Optional arguments to pass when instantiating a term from a class name.
         * @param mixed $evaluatorArgs Optional arguments to pass when evaluating the term.
         * @return static Returns the current instance for method chaining.
         * @throws InvalidArgumentException If the provided term is invalid or cannot be instantiated.
         */
        public function addTerm (ContractTerm|string $term, mixed $constructorArgs = [], mixed $evaluatorArgs = []) : static {
            if (is_string($term)) {
                if (!class_exists($term)) {
                    throw new InvalidArgumentException("Contract term class '$term' does not exist.");
                }
                if (!is_subclass_of($term, ContractTerm::class)) {
                    throw new InvalidArgumentException("Class '$term' is not a valid contract term.");
                }
                $term = new $term(...(is_array($constructorArgs) ? $constructorArgs : [$constructorArgs]));

            }
            $term->setArgs(is_array($evaluatorArgs) ? $evaluatorArgs : [$evaluatorArgs]);
            $term->setContext($this);
            $this->terms[] = $term;
            return $this;
        }

        /**
         * Adds multiple contract terms to a member.
         * @param (ContractTerm|string)[] $terms An array of contract terms to add, which can be instances of ContractTerm or class names with optional arguments.
         * @return static Returns the current instance for method chaining.
         */
        public function addTerms (array $terms) : static {
            foreach ($terms as $term) {
                if (is_array($term)) {
                    $this->addTerm(...$term);
                }
                else {
                    $this->addTerm($term);
                }
            }
            return $this;
        }

        /**
         * Binds a member to a contract for evaluation.
         * @param Contract $contract The contract to bind to.
         * @return static Returns the current instance for method chaining.
         */
        public function bindToContract (Contract $contract) : static {
            $this->contract = $contract;
            return $this;
        }

        /**
         * Clears all contract terms from a member.
         * @return static Returns the current instance for method chaining.
         */
        public function clearTerms () : static {
            $this->terms = [];
            return $this;
        }

        /**
         * Determines if a member exists in a given class or object.
         * @param object|string $target The class name or object to check for the member.
         * @param string $memberName The name of the member to check for.
         * @return bool Whether the member exists.
         */
        abstract public static function exists (object|string $target, string $memberName) : bool;

        /**
         * Gets the name of a member.
         * @return string The name of the member.
         */
        public function getName () : string {
            return $this->name;
        }

        /**
         * Generates a string representation of a member's signature.
         * @return string The member signature as a string.
         */
        abstract public function getSignature () : string;

        /**
         * Gets the contract terms of a member.
         * @return ContractTerm[] An array of contract terms.
         */
        public function getTerms () : array {
            return $this->terms;
        }

        /**
         * Gets the type of a member.
         * @return string|null The type of the member, or null if not set.
         */
        public function getType () : ?string {
            return $this->type;
        }

        /**
         * Removes a specific contract term from a member.
         * @param ContractTerm $term The contract term to remove.
         * @return static Returns the current instance for method chaining.
         */
        public function removeTerm (ContractTerm $term) : static {
            $this->terms = array_values(array_filter(
                $this->terms,
                static fn (ContractTerm $item) => $item !== $term
            ));
            return $this;
        }

        /**
         * Sets the contract terms for a member, replacing any existing terms.
         * @param (ContractTerm|string)[] $terms An array of contract terms to set, which can be instances of ContractTerm or class names with optional arguments.
         * @return static Returns the current instance for method chaining.
         */
        public function setTerms (array $terms) : static {
            $this->terms = $terms;
            return $this;
        }

        /**
         * Sets the type of a member.
         * @param string|null $type The type to set, or null to unset the type.
         * @return static Returns the current instance for method chaining.
         */
        public function setType (?string $type) : static {
            $this->type = $type;
            return $this;
        }
    }
?>