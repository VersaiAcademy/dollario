<?php
spl_autoload_register(function ($class) {
    $prefixes = [
        'Web3\\' => __DIR__ . '/web3.php/src/',
        'GuzzleHttp\\' => __DIR__ . '/guzzlehttp/guzzle/src/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
