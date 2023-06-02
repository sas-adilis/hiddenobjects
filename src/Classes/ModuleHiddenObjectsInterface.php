<?php

namespace Adilis\HiddenObjects\Classes;

interface ModuleHiddenObjectsInterface
{
    public function getPrefix(): string;

    public function getTable(): string;

    public function getModuleKey(): string;

    public function getName(): string;

    public function getDisplayName(): string;

    public function getDescription(): string;

    public function getDefaultTabName(): string;

    public function getFrenchTabName(): string;
}
