=== BreznGEO ===
Contributors: mifupadev
Tags: seo, ai, meta description, schema, llms.txt
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.2.1
Requires PHP: 8.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI meta descriptions, GEO blocks, internal link suggestions, Schema.org structured data, and llms.txt for WordPress. No subscription.

== Description ==

BreznGEO is a WordPress SEO plugin that automates AI meta descriptions, adds Schema.org structured data, and helps your content get discovered by AI-driven search. It covers GEO — Generative Engine Optimization — preparing your pages for AI overviews, answer engines, and retrieval-augmented search alongside traditional search engine optimization. No subscription required.

The plugin was originally built for Donau2Space.de and has run on the developer's own production sites since version 1.0. Stability is the priority: when something breaks, it affects the developer first. There are no upsells, no subscription tiers, and no features added just to expand the feature list. It works as a focused complement to your existing SEO setup — not a replacement.

All AI features are optional. Without an API key, the plugin falls back to local logic and keeps running normally — meta descriptions are extracted from post content, internal link suggestions use text-based matching, and all Schema.org, llms.txt, and robots.txt features work without any external service.

= Learn more =

* Website: <a href="https://brezngeo.com/">brezngeo.com</a>
* FAQ: <a href="https://brezngeo.com/faq.html">brezngeo.com/faq</a>
* Live demo: <a href="https://brezngeo.com/demo.html">brezngeo.com/demo</a>

= At a glance =

* Generates AI meta descriptions automatically on publish — falls back to clean local extraction without any API key
* Adds a GEO Quick Overview block to each post: AI-generated summary, key bullet points, optional FAQ
* Suggests internal links while writing — text-based matching works without AI; optional AI upgrade for semantic ranking
* Analyzes keyword usage in real time — checks title, headings, density, images, and more with locale-aware variant matching
* Bulk-generates descriptions for all existing posts that have none
* Adds Schema.org JSON-LD structured data for search engines and AI retrieval systems
* Serves `/llms.txt` — a machine-readable content index for AI discovery tools
* Manages AI crawler access per-bot via the robots.txt manager, directly from the admin
* Logs AI bot visits with hashed IPs — no plain text stored
* Free. No subscription. API costs go directly to your provider.

= What makes BreznGEO different =

* **AI is optional.** No API key means no AI and no costs. All non-AI features — Schema.org, llms.txt, internal link suggestions, and fallback meta extraction — continue to work normally.
* **No subscription.** The plugin is free. If you use AI generation, costs go directly to your chosen provider. There is no service fee or middle layer.
* **Works alongside existing SEO plugins.** When another SEO plugin is active, generated descriptions are written into that plugin's own meta field — no duplication, no conflicts.
* **Built for real sites.** It has been running on the developer's own production sites since version 1.0 — shipped only after being tested under real conditions.
* **No vendor lock-in.** Switch AI providers at any time without losing settings. The plugin works independently of any specific AI provider.

= AI Meta Generator =

Generates a 150–160 character meta description the moment a post is published. The prompt is fully customizable using `{title}`, `{content}`, `{excerpt}`, and `{language}` placeholders. Language is detected automatically from Polylang, WPML, or the WordPress site locale.

If no API key is configured or the AI request fails, a clean fallback excerpt is extracted from the post content — no description is left empty.

= GEO Block =

Adds an AI-generated Quick Overview block to each post: a short summary, key bullet points, and an optional FAQ. Rendered as a native `<details>` element — configurable as collapsible (default), always open, or stored without frontend output.

Supports three generation modes: automatic on publish, hybrid (auto only when fields are empty), or manual. Insertion position is configurable: after the first paragraph (default), top, or bottom. A quality gate suppresses FAQ generation on posts below a configurable word-count threshold. The post editor meta box includes live generate and clear buttons, a per-post enable toggle, and an optional prompt add-on field for author-level customization. Four built-in themes: Light, Dark, Minimal, Brezn.

= Internal Link Suggestions =

An editor meta box that surfaces relevant internal links while you write. Each suggestion is a phrase–target pair: a phrase found in your content, paired with an existing post that covers the same topic.

Text-based matching (title, tag, category, and excerpt overlap) works without AI. An optional AI upgrade sends the top 20 candidates to your connected provider for semantic ranking. Trigger options: manual button, on post save, or a timed interval. A settings page lets you exclude posts (such as legal pages) and boost specific pillar pages. Supported in both Gutenberg and Classic Editor.

= Bulk Generator =

