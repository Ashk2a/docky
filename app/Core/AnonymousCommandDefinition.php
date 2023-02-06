<?php

namespace App\Core;

readonly class AnonymousCommandDefinition
{
    public string $command;

    public string $description;

    public array $aliases;

    public array $containers;

    public function __construct(public string $signature, array $raw)
    {
        $this->command = $raw['command'];
        $this->description = $raw['description'] ?? '';
        $this->aliases = $raw['aliases'] ?? [];
        $this->containers = $raw['containers'] ?? [];
    }
}
