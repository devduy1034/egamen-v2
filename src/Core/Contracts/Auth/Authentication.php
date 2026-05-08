<?php
namespace LARAVEL\Core\Contracts\Auth;
interface Authentication
{
    public function attempt(array $options = []): bool;
    public function user(): ?\LARAVEL\DatabaseCore\Eloquent\Model;
    public function logout(): void;
    public function check(): bool;
    public function guard($guard = ""): Authentication;
}