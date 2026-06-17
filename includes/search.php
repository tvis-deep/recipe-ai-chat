<?php
defined('ABSPATH') || exit;
/*Central Weight Configuration*/
function recipe_ai_get_weights(){
    return [
        'exact_title'       => 500,
        'partial_title'     => 100,

        'ingredient'        => 80,
        'keyword'           => 60,
        'cuisine'           => 40,
        'search_text'       => 10,
        
        'phrase_bonus'      => 200,

        'nutrition_bonus'   => 300,
        'intent_bonus'      => 300,

    ];
}
/*Title Scoring*/
function recipe_ai_score_title(
    $query,
    $terms,
    $recipe
)
{
    $weights =
        recipe_ai_get_weights();

    $score = 0;

    $title =
        strtolower(
            $recipe['title']
        );

    /*
     * Exact phrase match
     */
    if (
        strpos(
            $title,
            strtolower($query)
        ) !== false
    ) {

        $score +=
            $weights['exact_title'];

    }

    /*
     * Individual terms
     */
    foreach ($terms as $term) {

        if (
            strpos(
                $title,
                $term
            ) !== false
        ) {
            $score +=
                $weights['partial_title'];
        }

    }

    return $score;
}
/*Generic Field Scorer*/
function recipe_ai_score_field(
    $terms,
    $field_value,
    $weight
)
{
   
    $score = 0;

    $field_value =
        strtolower(
            $field_value
        );

    foreach ($terms as $term) {

        if (
            strpos(
                $field_value,
                $term
            ) !== false
        ) {

            $score +=
                $weight;

        }

    }

    return $score;
}
/*Phrase Matching*/
function recipe_ai_score_phrase(
    $query,
    $recipe
)
{
    $weights =
        recipe_ai_get_weights();

    if (
        strpos(
            strtolower(
                $recipe['search_text']
            ),
            strtolower($query)
        ) !== false
    ) {
        return
            $weights['phrase_bonus'];

    }

    return 0;
}
/*Main Scoring Engine*/
function recipe_ai_calculate_score(
    $query,
    $terms,
    $recipe
)
{
    $weights =
        recipe_ai_get_weights();

    $score = 0;
    $debug = [];

    /*
     * Exact query match
     */
    $match = recipe_ai_score_phrase(
            $query,
            $recipe
        );
    $score += $match;
        
    $debug[] = "Exact query match: (+{$match})";

    /*
     * Title
     */
    $match = recipe_ai_score_title(
            $query,
            $terms,
            $recipe
        );
    $score += $match;

    $debug[] = "Title match: (+{$match})";

    /*
     * Ingredients
     */
    $match = recipe_ai_score_field(
            $terms,
            $recipe['ingredients_text'],
            $weights['ingredient']
        );
    $score += $match;
    $debug[] = "Ingredients match: (+{$match})";

    /*
     * Keywords
     */
    $match = recipe_ai_score_field(
            $terms,
            $recipe['keywords_text'],
            $weights['keyword']
        );
    $score += $match;
    $debug[] = "Keywords match: (+{$match})";


    /*
     * Cuisine
     */
    $match = recipe_ai_score_field(
            $terms,
            $recipe['cuisine_text'],
            $weights['cuisine']
        );
    $score += $match;
    $debug[] = "Cuisine match: (+{$match})";
    /*
     * Search Text
     */
    $match = recipe_ai_score_field(
            $terms,
            $recipe['search_text'],
            $weights['search_text']
        );
    $score += $match;
    $debug[] = "Search match: (+{$match})";

    return [
        'score' => $score,
        'debug' => $debug
    ];
}
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

/*Search Query Normalizer*/
function recipe_ai_extract_search_terms($query)
{
    $query = strtolower($query);

    $query = preg_replace(
        '/[^a-z0-9\s]/',
        ' ',
        $query
    );

    $words = preg_split(
        '/\s+/',
        $query
    );

    $words = array_filter(
        $words,
        function($word){

            return strlen($word) >= 3;

        }
    );

    $terms = [];

    foreach ($words as $word) {

        $word = trim($word);

        if (strlen($word) < 3) {
            continue;
        }

        if (
            recipe_ai_is_noise_word(
                $word
            )
        ) {
            continue;
        }

        $terms[] = $word;
    }

    return array_values(
        array_unique($terms)
    );

   
}

/*Create Scoring Function*/
function recipe_ai_calculate_score_old(
    array $terms,
    array $recipe
)
{
    $debug = [];

    $score = 0;

    foreach ($terms as $term) {

        /*
         * Title
         */
        if (
            stripos(
                $recipe['title'],
                $term
            ) !== false
        ) {
            $score += 100;
            $debug[] = "TITLE: {$term} (+100)";
        }

        /*
         * Ingredients
         */
        if (
            stripos(
                $recipe['ingredients_text'],
                $term
            ) !== false
        ) {
            $score += 70;
            $debug[] = "INGREDIENT: {$term} (+70)";
        }

        /*
         * Keywords
         */
        if (
            stripos(
                $recipe['keywords_text'],
                $term
            ) !== false
        ) {
            $score += 50;
            $debug[] = "keywords_text: {$term} (+10)";
        }

        /*
         * Cuisine
         */
        if (
            stripos(
                $recipe['cuisine_text'],
                $term
            ) !== false
        ) {
            $score += 30;
            $debug[] = "cuisine_text: {$term} (+30)";

        }

        /*
         * Search Text
         */
        if (
            stripos(
                $recipe['search_text'],
                $term
            ) !== false
        ) {
            $score += 10;
            $debug[] = "search_text: {$term} (+10)";

        }
    }

    return [
        'score' => $score,
        'debug' => $debug
    ];
}
/*Rewrite Search Function*/
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

    $terms =
        recipe_ai_extract_search_terms(
            $query
        );


    recipe_ai_log([
        'query' => $query,
        'terms' => $terms
    ]);

    $results = [];

    foreach ($recipes as $recipe) {

        $scoring  =  recipe_ai_calculate_score( $query, $terms, $recipe );
        $recipe['score'] = $scoring['score'];

        $recipe['debug'] =$scoring['debug'];

        if ($recipe['score'] <= 0) {
            continue;
        }


        $recipe['score'] =$recipe['score'];

        $results[] =$recipe;
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

    $top_results =
    array_slice(
        $results,
        0,
        10
    );

    recipe_ai_log([
        'query' => $query,
        'results' => array_map(
            function($recipe){

                return [

                    'recipe_id' =>
                        $recipe['recipe_id'],

                    'title' =>
                        $recipe['title'],

                    'score' =>
                        $recipe['score'],

                    'debug' =>
                        $recipe['debug']

                ];

            },
            $top_results
        )
    ]);

    return array_slice(
        $results,
        0,
        $limit
    );
}
/*Remove Common Words*/
function recipe_ai_is_noise_word($word)
{
    $noise_words = [

        'what',
        'can',
        'cook',
        'with',
        'make',
        'recipe',
        'recipes',
        'show',
        'give',
        'find',
        'need',
        'want',
        'best',
        'easy',
        'simple',
        'using',
        'have',
        'got',
        'some',
        'something',
        'any',
        'for',
        'and',
        'the',
        'from'

    ];

    return in_array(
        $word,
        $noise_words,
        true
    );
}
/*recipe search*/
function recipe_ai_search_old(
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