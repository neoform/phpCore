#!/usr/bin/env php
<?php

    $opts = getopt('', [ 'env:' ]);
    $env  = trim(strtolower(isset($opts['env']) ? $opts['env'] : ''));

    if (! $env) {
        echo "\033[1;31mERROR\033[0m --env {env_name} must be set\n";
        die;
    }

    $root = realpath(__DIR__ . '/..');
    require_once("{$root}/library/neoform/core.php");
    neoform\core::init([
        'extension'   => 'php',
        'environment' => $env,

        'application' => "{$root}/application/",
        'external'    => "{$root}/external/",
        'logs'        => "{$root}/logs/",
        'website'     => "{$root}/www/",
    ]);

    class regenerate_config extends cli\model {

        public function init() {
            config\dao::set(
                $this->opt('file'),
                $this->opt('env')
            );
        }
    }

    new regenerate_config('', ['env:', 'file::', ]);

