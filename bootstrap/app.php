<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Set execution time limit to prevent timeout errors
// 0 = unlimited, but we set a high value to prevent issues
@set_time_limit(300); // 5 minutes
@ini_set('max_execution_time', '300');
@ini_set('default_socket_timeout', '60');

// Suppress broken pipe errors from Laravel's built-in server
// This happens when stdout is redirected and the pipe closes
// Set error suppression at PHP level
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');

// Suppress broken pipe warnings globally for cli-server
if (php_sapi_name() === 'cli-server') {
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // Suppress broken pipe errors (errno 32) from server.php or any file_put_contents to stdout
        if ($errno === E_WARNING || $errno === E_NOTICE) {
            if (strpos($errstr, 'Broken pipe') !== false || 
                strpos($errstr, 'file_put_contents') !== false && strpos($errstr, 'stdout') !== false) {
                return true; // Suppress this specific error
            }
        }
        return false; // Let other errors through
    }, E_WARNING | E_NOTICE);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
