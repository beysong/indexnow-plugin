# IndexNow Plugin

Submit your sitemap to **IndexNow (Bing)** for fast search engine indexing.

## Features

- **API Key Auto-Generation** — Generate and save a random API key with one click
- **Automatic Verification** — Plugin serves the verification file at `/{apikey}.txt` automatically (no manual file upload needed)
- **Manual Sitemap Submission** — Submit your `sitemap.xml` to IndexNow on demand
- **Scheduled Auto-Submission** — Automatically submit on a schedule (hourly / daily / weekly / monthly)
- **Configurable Frequency** — Choose how often auto-submission runs

## How It Works

1. Click **Generate & Save API Key** — a key is auto-created and saved
2. The plugin automatically serves your verification file at `/{key}.txt`
3. Submit your sitemap to Bing manually, or enable **Auto submit** with your preferred frequency

## Configuration

| Field | Description |
|---|---|
| API Key | Your IndexNow API key (auto-generated or manually entered) |
| Generate & Save API Key | Generates a random key and saves it |
| Auto submit | Enable scheduled automatic submission |
| Submission Frequency | How often to submit: Hourly / Daily / Weekly / Monthly |
| Sitemap URL | Custom sitemap URL (defaults to `/sitemap.xml`) |

## Routes

| Route | Description |
|---|---|
| `GET /{apikey}.txt` | Serves the verification file (auto-handled by plugin) |
| `GET /beysong_indexnow/generate-key` | Generates/saves API key |
| `GET /beysong_indexnow/submit` | Manually triggers IndexNow submission |

## Verification

The plugin automatically responds to Bing's verification request at `/{apikey}.txt` — no manual file upload required.

## Requirements

- OctoberCMS 4.x
- PHP 8.0+
