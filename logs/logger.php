<?php

if (!defined('ABSPATH')) {
    exit;
}

function recipe_ai_log(
    $data,
    $filename = 'search-log.txt'
)
{
    $dir =
        plugin_dir_path(
            dirname(__FILE__)
        ) . 'logs/';

    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }

    $file =
        $dir . $filename;

    $timestamp =
        current_time(
            'mysql'
        );

    $content =
        "\n\n====================================\n";

    $content .=
        $timestamp . "\n";

    if (is_array($data) || is_object($data)) {

        $content .=
            print_r(
                $data,
                true
            );

    } else {

        $content .=
            $data;

    }

    file_put_contents(
        $file,
        $content,
        FILE_APPEND
    );
}