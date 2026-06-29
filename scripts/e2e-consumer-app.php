<?php

declare(strict_types=1);

/**
 * Create a temporary Laravel application, install this package through a
 * Composer path repository, and verify real consumer-app behavior.
 */
final class CommandFailure extends RuntimeException
{
    public function __construct(
        public readonly array $command,
        public readonly string $cwd,
        public readonly int $exitCode,
        public readonly string $output,
    ) {
        parent::__construct('Command failed with exit code '.$exitCode.': '.command_to_string($command));
    }
}

final readonly class CommandResult
{
    public function __construct(
        public int $exitCode,
        public string $output,
    ) {}
}

$options = parse_options($argv);
$packageRoot = realpath(dirname(__DIR__));

if ($packageRoot === false || ! is_file($packageRoot.DIRECTORY_SEPARATOR.'composer.json')) {
    fwrite(STDERR, "Could not locate package root.\n");
    exit(1);
}

$tempRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'zarbin-seo-e2e-'.date('YmdHis').'-'.bin2hex(random_bytes(4));
$appDir = $tempRoot.DIRECTORY_SEPARATOR.'consumer-app';
$server = null;

try {
    step('Creating temporary Laravel application');
    mkdir($tempRoot, 0777, true);
    run(composer_command([
        'create-project',
        'laravel/laravel',
        $appDir,
        $options['laravel'],
        '--no-interaction',
        '--prefer-dist',
    ]), $packageRoot, 900);

    step('Configuring Composer path repository');
    run(composer_command(['config', 'repositories.zarbin-seo', 'path', $packageRoot]), $appDir);
    run(composer_command(['require', 'zarbinco/zarbin-seo:@dev', '--no-interaction']), $appDir, 900);

    step('Configuring SQLite test database');
    configure_sqlite($appDir);

    step('Running package discovery and publishing resources');
    artisan($appDir, ['package:discover']);
    artisan($appDir, ['vendor:publish', '--tag=zarbin-seo-config', '--force']);
    artisan($appDir, ['vendor:publish', '--tag=zarbin-seo-migrations', '--force']);
    artisan($appDir, ['vendor:publish', '--tag=zarbin-seo-views', '--force']);
    assert_published_files($appDir);

    step('Running temporary application migrations');
    artisan($appDir, ['migrate', '--force'], 300);

    step('Running package Artisan smoke checks');
    artisan($appDir, ['zarbin-seo:doctor']);
    artisan($appDir, ['zarbin-seo:check']);
    artisan($appDir, ['zarbin-seo:sitemap', '--count']);
    artisan($appDir, ['zarbin-seo:robots']);
    artisan($appDir, ['route:list']);

    step('Adding a consumer Blade route');
    add_consumer_route_and_view($appDir);
    artisan($appDir, ['view:clear']);
    artisan($appDir, ['route:list']);

    step('Starting Laravel development server');
    $port = free_port();
    $server = start_server($appDir, $port);
    $baseUrl = 'http://127.0.0.1:'.$port;
    wait_for_url($baseUrl.'/robots.txt', 30);

    step('Requesting package routes and rendered consumer route');
    assert_http_contains($baseUrl.'/robots.txt', 'User-agent: *');
    assert_http_contains($baseUrl.'/sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?>');
    assert_http_contains($baseUrl.'/e2e-zarbin-seo', '<title>E2E Home');
    assert_http_contains($baseUrl.'/e2e-zarbin-seo', '<meta name="description" content="E2E Description">');

    success('Consumer app E2E smoke test passed.');
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, "\n[E2E] FAILED: ".$exception->getMessage()."\n");

    if ($exception instanceof CommandFailure) {
        fwrite(STDERR, "\nCommand: ".command_to_string($exception->command)."\n");
        fwrite(STDERR, "Working directory: {$exception->cwd}\n");
        fwrite(STDERR, "Exit code: {$exception->exitCode}\n");
        fwrite(STDERR, "Output:\n{$exception->output}\n");
    }

    fwrite(STDERR, "\nTemporary app: {$appDir}\n");
    exit(1);
} finally {
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }

    if (! $options['keep'] && is_dir($tempRoot)) {
        remove_directory($tempRoot);
    } elseif ($options['keep']) {
        note('Kept temporary app at: '.$appDir);
    }
}

