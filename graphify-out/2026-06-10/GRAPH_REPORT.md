# Graph Report - .  (2026-06-09)

## Corpus Check
- Narrowed corpus (user choice): 215 files · ~25,333 words. Code paths: app/, resources/, database/, tests/. (Skipped storage/ with 148k+ images.)

## Summary
- 1010 nodes · 1551 edges · 75 communities (62 shown, 13 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 44 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

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

## God Nodes (most connected - your core abstractions)
1. `ImportColorLdrawImagesTest` - 29 edges
2. `TestCase` - 26 edges
3. `BaseImportService` - 20 edges
4. `BaseImportMapper` - 19 edges
5. `ImportColorBricqerImagesTest` - 18 edges
6. `ImportInventoryJob` - 16 edges
7. `ImportInventoryJobTest` - 15 edges
8. `ImportColorLdrawImagesJob` - 14 edges
9. `ImportPartBricqerImageUrlsJobTest` - 13 edges
10. `PaginatedResource` - 12 edges

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

## Communities (75 total, 13 thin omitted)

### Community 0 - "Test Cases"
Cohesion: 0.06
Nodes (20): SearchControllerTest, self, BaseTestCase, ImportBrickerColorsJobTest, ExampleTest, ListPartsRequest, ImportPartBricklinkNumbersJobTest, ImportPartBricqerImageUrlsJobTest (+12 more)

### Community 1 - "Eloquent Relationships"
Cohesion: 0.05
Nodes (30): HasMany, BelongsToMany, BelongsToMany, MorphOne, BelongsTo, BelongsToMany, HasMany, MorphOne (+22 more)

### Community 2 - "Command Handlers"
Cohesion: 0.08
Nodes (24): RebrickableDownloader, Builder, Color, Part, RebrickableDownloader, Collection, Batchable, BricqerApi (+16 more)

### Community 3 - "React Cart Components"
Cohesion: 0.05
Nodes (9): CartDrawer(), CartItem(), useCart(), CartIcon(), insetFromOrigin(), SearchOverlay(), euro, searchProducts() (+1 more)

### Community 4 - "Filament Schemas & Tables"
Cohesion: 0.07
Nodes (21): BackedEnum, Schema, Table, Schema, TextColumn, Table, BackedEnum, Schema (+13 more)

### Community 5 - "Saloon API Responses"
Cohesion: 0.08
Nodes (16): Collection, Method, Response, LazyCollection, Method, Response, LegoVisualPage, Method (+8 more)

### Community 6 - "Blog & Article Controllers"
Cohesion: 0.07
Nodes (15): Article, BlogController, static, static, DateTimeInterface, ArticleFactory, ColorFactory, InventoryFactory (+7 more)

### Community 7 - "Filament Resources"
Cohesion: 0.07
Nodes (16): BricqerApi, CreateRecord, EditRecord, InventoryResource, LegoResource, LegoVisualResource, ListRecords, CreateInventory (+8 more)

### Community 8 - "Color Sync Jobs"
Cohesion: 0.08
Nodes (15): Collection, Collection, SyncPartColorsFromProducts, SyncPartColorsFromProducts, Command, ImportBrickerColorsCommand, ImportInventoryCommand, ImportMinifigBricklinkNumbersCommand (+7 more)

### Community 9 - "Report & Inventory Resources"
Cohesion: 0.08
Nodes (17): Collection, LazyCollection, LegoVisualPage, self, BaseResource, ReportResource, LegoVisualItemsResource, LegoVisualItemsResource (+9 more)

### Community 10 - "Product DTOs & Requests"
Cohesion: 0.11
Nodes (16): AnonymousResourceCollection, SearchController, Request, Product, Request, Product, Response, Request (+8 more)

### Community 12 - "Pagination Helpers"
Cohesion: 0.12
Nodes (9): PaginatedCollection, PaginatedRequest, static, self, self, ListPartColorsRequest, ListPartColorsResource, ListPartsResource (+1 more)

### Community 13 - "Service Providers"
Cohesion: 0.10
Nodes (9): Generator, BricqerServiceProvider, AppServiceProvider, RebrickableServiceProvider, RebrickableDownloadServiceTest, RebrickableServiceProvider, RebrickableDownloader, ServiceProvider (+1 more)

### Community 14 - "Filament Inventory Tables"
Cohesion: 0.12
Nodes (13): Table, Table, TextColumn, InventoriesRelationManager, Table, TextColumn, InventoriesRelationManager, Table (+5 more)

### Community 15 - "Field Transformers"
Cohesion: 0.12
Nodes (12): Expression, Expression, Expression, Expression, Expression, RebrickableFieldTransformer, BooleanTransformer, ColorIdExpressionTransformer (+4 more)

### Community 16 - "Inventory Eloquent Relations"
Cohesion: 0.13
Nodes (11): BelongsTo, BelongsTo, BelongsTo, Media, HasMedia, InteractsWithMedia, PartColorFactory, Pivot (+3 more)

### Community 17 - "DTO Builders"
Cohesion: 0.14
Nodes (9): PaginatedCollection, Response, PaginatedCollection, Response, PaginatedRequest, ListPartColorsRequest, ListPartsRequest, RebrickableResponse (+1 more)

### Community 18 - "Part Data Models"
Cohesion: 0.16
Nodes (10): self, Collection, Data, Part, PartColor, Color, LegoVisualItem, LegoVisualPage (+2 more)

### Community 19 - "Inventory Import Jobs"
Cohesion: 0.22
Nodes (3): ImportInventoryJob, ImportInventoryJobTest, Color

### Community 20 - "Bricqer Image Import Tests"
Cohesion: 0.19
Nodes (3): ImportColorBricqerImagesTest, PendingBatch, RebrickableDownloader

### Community 21 - "Import Mappers"
Cohesion: 0.16
Nodes (8): ColorImportMapper, InventoryImportMapper, InventoryMinifigImportMapper, InventoryPartImportMapper, MinifigImportMapper, PartCategoryImportMapper, PartImportMapper, BaseImportMapper

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
Cohesion: 0.21
Nodes (6): TextColumn, TextColumn, Table, MinifigBricklinkIdTextColumn, MinifigNameTextColumn, MinifigsTable

### Community 26 - "Category Select Inputs"
Cohesion: 0.21
Nodes (6): Schema, PartCategorySelect, PartNameTextInput, PartForm, Select, TextInput

### Community 29 - "Inertia Middleware"
Cohesion: 0.53
Nodes (3): Request, Middleware, HandleInertiaRequests

### Community 32 - "Rebrickable Downloader"
Cohesion: 0.60
Nodes (3): Generator, retrieveRebrickableDataFromUrl(), retrieveZipEntriesFromUrl()

### Community 33 - "Article Resource"
Cohesion: 0.60
Nodes (3): Request, ResourceCollection, ArticleResource

### Community 36 - "Dashboard Panel Provider"
Cohesion: 0.60
Nodes (3): DashboardPanelProvider, Panel, PanelProvider

## Knowledge Gaps
- **8 isolated node(s):** `Collection`, `SupportCollection`, `self`, `self`, `self` (+3 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **13 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `Test Cases` to `Inventory Import Jobs`, `Color Image Import Tests`, `Bricqer Image Import Tests`, `Service Providers`?**
  _High betweenness centrality (0.082) - this node is a cross-community bridge._
- **Why does `ImportInventoryJob` connect `Inventory Import Jobs` to `Command Handlers`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **What connects `Collection`, `SupportCollection`, `self` to the rest of the system?**
  _8 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Test Cases` be split into smaller, more focused modules?**
  _Cohesion score 0.05593561368209256 - nodes in this community are weakly interconnected._
- **Should `Eloquent Relationships` be split into smaller, more focused modules?**
  _Cohesion score 0.050203527815468114 - nodes in this community are weakly interconnected._
- **Should `Command Handlers` be split into smaller, more focused modules?**
  _Cohesion score 0.07688492063492064 - nodes in this community are weakly interconnected._
- **Should `React Cart Components` be split into smaller, more focused modules?**
  _Cohesion score 0.054901960784313725 - nodes in this community are weakly interconnected._