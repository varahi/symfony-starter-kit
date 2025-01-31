<?php

declare(strict_types=1);

namespace App\Infrastructure\ConsoleCommand;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'app:openapi-routes-diff', description: 'Находит расхождения ручек и документации openapi')]
final class OpenApiRoutesDiffCommand extends Command
{
    private const OPEN_API_FILE_ARGUMENT = 'openApiFile';

    public function __construct(private readonly RouterInterface $router)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::OPEN_API_FILE_ARGUMENT, InputArgument::REQUIRED, 'Файл OpenApi в формате yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $file */
        $file = $input->getArgument(self::OPEN_API_FILE_ARGUMENT);

        $io = new SymfonyStyle($input, $output);

        $openApiPaths = $this->getOpenApiPaths($file);

        $appPaths = $this->getAppPaths();

        $missingAppPaths = array_diff($openApiPaths, $appPaths);
        $missingOpenApiPaths = array_diff($appPaths, $openApiPaths);

        if (\count($missingAppPaths) === 0 && \count($missingOpenApiPaths) === 0) {
            $io->success('Расхождения ручек и документации openapi не найдены');

            return Command::SUCCESS;
        }

        if (\count($missingAppPaths) > 0) {
            $io->info(['Найдены ручки, которые не реализованы в приложении:', ...$missingAppPaths]);
        }

        if (\count($missingOpenApiPaths) > 0) {
            $io->info(["Найдены ручки, которые не описаны в {$file}:", ...$missingOpenApiPaths]);
        }

        return Command::FAILURE;
    }

    /**
     * @return string[]
     */
    private function getOpenApiPaths(string $file): array
    {
        $openApiValues = (array) Yaml::parseFile($file);

        if (\array_key_exists('paths', $openApiValues) === false) {
            throw new \InvalidArgumentException('Invalid yaml file');
        }

        $openApiPaths = array_keys($openApiValues['paths']);

        $result = [];

        foreach ($openApiPaths as $openApiPath) {
            $result[] = '/api'.$openApiPath;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    private function getAppPaths(): array
    {
        $routes = $this->router->getRouteCollection();

        $result = [];
        foreach ($routes as $route) {
            $path = $route->getPath();
            if (str_contains($path, 'api') === false) {
                continue;
            }
            $result[] = $route->getPath();
        }

        return $result;
    }
}
