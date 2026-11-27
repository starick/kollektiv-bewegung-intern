# Wiki — design

## Intent
A reference shelf for the team, next to Protocols. Protocols are the raw record of
each plenum, the wiki is the cleaned-up, current version people come back to:

- **How-tos** for the tool and for routines ("Stundenplan anlegen", "Stunden loggen").
  They change whenever the tool or the process changes.
- **Decisions worth rereading**: summaries of what plena decided on a topic, so nobody
  has to dig through months of protocols.
- **The link collection**, the most-used page: internal Google Sheets, information
  pages, PayPal links and similar.

Pages are Markdown, filed in a tree of categories, and every change is recorded in the
same audit trail protocols use.

## Goals
- Readers mainly browse: open the wiki, see the category tree, click down to a page.
- Search by title and text as a second way in.
- The link collection is one click away: pinned at the top of the wiki overview.
- Every page change is visible and attributed (who, when, line diff of the text).
- Pages link to protocols and to each other with plain Markdown links; those links
  keep working when a page is renamed or moved.

## Non-goals (for now)
- Public sharing and per-category view rights. Planned later; the data model must not
  get in the way of them, but nothing is built now.
- Markdown download.
- Manual ordering of the tree (drag and drop).
- Structured "sources" linking a page to protocols, and backlinks on protocols.
- History for categories themselves.

## Anti-goals
- Storing credentials in the wiki. The page history would keep every old password
  forever and every team member can read it. The link collection says *where* logins
  are kept instead. (A separate, encrypted credentials area may come much later.)
- A second copy of the Markdown editor and renderer that has to be kept in sync with
  the protocol one.
- Links that break because a page was renamed or moved to another category.

## Approaches

### Tree storage
- **A. Adjacency list (chosen).** `wiki_categories` with `parent_id`; pages have a
  nullable `category_id`. The whole tree is loaded in one query and assembled in PHP,
  which is plenty for dozens of categories. A move changes one `parent_id`. Fits
  per-category view rights later.
- **B. Nested set (`kalnoy/nestedset`).** Fast subtree queries, but a new dependency
  and left/right values rewritten on every move. Only pays off for large trees.
- **C. Category path as a string on the page.** No empty categories possible, renames
  rewrite every page. Doesn't fit the overview. Rejected.

### Sharing code with protocols
- **A. Extract the shared parts (chosen).** The Markdown renderer and the editor with
  live preview become neutral, shared pieces used by protocols and wiki. Protocols get
  a small refactor covered by their existing tests.
- **B. Copy into wiki versions.** Protocols untouched, but two editors to keep in sync.
  Rejected.

The user picked A for both.

## Design

### Data
- `wiki_categories`: `id` (ulid), `parent_id` (nullable, self-referencing), `name`,
  timestamps. Name unique among siblings.
- `wiki_pages`: `id` (ulid), `category_id` (nullable → top level), `title`, `slug`
  (unique), `text` (Markdown, longText, default `''`), `pinned` (bool, default false),
  `created_by`, `edited_by`, timestamps.
- Deleting a category is only allowed when it has no subcategories and no pages, so
  the foreign keys can restrict deletion.

### Models
- `WikiCategory`: `parent`, `children`, `pages` relations; a helper that returns the
  full tree, and one that returns a category's ancestors (for breadcrumbs and the
  cycle check). Not audited.
- `WikiPage`: `implements Auditable`, `use HasAuditTrail`, `HasUlids`,
  `$auditDiffed = ['text']`, `$auditExclude = ['edited_by', 'slug']`.
  Route key is `slug`.

### URLs and slugs
- Pages live at `/wiki/{slug}`. The category is **not** part of the URL, so moving a
  page never breaks a link.
- The slug is generated from the title (German transliteration, like protocols) when
  the page is created, with a `-2`, `-3` counter on collisions. Renaming a page keeps
  its slug.

