<?php 
/*Create Ingredient Dictionary*/
function recipe_ai_get_all_ingredients()
{
    global $wpdb;

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $rows =
        $wpdb->get_col(
            "SELECT ingredients_text FROM {$table}"
        );

    $ingredients = [];

    foreach ($rows as $row) {
    	if ($row) {
	        $parts =
	            explode(',', $row);

	        foreach ($parts as $ingredient) {

	            $ingredient =
	                trim(
	                    strtolower(
	                        $ingredient
	                    )
	                );

	            if (
	                strlen(
	                    $ingredient
	                ) < 3
	            ) {
	                continue;
	            }

	            $ingredients[$ingredient] =
	                true;
	        }
   		}
    }

    return array_keys(
        $ingredients
    );
}
/*Fuzzy Correction*/
function recipe_ai_fuzzy_match_term(
    $term
)
{
    static $dictionary = null;

    if (
        $dictionary === null
    ) {

        $dictionary =
            recipe_ai_get_all_ingredients();
    }

    $best_match = $term;

    $best_score = 999;

    foreach (
        $dictionary as $ingredient
    ) {

        $distance =
            levenshtein(
                $term,
                $ingredient
            );

        if (
            $distance
            < $best_score
        ) {

            $best_score =
                $distance;

            $best_match =
                $ingredient;
        }
    }

    if (
        $best_score <= 2
    ) {

        return $best_match;
    }

    return $term;
}
/*Apply To Search Terms*/
function recipe_ai_apply_fuzzy_matching(
    $terms
)
{
    $corrected = [];

    foreach (
        $terms as $term
    ) {

        $corrected[] =
            recipe_ai_fuzzy_match_term(
                $term
            );
    }

    return array_unique(
        $corrected
    );
}
/*Load ingredient Dictionary*/
function recipe_ai_get_ingredient_dictionary()
{
    static $dictionary = null;

    if ($dictionary !== null) {
        return $dictionary;
    }

    $file =
        RECIPE_AI_PATH .
        'data/ingredients.json';

    if (!file_exists($file)) {
        return [];
    }

    $dictionary =
        json_decode(
            file_get_contents($file),
            true
        );

    return is_array($dictionary)
        ? $dictionary
        : [];
}
/*ai fuzzy Correction for query*/
function recipe_ai_fuzzy_fix_query(
    $query
)
{
    $dictionary = recipe_ai_get_all_ingredients();
    // $dictionary = recipe_ai_get_ingredient_dictionary();

    $words =
        preg_split(
            '/\s+/',
            strtolower($query)
        );

    foreach (
        $words as &$word
    ) {

        $best_match = null;
        $best_score = 999;

        foreach (
            $dictionary as $ingredient
        ) {

            $ingredient_words =
                explode(
                    ' ',
                    strtolower($ingredient)
                );

            foreach (
                $ingredient_words as $candidate
            ) {

                $distance =
                    levenshtein(
                        $word,
                        $candidate
                    );

                if (
                    $distance < $best_score
                ) {

                    $best_score =
                        $distance;

                    $best_match =
                        $candidate;
                }
            }
        }

        if (
            $best_score <= 2
        ) {

            $word =
                $best_match;
        }
    }

    return implode(
        ' ',
        $words
    );
}
/*ai fuzzy Correction for terms*/
function recipe_ai_fuzzy_fix_terms(
    $words
)
{
    $dictionary = recipe_ai_get_all_ingredients();
    // $dictionary = recipe_ai_get_ingredient_dictionary();

    foreach (
        $words as &$word
    ) {

        $best_match = null;
        $best_score = 999;

        foreach (
            $dictionary as $ingredient
        ) {

            $ingredient_words =
                explode(
                    ' ',
                    strtolower($ingredient)
                );

            foreach (
                $ingredient_words as $candidate
            ) {

                $distance =
                    levenshtein(
                        $word,
                        $candidate
                    );

                if (
                    $distance < $best_score
                ) {

                    $best_score =
                        $distance;

                    $best_match =
                        $candidate;
                }
            }
        }

        if (
            $best_score <= 2
        ) {

            $word =
                $best_match;
        }
    }

    return $words;

}