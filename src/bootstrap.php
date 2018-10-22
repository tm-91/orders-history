<?php
// force utf-8 as primary encoding
if (PHP_VERSION_ID < 50600) {
    mb_internal_encoding('utf-8');
} else {
    ini_set('default_charset', 'utf-8');
}

// internal autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    if (file_exists('src/'.$class.'.php')) {
        require 'src/'.$class.'.php';
    }
});

// composer autoloader - patched automatically
require 'vendor/autoload.php';
