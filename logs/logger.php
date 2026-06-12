<?php

if (!defined('ABSPATH')) {
    exit;
}

function recipe_ai_log(
    $data,
    $filename = 'search-log.txt'
)
{
    if (
        !defined('RECIPE_AI_DEBUG')
        ||
        !RECIPE_AI_DEBUG
    ) {
        return;
    }

    $dir =
        plugin_dir_path(
            dirname(__FILE__)
        ) . 'logs/';

    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }

    $file =
        $dir . $filename;

    $content =
        "\n====================================\n";

    $content .=
        current_time('mysql') .
        "\n";

    if (
        is_array($data)
        ||
        is_object($data)
    ) {

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