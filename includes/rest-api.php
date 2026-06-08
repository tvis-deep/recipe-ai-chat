<?php

defined('ABSPATH') || exit;

add_action('rest_api_init', function () {

   /* register_rest_route(
        'recipe-ai/v1',
        '/chat',
        [
            'methods'  => 'POST',
            'callback' => 'recipe_ai_chat_callback',
            'permission_callback' => '__return_true'
        ]
    );*/
    /*Recipe chat Endpoint*/
     register_rest_route(
            'recipe-ai/v1',
            '/chat',
            [
                'methods'  => 'POST',
                'callback' => 'recipe_ai_chat_endpoint',
                'permission_callback' => '__return_true',
            ]
        );

    /*Recipe search Endpoint*/

    register_rest_route(
        'recipe-ai/v1',
        '/search',
        [
            'methods'  => 'GET',
            'callback' => 'recipe_ai_rest_search',
            'permission_callback' => '__return_true',
        ]
    );
    /*Recipe Detail Endpoint*/
    register_rest_route(
        'recipe-ai/v1',
        '/recipe/(?P<id>\d+)',
        [
            'methods' => 'GET',
            'callback' => 'recipe_ai_rest_recipe',
            'permission_callback' => '__return_true'
        ]
    );
    /*Related Recipes Endpoint*/
    register_rest_route(
        'recipe-ai/v1',
        '/recipe/(?P<id>\d+)/related',
        [
            'methods' => 'GET',
            'callback' => 'recipe_ai_rest_related',
            'permission_callback' => '__return_true',
        ]
    );
});
    /*Recipe chat callback*/

function recipe_ai_chat_endpoint(
    WP_REST_Request $request
)
{

    $message = sanitize_text_field(
        $request->get_param('message')
    );

    if (empty($message)) {

        return [
            'reply' => 'Please enter a search term.'
        ];

    }
    $results = recipe_ai_search(
        $message,
        10
    );
    if (empty($results)) {

        return [
            'reply' => 'No recipes found.'
        ];

    }

    return [
        'reply' => recipe_ai_build_chat_response(
            $message,
            $results
        )
    ];
}
    /*Recipe build chat response*/

function recipe_ai_build_chat_response(
    $query,
    $results
)
{
    ob_start();

    ?>

    <div class="recipe-ai-results">

        <div class="recipe-ai-heading">
            Found <?php echo count($results); ?>
            recipes for:
            <strong><?php echo esc_html($query); ?></strong>
        </div>

        <div class="recipe-ai-cards">

            <?php foreach ($results as $recipe) : ?>

                <div
                    class="recipe-ai-card"
                    data-id="<?php echo esc_attr($recipe['recipe_id']); ?>"
                >

                    <?php if (!empty($recipe['image_url'])) : ?>

                        <img
                            src="<?php echo esc_url($recipe['image_url']); ?>"
                            alt="<?php echo esc_attr($recipe['title']); ?>"
                        >

                    <?php endif; ?>

                    <h4>
                        <?php echo esc_html($recipe['title']); ?>
                    </h4>

                    <div class="recipe-score">

                        Score:
                        <?php echo intval($recipe['score']); ?>

                    </div>

                    <?php if (!empty($recipe['ingredient_matches'])) : ?>

                        <div class="ingredient-match">

                            Ingredient Matches:
                            <?php echo intval(
                                $recipe['ingredient_matches']
                            ); ?>

                        </div>

                    <?php endif; ?>

                    <button
                        class="recipe-view-btn"
                        data-id="<?php echo esc_attr($recipe['recipe_id']); ?>"
                    >
                        View Recipe
                    </button>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

    <?php

    return ob_get_clean();
}
    /*Recipe search callback*/

