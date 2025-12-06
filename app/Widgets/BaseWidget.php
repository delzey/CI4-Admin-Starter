<?php
namespace App\Widgets;

/**
 * All widgets extend this base class.
 */
abstract class BaseWidget
{
    public string $name        = '';
    public string $permission  = '';

    abstract public function data(): array;
}
