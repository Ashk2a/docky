<?php

namespace App\Core;

use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use Symfony\Component\Process\Process;

abstract class AnonymousCommand extends Command
{
    private const CONTAINER_OPTION = '--on=';

    private const ROOT_OPTION = '--root';

    private array $argv;

    public function __construct(readonly private AnonymousCommandDefinition $proxyDefinition)
    {
        $this->argv = array_slice($_SERVER['argv'], 2);
        $this->signature = $this->proxyDefinition->signature;
        $this->description = $this->proxyDefinition->description;
        $this->setAliases($this->proxyDefinition->aliases);

        parent::__construct();
    }

    public function configure(): void
    {
        // We don't use the symfony Input so ignore his validation.
        $this->ignoreValidationErrors();
    }

    public function __invoke(): int
    {
        $process = Process::fromShellCommandline(
            $this->buildProxyCommand()
        );

        $process->setTimeout(3600);
        $process->setTty(true);
        $process->run();

        echo $process->getOutput();

        return self::SUCCESS;
    }

    private function buildProxyCommand(): string
    {
        if (empty($this->proxyDefinition->containers)) {
            return trim($this->proxyDefinition->command.' '.implode(' ', $this->argv));
        }

        $container = $this->resolveContainer();
        $user = $this->resolveUserFromContainer($container);

        return sprintf(
            'docker compose exec -u %s "%s" %s',
            $user,
            $container,
            trim($this->proxyDefinition->command.' '.implode(' ', $this->argv))
        );
    }

    private function resolveContainer(): string
    {
        $allowedContainer = $this->proxyDefinition->containers;
        $availableContainers = array_keys(Config::get('docky.containers'));

        foreach (array_reverse($this->argv) as $k => $arg) {
            $arg = str($arg);

            if (! $arg->startsWith(self::CONTAINER_OPTION)) {
                continue;
            }

            $container = $arg->after(self::CONTAINER_OPTION)->value();

            if (! in_array($container, $availableContainers, true)) {
                throw new RuntimeException('No container named "'.$container.'" has been found.');
            }

            unset($this->argv[$k]);

            return $container;
        }

        return $allowedContainer[0];
    }

    private function resolveUserFromContainer(string $container): string
    {
        $defaultUser = Config::get('docky.containers.'.$container.'.user', 'root');

        if (in_array(self::ROOT_OPTION, $_SERVER['argv'], true)) {
            unset($this->argv[array_search(self::ROOT_OPTION, $this->argv, true)]);

            return 'root';
        }

        return $defaultUser;
    }
}
