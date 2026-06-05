<?php

function recipe_ai_openai($message)
{
    $api_key = 'YOUR_API_KEY';

    $response = wp_remote_post(
        'https://api.openai.com/v1/chat/completions',
        [
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
                        'You are a recipe assistant.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ]
            ])
        ]
    );

    $body = json_decode(
        wp_remote_retrieve_body($response),
        true
    );

    return $body['choices'][0]['message']['content'] ?? '';
}