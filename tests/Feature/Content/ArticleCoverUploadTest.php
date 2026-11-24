<?php

declare(strict_types=1);

namespace Tests\Feature\Content;

use App\Domain\Image\Jobs\DeleteCloudflareImageJob;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Integrations\Cloudflare\Requests\Images\UploadImageRequest;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Laravel\Facades\Saloon;
use Tests\TestCase;

class ArticleCoverUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Queue::fake();
        config(['cloudflare.id_prefix' => 'testing']);

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);
    }

    private function fakeUpload(): MockClient
    {
        return Saloon::fake([
            UploadImageRequest::class => fn (PendingRequest $request): MockResponse => MockResponse::make([
                'success' => true,
                'result' => ['id' => $request->body()->get('id')->value, 'metadata' => []],
            ]),
        ]);
    }

    public function test_cover_is_uploaded_on_create_and_not_stored_on_the_model(): void
    {
        $mockClient = $this->fakeUpload();

        Livewire::test(CreateArticle::class)->fillForm([
            'title' => 'Nieuws', 'slug' => 'nieuws', 'content' => '<p>Hallo</p>',
            'cover_upload' => UploadedFile::fake()->image('cover.jpg', 1200, 600),
        ])->call('create')->assertHasNoFormErrors();

        $article = Article::query()->where('slug', 'nieuws')->firstOrFail();
        $this->assertMatchesRegularExpression('/^testing-article-'.$article->id.'-[a-z0-9]{8}$/', (string) $article->cloudflare_image_id);
        $this->assertSame([], Storage::disk('local')->allFiles('article-covers'));

        $mockClient->assertSent(function (UploadImageRequest $request, $response) use ($article): bool {
            $body = $response->getPendingRequest()->body();

            return $body->get('id')->value === $article->cloudflare_image_id
                && $body->get('file')->filename === basename($body->get('file')->filename)
                && $body->get('file')->value !== ''
                && $body->get('metadata')->value === '{"article_id":"'.$article->id.'"}';
        });
        Queue::assertNotPushed(DeleteCloudflareImageJob::class);
    }

    public function test_replacing_cover_deletes_the_previous_image(): void
    {
        $this->fakeUpload();
        $article = Article::factory()->create(['cloudflare_image_id' => 'testing-article-old']);

        Livewire::test(EditArticle::class, ['record' => $article->slug])
            ->fillForm(['cover_upload' => UploadedFile::fake()->image('new.png', 800, 400)])
            ->call('save')
            ->assertHasNoFormErrors();

        $article->refresh();
        $this->assertNotSame('testing-article-old', $article->cloudflare_image_id);
        $this->assertStringStartsWith("testing-article-{$article->id}-", (string) $article->cloudflare_image_id);
        Queue::assertPushed(DeleteCloudflareImageJob::class, fn (DeleteCloudflareImageJob $job): bool => $job->imageId === 'testing-article-old');
    }

    public function test_cover_can_be_removed(): void
    {
        $mockClient = $this->fakeUpload();
        $article = Article::factory()->create(['cloudflare_image_id' => 'testing-article-old']);

        Livewire::test(EditArticle::class, ['record' => $article->slug])
            ->assertFormFieldExists('remove_cover')
            ->fillForm(['remove_cover' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($article->fresh()->cloudflare_image_id);
        $mockClient->assertNothingSent();
        Queue::assertPushed(DeleteCloudflareImageJob::class, fn (DeleteCloudflareImageJob $job): bool => $job->imageId === 'testing-article-old');
    }

    public function test_saving_without_cover_changes_keeps_the_image(): void
    {
        $mockClient = $this->fakeUpload();
        $article = Article::factory()->create(['cloudflare_image_id' => 'testing-article-old']);

        Livewire::test(EditArticle::class, ['record' => $article->slug])
            ->fillForm(['title' => 'Nieuwe titel'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('testing-article-old', $article->fresh()->cloudflare_image_id);
        $mockClient->assertNothingSent();
        Queue::assertNotPushed(DeleteCloudflareImageJob::class);
    }

    public function test_failed_upload_keeps_the_article_and_notifies(): void
    {
        Saloon::fake([
            UploadImageRequest::class => MockResponse::make(['success' => false, 'errors' => [['code' => 5400, 'message' => 'Bad image']]], 400),
        ]);
        $article = Article::factory()->create(['cloudflare_image_id' => 'testing-article-old']);

        Livewire::test(EditArticle::class, ['record' => $article->slug])
            ->fillForm(['title' => 'Bewaard', 'cover_upload' => UploadedFile::fake()->image('broken.jpg')])
            ->call('save')
            ->assertNotified('Cover image upload failed');

        $article->refresh();
        $this->assertSame('Bewaard', $article->title);
        $this->assertSame('testing-article-old', $article->cloudflare_image_id);
        $this->assertSame([], Storage::disk('local')->allFiles('article-covers'));
        Queue::assertNotPushed(DeleteCloudflareImageJob::class);
    }
}
