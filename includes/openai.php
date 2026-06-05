<?php

defined('ABSPATH') || exit;

function recipe_ai_openai_chat($message)
{
    $api_key = 'YOUR_OPENAI_API_KEY';

    $response = wp_remote_post(
        'https://api.openai.com/v1/chat/completions',
        [
            'timeout' => 60,

            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json'
            ],

            'body' => wp_json_encode([

                'model' => 'gpt-4o-mini',

                'messages' => [

                    [
                        'role' => 'system',
                        'content' =>
                            'You are a helpful recipe assistant.'
                    ],

                    [
                        'role' => 'user',
                        'content' => $message
                    ]

                ]

            ])
        ]
    );

    if (is_wp_error($response)) {
        return 'Unable to contact AI service.';
    }

    $body = json_decode(
        wp_remote_retrieve_body($response),
        true
    );

    return $body['choices'][0]['message']['content']
        ?? 'No response.';
}