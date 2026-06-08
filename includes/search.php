<?php

defined('ABSPATH') || exit;

function recipe_ai_search_by_ingredients(
    $query,
    $limit = 20
)
{
    global $wpdb;

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $user_ingredients =
        recipe_ai_extract_ingredients(
            $query
        );

    if (
        empty($user_ingredients)
    ) {
        return [];
    }

    $recipes =
        $wpdb->get_results(
            "
            SELECT *
            FROM {$table}
            ",
            ARRAY_A
        );

    $results = [];

    foreach ($recipes as $recipe) {

        $match =
            recipe_ai_calculate_ingredient_match(
                $user_ingredients,
                $recipe['ingredients_text']
            );

        if (
            $match['matched'] <= 0
        ) {
            continue;
        }

        $recipe['score'] =
            $match['matched'];

        $recipe['matched'] =
            $match['matched'];

        $results[] =
            $recipe;
    }

    usort(
        $results,
        function($a, $b){

            return
                $b['score']
                <=>
                $a['score'];

        }
    );

    return array_slice(
        $results,
        0,
        $limit
    );
}
add_action('init', function(){

    if (
        !isset($_GET['test-search'])
    ) {
        return;
    }

    echo '<pre>';
    print_r(
        recipe_ai_search_by_ingredients(
            'I have eggs flour butter'
        )
    );

    exit;
});