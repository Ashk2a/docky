<?php

namespace App\Core;

readonly class ProxyDefinition
{
    public string $command;

    public string $description;

    public array $aliases;

    public array $containers;

    public function __construct(public string $signature, array $rawDefinition)
    {
        $this->command = $rawDefinition['command'];
        $this->description = $rawDefinition['description'] ?? 'nothing';
        $this->aliases = $rawDefinition['aliases'] ?? [];
        $this->containers = $rawDefinition['containers'] ?? [];
    }
}
