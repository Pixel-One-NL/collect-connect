---
type: "query"
date: "2026-06-09T21:56:25.850742+00:00"
question: "Why does ImportInventoryJob connect Inventory Import Jobs to Command Handlers?"
contributor: "graphify"
source_nodes: ["ImportInventoryJob", "ImportInventoryCommand.php", "Command", "Inventory"]
---

# Q: Why does ImportInventoryJob connect Inventory Import Jobs to Command Handlers?

## Answer

The graph traversal (BFS) reveals the connection:

ImportInventoryJob (src=app/Domain/Product/Jobs/ImportInventoryJob.php loc=L19, community=19 "Inventory Import Jobs") is traversed alongside nodes from the Command Handlers community (many in community=8).

Key nodes:
- Command [community=8] (appears as a central hub)
- ImportInventoryCommand.php [src=app/Domain/Product/Commands/ImportInventoryCommand.php loc=L1 community=8]
- SyncPartColorsFromProductsCommand.php and other *Command.php files (community=8)
- Inventory [src=app/Models/Inventory.php loc=L13 community=1]
- Multiple Import*Command and Sync* files under Command namespace.

EDGES (all EXTRACTED with context=import):
- Command --imports--> ImportInventoryCommand.php (and 8+ other import/sync commands)
- Some command files --imports--> specific classes like SyncPartColorsFromProducts, Collection, ImportsRebrickableEntity

This indicates that ImportInventoryJob (the bridge in Inventory Import Jobs) connects to Command Handlers via the "import" relation in the codebase - jobs and commands both participate in the inventory/product/color/minifig import flows, with Command acting as the importing namespace/facade for various entity import commands. The heuristic matched on "import" and "Inventory" terms linking the communities.

## Source Nodes

- ImportInventoryJob
- ImportInventoryCommand.php
- Command
- Inventory