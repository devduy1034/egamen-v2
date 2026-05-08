<?php
namespace LARAVEL\DatabaseCore\Eloquent;

use LARAVEL\Core\Support\Facades\Hash;
use LARAVEL\DatabaseCore\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class Authenticate extends Model implements Authenticatable
{
    public function setPasswordAttribute(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->getAttribute($this->password);
    }

    /**
     * Get the column name for the password.
     */
    public function getAuthPasswordName()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken()
    {
        if (! empty($this->getRememberTokenName())) {
            return $this->getAttribute($this->getRememberTokenName());
        }
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value)
    {
        if (! empty($this->getRememberTokenName())) {
            $this->setAttribute($this->getRememberTokenName(), $value);
        }
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return $this->rememberToken ?? 'remember_token';
    }
}