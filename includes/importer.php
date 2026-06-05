<?php

defined('ABSPATH') || exit;

function recipe_ai_build_document($recipe)
{
    $document = '';

    $document .= 'Recipe Name: '
        . $recipe['name']
        . "\n\n";

    $document .= 'Summary: '
        . wp_strip_all_tags(
            $recipe['summary']
        )
        . "\n\n";

    if (!empty($recipe['tags']['cuisine'])) {

        $document .= 'Cuisine: '
            . implode(
                ', ',
                $recipe['tags']['cuisine']
            )
            . "\n";
    }

    if (!empty($recipe['tags']['keyword'])) {

        $document .= 'Keywords: '
            . implode(
                ', ',
                $recipe['tags']['keyword']
            )
            . "\n";
    }

    $document .= "\nIngredients:\n";

    foreach (
        $recipe['ingredients_flat']
        as $ingredient
    ) {

        $document .= '- '
            . $ingredient['name']
            . "\n";
    }

    $document .= "\nInstructions:\n";

    foreach (
        $recipe['instructions_flat']
        as $step
    ) {

        $document .= '- '
            . wp_strip_all_tags(
                $step['text']
            )
            . "\n";
    }

    if (!empty($recipe['nutrition'])) {

        $document .= "\nNutrition:\n";

        foreach (
            $recipe['nutrition']
            as $key => $value
        ) {

            $document .=
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $key
                    )
                )
                . ': '
                . $value
                . "\n";
        }
    }

    if (
        !empty(
            $recipe['parent']['post_content']
        )
    ) {

        $document .=
            "\nRecipe Article:\n";

        $document .=
            wp_strip_all_tags(
                $recipe['parent']['post_content']
            );
    }

    return trim($document);
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

    $document =
        recipe_ai_build_document(
            $recipe
        );

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

            'calories' =>
                $recipe['nutrition']['calories']
                ?? 0,

            'protein' =>
                $recipe['nutrition']['protein']
                ?? 0,

            'recipe_json' =>
                wp_json_encode(
                    $recipe
                ),

            'document' =>
                $document,

            'updated_at' =>
                current_time('mysql')

        ]
    );
}
/*Import Entire JSON File*/
function recipe_ai_import_json_file(
    $file_path
)
{
    if (
        !file_exists(
            $file_path
        )
    ) {
        return false;
    }

    $json =
        json_decode(
            file_get_contents(
                $file_path
            ),
            true
        );

    if (
        !is_array(
            $json
        )
    ) {
        return false;
    }

    $count = 0;

    foreach ($json as $recipe) {

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