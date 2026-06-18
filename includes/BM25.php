<?php 
/*Create tokenizer*/
function recipe_ai_tokenize($text)
{
    $text = strtolower($text);

    $text = preg_replace(
        '/[^a-z0-9\s]/',
        ' ',
        $text
    );

    return array_filter(
        explode(' ', $text)
    );
}
/*Document Frequency*/
function recipe_ai_document_frequency(
    $term,
    $recipes
)
{
    $count = 0;

    foreach ($recipes as $recipe) {

        if (
            strpos(
                strtolower(
                    $recipe['search_text']
                ),
                $term
            ) !== false
        ) {

            $count++;
        }
    }

    return max(
        1,
        $count
    );
}
/*BM25 Score*/
function recipe_ai_bm25_score(
    $terms,
    $recipe,
    $recipes
)
{
    $k1 = 1.5;
    $b  = 0.75;

    $doc =
        recipe_ai_tokenize(
            $recipe['search_text']
        );

    $doc_length =
        count($doc);

    $avg_length = 200;

    $N =
        count($recipes);

    $score = 0;

    foreach ($terms as $term) {

        $tf = 0;

        foreach ($doc as $word) {

            if ($word === $term) {
                $tf++;
            }
        }

        if ($tf === 0) {
            continue;
        }

        $df =
            recipe_ai_document_frequency(
                $term,
                $recipes
            );

        $idf =
            log(
                (
                    ($N - $df + 0.5)
                    /
                    ($df + 0.5)
                ) + 1
            );

        $score +=
            $idf *
            (
                (
                    $tf *
                    ($k1 + 1)
                )
                /
                (
                    $tf +
                    $k1 *
                    (
                        1 -
                        $b +
                        $b *
                        (
                            $doc_length /
                            $avg_length
                        )
                    )
                )
            );
    }

    return $score;
}
/*How To Use
$bm25 =
    recipe_ai_bm25_score(
        $terms,
        $recipe,
        $recipes
    );

$score +=
    ($bm25 * 50);
    */