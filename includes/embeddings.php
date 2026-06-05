<?php

defined('ABSPATH') || exit;
/*Generate Embedding*/
function recipe_ai_generate_embedding($text)
{
    $api_key = get_option(
        'recipe_ai_openai_key'
    );

    $response = wp_remote_post(
        'https://api.openai.com/v1/embeddings',
        [
            'timeout' => 60,

            'headers' => [
                'Authorization' =>
                    'Bearer ' . $api_key,

                'Content-Type' =>
                    'application/json'
            ],

            'body' => wp_json_encode([
                'model' =>
                    'text-embedding-3-small',

                'input' =>
                    $text
            ])
        ]
    );

    if (
        is_wp_error($response)
    ) {
        return false;
    }

    $body = json_decode(
        wp_remote_retrieve_body(
            $response
        ),
        true
    );

    return
        $body['data'][0]['embedding']
        ?? false;
}
/*Save Embedding*/
function recipe_ai_save_embedding(
    $recipe_id,
    $embedding
)
{
    global $wpdb;

    $table =
        $wpdb->prefix .
        'recipe_ai_embeddings';

    $wpdb->replace(
        $table,
        [

            'recipe_id' =>
                $recipe_id,

            'embedding' =>
                wp_json_encode(
                    $embedding
                ),

            'model' =>
                'text-embedding-3-small',

            'updated_at' =>
                current_time(
                    'mysql'
                )

        ]
    );
}
/*Generate Recipe Embedding*/
function recipe_ai_embed_recipe(
    $recipe_id
)
{
    global $wpdb;

    $recipes_table =
        $wpdb->prefix .
        'recipe_ai_recipes';

    $recipe =
        $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT *
                FROM {$recipes_table}
                WHERE recipe_id=%d
                ",
                $recipe_id
            ),
            ARRAY_A
        );

    if (!$recipe) {
        return false;
    }

    $embedding =
        recipe_ai_generate_embedding(
            $recipe['document']
        );

    if (!$embedding) {
        return false;
    }

    recipe_ai_save_embedding(
        $recipe_id,
        $embedding
    );

    return true;
}
/*Batch Processor*/
function recipe_ai_generate_embeddings_batch(
    $limit = 50
)
{
    global $wpdb;

    $recipes =
        $wpdb->get_results(
            "
            SELECT r.recipe_id,
                   r.document
            FROM {$wpdb->prefix}recipe_ai_recipes r

            LEFT JOIN
            {$wpdb->prefix}recipe_ai_embeddings e

            ON r.recipe_id = e.recipe_id

            WHERE e.recipe_id IS NULL

            LIMIT {$limit}
            ",
            ARRAY_A
        );

    $count = 0;

    foreach ($recipes as $recipe) {

        $embedding =
            recipe_ai_generate_embedding(
                $recipe['document']
            );

        if (!$embedding) {
            continue;
        }

        recipe_ai_save_embedding(
            $recipe['recipe_id'],
            $embedding
        );

        $count++;
    }

    return $count;
}
/*Temporary Test Route*/
add_action('init', function(){

    if (
        !isset($_GET['embed-recipes'])
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
        recipe_ai_generate_embeddings_batch(
            20
        );

    wp_die(
        "Generated {$count} embeddings"
    );

});