### Routes and permissions
- `Route::resource('wiki', WikiPageController::class)` (parameter `wiki` → page by
  slug) plus a resource for categories limited to `store`, `update`, `destroy`.
- Policies mirror protocols: every member of the shared team can view, create and
  edit pages and categories. Deleting a page is allowed for its creator or a super
  admin. Deleting a category is allowed for any team member, and only when it's empty.

### Overview page (`/wiki`)
```
Wiki                                   [+ Page] [+ Category]
┌──────────────────────────────────────────────────────────┐
│ 📌 Links                          updated 3 days ago     │
└──────────────────────────────────────────────────────────┘
[ Search…            ]
▾ Anleitungen
    ▾ Stundenplan
        Stundenplan anlegen
        Kurse eintragen
    Stunden loggen
▾ Beschlüsse
    ▸ Finanzen
    Raumnutzung
```
- Pinned pages at the top (normally just the link collection). Any page can be
  pinned or unpinned from the edit view.
- The tree is sorted alphabetically, categories before pages, top-level pages last.
- Categories can be renamed, moved and deleted (when empty) from the tree, in a small
  dialog. Moving a category into itself or one of its descendants is rejected by the
  server.
- Search covers title and text, like the protocol search. While a search term is set,
  the tree is replaced by a flat result list showing each page's category path.

### Page view (`/wiki/{slug}`)
- Breadcrumbs with the category path, title, rendered Markdown, last editor and date,
  and the audit trail underneath (same `AuditTrail.vue` as protocols).
- Menu: edit; delete when allowed.

### Create and edit
- Fields: title, category (a tree select, with "top level" as an option), pinned,
  and the Markdown text with the shared editor and live preview.
- Creating opens the editor directly (like an empty protocol).
- A change of category shows in the page's history with the category's **name**, not
  its id.

### Shared Markdown parts (refactor)
- `App\Markdown\ProtocolMarkdown` becomes a neutral `App\Markdown\Markdown`, same
  rules (raw HTML escaped, unsafe links dropped, only external https images rendered). Protocol and wiki
  both use it.
- The preview endpoint becomes a neutral `markdown.preview` route used by both editors.
- The edit/preview toggle, the text area and the preview logic move out of
  `protocol-edit-model.ts` and `Protocols/Edit.vue` into a shared Markdown editor
  component (plus a composable for the preview requests). Protocol edit uses it;
  protocol tests must stay green.

### Navigation and translations
- A "Wiki" entry in `Navbar.vue` next to Protocols.
- All new strings in `lang/de.json`, informal "du".

### Tests
Feature tests in `tests/Feature/Wiki/`, following `tests/Feature/Protocols/`: access
(team members only), create/edit/delete with the delete rule, slug generation and
stability on rename, audit trail entries including the text diff, category moves with
the cycle check, refusal to delete non-empty categories, search, and the shared preview
endpoint.

## Calls made while writing this up
Settled without the user; check these while reading.
- **Link stability covers wiki pages only.** Protocol slugs still change when a
  protocol's date changes or its description is added or removed (`Protocol::booted`),
  which breaks a wiki link to it. That's rare, and redirects for old protocol slugs are
  out of scope.
- **Category changes in the history** record the category's name (or its path) at the
  time of the change, so the history stays readable after the category is renamed or
  deleted.
- **`create` is a reserved slug**, because `/wiki/create` would otherwise hide a page
  titled "Create".
- **Sibling names are unique** and checked in validation. A MySQL unique index doesn't
  catch duplicates at the top level, where `parent_id` is NULL.
- **The preview test** moves from `ProtocolPreviewTest` to the shared `markdown.preview`
  route.
- **Pinned pages** show at the top and also in their normal place in the tree.

## Left to the builder
- Exact component split and names for the shared Markdown editor.
- How the tree is passed to the front end (nested JSON or flat list built into a tree
  in the browser) and which PrimeVue components render the tree and the tree select.
- Whether category rename/move/delete lives in a context menu or in buttons on hover.
