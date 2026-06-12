# Recipe AI Chat Project Knowledge

This file captures the current structure and behavior of the `Recipe AI Chat` WordPress plugin.

## What The Plugin Does

- Adds a recipe assistant chat UI through a WordPress shortcode.
- Loads recipe data into custom database tables.
- Supports recipe search, recipe detail lookup, and related recipe suggestions through REST endpoints.
- Includes OpenAI-related helpers for chat and embeddings.

## Main Plugin Entry Point

- File: `recipe-ai-chat.php`
- Defines the plugin constants `RECIPE_AI_URL` and `RECIPE_AI_PATH`.
- Loads the feature modules in `includes/`.
- Enqueues frontend assets:
  - `assets/chat.js`
  - `assets/chat.css`
- Localizes the frontend script with:
  - `RecipeAI.endpoint` -> REST chat endpoint
  - `RecipeAI.nonce` -> WordPress REST nonce
- Registers activation hooks for database table creation.

## Shortcode

- Shortcode: `[recipe_ai_chat]`
- File: `includes/shortcode.php`
- Renders:
  - Chat header
  - Default assistant greeting
  - Suggested prompt buttons
  - Message input and send button

## REST API

Defined in `includes/rest-api.php`.

### `POST /wp-json/recipe-ai/v1/chat`

- Callback: `recipe_ai_chat_endpoint()`
- Accepts a `message` parameter.
- Uses `recipe_ai_search()` to find matching recipes.
- Returns a rendered HTML response containing matching recipe cards.

### `GET /wp-json/recipe-ai/v1/search`

- Callback: `recipe_ai_rest_search()`
- Accepts query param `q`.
- Returns JSON search results with recipe metadata and score.

### `GET /wp-json/recipe-ai/v1/recipe/{id}`

- Callback: `recipe_ai_rest_recipe()`
- Returns a formatted recipe record from stored JSON.

### `GET /wp-json/recipe-ai/v1/recipe/{id}/related`

- Callback: `recipe_ai_rest_related()`
- Reuses recipe keywords to find similar recipes.

### Additional OpenAI Chat Route

- File: `includes/api.php`
- Route: `POST /wp-json/recipe-ai/v1/openai-chat`
- Callback: `recipe_ai_chat()`
- Passes the message into `recipe_ai_openai()`.

## Database Tables

Created in `includes/database.php`.

### `wp_recipe_ai_conversations`

- Stores chat sessions.
- Important columns:
  - `session_id`
  - `user_id`
  - `title`
  - timestamps

### `wp_recipe_ai_messages`

- Stores chat messages.
- Important columns:
  - `conversation_id`
  - `role`
  - `message`
  - token counts
  - timestamp

### `wp_recipe_ai_recipes`

- Stores imported recipe data and search-friendly text fields.
- Important columns:
  - `recipe_id`
  - `title`
  - `slug`
  - `image_url`
  - `ingredients_text`
  - `keywords_text`
  - `cuisine_text`
  - `calories`
  - `document`
  - `recipe_json`

### `wp_recipe_ai_embeddings`

- Stores recipe embeddings.
- Important columns:
  - `recipe_id`
  - `embedding`
  - `model`

## Import Flow

File: `includes/importer.php`

- `recipe_ai_import_recipe()`:
  - Flattens ingredient names.
  - Extracts keywords and cuisine tags.
  - Builds a searchable document string.
  - Saves the full recipe JSON.
- `recipe_ai_import_json_file()`:
  - Imports recipes from a JSON file.
- There is a temporary admin-triggered import route using:
  - `?recipe-import=1`
  - Requires `manage_options`
  - Imports from `WP_CONTENT_DIR . '/uploads/recipes.json'`

## Search Flow

File: `includes/search.php`

- `recipe_ai_search()` loads all recipes from the custom table.
- Query text is processed into ingredient-like words.
- Recipes are scored using:
  - Ingredient coverage
  - Keyword matches
  - Cuisine matches
  - Document matches
- Results are sorted by score descending.

Supporting helpers are in `includes/helpers.php` and `includes/ingredient-matcher.php`.

## Ingredient Matching

File: `includes/ingredient-matcher.php`

- Extracts likely ingredients from the user query based on known recipe ingredients.
- Compares query terms against stored `ingredients_text`.
- Calculates:
  - matched count
  - total recipe ingredients
  - coverage percentage

## OpenAI / Embeddings

### Chat

- File: `includes/openai.php`
- Sends chat completions to OpenAI.
- Uses model: `gpt-4o-mini`
- Current file contains a placeholder API key value:
  - `YOUR_API_KEY`

### Embeddings

- File: `includes/embeddings.php`
- Uses the option `recipe_ai_openai_key`.
- Generates embeddings with:
  - `text-embedding-3-small`
- Stores embeddings in the embeddings table.
- Includes a batch generator for recipes missing embeddings.

## Temporary Debug / Test Hooks

- `includes/search.php`
  - `?test-search=1`
- `includes/ingredient-matcher.php`
  - `?test-ingredients=1`
- `includes/embeddings.php`
  - `?embed-recipes=1`

These are useful for local testing, but should probably be removed or locked down before production use.

## Current Notes

- `includes/helpers.php` is currently empty.
- The repo’s `readme.md` currently contains a path-like value rather than project documentation.
- Some code paths still use `print_r()` debugging output inside the search flow.
- The OpenAI chat helper in `includes/openai.php` still needs a real API key source.

## Suggested Next Improvements

- Replace debug/test query-string routes with proper admin tools or WP-CLI commands.
- Remove `print_r()` calls from search functions.
- Store API keys via settings instead of hardcoding.
- Expand `readme.md` or this file with installation and usage instructions.
