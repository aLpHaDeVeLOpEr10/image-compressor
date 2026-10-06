# PicsCompressor

A free online image compressor and converter for JPG, PNG and WebP, with an admin dashboard for managing every page's content, SEO and language versions.

Compression runs in the visitor's browser where possible, so images are not uploaded. A server-side fallback (PHP GD) handles browsers that cannot encode WebP or PNG, and processes images in memory without storing them.

## Features

**Public site**

- Image compressor on the homepage: quality slider, target sizes (e.g. 100 KB), output format, before/after comparison.
- Converter tools: PNG to JPG, JPG to WebP, PNG to WebP, WebP to JPG.
- Language versions of any tool at `/{lang}/{slug}`, with a language switcher and `hreflang` links.
- SEO: canonical URLs, Open Graph and Twitter tags, JSON-LD structured data, `sitemap.xml`, `robots.txt` and `llms.txt`.
- Contact form (stored in the database, optional email notification), FAQ, About, Editorial policy and legal pages.

**Admin dashboard** (`/admin`)

- Dashboard with message and tool statistics.
- **Tools**: edit every piece of page text as key/value content, plus title, slug, status, meta tags and share image.
- **Add tool**: create a new tool page (its Blade file is generated) or a language version of an existing tool.
- **Trash**: trash and restore tools, or delete admin-added tools permanently.
- **Messages**: read, search, mark and delete contact form messages.

## Tech stack

- PHP 8.3+, Laravel 13
- MySQL (SQLite in-memory for tests)
- Tailwind CSS 4, Vite 8, vanilla JavaScript
- PHPUnit 12, Laravel Pint

## Requirements

- PHP 8.3 or newer with the `gd` extension (server-side compression). The `intl` extension is optional; it shows language names in their own language, e.g. "Español".
- Composer 2
- Node.js 20+ and npm
- MySQL 8 (or another database supported by Laravel)

## Installation

```bash
git clone <repository-url> image-compressor
cd image-compressor

composer install
cp .env.example .env
php artisan key:generate
```

Create a database, set the `DB_*` values in `.env`, then:

```bash
php artisan migrate
php artisan storage:link
php artisan tools:sync-content
php artisan admin:create

npm install
npm run build
```

- `storage:link` makes uploaded share images publicly accessible.
- `tools:sync-content` saves every tool's default content to the database so it can be edited in the admin.
- `admin:create` asks for a name, email and password (at least 12 characters) and creates the admin account.

`composer run setup` runs the install, key, migration and build steps in one go, but not `storage:link`, `tools:sync-content` or `admin:create`.

## Development

```bash
composer run dev
```

This starts the Laravel server and the Vite dev server. The site runs at `http://localhost:8000` and the admin sign-in page at `http://localhost:8000/jhasseaidsha12` (see `ADMIN_LOGIN_PATH`).

If a front-end change does not appear, make sure the Vite dev server is running, or run `npm run build`.

## Configuration

Most settings are environment variables in `.env`.

| Variable | Default | Purpose |
|---|---|---|
| `APP_URL` | `http://localhost:8000` | Base URL. Used for canonical links, the sitemap and structured data. In production this is `https://picscompressor.online`. |
| `ADMIN_LOGIN_PATH` | `jhasseaidsha12` | Secret path of the admin sign-in page. The rest of `/admin` returns 404 to anyone not signed in. |
| `SITE_BRAND` | `PicsCompressor` | Brand name shown across the site. |
| `SITE_TAGLINE` | `Free online image compressor` | Short tagline. |
| `SITE_INDEXABLE` | `true` in production | When `false`, every page sends `noindex, nofollow`. Keep `false` on staging. |
| `SITE_COMPANY_NAME`, `SITE_COUNTRY`, `SITE_FOUNDING_YEAR` | | Operator details for the legal pages and structured data. |
| `SITE_HOSTING_PROVIDER`, `SITE_JURISDICTION`, `SITE_CONTACT_RETENTION`, `SITE_LOG_RETENTION` | | Values shown in the privacy policy and terms. |
| `SITE_TWITTER_HANDLE`, `SITE_SOCIAL_*` | | Social profiles for meta tags and the footer. |
| `CONTACT_SEND_MAIL`, `CONTACT_NOTIFY_EMAIL` | `false`, empty | Email a notification for each contact message (requires `MAIL_*` settings). |
| `ANALYTICS_GA4_ID` | | Google Analytics 4 measurement ID. Leave empty to disable analytics. |
| `COMPRESSOR_MAX_UPLOAD_MB` | `20` | Maximum upload size. |
| `COMPRESSOR_MAX_PIXELS` | `50000000` | Maximum image size for in-browser compression. |
| `COMPRESSOR_SERVER_MAX_PIXELS` | `25000000` | Maximum image size for the server fallback. |
| `COMPRESSOR_DEFAULT_QUALITY` | `80` | Default quality slider value. |
| `COMPRESSOR_RATE_LIMIT_PER_MINUTE` | `30` | Server compression requests allowed per IP per minute. |

Other settings live in:

- `config/site.php`: brand assets, legal page dates and contact settings.
- `config/tools.php`: the built-in tools, the homepage tool, categories and available languages.
- `config/compressor.php`: formats, output options and target size presets.

## Managing content

### Tools and content keys

Every tool page gets its text from **content keys**: key/value pairs stored in the database and edited under **Tools → Edit** in the admin.

