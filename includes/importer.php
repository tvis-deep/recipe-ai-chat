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

    $search_parts = [];

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */
    $search_parts[] = strtolower(
        $recipe['name'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Ingredients
    |--------------------------------------------------------------------------
    */
    $search_parts[] = implode(
        ' ',
        $ingredients
    );

    /*
    |--------------------------------------------------------------------------
    | Keywords
    |--------------------------------------------------------------------------
    */
    $search_parts[] = implode(
        ' ',
        $keywords
    );

    /*
    |--------------------------------------------------------------------------
    | Cuisine
    |--------------------------------------------------------------------------
    */
    $search_parts[] = implode(
        ' ',
        $cuisine
    );
    /*Include Instructions*/
   
    if (!empty($recipe['instructions_flat'])) {
        foreach ($recipe['instructions_flat']as $instruction) {
            if (isset($instruction['text'])) {
                $search_parts[] = wp_strip_all_tags($instruction['text']);
            }
        }
    }
    /*Include Recipe Summary*/

    $search_parts[] =wp_strip_all_tags($recipe['summary'] ?? '');

    /*Include Parent Post Content*/
    if (!empty($recipe['parent']['post_content'])) {
        $search_parts[] =
            wp_strip_all_tags(
                $recipe['parent']['post_content']
            );
    }
    /*Include nutrition*/
    $nutrition =
        $recipe['nutrition']
        ?? [];

    foreach (
        $nutrition as $key => $value
    ) {

        if (
            !empty($value)
        ) {

            $search_parts[] =
                strtolower($key);

        }
    }
    /*Create Final Search Text*/
    $search_text =
        strtolower(
            implode(
                ' ',
                $search_parts
            )
        );

    $search_text =
        preg_replace(
            '/\s+/',
            ' ',
            $search_text
        );

    /*Meal Type*/
    $meal_types = $recipe['parent']['tags']['by_meal'] ?? [];
    
    /*cook methods*/
    $cook_methods =$recipe['parent']['tags']['by_method']?? [];

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

            'search_text' => $search_text,

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
                ),

            'meal_type_text' =>
                implode(
                    ',',
                    $meal_types
                ),

            'cook_method_text' =>
                implode(
                    ',',
                    $cook_methods
                ),

            'diet_text' =>
                implode(
                    ',',
                    recipe_ai_detect_diet(
                        $recipe
                    )
                ),

            'occasion_text' =>
                implode(
                    ',',
                    recipe_ai_detect_occasion(
                        $recipe
                    )
                ),

            'protein' =>
                intval(
                    $recipe['nutrition']['protein']
                    ?? 0
                ),

            'prep_time' =>
                intval(
                    $recipe['prep_time']
                    ?? 0
                ),

            'cook_time' =>
                intval(
                    $recipe['cook_time']
                    ?? 0
                ),

            'total_time' =>
                intval(
                    $recipe['total_time']
                    ?? 0
                ),

        ]
    );
}
/*Diet Detection*/
function recipe_ai_detect_diet(
    $recipe
)
{
    $text =
        strtolower(
            json_encode($recipe)
        );

    $diets = [];

    if (
        strpos(
            $text,
            'vegan'
        ) !== false
    ) {
        $diets[] = 'vegan';
    }

    if (
        strpos(
            $text,
            'vegetarian'
        ) !== false
    ) {
        $diets[] = 'vegetarian';
    }

    if (
        strpos(
            $text,
            'gluten free'
        ) !== false
    ) {
        $diets[] = 'gluten_free';
    }

    if (
        strpos(
            $text,
            'keto'
        ) !== false
    ) {
        $diets[] = 'keto';
    }

    return $diets;
}
/*Occasion Detection*/
function recipe_ai_detect_occasion(
    $recipe
)
{
    $text =
        strtolower(
            json_encode($recipe)
        );

    $occasions = [];

    if (
        strpos(
            $text,
            'christmas'
        ) !== false
    ) {
        $occasions[] =
            'christmas';
    }

    if (
        strpos(
            $text,
            'easter'
        ) !== false
    ) {
        $occasions[] =
            'easter';
    }

    if (
        strpos(
            $text,
            'party'
        ) !== false
    ) {
        $occasions[] =
            'party';
    }

    return $occasions;
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