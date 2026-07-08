# Graph Report - collect-connect  (2026-06-13)

## Corpus Check
- 425 files · ~830,428,064 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1607 nodes · 1912 edges · 295 communities (281 shown, 14 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 40 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `19ba92aa`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- [[_COMMUNITY_Test Cases|Test Cases]]
- [[_COMMUNITY_Eloquent Relationships|Eloquent Relationships]]
- [[_COMMUNITY_Command Handlers|Command Handlers]]
- [[_COMMUNITY_React Cart Components|React Cart Components]]
- [[_COMMUNITY_Filament Schemas & Tables|Filament Schemas & Tables]]
- [[_COMMUNITY_Saloon API Responses|Saloon API Responses]]
- [[_COMMUNITY_Blog & Article Controllers|Blog & Article Controllers]]
- [[_COMMUNITY_Filament Resources|Filament Resources]]
- [[_COMMUNITY_Color Sync Jobs|Color Sync Jobs]]
- [[_COMMUNITY_Report & Inventory Resources|Report & Inventory Resources]]
- [[_COMMUNITY_Product DTOs & Requests|Product DTOs & Requests]]
- [[_COMMUNITY_Color Image Import Tests|Color Image Import Tests]]
- [[_COMMUNITY_Pagination Helpers|Pagination Helpers]]
- [[_COMMUNITY_Service Providers|Service Providers]]
- [[_COMMUNITY_Filament Inventory Tables|Filament Inventory Tables]]
- [[_COMMUNITY_Field Transformers|Field Transformers]]
- [[_COMMUNITY_Inventory Eloquent Relations|Inventory Eloquent Relations]]
- [[_COMMUNITY_DTO Builders|DTO Builders]]
- [[_COMMUNITY_Part Data Models|Part Data Models]]
- [[_COMMUNITY_Inventory Import Jobs|Inventory Import Jobs]]
- [[_COMMUNITY_Bricqer Image Import Tests|Bricqer Image Import Tests]]
- [[_COMMUNITY_Import Mappers|Import Mappers]]
- [[_COMMUNITY_Category Table Columns|Category Table Columns]]
- [[_COMMUNITY_Cart Services & Collections|Cart Services & Collections]]
- [[_COMMUNITY_Rebrickable Connector|Rebrickable Connector]]
- [[_COMMUNITY_Minifig Table Columns|Minifig Table Columns]]
- [[_COMMUNITY_Category Select Inputs|Category Select Inputs]]
- [[_COMMUNITY_Paginated Collections|Paginated Collections]]
- [[_COMMUNITY_Base Import Mapper|Base Import Mapper]]
- [[_COMMUNITY_Inertia Middleware|Inertia Middleware]]
- [[_COMMUNITY_Minifig Import Service|Minifig Import Service]]
- [[_COMMUNITY_Part Import Service|Part Import Service]]
- [[_COMMUNITY_Rebrickable Downloader|Rebrickable Downloader]]
- [[_COMMUNITY_Article Resource|Article Resource]]
- [[_COMMUNITY_Base API Resources|Base API Resources]]
- [[_COMMUNITY_Base API Resources|Base API Resources]]
- [[_COMMUNITY_Dashboard Panel Provider|Dashboard Panel Provider]]
- [[_COMMUNITY_Color Import Service|Color Import Service]]
- [[_COMMUNITY_Inventory Import Service|Inventory Import Service]]
- [[_COMMUNITY_Minifig Import Service|Minifig Import Service]]
- [[_COMMUNITY_Category Import Service|Category Import Service]]
- [[_COMMUNITY_Part Import Service|Part Import Service]]
- [[_COMMUNITY_Color Sync Executor|Color Sync Executor]]
- [[_COMMUNITY_Community 75|Community 75]]
- [[_COMMUNITY_Community 76|Community 76]]
- [[_COMMUNITY_Community 77|Community 77]]
- [[_COMMUNITY_Community 78|Community 78]]
- [[_COMMUNITY_Community 79|Community 79]]
- [[_COMMUNITY_Community 80|Community 80]]
- [[_COMMUNITY_Community 81|Community 81]]
- [[_COMMUNITY_Community 82|Community 82]]
- [[_COMMUNITY_Community 83|Community 83]]
- [[_COMMUNITY_Community 84|Community 84]]
- [[_COMMUNITY_Community 85|Community 85]]
- [[_COMMUNITY_Community 86|Community 86]]
- [[_COMMUNITY_Community 87|Community 87]]
- [[_COMMUNITY_Community 88|Community 88]]
- [[_COMMUNITY_Community 89|Community 89]]
- [[_COMMUNITY_Community 90|Community 90]]
- [[_COMMUNITY_Community 91|Community 91]]
- [[_COMMUNITY_Community 92|Community 92]]
- [[_COMMUNITY_Community 93|Community 93]]
- [[_COMMUNITY_Community 94|Community 94]]
- [[_COMMUNITY_Community 95|Community 95]]
- [[_COMMUNITY_Community 96|Community 96]]
- [[_COMMUNITY_Community 97|Community 97]]
- [[_COMMUNITY_Community 98|Community 98]]
- [[_COMMUNITY_Community 99|Community 99]]
- [[_COMMUNITY_Community 100|Community 100]]
- [[_COMMUNITY_Community 101|Community 101]]
- [[_COMMUNITY_Community 102|Community 102]]
- [[_COMMUNITY_Community 103|Community 103]]
- [[_COMMUNITY_Community 104|Community 104]]
- [[_COMMUNITY_Community 105|Community 105]]
- [[_COMMUNITY_Community 106|Community 106]]
- [[_COMMUNITY_Community 107|Community 107]]
- [[_COMMUNITY_Community 108|Community 108]]
- [[_COMMUNITY_Community 109|Community 109]]
- [[_COMMUNITY_Community 110|Community 110]]
- [[_COMMUNITY_Community 111|Community 111]]
- [[_COMMUNITY_Community 112|Community 112]]
- [[_COMMUNITY_Community 113|Community 113]]
- [[_COMMUNITY_Community 114|Community 114]]
- [[_COMMUNITY_Community 115|Community 115]]
- [[_COMMUNITY_Community 116|Community 116]]
- [[_COMMUNITY_Community 288|Community 288]]
- [[_COMMUNITY_Community 289|Community 289]]
- [[_COMMUNITY_Community 290|Community 290]]
- [[_COMMUNITY_Community 291|Community 291]]
- [[_COMMUNITY_Community 292|Community 292]]

## God Nodes (most connected - your core abstractions)
1. `TestCase` - 24 edges
2. `BaseImportService` - 20 edges
3. `DiscoverBricqerImageUrlsJobTest` - 20 edges
4. `SyncBricqerInventoryJobTest` - 20 edges
5. `Quick Reference` - 20 edges
6. `BaseImportMapper` - 19 edges
7. `Usage` - 16 edges
8. `require` - 15 edges
9. `AttachBricqerImagesJobTest` - 15 edges
10. `AttachBricqerImagesJob` - 14 edges

## Surprising Connections (you probably didn't know these)
- `ColorImportMapper` --inherits--> `BaseImportMapper`  [EXTRACTED]
  app/Domain/Rebrickable/Mappers/Imports/ColorImportMapper.php → app/Domain/Rebrickable/Mappers/BaseImportMapper.php
- `InventoryImportMapper` --inherits--> `BaseImportMapper`  [EXTRACTED]
  app/Domain/Rebrickable/Mappers/Imports/InventoryImportMapper.php → app/Domain/Rebrickable/Mappers/BaseImportMapper.php
- `InventoryMinifigImportMapper` --inherits--> `BaseImportMapper`  [EXTRACTED]
  app/Domain/Rebrickable/Mappers/Imports/InventoryMinifigImportMapper.php → app/Domain/Rebrickable/Mappers/BaseImportMapper.php
- `InventoryPartImportMapper` --inherits--> `BaseImportMapper`  [EXTRACTED]
  app/Domain/Rebrickable/Mappers/Imports/InventoryPartImportMapper.php → app/Domain/Rebrickable/Mappers/BaseImportMapper.php
- `MinifigImportMapper` --inherits--> `BaseImportMapper`  [EXTRACTED]
  app/Domain/Rebrickable/Mappers/Imports/MinifigImportMapper.php → app/Domain/Rebrickable/Mappers/BaseImportMapper.php

## Import Cycles
- None detected.

## Communities (295 total, 14 thin omitted)

### Community 0 - "Test Cases"
Cohesion: 0.07
Nodes (18): SearchControllerTest, BaseTestCase, AttachBricqerImagesJobTest, SyncBricqerCommandTest, ImportBrickerColorsJobTest, ExampleTest, ProductColorImageTest, SyncPartColorsFromProductsTest (+10 more)

### Community 1 - "Eloquent Relationships"
Cohesion: 0.05
Nodes (30): HasMany, BelongsToMany, BelongsToMany, MorphOne, BelongsTo, BelongsToMany, HasMany, MorphOne (+22 more)

### Community 2 - "Command Handlers"
Cohesion: 0.30
Nodes (7): Dispatchable, InteractsWithQueue, ImportBrickerColorsJob, ImportMinifigBricklinkNumbersJob, ImportRebrickableEntityJob, Queueable, ShouldQueue

### Community 3 - "React Cart Components"
Cohesion: 0.05
Nodes (9): CartDrawer(), CartItem(), useCart(), CartIcon(), insetFromOrigin(), SearchOverlay(), euro, searchProducts() (+1 more)

### Community 4 - "Filament Schemas & Tables"
Cohesion: 0.21
Nodes (6): TextColumn, TextColumn, Table, MinifigBricklinkIdTextColumn, MinifigNameTextColumn, MinifigsTable

### Community 5 - "Saloon API Responses"
Cohesion: 0.07
Nodes (16): Collection, Method, Response, LazyCollection, Method, Response, LegoVisualPage, Method (+8 more)

### Community 6 - "Blog & Article Controllers"
Cohesion: 0.09
Nodes (11): static, ArticleFactory, ColorFactory, InventoryFactory, MinifigFactory, PartCategoryFactory, PartColorFactory, PartFactory (+3 more)

### Community 7 - "Filament Resources"
Cohesion: 0.21
Nodes (4): ListRecords, ListInventories, ListMinifigs, ListParts

### Community 8 - "Color Sync Jobs"
Cohesion: 0.15
Nodes (7): Command, ImportBrickerColorsCommand, ImportMinifigBricklinkNumbersCommand, ImportPartBricklinkNumbersCommand, SyncBricqerCommand, SyncPartColorsFromProductsCommand, SyncPartColorsFromProducts

### Community 9 - "Report & Inventory Resources"
Cohesion: 0.07
Nodes (19): Collection, LazyCollection, LegoVisualPage, self, BaseResource, ReportResource, LegoVisualItemsResource, LegoVisualItemsResource (+11 more)

### Community 10 - "Product DTOs & Requests"
Cohesion: 0.09
Nodes (18): AnonymousResourceCollection, SearchController, Request, Product, Request, Product, Response, Request (+10 more)

### Community 11 - "Color Image Import Tests"
Cohesion: 0.06
Nodes (34): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Artisan, Common Mistakes, Conventions, Correct Namespaces, Deployment (+26 more)

### Community 12 - "Pagination Helpers"
Cohesion: 0.10
Nodes (10): PaginatedCollection, PaginatedRequest, static, self, self, ListPartColorsRequest, ListPartsRequest, ListPartColorsResource (+2 more)

### Community 13 - "Service Providers"
Cohesion: 0.07
Nodes (17): RebrickableMapper, Generator, BricqerServiceProvider, ColorImportMapper, InventoryImportMapper, InventoryMinifigImportMapper, InventoryPartImportMapper, MinifigImportMapper (+9 more)

### Community 14 - "Filament Inventory Tables"
Cohesion: 0.07
Nodes (21): Table, Table, TextColumn, InventoriesRelationManager, Table, TextColumn, InventoriesRelationManager, Table (+13 more)

### Community 15 - "Field Transformers"
Cohesion: 0.12
Nodes (12): Expression, Expression, Expression, Expression, Expression, RebrickableFieldTransformer, BooleanTransformer, ColorIdExpressionTransformer (+4 more)

### Community 16 - "Inventory Eloquent Relations"
Cohesion: 0.13
Nodes (11): BelongsTo, BelongsTo, BelongsTo, Media, HasMedia, InteractsWithMedia, PartColorFactory, Pivot (+3 more)

### Community 17 - "DTO Builders"
Cohesion: 0.17
Nodes (7): PaginatedCollection, Response, PaginatedCollection, Response, PaginatedRequest, ListPartColorsRequest, ListPartsRequest

### Community 18 - "Part Data Models"
Cohesion: 0.16
Nodes (10): self, Collection, Data, Part, PartColor, Color, LegoVisualItem, LegoVisualPage (+2 more)

### Community 19 - "Inventory Import Jobs"
Cohesion: 0.15
Nodes (8): self, DiscoverBricqerImageUrlsJobTest, ImportPartBricklinkNumbersJobTest, PartsResource, RebrickableApi, Saloon, Color, PartColor

### Community 21 - "Import Mappers"
Cohesion: 0.07
Nodes (27): Access Itemable, Available Events, Changelog, Configuration, Contributors, Create Cart With Storing Item, Decrease Quantity, Delete All Items From Cart (+19 more)

### Community 22 - "Category Table Columns"
Cohesion: 0.16
Nodes (8): TextColumn, TextColumn, Table, PartCategoryNameTextColumn, PartNameTextColumn, PartCategorySelectFilter, SelectFilter, PartsTable

### Community 23 - "Cart Services & Collections"
Cohesion: 0.33
Nodes (4): Collection, Product, Driver, CartService

### Community 24 - "Rebrickable Connector"
Cohesion: 0.21
Nodes (5): AlwaysThrowOnErrors, BricqerConnector, Connector, HeaderAuthenticator, RebrickableConnector

### Community 25 - "Minifig Table Columns"
Cohesion: 0.08
Nodes (23): 10. Routing & Controllers → `rules/routing.md`, 11. HTTP Client → `rules/http-client.md`, 12. Events, Notifications & Mail → `rules/events-notifications.md`, `rules/mail.md`, 13. Error Handling → `rules/error-handling.md`, 14. Task Scheduling → `rules/scheduling.md`, 15. Architecture → `rules/architecture.md`, 16. Migrations → `rules/migrations.md`, 17. Collections → `rules/collections.md` (+15 more)

### Community 26 - "Category Select Inputs"
Cohesion: 0.21
Nodes (6): Schema, PartCategorySelect, PartNameTextInput, PartForm, Select, TextInput

### Community 28 - "Base Import Mapper"
Cohesion: 0.08
Nodes (23): 1. Add download timeout config to `config/rebrickable.php`, 1. Update `PartColor::registerMediaConversions()`, 2. (No changes) Storage / job / consumers, 2. Stream the download to disk in `retrieveZipEntriesFromUrl()`, 3. Stream-attach in `ImportColorLdrawImagesJob` (bounded memory), Current State (for reference), Decisions (confirmed with user), Decisions (confirmed with user) (+15 more)

### Community 29 - "Inertia Middleware"
Cohesion: 0.43
Nodes (3): Request, Middleware, HandleInertiaRequests

### Community 30 - "Minifig Import Service"
Cohesion: 0.06
Nodes (8): ColorImportService, InventoryImportService, InventoryMinifigImportService, InventoryPartImportService, MinifigImportService, PartCategoryImportService, PartImportService, BaseImportService

### Community 31 - "Part Import Service"
Cohesion: 0.09
Nodes (21): dependencies, gsap, @inertiajs/react, @inertiajs/vite, @phosphor-icons/react, react, react-dom, @vitejs/plugin-react (+13 more)

### Community 33 - "Article Resource"
Cohesion: 0.60
Nodes (3): Request, ResourceCollection, ArticleResource

### Community 36 - "Dashboard Panel Provider"
Cohesion: 0.60
Nodes (3): DashboardPanelProvider, Panel, PanelProvider

### Community 37 - "Color Import Service"
Cohesion: 0.11
Nodes (17): 1. Schema change: add `parts.ldraw_id`, 2. Populate `parts.ldraw_id` via the existing Bricklink job, 3. Pre-generate `part_colors` from existing products, 4. Switch LDraw image matching to use `ldraw_id`, 5. Order of operations / scheduling, 6. Files touched (summary), 7. Open questions for the user (none blocking), `app/Domain/Part/Commands/ImportPartColorImagesCommand.php` (+9 more)

### Community 38 - "Inventory Import Service"
Cohesion: 0.12
Nodes (16): 1. Migration: track LDraw import freshness on `colors`, 2. `PartColor` — add the `ldraw` media collection, 3. New download capability for LDraw ZIPs (binary entries), 4. New per-color job: `ImportColorLdrawImagesJob`, 5. Dispatcher: command + a dispatcher job, 6. End-of-run logging summary, 7. Remove / replace old CSV image logic, 8. Tests (+8 more)

### Community 39 - "Minifig Import Service"
Cohesion: 0.12
Nodes (16): 1. Schema change: `parts.bricqer_img_url`, 2. Bricqer integration: `LegoVisualResource` + `ListItemsRequest`, 3. Job + command: walk all pages, fill `parts.bricqer_img_url`, 4. Per-color attach pass: Bricqer image bytes -> `BRICQER_IMAGE_COLLECTION`, 5. Orchestration / order of operations, 6. Files touched (summary), 7. Risk / open notes (non-blocking), `app/Domain/Part/Commands/ImportPartBricqerImageUrlsCommand.php` (new) (+8 more)

### Community 40 - "Category Import Service"
Cohesion: 0.13
Nodes (15): require, binafy/laravel-cart, ext-intl, ext-zip, filament/filament, inertiajs/inertia-laravel, laravel/framework, laravel/scout (+7 more)

### Community 41 - "Part Import Service"
Cohesion: 0.24
Nodes (4): GenerateSignedUploadUrl, Facade, Bricqer, Rebrickable

### Community 75 - "Community 75"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 76 - "Community 76"
Cohesion: 0.17
Nodes (11): Audit Dependencies, Authorize Every Action, CSRF Protection, Encrypt Sensitive Database Fields, Escape Output to Prevent XSS, Keep Secrets Out of Code, Mass Assignment Protection, Prevent SQL Injection (+3 more)

### Community 77 - "Community 77"
Cohesion: 0.17
Nodes (11): Available Documentation, Common Pitfalls, Digging Deeper, Documentation, Features, File Structure, Installable Plugins, SaloonPHP Development (+3 more)

### Community 78 - "Community 78"
Cohesion: 0.18
Nodes (10): Always Implement `failed()`, Batch Related Jobs, Implement `ShouldBeUnique`, Queue & Job Best Practices, Rate Limit External API Calls in Jobs, `retryUntil()` Needs `$tries = 0`, Set `retry_after` Greater Than `timeout`, Use Exponential Backoff (+2 more)

### Community 79 - "Community 79"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pint, mockery/mockery, nunomaduro/collision (+2 more)

### Community 80 - "Community 80"
Cohesion: 0.20
Nodes (9): Advanced Query Patterns, Create Dynamic Relationships via Subquery FK, Prefer `whereIn` + Subquery Over `whereHas`, Sometimes Two Simple Queries Beat One Complex Query, Use `addSelect()` Subqueries for Single Values from Has-Many, Use Compound Indexes Matching `orderBy` Column Order, Use Conditional Aggregates Instead of Multiple Count Queries, Use Correlated Subqueries for Has-Many Ordering (+1 more)

### Community 81 - "Community 81"
Cohesion: 0.20
Nodes (9): Add Database Indexes, Always Eager Load Relationships, Chunk Large Datasets, Database Performance Best Practices, No Queries in Blade Templates, Prevent Lazy Loading in Development, Select Only Needed Columns, Use `cursor()` for Memory-Efficient Iteration (+1 more)

### Community 82 - "Community 82"
Cohesion: 0.20
Nodes (9): Always Queue Notifications, Events & Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Run `event:cache` in Production Deploy, Use `afterCommit()` on Notifications in Transactions, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 83 - "Community 83"
Cohesion: 0.22
Nodes (8): agents, cloud, guidelines, mcp, nightwatch, packages, sail, skills

### Community 84 - "Community 84"
Cohesion: 0.22
Nodes (8): preset, rules, array_indentation, blank_line_after_namespace, blank_lines_before_namespace, declare_strict_types, fully_qualified_strict_types, global_namespace_import

### Community 85 - "Community 85"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::memo()` to Avoid Redundant Hits Within a Request, Use `Cache::remember()` Instead of Manual Get/Put, Use Cache Tags to Invalidate Related Groups, Use `once()` for Per-Request Memoization

### Community 86 - "Community 86"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Avoid Hardcoded Table Names in Queries, Cast Date Columns Properly, Define Attribute Casts, Eloquent Best Practices, Use Correct Relationship Types, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 87 - "Community 87"
Cohesion: 0.22
Nodes (8): Add Indexes in the Migration, Generate Migrations with Artisan, Keep Migrations Focused, Migration Best Practices, Mirror Defaults in Model `$attributes`, Never Modify Deployed Migrations, Use `constrained()` for Foreign Keys, Write Reversible `down()` Methods by Default

### Community 88 - "Community 88"
Cohesion: 0.25
Nodes (7): autoload-dev, psr-4, minimum-stability, name, prefer-stable, Tests\\, type

### Community 89 - "Community 89"
Cohesion: 0.25
Nodes (7): Blade & Views Best Practices, Prefer Blade Components Over `@include`, Use `$attributes->merge()` in Component Templates, Use `@aware` for Deeply Nested Component Props, Use Blade Fragments for Partial Re-Renders (htmx/Turbo), Use `@pushOnce` for Per-Component Scripts, Use View Composers for Shared View Data

### Community 90 - "Community 90"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Enable `dontReportDuplicates()`, Error Handling Best Practices, Exception Reporting and Rendering, Force JSON Error Rendering for API Routes, Throttle High-Volume Exceptions, Use `ShouldntReport` for Exceptions That Should Never Log

### Community 91 - "Community 91"
Cohesion: 0.25
Nodes (7): Task Scheduling Best Practices, Use `environments()` to Restrict Tasks, Use `onOneServer()` on Multi-Server Deployments, Use `runInBackground()` for Concurrent Long Tasks, Use Schedule Groups for Shared Configuration, Use `takeUntilTimeout()` for Time-Bounded Processing, Use `withoutOverlapping()` on Variable-Duration Tasks

### Community 92 - "Community 92"
Cohesion: 0.25
Nodes (7): Call `Event::fake()` After Factory Setup, Testing Best Practices, Use `Exceptions::fake()` to Assert Exception Reporting, Use Factory States and Sequences, Use `LazilyRefreshDatabase` Over `RefreshDatabase`, Use Model Assertions Over Raw Database Assertions, Use `recycle()` to Share Relationship Instances Across Factories

### Community 93 - "Community 93"
Cohesion: 0.31
Nodes (3): Builder, Batchable, AttachBricqerImagesJob

### Community 94 - "Community 94"
Cohesion: 0.43
Nodes (3): Collection, ImportPartBricklinkNumbersJob, SupportCollection

### Community 95 - "Community 95"
Cohesion: 0.29
Nodes (6): Collect2Connect Webshop, Create a alias for the ssu command (optional), Installation, Introduction, Simple Setup Utility (SSU command), Usage

### Community 96 - "Community 96"
Cohesion: 0.29
Nodes (6): Choose `cursor()` vs. `lazy()` Correctly, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 97 - "Community 97"
Cohesion: 0.29
Nodes (6): Always Set Explicit Timeouts, Fake HTTP Calls in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Use Request Pooling for Concurrent Requests, Use Retry with Backoff for External APIs

### Community 98 - "Community 98"
Cohesion: 0.29
Nodes (6): Implement `ShouldQueue` on the Mailable Class, Mail Best Practices, Separate Content Tests from Sending Tests, Use `afterCommit()` on Mailables Inside Transactions, Use `assertQueued()` Not `assertSent()` for Queued Mailables, Use Markdown Mailables for Transactional Emails

### Community 99 - "Community 99"
Cohesion: 0.29
Nodes (6): Keep Controllers Thin, Routing & Controllers Best Practices, Type-Hint Form Requests, Use Implicit Route Model Binding, Use Resource Controllers, Use Scoped Bindings for Nested Resources

### Community 100 - "Community 100"
Cohesion: 0.29
Nodes (6): Conventions & Style, Follow Laravel Naming Conventions, No Inline JS/CSS in Blade, No Unnecessary Comments, Prefer Shorter Readable Syntax, Use Laravel String & Array Helpers

### Community 101 - "Community 101"
Cohesion: 0.29
Nodes (6): Always Use `validated()`, Array vs. String Notation for Rules, Use Form Request Classes, Use `Rule::when()` for Conditional Validation, Use the `after()` Method for Custom Validation, Validation & Forms Best Practices

### Community 102 - "Community 102"
Cohesion: 0.33
Nodes (6): php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 103 - "Community 103"
Cohesion: 0.33
Nodes (6): scripts, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall

### Community 104 - "Community 104"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, `env()` Only in Config Files, Use `App::environment()` for Environment Checks, Use Constants and Language Files, Use Encrypted Env or External Secrets

### Community 105 - "Community 105"
Cohesion: 0.40
Nodes (4): enableAllProjectMcpServers, enabledMcpjsonServers, permissions, allow

### Community 107 - "Community 107"
Cohesion: 0.29
Nodes (5): Builder, LegoVisualPage, BricqerApi, DiscoverBricqerImageUrlsJob, Throwable

### Community 108 - "Community 108"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 109 - "Community 109"
Cohesion: 0.24
Nodes (4): EditRecord, EditInventory, EditMinifig, EditPart

### Community 110 - "Community 110"
Cohesion: 0.33
Nodes (5): BackedEnum, Schema, Table, MinifigResource, Resource

### Community 111 - "Community 111"
Cohesion: 0.50
Nodes (3): Answer, Q: Why does ImportInventoryJob connect Inventory Import Jobs to Command Handlers?, Source Nodes

### Community 115 - "Community 115"
Cohesion: 0.36
Nodes (4): BackedEnum, Schema, Table, InventoryResource

### Community 288 - "Community 288"
Cohesion: 0.32
Nodes (4): TextColumn, Table, InventoryRebrickableIdTextColumn, InventoriesTable

### Community 289 - "Community 289"
Cohesion: 0.36
Nodes (4): BackedEnum, Schema, Table, PartResource

## Knowledge Gaps
- **364 isolated node(s):** `PreToolUse`, `allow`, `enableAllProjectMcpServers`, `enabledMcpjsonServers`, `@kilocode/plugin` (+359 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **14 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `BaseImportService` connect `Minifig Import Service` to `Community 106`, `Service Providers`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Why does `Throwable` connect `Community 107` to `Report & Inventory Resources`, `Saloon API Responses`, `Community 93`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Why does `TestCase` connect `Test Cases` to `Color Sync Jobs`, `Inventory Import Jobs`, `Bricqer Image Import Tests`, `Pagination Helpers`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **What connects `PreToolUse`, `allow`, `enableAllProjectMcpServers` to the rest of the system?**
  _364 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Test Cases` be split into smaller, more focused modules?**
  _Cohesion score 0.06502732240437159 - nodes in this community are weakly interconnected._
- **Should `Eloquent Relationships` be split into smaller, more focused modules?**
  _Cohesion score 0.050203527815468114 - nodes in this community are weakly interconnected._
- **Should `React Cart Components` be split into smaller, more focused modules?**
  _Cohesion score 0.054901960784313725 - nodes in this community are weakly interconnected._