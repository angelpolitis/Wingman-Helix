<?php
    /**
     * Project Name:    Wingman Helix - Contract Violation Exception
     * Created by:      Angel Politis
     * Creation Date:   Feb 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    # Use the Helix.Exceptions namespace.
    namespace Wingman\Helix\Exceptions;

    # Import the following classes to the current scope.
    use RuntimeException;
    use Throwable;
    use Wingman\Helix\Contract;

    /**
     * Thrown when an object fails to satisfy a contract.
     * @package Wingman\Helix\Exceptions
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ContractViolationException extends RuntimeException {
        /**
         * The contract that was violated.
         * @var Contract
         */
        protected Contract $contract;

        /**
         * Creates a new exception.
         * @param Contract $contract The contract that was violated.
         * @param string $message An optional error message.
         * @param int $code An optional error code.
         * @param Throwable|null $previous An optional previous exception for chaining.
         */
        public function __construct (Contract $contract, string $message = "", int $code = 0, ?Throwable $previous = null) {
            parent::__construct($message, $code, $previous);
            $this->contract = $contract;
        }

        /**
         * Gets the contract that was violated.
         * @return Contract The contract that was violated.
         */
        public function getContract () : Contract {
            return $this->contract;
        }
    }
?>