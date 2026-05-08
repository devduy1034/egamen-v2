<?php
namespace LARAVEL\Core\Hashing;
use LARAVEL\Core\Contracts\Hashing\Hasher;
class BcryptHasher implements Hasher
{
    /**
     * @throws \Exception
     */

    public function make(string $value, array $options = []): string
    {
        $key = config('app.secretkey');
        $combinedPassword = "@LARAVEL{$value}{$key}";
        $hash = password_hash($combinedPassword, PASSWORD_BCRYPT, $options);
        if ($hash === false) {
            throw new \Exception('Bcrypt hashing not supported.');
        }

        return $hash;
    }
    public function check(string $value, string $hashedValue, array $options = []): bool
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }
        $key = config('app.secretkey');
        $combinedPassword = "@LARAVEL{$value}{$key}";
        return password_verify($combinedPassword, $hashedValue);
    }
}