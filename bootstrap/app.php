<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});


error_reporting(E_ALL);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "\n\033[31m[Error {$errno}] {$errstr}\033[0m\n";
    echo "File: {$errfile}\n";
    echo "Line: {$errline}\n\n";
    return true;
});

set_exception_handler(function($exception) {
    echo "\n\033[31m[Exception] " . $exception->getMessage() . "\033[0m\n";
    echo "File: " . $exception->getFile() . "\n";
    echo "Line: " . $exception->getLine() . "\n\n";
    echo $exception->getTraceAsString() . "\n";
});


// try {
//     \App\Core\Database;
// } catch (\Exception $e) {
//     CLIHelper::error("Failed to connect to database. Please check your configuration.");
//     exit(1);
// }