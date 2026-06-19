<?php

defined('ABSPATH') || exit;

if (!function_exists('recipe_ai_normalize_ingredient')) {
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

        $tokens = preg_split('/\s+/', $ingredient) ?: [];
        $normalized = [];

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '' || strlen($token) < 2) {
                continue;
            }

            if (isset($alias_map[$token])) {
                $token = $alias_map[$token];
            } elseif (preg_match('/^[a-z]{4,}s$/', $token) && !preg_match('/(?:ss|us|is|os)$/', $token)) {
                $token = substr($token, 0, -1);
            }

            $normalized[] = $token;
        }

        $ingredient = implode(' ', $normalized);

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
            'passata' => 'passata',
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
            'green beans' => 'green beans',
            'brown rice' => 'brown rice',
            'white rice' => 'white rice',
            'salt pepper' => 'salt and pepper',
            'yogurt milk' => 'yogurt milk',
            'white wine stock' => 'white wine stock',
            'chicken thigh chicken breast' => 'chicken thigh chicken breast',
            'chicken thigh breast' => 'chicken thigh chicken breast',
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

        return $ingredient;
    }
}

if (!function_exists('recipe_ai_sanitize_ingredient_text')) {
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
}

if (!function_exists('recipe_ai_normalize_ingredient_options')) {
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
}

if (!function_exists('recipe_ai_get_normalized_recipe_ingredients')) {
    function recipe_ai_get_normalized_recipe_ingredients($ingredients_text)
    {
        $items = [];

        if (empty($ingredients_text)) {
            return [];
        }

        foreach (explode(',', strtolower((string) $ingredients_text)) as $ingredient) {
            $ingredient = recipe_ai_normalize_ingredient($ingredient);
            if ($ingredient === '') {
                continue;
            }
            $items[$ingredient] = true;
        }

        return array_keys($items);
    }
}

if (!function_exists('recipe_ai_tokenize_ingredients_query')) {
    function recipe_ai_tokenize_ingredients_query($query)
    {
        $query = strtolower((string) $query);
        $query = preg_replace('/[^a-z0-9\s\/\-\.]/', ' ', $query);
        $query = preg_replace('/\s+/', ' ', $query);
        $query = trim($query);

        return preg_split('/\s+/', $query) ?: [];
    }
}

if (!function_exists('recipe_ai_score_ingredient_candidate')) {
    function recipe_ai_score_ingredient_candidate($query, $candidate)
    {
        $query = recipe_ai_normalize_ingredient($query);
        $candidate = recipe_ai_normalize_ingredient($candidate);

        if ($query === '' || $candidate === '') {
            return 0;
        }

        if ($query === $candidate) {
            return 100;
        }

        if (strpos($query, $candidate) !== false || strpos($candidate, $query) !== false) {
            return 70;
        }

        $query_words = array_values(array_unique(recipe_ai_tokenize_ingredients_query($query)));
        $candidate_words = preg_split('/\s+/', $candidate) ?: [];
        $overlap = array_values(array_intersect($query_words, $candidate_words));

        if (count($overlap) >= 2) {
            return 40;
        }

        if (count($overlap) === 1 && strlen($overlap[0]) >= 4) {
            return 10;
        }

        return 0;
    }
}

function recipe_ai_extract_ingredients($query)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_recipes';

    $recipes = $wpdb->get_results(
        "SELECT ingredients_normalized, ingredients_text FROM {$table}",
        ARRAY_A
    );

    $found = [];

        foreach ($recipes as $row) {
            $ingredients = [];

        if (!empty($row['ingredients_normalized'])) {
            $ingredients = explode(',', $row['ingredients_normalized']);
        } elseif (!empty($row['ingredients_text'])) {
            $ingredients = recipe_ai_get_normalized_recipe_ingredients($row['ingredients_text']);
        }

        foreach ($ingredients as $ingredient) {
            $ingredient = recipe_ai_sanitize_ingredient_text($ingredient);
            $score = recipe_ai_score_ingredient_candidate($query, $ingredient);
            if ($score <= 0) {
                continue;
            }
            $found[$ingredient] = max($found[$ingredient] ?? 0, $score);
        }
    }

    arsort($found);

    $result = [];
    foreach ($found as $ingredient => $score) {
        $result[] = [
            'ingredient' => $ingredient,
            'matches' => $score,
        ];
    }

    return $result;
}

