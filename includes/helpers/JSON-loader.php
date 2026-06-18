<?php 
/* Generic JSON loader function*/
function recipe_ai_load_json(
    $file
)
{
    static $cache = [];

    if (
        isset($cache[$file])
    ) {
        return $cache[$file];
    }

    $path =
        RECIPE_AI_PATH .
        'data/' .
        $file;

    if (
        !file_exists($path)
    ) {
        return [];
    }

    $cache[$file] =
        json_decode(
            file_get_contents(
                $path
            ),
            true
        );

    return $cache[$file];
}
