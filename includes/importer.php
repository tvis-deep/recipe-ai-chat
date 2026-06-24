<?php

defined('ABSPATH') || exit;
function recipe_ai_normalize_ingredient($ingredient)
{
    static $canonical_map = null;
    static $alias_map = null;

    if ($canonical_map === null) {
        $canonical_map = [
            'capsicum' => 'capsicum',
            'bell pepper' => 'capsicum',
            'courgette' => 'zucchini',
            'zucchini' => 'zucchini',
            'aubergine' => 'eggplant',
            'eggplant' => 'eggplant',
            'coriander' => 'coriander',
            'cilantro' => 'coriander',
            'rocket' => 'arugula',
            'arugula' => 'arugula',
            'prawns' => 'prawn',
            'shrimp' => 'prawn',
            'garbanzo beans' => 'chickpeas',
            'chickpea' => 'chickpeas',
            'chickpeas' => 'chickpeas',
            'mince' => 'ground beef',
            'beef mince' => 'ground beef',
            'ground beef' => 'ground beef',
            'tomato passata' => 'passata',
            'tomato sauce' => 'tomato sauce',
            'plain yoghurt' => 'yogurt',
            'greek yoghurt' => 'yogurt',
            'natural yoghurt' => 'yogurt',
            'self raising flour' => 'self-raising flour',
            'self rising flour' => 'self-raising flour',
            'salt and pepper' => 'salt and pepper',
            'white wine or stock' => 'white wine stock',
            'white wine stock' => 'white wine stock',
            'greek yoghurt or milk' => 'yogurt milk',
            'greek yoghurt milk' => 'yogurt milk',
            'greek yogurt or milk' => 'yogurt milk',
            'greek yogurt milk' => 'yogurt milk',
            'chicken thighs or breast' => 'chicken thigh chicken breast',
            'chicken thigh/breast' => 'chicken thigh chicken breast',
            'chicken thigh breast' => 'chicken thigh chicken breast',
            'chicken thigh chicken breast' => 'chicken thigh chicken breast',
            'chicken pieces' => 'chicken',
            'beef mince' => 'ground beef',
            'beef stock powder' => 'beef stock powder',
            'chicken stock powder' => 'chicken stock powder',
            'stock powder' => 'stock powder',
            'a splash vanilla' => 'vanilla',
            'splash vanilla' => 'vanilla',
            'drizzle olive oil' => 'olive oil',
            'water tap' => 'water',
            'oil bench' => 'oil',
            'oil bench your hands' => 'oil',
            'lovely drizzle chilli oil olive oil that kick' => 'chilli oil olive oil',
            'garlic dollop' => 'garlic',
            'juice lemon' => 'lemon',
            'zest lemon' => 'lemon zest',
        ];

        $alias_map = [
            'tbsp' => 'tablespoon',
            'tsp' => 'teaspoon',
            'litre' => 'liter',
            'litres' => 'liter',
            'kgs' => 'kg',
            'grams' => 'gram',
            'millilitres' => 'ml',
            'milliliters' => 'ml',
            'eggplant' => 'eggplant',
            'aubergines' => 'eggplant',
            'zucchinis' => 'zucchini',
            'courgettes' => 'zucchini',
            'tomatoes' => 'tomato',
            'potatoes' => 'potato',
            'carrots' => 'carrot',
            'onions' => 'onion',
            'mushrooms' => 'mushroom',
            'capsicums' => 'capsicum',
            'beans' => 'bean',
            'peas' => 'pea',
            'cherries' => 'cherry',
            'blueberries' => 'blueberry',
            'strawberries' => 'strawberry',
            'raspberries' => 'raspberry',
            'blackberries' => 'blackberry',
            'leaves' => 'leaf',
            'cloves' => 'clove',
            'slices' => 'slice',
            'pieces' => 'piece',
            'fillets' => 'fillet',
            'breasts' => 'breast',
            'thighs' => 'thigh',
            'drumsticks' => 'drumstick',
            'wings' => 'wing',
            'necks' => 'neck',
            'stocks' => 'stock',
            'stocks powder' => 'stock powder',
            'crumbs' => 'crumb',
            'breadcrumbs' => 'breadcrumb',
        ];
    }

    $ingredient = strtolower(trim((string) $ingredient));
    $ingredient = wp_strip_all_tags($ingredient);
    $ingredient = html_entity_decode($ingredient, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ingredient = preg_replace('/[\x{2013}\x{2014}]/u', '-', $ingredient);
    $ingredient = preg_replace('/\b(?:for the|optional|optional:|to taste|to serve|for serving|sauce:|dressing:|icing:|filling:|topping:|base:|mix:|ingredients?:)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:cup|cups|tbsp|tsp|teaspoon|teaspoons|tablespoon|tablespoons|kg|g|gram|grams|ml|l|liter|litre|oz|ounce|ounces|lb|pound|pounds|pinch|dash|clove|cloves|slice|slices|can|cans|packet|packets|piece|pieces|stick|sticks|stalk|stalks|bulb|bulbs|head|heads|bunch|bunches|jar|jars|tin|tins|sheet|sheets|block|blocks|punnet|punnets)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\b\d+(?:[\/\.\-]\d+)?\b/', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:fresh|large|small|medium|extra|lean|skinless|boneless|diced|chopped|sliced|minced|ground|grated|crushed|finely|roughly|optional|room temperature|warm|cold|hot|melted|soft|big|little|light|dark|sweetened|unsweetened|unsalted|salted)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:a|an|the|of|and|or|with|from|on|in|to|for)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\s+/', ' ', $ingredient);
    $ingredient = trim($ingredient, " \t\n\r\0\x0B-");

    if ($ingredient === '') {
        return '';
    }

    $parts = preg_split('/\s+/', $ingredient) ?: [];
    $clean_parts = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        if (isset($alias_map[$part])) {
            $part = $alias_map[$part];
        } elseif (preg_match('/^[a-z]{4,}s$/', $part) && !preg_match('/(?:ss|us|is|os)$/', $part)) {
            $part = substr($part, 0, -1);
        }

        $clean_parts[] = $part;
    }

    $ingredient = implode(' ', $clean_parts);
    $ingredient = preg_replace('/\s+/', ' ', $ingredient);
    $ingredient = trim($ingredient);

    if (isset($canonical_map[$ingredient])) {
        return $canonical_map[$ingredient];
    }

    $phrases = [
        'salt and pepper' => 'salt and pepper',
        'olive oil' => 'olive oil',
        'chicken stock powder' => 'chicken stock powder',
        'beef stock powder' => 'beef stock powder',
        'self-raising flour' => 'self-raising flour',
        'brown sugar' => 'brown sugar',
        'white sugar' => 'white sugar',
        'plain flour' => 'plain flour',
        'baking powder' => 'baking powder',
        'baking soda' => 'baking soda',
        'coconut milk' => 'coconut milk',
        'tomato paste' => 'tomato paste',
        'tomato passata' => 'passata',
        'cream cheese' => 'cream cheese',
        'soy sauce' => 'soy sauce',
        'oyster sauce' => 'oyster sauce',
        'fish sauce' => 'fish sauce',
        'red onion' => 'red onion',
        'green onion' => 'green onion',
        'spring onion' => 'spring onion',
        'cherry tomato' => 'cherry tomato',
        'chicken breast' => 'chicken breast',
        'chicken thigh' => 'chicken thigh',
        'ground beef' => 'ground beef',
        'prawn' => 'prawn',
        'zucchini' => 'zucchini',
        'eggplant' => 'eggplant',
        'capsicum' => 'capsicum',
        'coriander' => 'coriander',
        'chickpeas' => 'chickpeas',
        'salt pepper' => 'salt and pepper',
            'white wine stock' => 'white wine stock',
            'yogurt milk' => 'yogurt milk',
            'chicken thigh chicken breast' => 'chicken thigh chicken breast',
            'chicken thigh breast' => 'chicken thigh chicken breast',
            'chicken' => 'chicken',
        'vanilla' => 'vanilla',
        'olive oil' => 'olive oil',
        'water' => 'water',
        'oil' => 'oil',
        'chilli oil olive oil' => 'chilli oil olive oil',
        'garlic' => 'garlic',
        'lemon' => 'lemon',
        'lemon zest' => 'lemon zest',
    ];

    if (isset($phrases[$ingredient])) {
        return $phrases[$ingredient];
    }

    $tokens = preg_split('/\s+/', $ingredient);
    $tokens = array_filter($tokens, static function ($token) {
        return strlen($token) >= 2;
    });

    $normalized = [];
    foreach ($tokens as $token) {
        $token = trim($token);
        if ($token === '') {
            continue;
        }

        if (preg_match('/^[a-z]{4,}s$/', $token) && !preg_match('/(?:ss|us|is|os)$/', $token)) {
            $token = substr($token, 0, -1);
        }

        $normalized[] = $token;
    }

    $ingredient = implode(' ', $normalized);

    return $ingredient;
}