/**
 * @return array{keep: bool, laravel: string}
 */
function parse_options(array $argv): array
{
    $options = [
        'keep' => false,
        'laravel' => '^12.0',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--keep') {
            $options['keep'] = true;

            continue;
        }

        if (str_starts_with($argument, '--laravel=')) {
            $version = trim(substr($argument, strlen('--laravel=')));
            $options['laravel'] = $version === '' ? $options['laravel'] : $version;

            continue;
        }

        if ($argument === '--help' || $argument === '-h') {
            echo "Usage: php scripts/e2e-consumer-app.php [--keep] [--laravel=^12.0]\n";
            exit(0);
        }

        throw new InvalidArgumentException('Unknown option: '.$argument);
    }

    return $options;
}

/**
 * @param  array<int, string>  $arguments
 * @return array<int, string>
 */
function composer_command(array $arguments): array
{
    $binary = getenv('COMPOSER_BINARY');

    if (is_string($binary) && trim($binary) !== '') {
        return [trim($binary), ...$arguments];
    }

    if (DIRECTORY_SEPARATOR === '\\') {
        $path = trim((string) shell_exec('where.exe composer.bat 2>NUL'));
        $first = strtok($path, PHP_EOL);

        if (is_string($first) && trim($first) !== '') {
            $phar = dirname(trim($first)).DIRECTORY_SEPARATOR.'composer.phar';

            if (is_file($phar)) {
                return [PHP_BINARY, $phar, ...$arguments];
            }

            return [trim($first), ...$arguments];
        }

        return ['composer.bat', ...$arguments];
    }

    return ['composer', ...$arguments];
}

function artisan(string $appDir, array $arguments, int $timeout = 120): CommandResult
{
    return run([PHP_BINARY, 'artisan', ...$arguments], $appDir, $timeout);
}

function run(array $command, string $cwd, int $timeout = 300): CommandResult
{
    note('$ '.command_to_string($command));

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open(command_to_shell($command), $descriptorSpec, $pipes, $cwd);

    if (! is_resource($process)) {
        throw new RuntimeException('Could not start command: '.command_to_string($command));
    }

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $output = '';
    $start = time();

    while (true) {
        $output .= stream_get_contents($pipes[1]);
        $output .= stream_get_contents($pipes[2]);

        $status = proc_get_status($process);

        if (! $status['running']) {
            break;
        }

        if ((time() - $start) > $timeout) {
            proc_terminate($process);
            throw new CommandFailure($command, $cwd, 124, $output."\nCommand timed out after {$timeout} seconds.");
        }

        usleep(100_000);
    }

    $output .= stream_get_contents($pipes[1]);
    $output .= stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        throw new CommandFailure($command, $cwd, $exitCode, $output);
    }

    return new CommandResult($exitCode, $output);
}

function configure_sqlite(string $appDir): void
{
    $databaseDir = $appDir.DIRECTORY_SEPARATOR.'database';
    $databasePath = $databaseDir.DIRECTORY_SEPARATOR.'database.sqlite';

    if (! is_dir($databaseDir)) {
        mkdir($databaseDir, 0777, true);
    }

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $envPath = $appDir.DIRECTORY_SEPARATOR.'.env';
    $env = is_file($envPath) ? (string) file_get_contents($envPath) : '';
    $env = set_env_value($env, 'DB_CONNECTION', 'sqlite');
    $env = set_env_value($env, 'DB_DATABASE', normalize_path($databasePath));
    $env = set_env_value($env, 'APP_URL', 'http://127.0.0.1');

    file_put_contents($envPath, $env);
}

function set_env_value(string $env, string $key, string $value): string
{
    $line = $key.'='.$value;

    if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $env) === 1) {
        return (string) preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $env);
    }

    return rtrim($env).PHP_EOL.$line.PHP_EOL;
}

function assert_published_files(string $appDir): void
{
    $config = $appDir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'zarbin-seo.php';
    $views = $appDir.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'zarbin-seo';
    $migrations = glob($appDir.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'*create_zarbin_seo_meta_table*') ?: [];

    assert_file($config, 'Published config file was not found.');

    if ($migrations === []) {
        throw new RuntimeException('Published migration was not found.');
    }

    if (! is_dir($views)) {
        throw new RuntimeException('Published views directory was not found.');
    }
}