function recipe_ai_extract_ingredients_new($query)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_recipes';

    $recipes = $wpdb->get_results(
        "SELECT ingredients_normalized, ingredients_text FROM {$table}",
        ARRAY_A
    );

    $all_ingredients = [];

        foreach ($recipes as $row) {
            $ingredients = [];

        if (!empty($row['ingredients_normalized'])) {
            $ingredients = explode(',', $row['ingredients_normalized']);
        } elseif (!empty($row['ingredients_text'])) {
            $ingredients = recipe_ai_get_normalized_recipe_ingredients($row['ingredients_text']);
        }

        foreach ($ingredients as $ingredient) {
            $ingredient = recipe_ai_sanitize_ingredient_text($ingredient);
            $expanded = recipe_ai_normalize_ingredient_options($ingredient);
            if (!empty($expanded)) {
                foreach ($expanded as $expanded_item) {
                    $all_ingredients[$expanded_item] = true;
                }
                continue;
            }

            $ingredient = trim(strtolower($ingredient));
            if (strlen($ingredient) < 3) {
                continue;
            }
            $all_ingredients[$ingredient] = true;
        }
    }

    $found = [];

    foreach (array_keys($all_ingredients) as $ingredient) {
        if (recipe_ai_score_ingredient_candidate($query, $ingredient) > 0) {
            $found[] = $ingredient;
        }
    }

    return array_values(array_unique($found));
}

function recipe_ai_extract_ingredients_from_words($words)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_recipes';

    $recipes = $wpdb->get_results(
        "SELECT ingredients_normalized, ingredients_text FROM {$table}",
        ARRAY_A
    );

    $found = [];

        foreach ($recipes as $row) {
            $ingredients = [];

        if (!empty($row['ingredients_normalized'])) {
            $ingredients = explode(',', $row['ingredients_normalized']);
        } elseif (!empty($row['ingredients_text'])) {
            $ingredients = recipe_ai_get_normalized_recipe_ingredients($row['ingredients_text']);
        }

        foreach ($ingredients as $ingredient) {
            $ingredient = recipe_ai_sanitize_ingredient_text($ingredient);
            $expanded = recipe_ai_normalize_ingredient_options($ingredient);
            if (!empty($expanded)) {
                foreach ($expanded as $expanded_item) {
                    foreach ($words as $word) {
                        $word = recipe_ai_normalize_ingredient($word);

                        if ($word === '' || strlen($word) < 3) {
                            continue;
                        }

                        if (stripos($expanded_item, $word) !== false) {
                            $found[$expanded_item] = true;
                            break 2;
                        }
                    }
                }
                continue;
            }

            foreach ($words as $word) {
                $word = recipe_ai_normalize_ingredient($word);

                if ($word === '' || strlen($word) < 3) {
                    continue;
                }

                if (stripos($ingredient, $word) !== false) {
                    $found[$ingredient] = true;
                    break;
                }
            }
        }
    }

    return array_keys($found);
}

function recipe_ai_calculate_ingredient_match(
    array $user_ingredients,
    string $recipe_ingredients
)
{
    $recipe_ingredients = !empty($recipe_ingredients)
        ? explode(',', strtolower($recipe_ingredients))
        : [];

    $recipe_ingredients = array_values(array_filter(array_map('trim', $recipe_ingredients)));

    $matched = 0;

    foreach ($user_ingredients as $ingredient) {
        $ingredient = recipe_ai_sanitize_ingredient_text($ingredient);
        $ingredient = recipe_ai_normalize_ingredient($ingredient);

        foreach ($recipe_ingredients as $recipe_ingredient) {
            $recipe_ingredient = recipe_ai_sanitize_ingredient_text($recipe_ingredient);
            $recipe_ingredient = recipe_ai_normalize_ingredient($recipe_ingredient);

            if ($ingredient === '' || $recipe_ingredient === '') {
                continue;
            }

            if (
                $ingredient === $recipe_ingredient ||
                stripos($recipe_ingredient, $ingredient) !== false ||
                stripos($ingredient, $recipe_ingredient) !== false
            ) {
                $matched++;
                break;
            }
        }
    }

    return [
        'matched' => $matched,
        'total' => count($recipe_ingredients),
        'coverage' => round(($matched / max(count($user_ingredients), 1)) * 100),
    ];
}

add_action('init', function () {
    if (!isset($_GET['test-ingredients'])) {
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