function recipe_ai_sanitize_ingredient_text($ingredient)
{
    $ingredient = html_entity_decode((string) $ingredient, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ingredient = preg_replace('/[\x{2013}\x{2014}]/u', '-', $ingredient);
    $ingredient = preg_replace('/\x{00c2}/u', '', $ingredient);
    $ingredient = preg_replace('/^\s*(?:for the|for a|for an|optional|optional:|to taste|to serve|for serving|sauce|dressing|icing|filling|topping|base|mix|ingredients?)\s*:?\s*/i', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:for the|for a|for an|optional|optional:|to taste|to serve|for serving|sauce:|dressing:|icing:|filling:|topping:|base:|mix:|ingredients?:)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/:\s*/', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:a|an|the|of|and|or|with|from|on|in|to|for)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:that kick|classic touch|your hands|for the bench)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:a\s+big\s+dollop|a\s+splash\s+of|drizzle\s+of|little\s+bit\s+of|big\s+spoonfuls?|shake\s+of|pinch\s+of|dash\s+of|juice\s+of|zest\s+of|for that kick|classic touch|for the bench|your hands)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\b(?:optional|to taste|to serve|for serving)\b/i', ' ', $ingredient);
    $ingredient = preg_replace('/\s+/', ' ', $ingredient);
    return trim($ingredient);
}

