<?php 
/*Main Extractor*/
function recipe_ai_extract_entities(
    $query
)
{
    return [

        'ingredients' =>
            recipe_ai_detect_ingredients(
                $query
            ),

        'diets' =>
            recipe_ai_detect_diets(
                $query
            ),

        'meal_types' =>
            recipe_ai_detect_meal_types(
                $query
            ),

        'methods' =>
            recipe_ai_detect_methods(
                $query
            ),

        'occasions' =>
            recipe_ai_detect_occasions(
                $query
            )

    ];
}
/*Ingredient Detection*/
function recipe_ai_detect_ingredients_old(
    $query
)
{
    global $wpdb;

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $ingredients =
        $wpdb->get_col(
            "SELECT ingredients_text
             FROM {$table}"
        );
    print_r($ingredients);

    $query =
        strtolower($query);

    $found = [];

    foreach (
        $ingredients as $ingredient
    ) {
        if (!empty($ingredient)) {
            if (
                strpos(
                    $query,
                    strtolower(
                        $ingredient
                    )
                ) !== false
            ) {

                $found[] =
                    $ingredient;
            }
        }
    }

    return $found;
}
function recipe_ai_detect_ingredients($query)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_recipes';

    $query = strtolower($query);

    $query_words = preg_split(
        '/\s+/',
        $query
    );

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

        $ingredient_words = preg_split(
            '/\s+/',
            $ingredient
        );

        $matches = 0;

        foreach ($query_words as $query_word) {

            if (
                strlen($query_word) < 3
            ) {
                continue;
            }

            foreach (
                $ingredient_words
                as $ingredient_word
            ) {

                if (
                    $query_word ===
                    $ingredient_word
                ) {

                    $matches++;

                }

            }

        }

        if ($matches > 0) {

            $found[] = [
                'ingredient' => $ingredient,
                'matches'    => $matches
            ];

        }
    }

    usort(
        $found,
        fn($a, $b)
            => $b['matches']
            <=> $a['matches']
    );

    return $found;
}
/*Occasion Detection*/
function recipe_ai_detect_occasions(
    $query
)
{
    return recipe_ai_detect_entities(
        $query,
        recipe_ai_load_json(
            'occasions.json'
        )
    );
}
/*Cooking Method Detection*/
function recipe_ai_detect_methods(
    $query
)
{
    return recipe_ai_detect_entities(
        $query,
        recipe_ai_load_json(
            'cooking_methods.json'
        )
    );
}
/*Meal Type Detection*/
function recipe_ai_detect_meal_types(
    $query
)
{
    return recipe_ai_detect_entities(
        $query,
        recipe_ai_load_json(
            'meal_types.json'
        )
    );
}
/*Diet Detection*/
function recipe_ai_detect_diets(
    $query
)
{
    return recipe_ai_detect_entities(
        $query,
        recipe_ai_load_json(
            'diets.json'
        )
    );
}
/*Create Generic Detector*/
function recipe_ai_detect_entities(
    $query,
    $dictionary
)
{
    $query = strtolower($query);

    $found = [];

    foreach (
        $dictionary as $entity => $aliases
    ) {

        foreach ($aliases as $alias) {

            if (
                strpos(
                    $query,
                    strtolower($alias)
                ) !== false
            ) {

                $found[] = $entity;

                break;
            }
        }
    }

    return array_unique(
        $found
    );
}