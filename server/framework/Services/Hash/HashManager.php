<?php

namespace Framework\Services\Hash;

/**
 * Class HashManager
 *
 * The HashManager class provides methods for securely hashing and verifying passwords.
 * It uses the bcrypt algorithm and allows customization of hashing options.
 *
 * @package Framework\Services\Hash
 */
class HashManager
{
    /**
     * The number of rounds for bcrypt hashing.
     *
     * @var int
     */
    protected $rounds = 10;

    /**
     * Create a new hashed password.
     *
     * @param string $value The password to hash.
     * @param array $options Additional options for customizing the hashing process.
     * @return string The hashed password.
     * @throws RuntimeException If bcrypt hashing is not supported.
     */
    public function make($value, array $options = [])
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);

        if ($hash === false) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    /**
     * Check if a given value matches a hashed password.
     *
     * @param string $value The plain text password to check.
     * @param string $hashedValue The hashed password to compare against.
     * @return bool True if the passwords match, false otherwise.
     */
    public function check($value, $hashedValue)
    {
        if (is_null($hashedValue) || strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the hashed value needs to be rehashed.
     *
     * @param string $hashedValue The hashed password to check.
     * @param array $options Additional options for customizing the hashing process.
     * @return bool True if the password needs rehashing, false otherwise.
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);
    }

    /**
     * Check if a value is hashed.
     *
     * @param string $value The value to check.
     * @return bool True if the value is hashed, false otherwise.
     */
    public function isHashed($value)
    {
        return password_get_info($value)['algo'] !== null;
    }

    /**
     * Get information about the hashed value.
     *
     * @param string $hashedValue The hashed value to inspect.
     * @return array Information about the hashed value.
     */
    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    /**
     * Set the number of rounds for bcrypt hashing.
     *
     * @param int $rounds The number of rounds.
     * @return $this
     */
    public function setRounds($rounds)
    {
        $this->rounds = (int) $rounds;

        return $this;
    }

    /**
     * Get the cost parameter for bcrypt hashing.
     *
     * @param array $options Additional options for customizing the hashing process.
     * @return int The cost parameter.
     */
    protected function cost(array $options = [])
    {
        return $options['rounds'] ?? $this->rounds;
    }
}