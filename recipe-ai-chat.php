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
require_once RECIPE_AI_PATH . 'includes/conversations.php';

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
