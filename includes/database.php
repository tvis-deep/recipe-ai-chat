<?php

defined('ABSPATH') || exit;

function recipe_ai_create_tables()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset_collate = $wpdb->get_charset_collate();

    $conversations = $wpdb->prefix . 'recipe_ai_conversations';
    $messages      = $wpdb->prefix . 'recipe_ai_messages';

    $sql = "

    CREATE TABLE {$conversations} (

        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

        session_id VARCHAR(100) NOT NULL,

        user_id BIGINT UNSIGNED NULL,

        title VARCHAR(255) NULL,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY (id),

        UNIQUE KEY session_id (session_id),

        KEY user_id (user_id),

        KEY updated_at (updated_at)

    ) {$charset_collate};

    CREATE TABLE {$messages} (

        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

        conversation_id BIGINT UNSIGNED NOT NULL,

        role ENUM('system','user','assistant') NOT NULL,

        message LONGTEXT NOT NULL,

        prompt_tokens INT UNSIGNED DEFAULT 0,

        completion_tokens INT UNSIGNED DEFAULT 0,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (id),

        KEY conversation_id (conversation_id),

        KEY role (role),

        KEY created_at (created_at)

    ) {$charset_collate};

    ";

    dbDelta($sql);
}
function recipe_ai_create_recipe_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table = $wpdb->prefix . 'recipe_ai_recipes';

    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (

        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

        recipe_id BIGINT UNSIGNED NOT NULL,

        title VARCHAR(255) NOT NULL,

        slug VARCHAR(255),

        image_url TEXT,

        ingredients_text LONGTEXT,

        keywords_text LONGTEXT,

        cuisine_text TEXT,

        calories INT DEFAULT 0,

        document LONGTEXT,

        recipe_json LONGTEXT,

        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (id),

        UNIQUE KEY recipe_id (recipe_id),

        KEY title (title)

    ) {$charset};";

    dbDelta($sql);
}
function recipe_ai_create_embeddings_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table = $wpdb->prefix . 'recipe_ai_embeddings';

    $charset = $wpdb->get_charset_collate();

    $sql = "

    CREATE TABLE {$table} (

        recipe_id BIGINT UNSIGNED NOT NULL,

        embedding LONGTEXT NOT NULL,

        model VARCHAR(100) NOT NULL,

        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (recipe_id)

    ) {$charset};

    ";

    dbDelta($sql);
}
