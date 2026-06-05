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