function recipe_ai_normalize_ingredient_options($ingredient)
{
    $ingredient = recipe_ai_sanitize_ingredient_text($ingredient);

    if ($ingredient === '') {
        return [];
    }

    if (preg_match('/\bsalt\s+and\s+pepper\b/i', $ingredient)) {
        return ['salt and pepper'];
    }

    $raw_parts = preg_split('/\s+(?:or|\/)\s+/i', $ingredient) ?: [];

    $parts = [];
    foreach ($raw_parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        $normalized = recipe_ai_normalize_ingredient($part);
        if ($normalized === '') {
            continue;
        }

        $parts[$normalized] = true;
    }

    return array_keys($parts);
}

function recipe_ai_extract_normalized_ingredients($ingredients_flat)
{
    $normalized = [];

    if (empty($ingredients_flat) || !is_array($ingredients_flat)) {
        return [];
    }

    foreach ($ingredients_flat as $ingredient) {
        $name = is_array($ingredient) ? ($ingredient['name'] ?? '') : $ingredient;
        $name = recipe_ai_sanitize_ingredient_text($name);
        $normalized_items = recipe_ai_normalize_ingredient_options($name);
        foreach ($normalized_items as $normalized_item) {
            $normalized[$normalized_item] = true;
        }
    }

    return array_keys($normalized);
}

