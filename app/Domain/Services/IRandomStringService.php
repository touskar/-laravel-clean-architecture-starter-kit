<?php

namespace App\Domain\Services;

/**
 * IRandomStringService - Service for generating cryptographically secure random strings
 */
interface IRandomStringService
{
    /**
     * Generate a cryptographically secure random string
     *
     * @param int $length The desired length of the random string
     * @return string A secure random string containing alphanumeric characters
     * @throws \InvalidArgumentException if length is less than 1
     */
    public function generate(int $length = 32): string;

    /**
     * Generate a cryptographically secure random hex string
     *
     * @param int $byteLength The number of random bytes to generate (will produce 2x characters in hex)
     * @return string A secure random hex string
     * @throws \InvalidArgumentException if byteLength is less than 1
     */
    public function generateHex(int $byteLength): string;

    /**
     * Generate a cryptographically secure random alphanumeric string
     * (contains only A-Z, a-z, and 0-9)
     *
     * @param int $length The desired length of the alphanumeric string
     * @return string A secure random alphanumeric string
     * @throws \InvalidArgumentException if length is less than 1
     */
    public function generateAlphaNum(int $length): string;

    /**
     * Generate a cryptographically secure random numeric string
     * (contains only digits 0-9)
     *
     * @param int $length The desired length of the numeric string
     * @return string A secure random numeric string
     * @throws \InvalidArgumentException if length is less than 1
     */
    public function generateNumber(int $length): string;
}
