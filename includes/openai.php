<?php

defined('ABSPATH') || exit;

function recipe_ai_openai(
    array $history
)
{
    $api_key = 'YOUR_API_KEY';

    $messages = [

        [
            'role' => 'system',
            'content' =>
                'You are a recipe assistant.'
        ]

    ];

    $messages = array_merge(
        $messages,
        $history
    );

    $response = wp_remote_post(
        'https://api.openai.com/v1/chat/completions',
        [
            'headers' => [
                'Authorization' =>
                    'Bearer ' . $api_key,

                'Content-Type' =>
                    'application/json'
            ],

            'body' => wp_json_encode([
                'model' => 'gpt-4o-mini',
                'messages' => $messages
            ])
        ]
    );

    $body = json_decode(
        wp_remote_retrieve_body(
            $response
        ),
        true
    );

    return $body['choices'][0]
        ['message']['content']
        ?? '';
}