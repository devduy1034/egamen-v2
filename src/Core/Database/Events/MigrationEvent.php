<?php
namespace LARAVEL\DatabaseCore\Events;

use Illuminate\Contracts\Database\Events\MigrationEvent as MigrationEventContract;
use LARAVEL\DatabaseCore\Migrations\Migration;

abstract class MigrationEvent implements MigrationEventContract
{
    /**
     * A migration instance.
     *
     * @var \LARAVEL\DatabaseCore\Migrations\Migration
     */
    public $migration;

    /**
     * The migration method that was called.
     *
     * @var string
     */
    public $method;

    /**
     * Create a new event instance.
     *
     * @param  \LARAVEL\DatabaseCore\Migrations\Migration  $migration
     * @param  string  $method
     * @return void
     */
    public function __construct(Migration $migration, $method)
    {
        $this->method = $method;
        $this->migration = $migration;
    }
}
