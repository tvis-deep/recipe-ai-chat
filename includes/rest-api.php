<?php

defined('ABSPATH') || exit;

add_action('rest_api_init', function () {

    register_rest_route(
        'recipe-ai/v1',
        '/chat',
        [
            'methods'  => 'POST',
            'callback' => 'recipe_ai_chat_callback',
            'permission_callback' => '__return_true'
        ]
    );

});
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