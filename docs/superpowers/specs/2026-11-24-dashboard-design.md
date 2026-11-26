# Dashboard — short design

## Intent
The dashboard becomes the start page: what's current, at a glance. It replaces the
Jetstream welcome text.

## What it shows
- **Newest timetable** (highest year/week): small live preview of its design (the real
  display component rendered at 800px and scaled down), week, course count, author, link.
- **Newest protocol** (latest date): date, description, topics as tags, participant
  count, last editor and time, link. No text.
- **Latest wiki changes**: the last 10 changed pages, **one line per page** (user's call),
  showing the page's most recent change: created / edited / deleted, by whom, when.
  Deleted pages have no link.
- Each box has an empty state with a link to create one.

## Permissions
Timetables are visible to every logged-in user. Protocol and wiki boxes are only sent
and shown to members of the shared team (`belongsToSharedTeam`), like their modules.

## Approaches
- **Preview:** render the existing display components scaled down (chosen) vs. a stored
  JPG (doesn't exist, would need generating on save) vs. no preview (the user asked to
  "show" the timetable).
- **Wiki changes:** latest audit per wiki page via `max(id)` grouped by page (chosen) vs.
  pages ordered by `updated_at` (misses deletions and who changed them).

## Left to the builder
Exact layout and component split; boxes stack on phones.
