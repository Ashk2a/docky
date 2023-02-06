<?php

namespace App\Providers;

use App\Core\AnonymousCommand;
use App\Core\AnonymousCommandDefinition;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use JsonException;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->loadDockyConfig();
        $containers = $this->extractContainersFromConfig($config);
        $commands = $this->buildAnonymousCommandsFromConfig($config);

        Config::set('docky.containers', $containers);

        Artisan::starting(static fn (Artisan $artisan) => $artisan->addCommands($commands));
    }

    private function loadDockyConfig(): array
    {
        if (! file_exists('docky.json')) {
            throw new RuntimeException('docky.json not found');
        }

        try {
            $config = json_decode(file_get_contents('docky.json'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('Invalid json format in docky.json');
        }

        return $config;
    }

    private function extractContainersFromConfig(array $config): array
    {
        $containers = Arr::get($config, 'containers', []);

        if (empty($containers)) {
            throw new RuntimeException('No containers defined in docky.json');
        }

        return $containers;
    }

    private function buildAnonymousCommandsFromConfig(array $config): array
    {
        $proxies = Arr::get($config, 'commands.customs', []);
        $commands = [];

        foreach ($proxies as $signature => $proxy) {
            $definition = new AnonymousCommandDefinition($signature, $proxy);
            $commands[] = new class($definition) extends AnonymousCommand
            {
            };
        }

        return $commands;
    }
}
