<?php

defined('ABSPATH') || exit;
/*Build Search Document*/
function recipe_ai_build_document($recipe)
{
    $parts = [];

    $parts[] = $recipe['name'] ?? '';

    $parts[] = wp_strip_all_tags(
        $recipe['summary'] ?? ''
    );

    if (!empty($recipe['ingredients_flat'])) {

        foreach (
            $recipe['ingredients_flat']
            as $ingredient
        ) {
            $parts[] = $ingredient['name'];
        }
    }

    if (
        !empty(
            $recipe['tags']['keyword']
        )
    ) {

        $parts = array_merge(
            $parts,
            $recipe['tags']['keyword']
        );
    }

    return implode(
        ' ',
        array_filter($parts)
    );
}
/*Import Single Recipe*/
function recipe_ai_import_recipe(
    array $recipe
)
{
    global $wpdb;

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $ingredients = [];

    if (
        !empty(
            $recipe['ingredients_flat']
        )
    ) {

        foreach (
            $recipe['ingredients_flat']
            as $ingredient
        ) {

            $ingredients[] =
                strtolower(
                    trim(
                        $ingredient['name']
                    )
                );
        }
    }

    $keywords =
        $recipe['tags']['keyword']
        ?? [];

    $cuisine =
        $recipe['tags']['cuisine']
        ?? [];

    $wpdb->replace(
        $table,
        [

            'recipe_id' =>
                $recipe['id'],

            'title' =>
                $recipe['name'],

            'slug' =>
                $recipe['slug'],

            'image_url' =>
                $recipe['image_url'],

            'ingredients_text' =>
                implode(
                    ',',
                    $ingredients
                ),

            'keywords_text' =>
                implode(
                    ',',
                    $keywords
                ),

            'cuisine_text' =>
                implode(
                    ',',
                    $cuisine
                ),

            'calories' =>
                intval(
                    $recipe['nutrition']['calories']
                    ?? 0
                ),

            'document' =>
                recipe_ai_build_document(
                    $recipe
                ),

            'recipe_json' =>
                wp_json_encode(
                    $recipe
                ),

            'updated_at' =>
                current_time(
                    'mysql'
                )

        ]
    );
}
/*Import Entire JSON File*/
function recipe_ai_import_json_file(
    $file_path
)
{
    if (
        !file_exists($file_path)
    ) {
        return 0;
    }

    $json =
        file_get_contents(
            $file_path
        );

    $recipes =
        json_decode(
            $json,
            true
        );

    if (
        empty($recipes)
    ) {
        return 0;
    }

    $count = 0;

    foreach (
        $recipes
        as $recipe
    ) {

        recipe_ai_import_recipe(
            $recipe
        );

        $count++;
    }

    return $count;
}

add_action('init', function () {

    if (
        !isset(
            $_GET['recipe-import']
        )
    ) {
        return;
    }

    if (
        !current_user_can(
            'manage_options'
        )
    ) {
        return;
    }

    $count =
        recipe_ai_import_json_file(
            WP_CONTENT_DIR .
            '/uploads/recipes.json'
        );

    wp_die(
        "Imported {$count} recipes."
    );

});