function add_consumer_route_and_view(string $appDir): void
{
    $routesPath = $appDir.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php';
    $route = <<<'PHP'

Route::get('/e2e-zarbin-seo', function () {
    return view('e2e-zarbin-seo');
})->name('e2e-zarbin-seo');
PHP;

    file_put_contents($routesPath, rtrim((string) file_get_contents($routesPath)).PHP_EOL.$route.PHP_EOL);

    $viewPath = $appDir.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'e2e-zarbin-seo.blade.php';
    $view = <<<'BLADE'
<!doctype html>
<html lang="en">
<head>
    {!! seo()->title('E2E Home')->description('E2E Description')->canonical(url('/e2e-zarbin-seo'))->render() !!}
</head>
<body>
    <h1>Zarbin SEO E2E</h1>
</body>
</html>
BLADE;

    file_put_contents($viewPath, $view.PHP_EOL);
}

function free_port(): int
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);

    if (! is_resource($socket)) {
        throw new RuntimeException('Could not allocate free port: '.$errstr);
    }

    $name = stream_socket_get_name($socket, false);
    fclose($socket);

    if (! is_string($name) || ! str_contains($name, ':')) {
        return random_int(18000, 23000);
    }

    return (int) substr(strrchr($name, ':'), 1);
}

function start_server(string $appDir, int $port): mixed
{
    $command = [PHP_BINARY, 'artisan', 'serve', '--host=127.0.0.1', '--port='.$port];
    note('$ '.command_to_string($command));
    $serverLog = $appDir.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'zarbin-seo-e2e-server.log';

    $process = proc_open(command_to_shell($command), [
        0 => ['pipe', 'r'],
        1 => ['file', $serverLog, 'a'],
        2 => ['file', $serverLog, 'a'],
    ], $pipes, $appDir);

    if (! is_resource($process)) {
        throw new RuntimeException('Could not start Laravel development server.');
    }

    fclose($pipes[0]);

    return $process;
}

function wait_for_url(string $url, int $timeout): void
{
    $start = time();

    while ((time() - $start) <= $timeout) {
        try {
            http_get($url);

            return;
        } catch (Throwable) {
            usleep(250_000);
        }
    }

    throw new RuntimeException('Timed out waiting for URL: '.$url);
}

function assert_http_contains(string $url, string $needle): void
{
    $body = http_get($url);

    if (! str_contains($body, $needle)) {
        throw new RuntimeException("Response from {$url} did not contain expected text: {$needle}\nResponse:\n".$body);
    }
}

function http_get(string $url): string
{
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 10,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    if ($body === false) {
        throw new RuntimeException('HTTP request failed: '.$url);
    }

    return $body;
}

function assert_file(string $path, string $message): void
{
    if (! is_file($path)) {
        throw new RuntimeException($message.' Path: '.$path);
    }
}

function remove_directory(string $directory): void
{
    $real = realpath($directory);
    $temp = realpath(sys_get_temp_dir());

    if ($real === false || $temp === false || ! str_starts_with($real, $temp)) {
        throw new RuntimeException('Refusing to remove non-temp directory: '.$directory);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir() && ! $item->isLink()) {
            rmdir($item->getPathname());
        } else {
            chmod($item->getPathname(), 0777);
            unlink($item->getPathname());
        }
    }

    rmdir($real);
}

function command_to_string(array $command): string
{
    return implode(' ', array_map(static function (mixed $part): string {
        $part = (string) $part;

        return str_contains($part, ' ') ? '"'.$part.'"' : $part;
    }, $command));
}

function command_to_shell(array $command): string
{
    return implode(' ', array_map(static function (mixed $part): string {
        $part = (string) $part;

        if (DIRECTORY_SEPARATOR !== '\\') {
            return escapeshellarg($part);
        }

        $part = str_replace(['%', '"'], ['%%', '\"'], $part);

        return '"'.$part.'"';
    }, $command));
}

function normalize_path(string $path): string
{
    return str_replace(DIRECTORY_SEPARATOR, '/', $path);
}

function step(string $message): void
{
    echo PHP_EOL."[E2E] {$message}".PHP_EOL;
}

function note(string $message): void
{
    echo "[E2E] {$message}".PHP_EOL;
}

function success(string $message): void
{
    echo PHP_EOL."[E2E] OK: {$message}".PHP_EOL;
}
