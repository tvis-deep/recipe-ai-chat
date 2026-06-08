<?php

add_action('rest_api_init', function() {

    register_rest_route(
        'recipe-ai/v1',
        '/openai-chat',
        [
            'methods'  => 'POST',
            'callback' => 'recipe_ai_chat',
            'permission_callback' => '__return_true'
        ]
    );

});

function recipe_ai_chat($request)
{
    $message = sanitize_text_field(
        $request->get_param('message')
    );

    $response = recipe_ai_openai([$message]);

    return [
        'success' => true,
        'reply'   => $response
    ];
}