function recipe_ai_flatten_string_list($value)
{
    if (empty($value)) {
        return [];
    }

    if (is_string($value)) {
        $value = [$value];
    }

    $out = [];

    foreach ((array) $value as $item) {
        if (is_array($item)) {
            foreach ($item as $nested) {
                if (is_string($nested) || is_numeric($nested)) {
                    $nested = trim((string) $nested);
                    if ($nested !== '') {
                        $out[] = $nested;
                    }
                }
            }
            continue;
        }

        if (is_string($item) || is_numeric($item)) {
            $item = trim((string) $item);
            if ($item !== '') {
                $out[] = $item;
            }
        }
    }

    return array_values(array_unique($out));
}

function recipe_ai_get_recipe_tag_values($recipe, array $paths)
{
    foreach ($paths as $path) {
        $cursor = $recipe;
        $found = true;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                $found = false;
                break;
            }
            $cursor = $cursor[$segment];
        }

        if ($found && !empty($cursor)) {
            return recipe_ai_flatten_string_list($cursor);
        }
    }

    return [];
}

function recipe_ai_extract_meal_types_from_recipe($recipe)
{
    $candidates = [];

    $course = recipe_ai_get_recipe_tag_values($recipe, ['tags.course', 'parent.tags.by_meal']);
    $keywords = recipe_ai_get_recipe_tag_values($recipe, ['tags.keyword']);
    $text = strtolower(
        trim(
            implode(
                ' ',
                [
                    $recipe['name'] ?? '',
                    wp_strip_all_tags($recipe['summary'] ?? ''),
                    implode(' ', recipe_ai_flatten_string_list($keywords)),
                ]
            )
        )
    );

    foreach (recipe_ai_flatten_string_list($course) as $item) {
        $item_l = strtolower($item);
        if (
            preg_match('/\b(breakfast|dessert|dinner|lunch|snack|appetizer|starter|side dish|sides|main course)\b/', $item_l) &&
            !preg_match('/\brecipes?\b/', $item_l)
        ) {
            $candidates[] = $item_l;
        }
    }

    $map = [
        'breakfast' => ['breakfast'],
        'brunch' => ['breakfast'],
        'lunch' => ['lunch'],
        'dinner' => ['dinner'],
        'dessert' => ['dessert'],
        'sweets & baking' => ['dessert'],
        'sweets and baking' => ['dessert'],
        'sweet' => ['dessert'],
        'snack' => ['snack'],
        'appetizer' => ['appetizer'],
        'starter' => ['appetizer'],
        'side' => ['side dish'],
        'side dish' => ['side dish'],
        'sides' => ['side dish'],
        'main course' => ['main course'],
        'main' => ['main course'],
    ];

    foreach ($map as $needle => $types) {
        if (strpos($text, $needle) !== false) {
            $candidates = array_merge($candidates, $types);
        }
    }

    return array_values(array_unique($candidates));
}

