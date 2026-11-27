# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

### [1.8.1](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.8.0...v1.8.1) (2026-11-27)


### Bug Fixes

* Markdown fix ([be67c94](https://github.com/starick/kollektiv-bewegung-intern/commit/be67c94a090fa4a8dd1d5cbbee18fc16d998c138))

## [1.8.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.7.0...v1.8.0) (2026-11-26)


### Features

* add design for the wiki module ([90790f9](https://github.com/starick/kollektiv-bewegung-intern/commit/90790f9076ac15b50792ee4608f2f9dd7d97cf1c))
* add implementation plan for the wiki module ([61ec813](https://github.com/starick/kollektiv-bewegung-intern/commit/61ec8137cb504cf366c50be4d551ce2fa22267d0))
* add wiki categories as a tree ([dae377f](https://github.com/starick/kollektiv-bewegung-intern/commit/dae377fb9b1ec135d5924c1471d8d130141deb8b))
* add wiki pages with slugs, search and audit trail ([95a7d1c](https://github.com/starick/kollektiv-bewegung-intern/commit/95a7d1c9bc5c3f36ded2a372f827409b99fabce2))
* add wiki pages, category tree and navigation ([2d77f82](https://github.com/starick/kollektiv-bewegung-intern/commit/2d77f82d27a4bc259671208198621aeb37a562b5))
* drop the redundant wiki link from page breadcrumbs ([e2ba03d](https://github.com/starick/kollektiv-bewegung-intern/commit/e2ba03d2ff7e0d16ad56ce93b1bfe06b6f19bddc))
* drop the wiki seeder tests ([0fca8a9](https://github.com/starick/kollektiv-bewegung-intern/commit/0fca8a957e37fa8d282f3814625314aeb2e3741c))
* embed youtube and vimeo links as click-to-load videos ([ec84e7d](https://github.com/starick/kollektiv-bewegung-intern/commit/ec84e7d0ef46d910d143476e38fdc180a0af4b46))
* hide pinned wiki pages while searching ([eb747d3](https://github.com/starick/kollektiv-bewegung-intern/commit/eb747d399281c7adba57a2137cb911c659aa1a7b))
* list pinned wiki pages on top of the page sidebar ([e881108](https://github.com/starick/kollektiv-bewegung-intern/commit/e881108beacf0bd99b44a23d624cc3f798aba626))
* seed a pinned link collection stub ([7e8c7fc](https://github.com/starick/kollektiv-bewegung-intern/commit/7e8c7fc8fcd65ae2bc99dcd9180c753360830265))
* seed german wiki guides about timetables, protocols, the wiki and markdown ([7335aca](https://github.com/starick/kollektiv-bewegung-intern/commit/7335aca9622a9180369edc05c55ba5b13305cd26))
* show the wiki tree beside a page on desktop ([4891140](https://github.com/starick/kollektiv-bewegung-intern/commit/48911403675de28b2a817ec8455e561818538501))


### Bug Fixes

* responsive design for timetable controls ([4d98012](https://github.com/starick/kollektiv-bewegung-intern/commit/4d98012ce68a4df8aefa33747c5e9324fd899d3a))
* show pin changes as booleans in the history and cap wiki slug length ([30ee577](https://github.com/starick/kollektiv-bewegung-intern/commit/30ee577bf442a7fc82a6bcfde33a76591dadc515))

## [1.7.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.6.0...v1.7.0) (2026-11-23)


### Features

* add design and plan for layered timetable export ([bf49d73](https://github.com/starick/kollektiv-bewegung-intern/commit/bf49d735253905e72f18312963d6f64b243a577f))
* add pptx unit and color helpers with vitest setup ([9481ac7](https://github.com/starick/kollektiv-bewegung-intern/commit/9481ac7d0d217fe8519936fa32073b25c757ea2d))
* collect timetable layers from the rendered DOM ([64a2f0f](https://github.com/starick/kollektiv-bewegung-intern/commit/64a2f0f9cee35316bfe68dc6e01bc7c6c7ef8200))
* export timetables as layered pptx for canva ([5877024](https://github.com/starick/kollektiv-bewegung-intern/commit/5877024b32b35e64daad6c77e565a13922a6b3f3))
* link sharing qol ([1c819f3](https://github.com/starick/kollektiv-bewegung-intern/commit/1c819f3f0d74ce78ec21155be48e411620164f56))
* link sharing qol ([baf5945](https://github.com/starick/kollektiv-bewegung-intern/commit/baf594518c86777e8d8c04f1cf1074f21153bad7))
* make protocol routes via slug ([bb7db6d](https://github.com/starick/kollektiv-bewegung-intern/commit/bb7db6d4dc2e9c870c90a78a98de3157aeb4ec14))
* make protocols shareable (no login required) ([7f817f4](https://github.com/starick/kollektiv-bewegung-intern/commit/7f817f40f4a22f7710271b04735be2aa7f6b9470))
* replace design menu with save button and download split button ([4b2c914](https://github.com/starick/kollektiv-bewegung-intern/commit/4b2c914acda1096d41ecdba3f60ee93119e6c426))
* respect z-index order, split wrapped text and export the timetable element itself ([d813047](https://github.com/starick/kollektiv-bewegung-intern/commit/d813047e3216ca7f5ead77a4e376f8989b4d3d3c))

## [1.6.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.5.0...v1.6.0) (2026-11-23)


### Features

* add a generic audit trail based on laravel-auditing and show the protocol history on the protocol page ([ea9e468](https://github.com/starick/kollektiv-bewegung-intern/commit/ea9e468ced949a52ee44c9333a9ef6ac184cc5a0))
* align the protocol edit view with the protocol page and add a Markdown preview that works without saving ([8cb73c2](https://github.com/starick/kollektiv-bewegung-intern/commit/8cb73c269083844b0cdd4a9e4aa835433c415dd5))
* allow creating protocols without an initial file so the Markdown starts empty ([d73c64c](https://github.com/starick/kollektiv-bewegung-intern/commit/d73c64c20957fb36cbf8f53c9de1fa5e5696d577))
* show text changes in the audit trail as a line diff instead of only marking them as changed ([f97bde7](https://github.com/starick/kollektiv-bewegung-intern/commit/f97bde744fc8fd3ffc1e3e621138b02bb97b060b))

## [1.5.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.4.0...v1.5.0) (2026-11-02)


### Features

* adding participants field to protocols ([687b381](https://github.com/starick/kollektiv-bewegung-intern/commit/687b3817be3ef7caf30ddb32909bcf7f2a6a527d))
* editing protocols ([d10f38d](https://github.com/starick/kollektiv-bewegung-intern/commit/d10f38d13bfe8a4f424fd0d01ec618520673b64e))
* styling protocol view improved ([4d3ba96](https://github.com/starick/kollektiv-bewegung-intern/commit/4d3ba9604f95620f0d69cdeb2688e77bcbf116a9))

## [1.4.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.3.0...v1.4.0) (2026-11-02)


### Features

* add german translation ([b491638](https://github.com/starick/kollektiv-bewegung-intern/commit/b4916385a49f2e478536fb6f34202c29ee601f08))
* add german translation ([d5ec70b](https://github.com/starick/kollektiv-bewegung-intern/commit/d5ec70b19afa274b89355a4ddbdf7b503b3fbe81))
* add Protocol model with factory ([390439e](https://github.com/starick/kollektiv-bewegung-intern/commit/390439eec3da3a3e2b57a8d20b10f960b13b505c))
* protocol feature code review ([bc8ed35](https://github.com/starick/kollektiv-bewegung-intern/commit/bc8ed356f768655e5f5aaf032e44adf6c32ec436))
* protocol list view ([110ec4e](https://github.com/starick/kollektiv-bewegung-intern/commit/110ec4e92560691a4c25cfdb5166b183d179c0da))
* QoL changes on protocol views ([bb9c66c](https://github.com/starick/kollektiv-bewegung-intern/commit/bb9c66cec9c7ef16021fe2755c5a546c55dd63bb))
* styling ([1e5c0e3](https://github.com/starick/kollektiv-bewegung-intern/commit/1e5c0e3c3f204bc78058f8927f7637fe976dc396))
* translation adjustments ([38e451b](https://github.com/starick/kollektiv-bewegung-intern/commit/38e451baccfe776b5316a4d8329ffe8e3ccf06ca))
* view, create and delete* protocols (*owners and admins only) ([8b5ed99](https://github.com/starick/kollektiv-bewegung-intern/commit/8b5ed9909f8ecdc1445b37e1d4e94d608f4e223f))

## [1.3.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.2.6...v1.3.0) (2026-11-02)


### Features

* calculate timetable week from uploaded sheet ([fb5a0b1](https://github.com/starick/kollektiv-bewegung-intern/commit/fb5a0b1fac4bce9bfb075c2640d6e890b3868a86))

### [1.2.6](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.2.5...v1.2.6) (2026-11-02)

### [1.2.5](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.2.4...v1.2.5) (2026-10-26)

### [1.2.4](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.2.3...v1.2.4) (2026-10-26)

### [1.2.3](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.2.2...v1.2.3) (2026-10-26)

### [1.2.2](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.2.1...v1.2.2) (2026-05-31)

### [1.2.1](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.2.0...v1.2.1) (2026-05-31)

## [1.2.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.1.1...v1.2.0) (2026-05-31)


### Features

* add bubble layout option and update design templates ([a744e69](https://github.com/starick/kollektiv-bewegung-intern/commit/a744e6995059edb03b2961548942f48e930c9743))
* add foliage template ([25fbdc8](https://github.com/starick/kollektiv-bewegung-intern/commit/25fbdc833d0728115c705e877403816707552afb))
* add footer element with styling ([777c75f](https://github.com/starick/kollektiv-bewegung-intern/commit/777c75f5ee65e911b52b57a4bb67276cdb55a574))
* improve design templates ([e4b0f0d](https://github.com/starick/kollektiv-bewegung-intern/commit/e4b0f0d82cb384e4cedbf3ac48462d77f819a9b7))
* make time tables filterable for own tables only ([5955cc6](https://github.com/starick/kollektiv-bewegung-intern/commit/5955cc6ac163b79f6775d2b2b94214406fe2178c))
* replace html2canvas with html-to-image package. Use the opportunity to udpate dependencies ([e910164](https://github.com/starick/kollektiv-bewegung-intern/commit/e9101647f0080cd2e83a8c7716e1b699255c8ed0))

## [1.1.0](https://github.com/starick/kollektiv-bewegung-intern/compare/v1.1.1...v1.1.0) (2026-05-31)


### Features

* add bubble layout option and update design templates ([a744e69](https://github.com/starick/kollektiv-bewegung-intern/commit/a744e6995059edb03b2961548942f48e930c9743))
* add foliage template ([25fbdc8](https://github.com/starick/kollektiv-bewegung-intern/commit/25fbdc833d0728115c705e877403816707552afb))
* add footer element with styling ([777c75f](https://github.com/starick/kollektiv-bewegung-intern/commit/777c75f5ee65e911b52b57a4bb67276cdb55a574))
* improve design templates ([e4b0f0d](https://github.com/starick/kollektiv-bewegung-intern/commit/e4b0f0d82cb384e4cedbf3ac48462d77f819a9b7))
* make time tables filterable for own tables only ([5955cc6](https://github.com/starick/kollektiv-bewegung-intern/commit/5955cc6ac163b79f6775d2b2b94214406fe2178c))
* replace html2canvas with html-to-image package. Use the opportunity to udpate dependencies ([e910164](https://github.com/starick/kollektiv-bewegung-intern/commit/e9101647f0080cd2e83a8c7716e1b699255c8ed0))