Finds all published posts without a meta description (including descriptions set by Rank Math, Yoast, AIOSEO, or SEOPress) and generates them in configurable batches with rate-limiting between batches. A live progress log and per-batch cost estimate are shown during the run.

= Multi-Provider AI Support =

Choose from four AI providers and switch at any time without losing your settings:

* OpenAI (GPT-4.1, GPT-4o, GPT-4o mini, and more)
* Anthropic Claude (Claude 3.5 Sonnet, Claude 3 Haiku, and more)
* Google Gemini (Gemini 2.0 Flash, Gemini 1.5 Pro, and more)
* xAI Grok (Grok 3, Grok 3 mini, and more)

= Schema.org Enhancer (GEO) =

Injects JSON-LD structured data for search engines and AI retrieval systems:

* Organization — site name, URL, logo, and social `sameAs` links
* Article — headline, dates, description, and publisher
* Author — person name, author URL, Twitter link
* Speakable — marks up your H1 and first paragraph for voice and AI assistants
* BreadcrumbList — skipped automatically when Rank Math or Yoast is active
* AI Meta Tags — `max-snippet:-1, max-image-preview:large, max-video-preview:-1` directives

= llms.txt =

Serves a machine-readable index of your published content at `/llms.txt`, following the emerging llms.txt convention increasingly supported by AI indexing tools. Supports custom title, description sections, featured resource links, pagination for large sites, and HTTP ETag / Last-Modified caching.

= robots.txt Manager =

Block individual AI training and data-harvesting bots directly from the WordPress admin — no manual file editing. Supports 13 known bots: GPTBot, ClaudeBot, Google-Extended, PerplexityBot, CCBot, Applebot-Extended, Bytespider, DataForSeoBot, ImagesiftBot, Omgili, Diffbot, FacebookBot, and Amazonbot.

= Crawler Log =

Automatically logs visits from known AI bots. Stores the bot name, a SHA-256-hashed IP address, and the requested URL. Entries older than 90 days are purged automatically. A 30-day summary is shown on the plugin dashboard.

= Keyword Analysis =

A post editor meta box that analyzes keyword usage in real time. Enter a primary keyword and optional secondary keywords — the plugin checks title, headings, keyword density, image alt text, meta description, URL slug, first and last paragraph, image titles and captions, and excerpt. Each check reports pass, warning, or fail status with actionable feedback. Three update modes: live (debounced while typing), manual (button click), or on save. Optional AI features (when an API key is configured): keyword suggestions, content optimization tips, and semantic keyword analysis. Supports locale-aware keyword variant matching for English and German. Configurable via a dedicated settings page (target density, minimum occurrences, post types, debounce interval).

= Post Editor Integration =

A "Meta Description" meta box in the post and page editor shows the current description, its source (AI / Fallback / Manual), a live character counter, and a "Regenerate with AI" button. A sidebar SEO widget displays word count, reading time, heading structure, and link counts with live warnings.

= Link Analysis Dashboard =

Identifies posts without internal links, posts with an unusually high external-link count, and your top pillar pages by inbound internal link count — loaded on demand with a one-hour cache.

= Secure API Key Storage =

API keys are obfuscated using XOR with a key derived from your WordPress authentication salts before being written to the database. Keys never appear in plain text in database dumps or export files. No OpenSSL extension required.

= Compatibility =

Works standalone or alongside any major SEO plugin. When Rank Math, Yoast SEO, AIOSEO, or SEOPress is active, generated descriptions are written to that plugin's own meta field. Existing descriptions set by those plugins are always respected and never overwritten.

== Installation ==

1. Download the plugin zip and go to **Plugins → Add New → Upload Plugin** in your WordPress admin.
2. Upload the zip file and click **Install Now**, then **Activate**.
3. Go to **BreznGEO → AI Provider**.
4. Select your preferred AI provider, paste your API key, and click **Test connection**.
5. Choose a model and optionally enter token costs for cost estimation.
6. Go to **BreznGEO → Meta Generator** to select post types and configure Schema.org types.
7. To serve a content index, go to **BreznGEO → llms.txt**, enable it, and save.
8. To manage AI crawler access, go to **BreznGEO → robots.txt** and select the bots to block.

The plugin works without an API key — fallback meta extraction runs automatically on publish.

== Frequently Asked Questions ==

= Do I need an API key? =

An API key is required for AI-generated meta descriptions. Without one, the plugin automatically falls back to extracting a clean 150–160 character excerpt from the post content. All other features (Schema.org, llms.txt, robots.txt manager, crawler log) work without an API key.

= How much does it cost to generate meta descriptions? =

