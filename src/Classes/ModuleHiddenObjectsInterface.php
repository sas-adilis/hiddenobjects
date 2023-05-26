<?php
namespace Adilis\HiddenObjects\Classes;

interface ModuleHiddenObjectsInterface {

    public function getPrefix(): string;
    public function getTable(): string;
}