function recipe_ai_rest_search(
    WP_REST_Request $request
)
{
    $query = sanitize_text_field(
        $request->get_param('q')
    );

    if (empty($query)) {

        return new WP_REST_Response(
            [
                'success' => false,
                'message' => 'Search query missing'
            ],
            400
        );
    }

    $results = recipe_ai_search(
        $query,
        20
    );
    $response_results = [];

    foreach ($results as $recipe) {

        $response_results[] = [

            'recipe_id' => $recipe['recipe_id'],

            'title' => $recipe['title'],

            'slug' => $recipe['slug'],

            'image_url' => $recipe['image_url'],

            'calories' => $recipe['calories'],

            'score' => $recipe['score'],

            'ingredient_matches' =>
                $recipe['ingredient_matches']
                ?? 0

        ];
    }

    return new WP_REST_Response(
        [
            'success' => true,
            'query'   => $query,
            'count'   => count($results),
            'results' => $response_results
        ],
        200
    );
}
    /*Recipe Detail callback*/

function recipe_ai_rest_recipe(
    WP_REST_Request $request
)
{
    global $wpdb;

    $recipe_id = absint(
        $request['id']
    );

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $recipe = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT *
            FROM {$table}
            WHERE recipe_id = %d
            ",
            $recipe_id
        ),
        ARRAY_A
    );

    if (!$recipe) {

        return new WP_REST_Response(
            [
                'success' => false,
                'message' => 'Recipe not found'
            ],
            404
        );
    }

    $recipe_json =
        json_decode(
            $recipe['recipe_json'],
            true
        );

    return new WP_REST_Response(
        [
            'success' => true,
            'recipe'  => recipe_ai_format_recipe_response(
                $recipe_json
            )
        ]
    );
}
/* helper recipe respose*/
function recipe_ai_format_recipe_response(
    array $recipe
)
{
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

            $ingredients[] = [

                'amount' =>
                    $ingredient['amount'] ?? '',

                'unit' =>
                    $ingredient['unit'] ?? '',

                'name' =>
                    $ingredient['name'] ?? ''

            ];
        }
    }

    $instructions = [];

    if (
        !empty(
            $recipe['instructions_flat']
        )
    ) {

        foreach (
            $recipe['instructions_flat']
            as $instruction
        ) {

            $instructions[] =
                wp_strip_all_tags(
                    $instruction['text']
                );
        }
    }

    return [

        'id' =>
            $recipe['id'],

        'title' =>
            $recipe['name'],

        'image' =>
            $recipe['image_url'],

        'summary' =>
            wp_strip_all_tags(
                $recipe['summary']
            ),

        'servings' =>
            $recipe['servings'],

        'prep_time' =>
            $recipe['prep_time'],

        'cook_time' =>
            $recipe['cook_time'],

        'ingredients' =>
            $ingredients,

        'instructions' =>
            $instructions,

        'nutrition' =>
            $recipe['nutrition'] ?? [],

        'video' =>
            $recipe['video_embed'] ?? ''

    ];
}
/*Related Recipes callback*/
function recipe_ai_rest_related(
    WP_REST_Request $request
)
{
    global $wpdb;

    $recipe_id =
        absint(
            $request['id']
        );

    $table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $recipe =
        $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT *
                FROM {$table}
                WHERE recipe_id=%d
                ",
                $recipe_id
            ),
            ARRAY_A
        );

    if (!$recipe) {
        return [];
    }

    $query =
        str_replace(
            ',',
            ' ',
            $recipe['keywords_text']
        );

    $results =
        recipe_ai_search(
            $query,
            6
        );

    return [
        'success' => true,
        'results' => $results
    ];
}
function recipe_ai_chat_callback(
    WP_REST_Request $request
)
{
    $message = sanitize_textarea_field(
        $request->get_param('message')
    );

    if (!$message) {

        return new WP_Error(
            'empty_message',
            'Message required'
        );

    }

    $conversation_id =
        recipe_ai_get_conversation_id();

    recipe_ai_save_message(
        $conversation_id,
        'user',
        $message
    );

    $history =
        recipe_ai_get_history(
            $conversation_id
        );

    $reply =
        recipe_ai_openai_chat(
            $history
        );

    recipe_ai_save_message(
        $conversation_id,
        'assistant',
        $reply
    );

    return [
        'success' => true,
        'reply' => $reply
    ];
}
