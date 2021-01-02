<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

const USE_COMPOSER_AUTOLOADER = true;

$autoloaderFile = getcwd() . '/src/Helper/Autoloader.php';
require $autoloaderFile;

if(USE_COMPOSER_AUTOLOADER) {
    \PHPSimpleLib\Helper\Autoloader::useComposerAutoloader();
} else {
    \PHPSimpleLib\Helper\Autoloader::useDefaultAutoloader();
}