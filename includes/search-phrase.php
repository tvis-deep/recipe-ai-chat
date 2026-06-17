<?php 
/*Phrase Extractor*/
function recipe_ai_extract_phrases(
    $query
)
{
    $query =
        strtolower(
            trim($query)
        );

    $words =
        preg_split(
            '/\s+/',
            $query
        );

    $phrases = [];

    $count =
        count($words);

    for (
        $length = 1;
        $length <= $count;
        $length++
    ) {

        for (
            $i = 0;
            $i <= ($count - $length);
            $i++
        ) {

            $phrase =
                implode(
                    ' ',
                    array_slice(
                        $words,
                        $i,
                        $length
                    )
                );

            $phrases[] =
                $phrase;
        }
    }

    /*
     * Longest phrases first
     */

    usort(
        $phrases,
        function(
            $a,
            $b
        ) {
            return
                strlen($b)
                <=>
                strlen($a);
        }
    );

    return
        array_unique(
            $phrases
        );
}
/*Phrase Score*/
function recipe_ai_score_phrase_match(
    $phrases,
    $recipe
)
{
    $weights =
        recipe_ai_get_weights();

    $score = 0;

    
    $search_text =
        strtolower(
            $recipe['search_text']
        );

    foreach (
        $phrases as $phrase
    ) {

        if (
            strpos(
                $search_text,
                $phrase
            ) !== false
        ) {

            $word_count =
                count(
                    explode(
                        ' ',
                        $phrase
                    )
                );

            /*
             * Longer phrase
             * = bigger score
             */

            $score +=
                (
                    $weights['phrase_bonus']
                    * $word_count
                );
        }
    }

    return $score;
}
