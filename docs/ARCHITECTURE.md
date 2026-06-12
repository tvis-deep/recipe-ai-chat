# Architecture

This document explains how the plugin is organized and how data flows through it.

## Plugin Bootstrap

- File: `recipe-ai-chat.php`
- Defines plugin constants.
- Loads all feature modules from `includes/`.
- Enqueues the chat frontend assets.
- Registers activation hooks for table creation.

## Feature Modules

### `includes/shortcode.php`

- Registers the `[recipe_ai_chat]` shortcode.
- Outputs the chat shell markup and starter prompts.

### `includes/rest-api.php`

- Registers the recipe AI REST routes.
- Handles:
  - chat search replies
  - search results
  - recipe detail responses
  - related recipe lookup

### `includes/search.php`

- Implements the scoring logic used to rank recipes.
- Uses ingredient overlap and keyword matching.

### `includes/ingredient-matcher.php`

- Extracts ingredient-like terms from user queries.
- Compares the query against stored recipe ingredient data.

### `includes/importer.php`

- Imports recipes from a JSON file.
- Builds searchable text fields before persisting to the database.

### `includes/database.php`

- Creates the custom tables used by the plugin.
- Stores conversations, messages, recipes, and embeddings.

### `includes/embeddings.php`

- Generates OpenAI embeddings for recipe documents.
- Stores embedding vectors in a dedicated table.

### `includes/openai.php`

- Sends chat requests to OpenAI.
- Currently uses a hardcoded placeholder API key value.

### `includes/api.php`

- Registers an additional OpenAI chat route.
- Acts as a simple wrapper around the OpenAI helper.

## Data Model

### Conversations

Each chat session is stored with a session ID and optional user ID.

### Messages

Each message stores:

- role
- message body
- token counts
- conversation relationship

### Recipes

Each imported recipe stores:

- recipe identity
- title and slug
- image URL
- normalized ingredients
- keywords
- cuisine text
- calories
- full recipe JSON
- searchable document text

### Embeddings

Embeddings are stored by `recipe_id` so future semantic search can be added or expanded.

## Request Flow

### Chat Search

1. Browser sends a message to `POST /wp-json/recipe-ai/v1/chat`.
2. The message is sanitized.
3. `recipe_ai_search()` ranks recipes.
4. The response returns rendered HTML for matching recipe cards.

### Recipe Detail

1. Browser requests `GET /wp-json/recipe-ai/v1/recipe/{id}`.
2. The plugin loads the stored JSON for that recipe.
3. The response is normalized into a frontend-friendly schema.

### Related Recipes

1. Browser requests `GET /wp-json/recipe-ai/v1/recipe/{id}/related`.
2. The plugin reuses the recipe keywords as a query.
3. Matching recipes are returned as related results.

## Development Notes

- There are `init` hooks with query-string test entry points for local debugging.
- Search functions currently include debugging output that should be cleaned up before production.
- The search and ingredient matching logic is mostly database-driven rather than external-service-driven right now.
