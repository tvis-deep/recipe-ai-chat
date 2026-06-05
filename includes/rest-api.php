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

function recipe_ai_chat_callback(WP_REST_Request $request)
{
    $message = sanitize_textarea_field(
        $request->get_param('message')
    );

    if (empty($message)) {

        return new WP_Error(
            'empty_message',
            'Message is required',
            ['status' => 400]
        );

    }

    $reply = recipe_ai_openai_chat($message);

    return rest_ensure_response([
        'success' => true,
        'reply'   => $reply
    ]);
}