function recipe_ai_extract_cook_methods_from_recipe($recipe)
{
    $text = strtolower(
        trim(
            implode(
                ' ',
                [
                    $recipe['name'] ?? '',
                    wp_strip_all_tags($recipe['summary'] ?? ''),
                    wp_strip_all_tags(
                        implode(
                            ' ',
                            array_map(
                                static function ($instruction) {
                                    return $instruction['text'] ?? '';
                                },
                                $recipe['instructions_flat'] ?? []
                            )
                        )
                    ),
                    implode(' ', recipe_ai_get_recipe_tag_values($recipe, ['tags.by_method', 'parent.tags.by_method', 'tags.keyword'])),
                ]
            )
        )
    );

    $map = [
        'air fryer' => ['air fryer'],
        'slow cooker' => ['slow cooker'],
        'crock pot' => ['slow cooker'],
        'oven baked' => ['oven baked'],
        'oven roast' => ['oven baked'],
        'tray bake' => ['tray bake'],
        'sheet pan' => ['tray bake'],
        'no bake' => ['no bake'],
        'stir-fry' => ['stir fry'],
        'stir fry' => ['stir fry'],
        'pan fry' => ['fried'],
        'fried' => ['fried'],
        'grill' => ['grilled'],
        'barbecue' => ['barbecue'],
        'bbq' => ['barbecue'],
        'one pot' => ['one pot'],
        'one-pan' => ['one pan'],
        'one pan' => ['one pan'],
        'soup' => ['soup'],
        'salad' => ['salad'],
        'pasta' => ['pasta'],
        'casserole' => ['casserole'],
        'braise' => ['braised'],
        'braised' => ['braised'],
        'steam' => ['steamed'],
        'steamed' => ['steamed'],
    ];

    $found = [];
    foreach ($map as $needle => $methods) {
        if (strpos($text, $needle) !== false) {
            $found = array_merge($found, $methods);
        }
    }

    $found = array_values(array_unique($found));

    $specific = [
        'air fryer',
        'slow cooker',
        'oven baked',
        'tray bake',
        'no bake',
        'stir fry',
        'fried',
        'grilled',
        'barbecue',
        'one pot',
        'one pan',
        'soup',
        'salad',
        'pasta',
        'casserole',
        'braised',
        'steamed',
    ];

    $has_specific = (bool) array_intersect($found, $specific);

    if ($has_specific) {
        $found = array_values(array_filter($found, static function ($method) use ($specific) {
            return in_array($method, $specific, true);
        }));
    }

    return $found;
}

function recipe_ai_extract_diets_from_recipe($recipe)
{
    $text = strtolower(
        trim(
            implode(
                ' ',
                [
                    $recipe['name'] ?? '',
                    wp_strip_all_tags($recipe['summary'] ?? ''),
                    wp_strip_all_tags(json_encode($recipe['ingredients_flat'] ?? [])),
                    wp_strip_all_tags(json_encode($recipe['nutrition'] ?? [])),
                    implode(' ', recipe_ai_get_recipe_tag_values($recipe, ['tags.keyword', 'custom_fields.diet'])),
                ]
            )
        )
    );

    $found = [];

    $map = [
        'vegan' => 'vegan',
        'vegetarian' => 'vegetarian',
        'gluten free' => 'gluten_free',
        'gluten-free' => 'gluten_free',
        'dairy free' => 'dairy_free',
        'dairy-free' => 'dairy_free',
        'keto' => 'keto',
        'high protein' => 'high_protein',
        'low carb' => 'low_carb',
        'low calorie' => 'low_calorie',
    ];

    foreach ($map as $needle => $diet) {
        if (strpos($text, $needle) !== false) {
            $found[] = $diet;
        }
    }

    $found = array_values(array_unique($found));

    $explicit_sources = recipe_ai_get_recipe_tag_values($recipe, ['custom_fields.diet']);
    if (!empty($explicit_sources)) {
        $explicit = [];
        foreach ($explicit_sources as $source) {
            $source_l = strtolower($source);
            foreach ($map as $needle => $diet) {
                if (strpos($source_l, $needle) !== false) {
                    $explicit[] = $diet;
                }
            }
        }
        if (!empty($explicit)) {
            return array_values(array_unique($explicit));
        }
    }

    return $found;
}

