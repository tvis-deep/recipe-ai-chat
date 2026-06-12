<?php

defined('ABSPATH') || exit;

function recipe_ai_extract_ingredients($query)
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

   /* $found = [];

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
    );*/

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
function recipe_ai_extract_ingredients_new($query)
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
            foreach (explode(',', $ingredients_text) as $ingredient) {

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

        $matched_words = [];

        foreach ($ingredient_words as $word) {

            if (
                strlen($word) < 3
            ) {
                continue;
            }

            if (
                preg_match(
                    '/\b' . preg_quote($word, '/') . '\b/i',
                    $query
                )
            ) {
                $matched_words[] = $word;
            }
        }

        if (
            count($matched_words) >= 2
        ) {

            $found[] =
                implode(
                    ' ',
                    $matched_words
                );

        } elseif (
            count($matched_words) === 1
        ) {

            $found[] =
                $matched_words[0];

        }
    }

    return array_values(
        array_unique($found)
    );
}
function recipe_ai_extract_ingredients_from_words($words)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_recipes';

    $recipes = $wpdb->get_col(
        "SELECT ingredients_text FROM {$table}"
    );

    /*$query = strtolower($query);

    $words = preg_split(
        '/\s+/',
        $query
    );*/

    $found = [];

    foreach ($recipes as $ingredients_text) {
        if (!empty($ingredients_text)) {
            $ingredients = explode(
                ',',
                strtolower($ingredients_text)
            );

            foreach ($ingredients as $ingredient) {

                $ingredient = trim(
                    $ingredient
                );

                foreach ($words as $word) {

                    $word = trim($word);

                    if (
                        strlen($word) < 3
                    ) {
                        continue;
                    }

                    if (
                        stripos(
                            $ingredient,
                            $word
                        ) !== false
                    ) {
                        $found[] =
                            $ingredient;

                        break;
                    }
                }
            }
        }

    }

    return array_unique(
        $found
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

        /*if (
            in_array(
                $ingredient,
                $recipe_ingredients
            )
        ) {
            $matched++;
        }*/
        foreach ($recipe_ingredients as $recipe_ingredient) {
            if (
                stripos(
                    $recipe_ingredient,
                    $ingredient
                ) !== false
                ||
                stripos(
                    $ingredient,
                    $recipe_ingredient
                ) !== false
            ) {
                $matched++;
                break;
            }
        }

    }

    return [
        'matched' => $matched,
        'total' => count(
            $recipe_ingredients
        ),
        'coverage' =>
            round(
                (
                    $matched /
                    max(
                        count($user_ingredients),
                        1
                    )
                ) * 100
            )
    ];

    /*return [
        'matched' => $matched,
        'total'   => count(
            $recipe_ingredients
        )
    ];*/
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