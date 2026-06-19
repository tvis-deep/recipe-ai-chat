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
    return recipe_ai_detect_ingredients($query);
}
function recipe_ai_detect_ingredients($query)
{
    if (function_exists('recipe_ai_extract_ingredients')) {
        return recipe_ai_extract_ingredients($query);
    }

    return [];
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
