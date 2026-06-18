<?php 
/*Recipe Search Synonym Dictionary From synonyms JSON*/
function recipe_ai_get_synonyms()
{
    static $synonyms = null;

    if ( $synonyms !== null ) {
        return $synonyms;
    }

    $file = RECIPE_AI_PATH . 'data/synonyms.json';

    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents( $file );

    $synonyms = json_decode( $json, true );

    return is_array( $synonyms ) ? $synonyms : [];
}
/* Expand Search Synonyms */
function recipe_ai_expand_synonyms(
    array $terms
)
{
    $synonyms =
        recipe_ai_get_synonyms();

    $expanded =
        $terms;

    foreach (
        $terms as $term
    ) {

        $term =
            strtolower(
                trim($term)
            );

        if (
            !isset(
                $synonyms[$term]
            )
        ) {
            continue;
        }

        $expanded =
            array_merge(
                $expanded,
                $synonyms[$term]
            );
    }

    return array_values(
        array_unique(
            $expanded
        )
    );
}