function recipe_ai_extract_occasions_from_recipe($recipe)
{
    $text = strtolower(
        trim(
            implode(
                ' ',
                [
                    $recipe['name'] ?? '',
                    wp_strip_all_tags($recipe['summary'] ?? ''),
                    implode(' ', recipe_ai_get_recipe_tag_values($recipe, ['tags.keyword', 'custom_fields.occasion'])),
                ]
            )
        )
    );

    $map = [
        'christmas' => 'christmas',
        'easter' => 'easter',
        'thanksgiving' => 'thanksgiving',
        'birthday' => 'birthday',
        'party' => 'party',
        'bbq' => 'bbq',
        'barbecue' => 'bbq',
        'summer' => 'summer',
        'winter' => 'winter',
        'holiday' => 'holiday',
        'valentine' => 'valentine',
        'mother\'s day' => 'mothers_day',
        'mothers day' => 'mothers_day',
        'father\'s day' => 'fathers_day',
        'fathers day' => 'fathers_day',
    ];

    $found = [];
    foreach ($map as $needle => $occasion) {
        if (strpos($text, $needle) !== false) {
            $found[] = $occasion;
        }
    }

    $explicit_sources = recipe_ai_get_recipe_tag_values($recipe, ['custom_fields.occasion']);
    if (!empty($explicit_sources)) {
        $explicit = [];
        foreach ($explicit_sources as $source) {
            $source_l = strtolower($source);
            foreach ($map as $needle => $occasion) {
                if (strpos($source_l, $needle) !== false) {
                    $explicit[] = $occasion;
                }
            }
        }
        if (!empty($explicit)) {
            return array_values(array_unique($explicit));
        }
    }

    return array_values(array_unique($found));
}

