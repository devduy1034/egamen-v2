<?php
namespace LARAVEL\DatabaseCore\Traits\Eloquent;
trait CommitQueryMethod
{
    public function get()
    {
        $sql = $this->pase();
        return $this->request($sql);
    }

    /**
     * View query builder to sql statement.
     *
     * @return void
     */
    public function toSql(): void
    {
        echo $this->pase();
        exit(0);
    }

    /**
     * Get full sql statement
     *
     * @return string
     */
    public function getFullSql(): string
    {
        return $this->pase();
    }
}
