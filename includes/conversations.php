<?php

defined('ABSPATH') || exit;

function recipe_ai_get_session_id()
{
    if (!empty($_COOKIE['recipe_ai_session'])) {
        return sanitize_text_field(
            $_COOKIE['recipe_ai_session']
        );
    }

    $session_id = wp_generate_uuid4();

    setcookie(
        'recipe_ai_session',
        $session_id,
        time() + YEAR_IN_SECONDS,
        COOKIEPATH,
        COOKIE_DOMAIN
    );

    $_COOKIE['recipe_ai_session'] = $session_id;

    return $session_id;
}
/*Get or Create Conversation*/
function recipe_ai_get_conversation_id()
{
    global $wpdb;

    $session_id = recipe_ai_get_session_id();

    $table = $wpdb->prefix .
        'recipe_ai_conversations';

    $conversation_id = $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT id
            FROM {$table}
            WHERE session_id = %s
            ",
            $session_id
        )
    );

    if ($conversation_id) {
        return (int) $conversation_id;
    }

    $wpdb->insert(
        $table,
        [
            'session_id' => $session_id,
            'user_id'    => get_current_user_id()
        ]
    );

    return $wpdb->insert_id;
}
/*Save Message*/
function recipe_ai_save_message(
    $conversation_id,
    $role,
    $message
)
{
    global $wpdb;

    $table = $wpdb->prefix .
        'recipe_ai_messages';

    $wpdb->insert(
        $table,
        [
            'conversation_id' => $conversation_id,
            'role' => $role,
            'message' => $message
        ]
    );
}
/*Load History*/
function recipe_ai_get_history(
    $conversation_id,
    $limit = 20
)
{
    global $wpdb;

    $table = $wpdb->prefix .
        'recipe_ai_messages';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT role, message
            FROM {$table}
            WHERE conversation_id = %d
            ORDER BY id DESC
            LIMIT %d
            ",
            $conversation_id,
            $limit
        ),
        ARRAY_A
    );

    $rows = array_reverse($rows);

    $messages = [];

    foreach ($rows as $row) {

        $messages[] = [
            'role' => $row['role'],
            'content' => $row['message']
        ];

    }

    return $messages;
}