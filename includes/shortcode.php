
<?php

defined('ABSPATH') || exit;

add_shortcode(
    'recipe_ai_chat',
    'recipe_ai_chat_shortcode'
);

function recipe_ai_chat_shortcode()
{
    wp_enqueue_script('recipe-ai-chat');
    wp_enqueue_style('recipe-ai-chat');

    ob_start();
    ?>

    <div id="recipe-ai-chat-wrapper">

        <div id="recipe-ai-header">

            <h3>Recipe Assistant</h3>

            <p>
                Ask me anything about recipes,
                ingredients, meals and cooking.
            </p>

        </div>

        <div id="recipe-ai-messages">

            <div class="recipe-ai-message ai">

                Hi 👋

                What would you like to cook today?

            </div>

        </div>

        <div id="recipe-ai-suggestions">

            <button class="recipe-ai-suggestion">
                Quick dinner ideas
            </button>

            <button class="recipe-ai-suggestion">
                High protein breakfast
            </button>

            <button class="recipe-ai-suggestion">
                Healthy lunch recipes
            </button>

            <button class="recipe-ai-suggestion">
                What can I cook with chicken?
            </button>

        </div>

        <div id="recipe-ai-input-wrap">

            <textarea
                id="recipe-ai-input"
                placeholder="Ask anything..."
            ></textarea>

            <button id="recipe-ai-send">
                Send
            </button>

        </div>

    </div>

    <?php

    return ob_get_clean();
}