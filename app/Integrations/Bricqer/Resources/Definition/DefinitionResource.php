<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources\Definition;

use App\Integrations\Bricqer\DataTransferObjects\Definition\Definition;
use App\Integrations\Bricqer\Requests\Definition\GetDefinitionRequest;
use App\Integrations\Bricqer\Requests\Definition\ListDefinitionsRequest;
use App\Integrations\Bricqer\Resources\BaseResource;
use Generator;
use Illuminate\Support\LazyCollection;

class DefinitionResource extends BaseResource
{
    public function get(string $definitionId): Definition
    {
        return $this->connector->send(new GetDefinitionRequest($definitionId))->dtoOrFail();
    }

    /**
     * Lazily fetch every definition across all pages.
     *
     * Each page is requested on demand via the `?page` query parameter and the
     * generator yields one {@see Definition} at a time, so the full ~31K item
     * data set is never materialised in memory at once. Pagination stops when
     * the API reports no `next` link.
     *
     * @return LazyCollection<int, Definition>
     */
    public function list(): LazyCollection
    {
        return LazyCollection::make(function (): Generator {
            $page = 1;

            do {
                $response = $this->connector->send(new ListDefinitionsRequest($page));
                $payload = $response->json();

                foreach ($payload['results'] ?? [] as $result) {
                    yield Definition::from($result);
                }

                $hasNextPage = ($payload['page']['links']['next'] ?? null) !== null;
                $page++;
            } while ($hasNextPage);
        });
    }
}