Cost depends on the AI provider and model you choose. A single meta description typically uses fewer than 1,500 tokens (input + output combined). As a rough reference, 1,000 descriptions with GPT-4o mini has cost around $0.50–$1.00 at recent rates — but AI provider pricing changes over time. The AI Provider settings page links directly to the current pricing page for each supported provider.

= Are my API keys stored securely? =

Keys are obfuscated using XOR encryption with a key derived from your WordPress authentication salts before being written to the database. They do not appear in plain text in database dumps or export files. For the highest level of protection, define your API keys as constants in `wp-config.php` — the plugin will use them automatically and nothing is stored in the database.

= What is llms.txt? =

`llms.txt` is an emerging convention (similar in spirit to `robots.txt` or `sitemap.xml`) that gives AI language models and retrieval-augmented generation (RAG) tools a structured, machine-readable index of a site's content. Support varies by tool and is still evolving. The plugin serves it at `yourdomain.com/llms.txt` with proper HTTP caching headers.

= Is this compatible with Rank Math / Yoast SEO / AIOSEO / SEOPress? =

Yes. When one of these plugins is active, BreznGEO writes generated descriptions directly into that plugin's meta field. It also checks for existing descriptions from all four plugins before generating, and skips posts that already have one. Breadcrumb and standalone meta description output is suppressed automatically to avoid conflicts.

= Does it work with Polylang or WPML? =

Yes. The meta generator detects the post language from Polylang (`pll_get_post_language()`), WPML (`ICL_LANGUAGE_CODE`), or the WordPress site locale, and includes it in the prompt so the AI writes in the correct language.

= How does the Bulk Generator handle rate limits? =

Posts are processed in configurable batches (1–20 per batch) with a 6-second pause between batches. Each post is retried up to three times with a 1-second delay between attempts. A transient-based lock prevents simultaneous runs. The lock expires automatically after 15 minutes and can also be released manually from the Bulk Generator page.

= Does the Crawler Log store personal data? =

No. IP addresses are hashed with SHA-256 before storage — the original IP is never saved. Entries are purged automatically after 90 days.

= Will it slow down my site? =

No front-end overhead beyond the inline JSON-LD and optional meta tags in `wp_head`. The llms.txt response is cached via WordPress transients and served with HTTP 304 when the ETag matches. No external HTTP requests are made during normal page loads — AI API calls only happen on post publish or when explicitly triggered from the admin.

= Can I add a custom AI provider? =

Yes. Implement the `BreznGEO\Providers\ProviderInterface` interface (five methods: `getId`, `getName`, `getModels`, `testConnection`, `generateText`), place the class in `includes/Providers/`, and register it in `Core::register_hooks()`. It will appear in all admin dropdowns automatically.

== Screenshots ==

1. Dashboard — provider status, meta coverage stats, crawler log summary.
2. AI Provider settings — provider selector, API key entry, connection test, model picker, cost configuration.
3. Meta Generator settings — post type selection, token limit, prompt editor, Schema.org toggles.
4. Bulk Generator — batch controls, live progress log, cost estimate.
5. llms.txt configuration — enable toggle, custom sections, post types, pagination settings.
6. robots.txt / AI Bots — per-bot block checkboxes.
7. Post editor — Meta Description meta box with source badge and AI regeneration button.
8. Post editor — SEO Analysis sidebar widget with live stats and warnings.

== External Services ==

This plugin can optionally connect to external AI services. All AI features are opt-in and disabled by default. No data is transmitted unless the user has explicitly enabled AI generation and configured an API key.

The following features may send data to the selected AI provider:

* **Meta Descriptions** — post title and content excerpt are sent to generate a meta description. Triggered on publish, on update, or via the Bulk Generator.
* **GEO Block** — post title and content are sent to generate a Quick Overview block (summary, key points, optional FAQ). Triggered on publish/update or manually from the post editor.
* **Internal Link Suggestions (AI upgrade)** — up to 20 pre-scored candidate link pairs (post titles and URLs) are sent for semantic ranking. Triggered manually, on save, or on a timed interval — all configurable by the user.
* **Keyword Analysis (AI upgrade)** — post content and keyword are sent for AI-powered keyword suggestions, content optimization tips, and semantic keyword analysis. Triggered manually from the post editor meta box.

No data is transmitted during normal page loads or to visitors.

= OpenAI =
* Data sent: Post title and content excerpt (meta descriptions, GEO Block); candidate post titles and URLs (link suggestions); post content and keyword (keyword analysis).
* API endpoint: `https://api.openai.com/v1/`
* Privacy policy: https://openai.com/policies/privacy-policy/
* Terms of use: https://openai.com/policies/terms-of-use/

