# Recipe AI Chat

WordPress plugin for adding a recipe assistant chat and recipe search experience to a site.

## What It Does

- Adds a `[recipe_ai_chat]` shortcode for rendering the chat UI.
- Loads recipe data into custom database tables.
- Exposes REST endpoints for:
  - recipe search
  - recipe details
  - related recipes
  - chat responses
- Includes OpenAI helpers for chat completions and embeddings.

## Main Files

- [recipe-ai-chat.php](./recipe-ai-chat.php)
- [PROJECT-KNOWLEDGE.md](./PROJECT-KNOWLEDGE.md)
- [docs/ARCHITECTURE.md](./docs/ARCHITECTURE.md)
- [docs/MEMORY.md](./docs/MEMORY.md)

## Frontend

- `assets/chat.js`
- `assets/chat.css`

The frontend script is localized with the REST endpoint and a WordPress REST nonce.

## Shortcode

Use this shortcode in a post, page, or template:

```text
[recipe_ai_chat]
```

## REST Endpoints

- `POST /wp-json/recipe-ai/v1/chat`
- `GET /wp-json/recipe-ai/v1/search`
- `GET /wp-json/recipe-ai/v1/recipe/{id}`
- `GET /wp-json/recipe-ai/v1/recipe/{id}/related`
- `POST /wp-json/recipe-ai/v1/openai-chat`

## Data Storage

The plugin creates custom tables for:

- conversations
- messages
- imported recipes
- embeddings

## Import Flow

Recipes are imported from JSON and stored in a search-friendly format with:

- title
- ingredients
- keywords
- cuisine
- full JSON payload

## Notes

- There are temporary debug/test routes in the codebase for local development.
- `includes/openai.php` currently contains a placeholder API key string and should be configured before real use.
- `includes/search.php` includes `print_r()` debug output that may need removal for production.
