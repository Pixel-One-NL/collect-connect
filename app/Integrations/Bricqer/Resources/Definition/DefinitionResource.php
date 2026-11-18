<?php

declare(strict_types=1);

namespace App\Integrations\Bricqer\Resources\Definition;

use App\Integrations\Bricqer\DataTransferObjects\Definition\Definition;
use App\Integrations\Bricqer\Requests\Definition\GetDefinitionPictureRequest;
use App\Integrations\Bricqer\Requests\Definition\GetDefinitionRequest;
use App\Integrations\Bricqer\Requests\Definition\ListDefinitionsRequest;
use App\Integrations\Bricqer\Resources\BaseResource;
use Generator;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use RuntimeException;
use Saloon\Exceptions\Request\Statuses\NotFoundException;

class DefinitionResource extends BaseResource
{
    public function get(string $definitionId): Definition
    {
        return $this->connector->send(new GetDefinitionRequest($definitionId))->dtoOrFail();
    }

    /**
     * Find the public image behind a definition's login protected picture path.
     *
     * The target can't be derived from the path: part colors map to another
     * color id on another host, so Bricqer's redirect has to be read.
     *
     * @return string|null Absolute URL, or null when Bricqer has no picture
     *
     * @throws RuntimeException When the redirect isn't public, e.g. the login page after a rejected API key
     */
    public function resolvePictureUrl(string $picturePath): ?string
    {
        try {
            $location = $this->connector->send(new GetDefinitionPictureRequest($picturePath))->header('Location');
        } catch (NotFoundException) {
            return null;
        }

        if (! is_string($location) || $location === '') {
            return null;
        }

        if (! Str::startsWith($location, ['https://', 'http://'])) {
            throw new RuntimeException("Bricqer picture {$picturePath} redirected to non-public location {$location}.");
        }

        return $location;
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