Default values live in `resources/content/tool-content/`:

| File | Content |
|---|---|
| `_shared.php` | Text on every tool page: compressor widget labels and messages, badges, FAQ and related-tools headings, structured data. |
| `_site.php` | Header, footer and navigation. Edited on the homepage tool; applies to every page. |
| `<tool-key>.php` | A tool's own content: heading, introduction, sections, features and FAQs. |

How keys work:

- **Saved values win.** If a key is missing from the database, the default from these files is used.
- **Numbered keys form lists**, such as `feature_1`, `how_to_step_1_title` or `faq_1_question` with `faq_1_answer`. Add the next number to add an item; clear an item's first value to hide it.
- **Placeholders** keep values correct when routes or limits change: `{brand}`, `{year}`, `{operator}`, `{max_upload_mb}`, `{max_megapixels}`, `{server_max_megapixels}`, and links such as `{url:home}`, `{url:pages.faq}` or `{url:tools.png-to-jpg}`.
- **Types**: text and textarea values are escaped. HTML values are output as HTML and are only editable by admins.
- **In Blade views**, content is available as `$content['key']`. Widget messages (`widget_msg_*`) use `:tokens` such as `:size` that JavaScript fills in.

After adding new default keys in code, run `php artisan tools:sync-content`, or click **Add missing keys** in a tool's editor. Existing edited values are never overwritten.

### Built-in tools vs admin-added tools

- **Built-in tools** are listed in `config/tools.php` (`pages`). Each has a definition file in `resources/content/tools/` and a view in `resources/views/tools/content/`. They can be trashed and restored, but not deleted permanently.
- **Admin-added tools** (**Tools → Add tool**) are stored in the database and served at `/tools/{slug}`. Creating one generates `resources/views/tools/content/{name}.blade.php` from a starter template, and never overwrites an existing file. Deleting the tool keeps its Blade file.

**Blade files created in the admin exist only on that server.** Commit files created locally. On a production server the views directory must be writable, and files created there should be copied back into the repository so the next deploy does not remove them.

### Language versions (child tools)

**Tools → Add tool → Child tool** (or **Create child tool** on a tool) creates a translated copy with its own language, slug, meta tags and a copy of every content key.

- Served at `/{lang}/{slug}`, or `/{lang}` for the homepage.
- New versions start as drafts. Translate the values, then publish.
- To translate quickly: in the editor, use **Deep Edit → Download JSON**, translate the values (keep keys, HTML tags, `{placeholders}` and `:tokens` unchanged), then **Merge JSON** and save.
- Available languages are set in `config/tools.php` (`languages`).

### Trash

Trashed tools and their language versions return 404 and are removed from menus, the sitemap and `hreflang` links. A trashed tool still reserves its slug and language until it is deleted permanently.

## Artisan commands

| Command | Purpose |
|---|---|
| `php artisan admin:create` | Create an admin user, or grant admin access to an existing email and reset its password. Accepts `--name`, `--email` and `--password`. |
| `php artisan tools:sync-content` | Create records for all tools and add missing default content keys, including for language versions. Safe to run on every deploy. |
| `php artisan site:launch-check` | Check that `APP_URL`, operator details and other SEO-critical settings are ready for launch. |
| `php artisan images:generate-examples` | Regenerate the example figures, the 512px logo and `resources/content/figures.json`. Needs Arial or DejaVu fonts (`--font-dir`). |

## Testing

```bash
php artisan test
```

Tests use an in-memory SQLite database and never write into `resources/views`. Run a single file or test with:

```bash
php artisan test tests/Feature/ChildToolTest.php
php artisan test --filter=test_child_tool_copies_every_parent_key_and_value
```

Format PHP code before committing:

```bash
vendor/bin/pint
```

## Deployment

A typical deploy:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan tools:sync-content
npm ci && npm run build
php artisan storage:link
php artisan optimize
```

Before going live:

- Set `APP_ENV=production`, `APP_DEBUG=false`, `SITE_INDEXABLE=true` and `APP_URL=https://picscompressor.online`.
- Run `php artisan site:launch-check` and fix anything it reports.
- Create the admin account with `php artisan admin:create`.
- Make sure the web server can write to `storage/`, `bootstrap/cache/` and, if tools will be added in the admin, `resources/views/tools/content/`.

When a built-in tool's slug changes in the admin, cached routes are cleared automatically. Admin-added tools and language versions are resolved per request and need no cache refresh.

## Project structure

```
app/
  Console/Commands/        admin:create, tools:sync-content, site:launch-check, images:generate-examples
  Http/Controllers/        Public pages, tools, contact form, SEO endpoints
  Http/Controllers/Admin/  Dashboard, tools, messages, authentication
  Models/                  Tool, ToolContentField, ContactMessage, User
  Services/ImageCompression/  Server-side GD compressor
  Support/Seo/             Meta tags and structured data
  Tools/                   Tool registry, content keys, synchronizer, Blade file generator
config/
  site.php  tools.php  compressor.php
resources/
  content/tools/           Built-in tool definitions
  content/tool-content/    Default content keys (_shared, _site, per tool)
  js/compressor/           In-browser compression engine
  js/admin/                Admin content editor
  views/tools/content/     Tool page content views
  views/admin/             Admin dashboard views
routes/web.php             Public, admin and localized tool routes
tests/Feature/             Feature tests
```
