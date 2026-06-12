# Project Memory

Short reference notes for future work on the `Recipe AI Chat` plugin.

## Purpose

- Recipe assistant chat for WordPress.
- Recipe search and related recipe discovery.
- Recipe import and indexing from JSON.

## Important Files

- `recipe-ai-chat.php`
- `includes/rest-api.php`
- `includes/search.php`
- `includes/importer.php`
- `includes/database.php`
- `includes/embeddings.php`
- `includes/openai.php`
- `includes/shortcode.php`

## Key Tables

- `wp_recipe_ai_conversations`
- `wp_recipe_ai_messages`
- `wp_recipe_ai_recipes`
- `wp_recipe_ai_embeddings`

## Current Behavior To Remember

- `[recipe_ai_chat]` renders the assistant UI.
- Search results are scored with ingredient and keyword matching.
- The plugin imports recipe data from JSON into a custom recipe table.
- Chat routes currently mix rendered HTML and JSON responses depending on the endpoint.

## Known Cleanup Items

- Remove `print_r()` debug output from search code.
- Replace temporary query-string test hooks with safer admin tools or WP-CLI commands.
- Replace the placeholder OpenAI key handling in `includes/openai.php`.
- Check whether `readme.md` should eventually be turned into a WordPress.org-style plugin readme if distribution is planned.

## Working Assumptions

- Recipe data source is a JSON export.
- WordPress is responsible for UI rendering and REST exposure.
- Embeddings are prepared for future semantic search, even if search currently relies on text matching.
