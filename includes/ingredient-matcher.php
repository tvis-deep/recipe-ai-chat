<?php

defined('ABSPATH') || exit;

function recipe_ai_extract_ingredients($query)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_recipes';

    $query = strtolower($query);

    $recipes = $wpdb->get_col(
        "SELECT ingredients_text FROM {$table}"
    );

    $all_ingredients = [];

    foreach ($recipes as $ingredients_text) {
        if (!empty($ingredients_text)) {
            $ingredients = explode(',', $ingredients_text);

            foreach ($ingredients as $ingredient) {

                $ingredient = trim(
                    strtolower($ingredient)
                );

                if (strlen($ingredient) < 3) {
                    continue;
                }

                $all_ingredients[$ingredient] = true;
            }
        }
    }

    $found = [];

    foreach (array_keys($all_ingredients) as $ingredient) {

        if (
            strpos($query, $ingredient)
            !== false
        ) {
            $found[] = $ingredient;
        }
    }

    return array_values(
        array_unique($found)
    );
}
function recipe_ai_calculate_ingredient_match(
    array $user_ingredients,
    string $recipe_ingredients
)
{
    $recipe_ingredients =
        strtolower($recipe_ingredients);

            $recipe_ingredients =
                explode(
                    ',',
                    $recipe_ingredients
                );

            $recipe_ingredients =
                array_map(
                    'trim',
                    $recipe_ingredients
                );

    $matched = 0;

    foreach (
        $user_ingredients
        as $ingredient
    ) {

        if (
            in_array(
                $ingredient,
                $recipe_ingredients
            )
        ) {
            $matched++;
        }
    }

    return [
        'matched' => $matched,
        'total'   => count(
            $recipe_ingredients
        )
    ];
}
add_action('init', function(){

    if (
        !isset($_GET['test-ingredients'])
    ) {
        return;
    }

    echo '<pre>';

    print_r(
        recipe_ai_extract_ingredients(
            'I have eggs flour butter bread'
        )
    );

    exit;
});