/*Build Search Document*/
function recipe_ai_build_document($recipe)
{
    $parts = [];

    $parts[] = $recipe['name'] ?? '';

    $parts[] = wp_strip_all_tags(
        $recipe['summary'] ?? ''
    );

    if (!empty($recipe['ingredients_flat'])) {

        foreach (
            $recipe['ingredients_flat']
            as $ingredient
        ) {
            $parts[] = $ingredient['name'];
        }
    }

    if (
        !empty(
            $recipe['tags']['keyword']
        )
    ) {

        $parts = array_merge(
            $parts,
            $recipe['tags']['keyword']
        );
    }

    return implode(
        ' ',
        array_filter($parts)
    );
}
/*Import Single Recipe*/
function recipe_ai_import_recipe(
    array $recipe
)
{
    global $wpdb;

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $ingredients = [];

    if (
        !empty(
            $recipe['ingredients_flat']
        )
    ) {

        foreach (
            $recipe['ingredients_flat']
            as $ingredient
        ) {

            $ingredients[] =
                strtolower(
                    trim(
                        $ingredient['name']
                    )
                );
        }
    }

    $ingredients_normalized = recipe_ai_extract_normalized_ingredients(
        $recipe['ingredients_flat'] ?? []
    );

    $keywords = recipe_ai_get_recipe_tag_values($recipe, ['tags.keyword']);
    $cuisine = recipe_ai_get_recipe_tag_values($recipe, ['tags.cuisine']);

    $search_parts = [];

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */
    $search_parts[] = strtolower(
        $recipe['name'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Ingredients
    |--------------------------------------------------------------------------
    */
    $search_parts[] = implode(
        ' ',
        $ingredients
    );

    /*
    |--------------------------------------------------------------------------
    | Keywords
    |--------------------------------------------------------------------------
    */
    $search_parts[] = implode(
        ' ',
        $keywords
    );

    /*
    |--------------------------------------------------------------------------
    | Cuisine
    |--------------------------------------------------------------------------
    */
    $search_parts[] = implode(' ', $cuisine);
    /*Include Instructions*/
   
    if (!empty($recipe['instructions_flat'])) {
        foreach ($recipe['instructions_flat']as $instruction) {
            if (isset($instruction['text'])) {
                $search_parts[] = wp_strip_all_tags($instruction['text']);
            }
        }
    }
    /*Include Recipe Summary*/

    $search_parts[] =wp_strip_all_tags($recipe['summary'] ?? '');

    /*Include Parent Post Content*/
    if (!empty($recipe['parent']['post_content'])) {
        $search_parts[] =
            wp_strip_all_tags(
                $recipe['parent']['post_content']
            );
    }
    /*Include nutrition*/
    $nutrition =
        $recipe['nutrition']
        ?? [];

    foreach (
        $nutrition as $key => $value
    ) {

        if (
            !empty($value)
        ) {

            $search_parts[] =
                strtolower($key);

        }
    }
    /*Create Final Search Text*/
    $search_text =
        strtolower(
            implode(
                ' ',
                $search_parts
            )
        );

    $search_text =
        preg_replace(
            '/\s+/',
            ' ',
            $search_text
        );

    /*Meal Type*/
    $meal_types = recipe_ai_extract_meal_types_from_recipe($recipe);
    if (empty($meal_types)) {
        $meal_types = recipe_ai_detect_meal_types($recipe['name'] ?? '');
    }

    /*cook methods*/
    $cook_methods = recipe_ai_extract_cook_methods_from_recipe($recipe);

    $diet_text = recipe_ai_extract_diets_from_recipe($recipe);
    if (empty($diet_text)) {
        $diet_text = recipe_ai_detect_diet($recipe);
    }

    $occasion_text = recipe_ai_extract_occasions_from_recipe($recipe);
    if (empty($occasion_text)) {
        $occasion_text = recipe_ai_detect_occasion($recipe);
    }

    $wpdb->replace(
        $table,
        [

            'recipe_id' =>
                $recipe['id'],

            'title' =>
                $recipe['name'],

            'slug' =>
                $recipe['slug'],

            'image_url' =>
                $recipe['image_url'],

            'ingredients_text' =>
                implode(
                    ',',
                    $ingredients
                ),

            'ingredients_normalized' =>
                implode(
                    ',',
                    $ingredients_normalized
                ),

            'keywords_text' =>
                implode(
                    ',',
                    $keywords
                ),

            'cuisine_text' =>
                implode(
                    ',',
                    $cuisine
                ),

            'calories' =>
                intval(
                    $recipe['nutrition']['calories']
                    ?? 0
                ),

            'search_text' => $search_text,

            'document' =>
                recipe_ai_build_document(
                    $recipe
                ),

            'recipe_json' =>
                wp_json_encode(
                    $recipe
                ),

            'updated_at' =>
                current_time(
                    'mysql'
                ),

            'meal_type_text' =>
                implode(
                    ',',
                    $meal_types
                ),

            'cook_method_text' =>
                implode(
                    ',',
                    $cook_methods
                ),

            'diet_text' =>
                implode(
                    ',',
                    $diet_text
                ),

            'occasion_text' =>
                implode(
                    ',',
                    $occasion_text
                ),

            'protein' =>
                intval(
                    $recipe['nutrition']['protein']
                    ?? 0
                ),

            'prep_time' =>
                intval(
                    $recipe['prep_time']
                    ?? 0
                ),

            'cook_time' =>
                intval(
                    $recipe['cook_time']
                    ?? 0
                ),

            'total_time' =>
                intval(
                    $recipe['total_time']
                    ?? 0
                ),

        ]
    );
}
/*Diet Detection*/
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
/*Occasion Detection*/
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
/*Import Entire JSON File*/
function recipe_ai_import_json_file(
    $file_path
)
{
    if (
        !file_exists($file_path)
    ) {
        return 0;
    }

    $json =
        file_get_contents(
            $file_path
        );

    $recipes =
        json_decode(
            $json,
            true
        );

    if (
        empty($recipes)
    ) {
        return 0;
    }

    $count = 0;

    foreach (
        $recipes
        as $recipe
    ) {

        recipe_ai_import_recipe(
            $recipe
        );

        $count++;
    }

    return $count;
}

add_action('init', function () {

    if (
        !isset(
            $_GET['recipe-import']
        )
    ) {
        return;
    }

    if (
        !current_user_can(
            'manage_options'
        )
    ) {
        return;
    }

    $count =
        recipe_ai_import_json_file(
            WP_CONTENT_DIR .
            '/uploads/recipes.json'
        );

    wp_die(
        "Imported {$count} recipes."
    );

});
