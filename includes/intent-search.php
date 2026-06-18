<?php   
/*Intent Extraction*/
function recipe_ai_detect_intent(
    $query
)
{
    $query = strtolower($query);

    $intent = [
        'meal_type' => [],
        'diet'      => [],
        'cook_type' => [],
        'nutrition' => [],
        'time'      => [],
    ];

    /*
     * Meal Type
     */
    if (strpos($query, 'breakfast') !== false) {
        $intent['meal_type'][] = 'breakfast';
    }

    if (strpos($query, 'lunch') !== false) {
        $intent['meal_type'][] = 'lunch';
    }

    if (strpos($query, 'dinner') !== false) {
        $intent['meal_type'][] = 'dinner';
    }

    if (strpos($query, 'dessert') !== false) {
        $intent['meal_type'][] = 'dessert';
    }

    /*
     * Diet
     */
    if (strpos($query, 'vegetarian') !== false) {
        $intent['diet'][] = 'vegetarian';
    }

    if (strpos($query, 'vegan') !== false) {
        $intent['diet'][] = 'vegan';
    }

    if (strpos($query, 'keto') !== false) {
        $intent['diet'][] = 'keto';
    }

    /*
     * Cooking Method
     */
    if (strpos($query, 'air fryer') !== false) {
        $intent['cook_type'][] = 'air_fryer';
    }

    if (strpos($query, 'slow cooker') !== false) {
        $intent['cook_type'][] = 'slow_cooker';
    }

    /*
     * Nutrition
     */
    if (
        strpos($query, 'high protein')
        !== false
    ) {
        $intent['nutrition'][] =
            'high_protein';
    }

    if (
        strpos($query, 'low calorie')
        !== false
    ) {
        $intent['nutrition'][] =
            'low_calorie';
    }

    /*
     * Time
     */
    if (
        strpos($query, 'quick')
        !== false
    ) {
        $intent['time'][] =
            'quick';
    }

    return $intent;
}
/*Intent Scoring*/
function recipe_ai_score_intent(
    $intent,
    $recipe
)
{
    $weights =
        recipe_ai_get_weights();

    $score = 0;

    /*
     * Meal Type
     */
    foreach (
        $intent['meal_type']
        as $meal
    ) {

        if (
            strpos(
                strtolower(
                    $recipe['meal_type_text']
                ),
                $meal
            ) !== false
        ) {

            $score +=
                $weights['intent_bonus'];
        }
    }

    /*
     * Cooking Method
     */
    foreach (
        $intent['cook_type']
        as $method
    ) {

        if (
            strpos(
                strtolower(
                    $recipe['cook_method_text']
                ),
                str_replace(
                    '_',
                    ' ',
                    $method
                )
            ) !== false
        ) {

            $score +=
                $weights['intent_bonus'];
        }
    }

    return $score;
}
/*Nutrition Intent*/
function recipe_ai_score_nutrition(
    $intent,
    $recipe
)
{
    $weights =
        recipe_ai_get_weights();

    $score = 0;

    if (
        in_array(
            'high_protein',
            $intent['nutrition']
        )
    ) {

        if (
            $recipe['protein']
            >= 20
        ) {

            $score +=
                $weights['nutrition_bonus'];
        }

    }

    if (
        in_array(
            'low_calorie',
            $intent['nutrition']
        )
    ) {

        if (
            $recipe['calories']
            <= 300
        ) {

            $score +=
                $weights['nutrition_bonus'];
        }

    }

    return $score;
}
/*Diet Detection helper for import*/
function recipe_ai_detect_diet(
    $recipe
)
{
    $text =
        strtolower(
            json_encode($recipe)
        );

    $diets = [];

    if (
        strpos(
            $text,
            'vegan'
        ) !== false
    ) {
        $diets[] = 'vegan';
    }

    if (
        strpos(
            $text,
            'vegetarian'
        ) !== false
    ) {
        $diets[] = 'vegetarian';
    }

    if (
        strpos(
            $text,
            'gluten free'
        ) !== false
    ) {
        $diets[] = 'gluten_free';
    }

    if (
        strpos(
            $text,
            'keto'
        ) !== false
    ) {
        $diets[] = 'keto';
    }

    return $diets;
}
/*Occasion Detection helper for import*/
function recipe_ai_detect_occasion(
    $recipe
)
{
    $text =
        strtolower(
            json_encode($recipe)
        );

    $occasions = [];

    if (
        strpos(
            $text,
            'christmas'
        ) !== false
    ) {
        $occasions[] =
            'christmas';
    }

    if (
        strpos(
            $text,
            'easter'
        ) !== false
    ) {
        $occasions[] =
            'easter';
    }

    if (
        strpos(
            $text,
            'party'
        ) !== false
    ) {
        $occasions[] =
            'party';
    }

    return $occasions;
}
/*
$intent =
    recipe_ai_detect_intent(
        $query
    );

    $score +=
    recipe_ai_score_intent(
        $intent,
        $recipe
    );

$score +=
    recipe_ai_score_nutrition(
        $intent,
        $recipe
    );
*/