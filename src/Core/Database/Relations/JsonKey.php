<?php
namespace LARAVEL\DatabaseCore\Relations;

class JsonKey
{
    public function __construct(protected string $column)
    {
        //
    }

    public function __toString(): string
    {
        return $this->column;
    }
}
