<?php

namespace App\Infrastructure\Services;

use App\Domain\Services\IRandomStringService;

/**
 * RandomStringServiceImpl - Implementation for generating cryptographically secure random strings
 */
class RandomStringServiceImpl implements IRandomStringService
{
    /**
     * Characters for alphanumeric generation (A-Z, a-z, 0-9)
     */
    private const ALPHANUMERIC_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Characters for numeric generation (0-9)
     */
    private const NUMERIC_CHARS = '0123456789';

    /**
     * Generate a cryptographically secure random string
     *
     * @param int $length The desired length of the random string
     * @return string A secure random string containing alphanumeric characters
     * @throws \InvalidArgumentException if length is less than 1
     */
    public function generate(int $length = 32): string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('Length must be at least 1');
        }

        return $this->generateAlphaNum($length);
    }

    /**
     * Generate a cryptographically secure random hex string
     *
     * @param int $byteLength The number of random bytes to generate (will produce 2x characters in hex)
     * @return string A secure random hex string
     * @throws \InvalidArgumentException if byteLength is less than 1
     */
    public function generateHex(int $byteLength): string
    {
        if ($byteLength < 1) {
            throw new \InvalidArgumentException('Byte length must be at least 1');
        }

        return bin2hex(random_bytes($byteLength));
    }

    /**
     * Generate a cryptographically secure random alphanumeric string
     * (contains only A-Z, a-z, and 0-9)
     *
     * @param int $length The desired length of the alphanumeric string
     * @return string A secure random alphanumeric string
     * @throws \InvalidArgumentException if length is less than 1
     */
    public function generateAlphaNum(int $length): string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('Length must be at least 1');
        }

        return $this->generateFromCharset(self::ALPHANUMERIC_CHARS, $length);
    }

    /**
     * Generate a cryptographically secure random numeric string
     * (contains only digits 0-9)
     *
     * @param int $length The desired length of the numeric string
     * @return string A secure random numeric string
     * @throws \InvalidArgumentException if length is less than 1
     */
    public function generateNumber(int $length): string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('Length must be at least 1');
        }

        return $this->generateFromCharset(self::NUMERIC_CHARS, $length);
    }

    /**
     * Generate a random string from a given character set
     *
     * @param string $charset The character set to use
     * @param int $length The desired length
     * @return string A secure random string
     */
    private function generateFromCharset(string $charset, int $length): string
    {
        $charsetLength = strlen($charset);
        $result = '';

        // Generate enough random bytes
        $randomBytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            // Use modulo bias-free method
            $randomIndex = ord($randomBytes[$i]) % $charsetLength;
            $result .= $charset[$randomIndex];
        }

        return $result;
    }
}
