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

        $recipe['score'] = $match['matched'];
        // $coverage =( $matched / count($user_ingredients)) * 100;

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
/*Add Keyword Search*/
function recipe_ai_calculate_keyword_score(
    $words,
    $recipe
)
{
    $score = 0;

    // $query = strtolower($query);

    $title =
        strtolower(
            $recipe['title']
        );

    $keywords =
        strtolower(
            $recipe['keywords_text']
        );

    $cuisine =
        strtolower(
            $recipe['cuisine_text']
        );

    $document =
        strtolower(
            $recipe['document']
        );

   /* $words =
        preg_split(
            '/\s+/',
            $query
        );*/

    foreach ($words as $word) {

        if (strlen($word) < 3) {
            continue;
        }

        if (
            strpos(
                $title,
                $word
            ) !== false
        ) {
            $score += 50;
        }

        if (
            strpos(
                $keywords,
                $word
            ) !== false
        ) {
            $score += 20;
        }

        if (
            strpos(
                $cuisine,
                $word
            ) !== false
        ) {
            $score += 15;
        }

        if (
            strpos(
                $document,
                $word
            ) !== false
        ) {
            $score += 5;
        }
    }

    return $score;
}
/*Exact Phrase Boost*/
function recipe_ai_exact_match_score(
    $query,
    $recipe
)
{
    $query =
        strtolower(
            trim($query)
        );

    $title =
        strtolower(
            $recipe['title']
        );

    if (
        strpos(
            $title,
            $query
        ) !== false
    ) {
        return 100;
    }

    return 0;
}
/*recipe search*/
function recipe_ai_search(
    $query,
    $limit = 20
)
{
    global $wpdb;

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $recipes =
        $wpdb->get_results(
            "SELECT * FROM {$table}",
            ARRAY_A
        );

    $user_ingredients =
        recipe_ai_extract_ingredients(
            $query
        );
    print_r($user_ingredients);

    $user_ingredients =
        recipe_ai_extract_ingredients_from_words(
            $user_ingredients
        );

    $results = [];

    foreach ($recipes as $recipe) {
        if (empty($recipe['ingredients_text'])) {
            continue;
        }
        $score = 0;

        /*
         * Ingredient Score
         */
        if (
            !empty(
                $user_ingredients
            )
        ) {

            $match =
                recipe_ai_calculate_ingredient_match(
                    $user_ingredients,
                    $recipe['ingredients_text']
                );

  
            

            // $score +=($match['matched']* 30);
            $score += ($match['coverage']* 2);

            $recipe['ingredient_matches'] =$match['matched'];
            $recipe['match'] = $match;
        }

        /*
         * Keyword Score
         */
        $score +=
            recipe_ai_calculate_keyword_score(
                $user_ingredients,
                $recipe
            );

        /*
         * Exact Match
         */
        /*$score +=
            recipe_ai_exact_match_score(
                $user_ingredients,
                $recipe
            );*/

        if ($score <= 0) {
            continue;
        }

        $recipe['score'] =
            $score;

        $results[] =
            $recipe;
    }
    usort(
        $results,
        fn($a, $b)
            => $b['score']
            <=> $a['score']
    );

    print_r($user_ingredients);
    print_r($results);

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
        recipe_ai_search(
            'I have eggs flour butter'
        )
    );
    // print_r(
    //     recipe_ai_search_by_ingredients(
    //         'I have eggs flour butter'
    //     )
    // );

    exit;
});