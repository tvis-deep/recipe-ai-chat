<?php

/*
Plugin Name: Recipe AI Chat
*/

defined('ABSPATH') || exit;

define('RECIPE_AI_URL', plugin_dir_url(__FILE__));
define('RECIPE_AI_PATH', plugin_dir_path(__FILE__));

require_once RECIPE_AI_PATH . 'includes/api.php';
require_once RECIPE_AI_PATH . 'includes/openai.php';

require_once RECIPE_AI_PATH . 'includes/rest-api.php';
require_once RECIPE_AI_PATH . 'includes/shortcode.php';

add_action('wp_enqueue_scripts', function() {

    wp_enqueue_script(
        'recipe-ai-chat',
        RECIPE_AI_URL . 'assets/chat.js',
        ['jquery'],
        time(),
        true
    );

    wp_localize_script(
        'recipe-ai-chat',
        'RecipeAI',
        [
            'endpoint' => rest_url('recipe-ai/v1/chat'),
            'nonce'    => wp_create_nonce('wp_rest')
        ]
    );

    wp_enqueue_style(
        'recipe-ai-chat',
        RECIPE_AI_URL . 'assets/chat.css'
    );
});

/* Register Activation Hook for create tables*/
require_once plugin_dir_path(__FILE__) . 'includes/database.php';

register_activation_hook(
    __FILE__,
    'recipe_ai_create_tables'
);
/*Create Conversation*/
function recipe_ai_create_conversation($session_id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_conversations';

    $wpdb->insert(
        $table,
        [
            'session_id' => $session_id,
            'user_id'    => get_current_user_id() ?: null,
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

    $table = $wpdb->prefix . 'recipe_ai_messages';

    $wpdb->insert(
        $table,
        [
            'conversation_id' => $conversation_id,
            'role'            => $role,
            'message'         => $message,
        ]
    );

    return $wpdb->insert_id;
}
/*Load Chat History*/
function recipe_ai_get_history(
    $conversation_id,
    $limit = 20
)
{
    global $wpdb;

    $table = $wpdb->prefix . 'recipe_ai_messages';

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
            'role'    => $row['role'],
            'content' => $row['message']
        ];
    }

    return $messages;
}
/* chat shortcode*/
function recipe_chat_func( $atts ) {
    $attributes = shortcode_atts( array(
        'title' => false,
        'limit' => 4,
    ), $atts );
    
    ob_start();
    ?>
    <div id="recipe-chat">
        <div id="recipe-messages"></div>
        <textarea id="recipe-input"></textarea>
        <button id="recipe-send">
            Send
        </button>
    </div>
    <?php

    return ob_get_clean();

}
add_shortcode( 'recipe-chat', 'recipe_chat_func' );