= Anthropic Claude =
* Data sent: Post title and content excerpt (meta descriptions, GEO Block); candidate post titles and URLs (link suggestions); post content and keyword (keyword analysis).
* API endpoint: `https://api.anthropic.com/`
* Privacy policy: https://www.anthropic.com/privacy
* Terms of use: https://www.anthropic.com/legal/consumer-terms

= Google Gemini =
* Data sent: Post title and content excerpt (meta descriptions, GEO Block); candidate post titles and URLs (link suggestions); post content and keyword (keyword analysis).
* API endpoint: `https://generativelanguage.googleapis.com/`
* Privacy policy: https://policies.google.com/privacy
* Terms of use: https://ai.google.dev/gemini-api/terms?hl=en

= xAI Grok =
* Data sent: Post title and content excerpt (meta descriptions, GEO Block); candidate post titles and URLs (link suggestions); post content and keyword (keyword analysis).
* API endpoint: `https://api.x.ai/`
* Privacy policy: https://x.ai/privacy-policy
* Terms of use: https://x.ai/legal/terms-of-service

== Changelog ==

= 1.2.1 =
* Security: Added ABSPATH direct access guards to all PHP class files.
* i18n: Complete German translation — all 394 UI strings now translated.
* i18n: Regenerated .po/.mo/.pot translation files.

= 1.2.0 =
* New: Keyword Analysis meta box in the post editor — checks keyword usage across title, headings, density, image alts, meta description, slug, first/last paragraph, image title/caption, and excerpt.
* New: Primary and secondary keyword support with configurable minimum occurrences.
* New: Three analysis update modes: live (debounced), manual, and on-save.
* New: Locale-aware keyword variant matching for English and German (compound words, suffixes).
* New: Optional AI-powered keyword suggestions, content optimization tips, and semantic keyword analysis.
* New: Keyword Analysis settings page with target density, minimum occurrences, post type selection, and debounce configuration.

= 1.1.0 =
* Fixed Google Gemini API terms URL that caused too many redirects during WordPress.org review.
* Improved input sanitization in Schema.org meta box — uses `map_deep()` with `sanitize_textarea_field` instead of relying on downstream sanitization with phpcs suppression.
* Improved input sanitization in Internal Link Suggestions AJAX handler — uses `absint()` and standard `isset()` pattern.
* Removed all `phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` comments — all `$_POST` data is now sanitized inline at the point of access.

= 1.0.0 =
* Initial release as BreznGEO.
* AI Meta Generator with auto-publish trigger, customizable prompt, and Polylang/WPML language detection.
* Fallback meta extraction (sentence-boundary-aware, 150–160 characters) for use without an API key or on API failure.
* Bulk Generator with batched AJAX processing, rate limiting, transient lock, per-post retry logic, and cost estimation.
* Schema.org Enhancer: Organization, Article, Author, Speakable, BreadcrumbList JSON-LD; AI indexing meta tags.
* Standalone meta description output with automatic suppression when Rank Math, Yoast, or AIOSEO is active.
* Native field write-through for Rank Math, Yoast SEO, AIOSEO, and SEOPress.
* llms.txt with pagination, ETag/Last-Modified HTTP caching, custom sections, and manual cache clear.
* robots.txt manager for 13 known AI and data-harvesting crawlers.
* Crawler Log database table with SHA-256 IP hashing and weekly auto-purge.
* GEO Quick Overview block — AI-generated per-post summary, key bullet points, optional FAQ; four built-in themes (Light, Dark, Minimal, Brezn).
* Internal Link Suggestions — editor meta box with text-based and optional AI-powered matching.
* Meta Description meta box with source badge, character counter, and inline AI regeneration.
* SEO Analysis sidebar widget with live content statistics and warnings.
* Link Analysis dashboard panel: no-internal-links report, external-link outliers, pillar page ranking.
* KeyVault API key obfuscation using XOR with WP salts.
* Multi-provider support: OpenAI, Anthropic, Google Gemini, xAI Grok.
* `brezngeo_prompt` filter and `brezngeo_meta_saved` action hooks for developers.

== Upgrade Notice ==

= 1.2.1 =
Adds ABSPATH security guards to all files and completes German translation.

= 1.2.0 =
Adds Keyword Analysis: real-time keyword checks in the post editor with optional AI-powered suggestions.

= 1.1.0 =
Fixes WordPress.org review issues: corrected Google Gemini terms URL and improved inline input sanitization.

= 1.0.0 =
Initial release.
