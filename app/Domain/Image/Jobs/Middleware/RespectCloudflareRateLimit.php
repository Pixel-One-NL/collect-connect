<?php

declare(strict_types=1);

namespace App\Domain\Image\Jobs\Middleware;

use App\Domain\Image\Jobs\DeleteCloudflareImageJob;
use App\Domain\Image\Jobs\ImportImageJob;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Sleep;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;

/**
 * Exceeding Cloudflare's global rate limit blocks every API call for 5 minutes,
 * so one 429 pauses all image jobs instead of only the job that received it.
 *
 * Production releases the job for the cool-down; elsewhere it sleeps and retries.
 */
class RespectCloudflareRateLimit
{
    public const string BLOCKED_UNTIL_CACHE_KEY = 'cloudflare-images:blocked-until';

    public const int DEFAULT_RETRY_AFTER_SECONDS = 300;

    public function handle(ImportImageJob|DeleteCloudflareImageJob $job, Closure $next): void
    {
        $secondsBlocked = (int) Cache::get(self::BLOCKED_UNTIL_CACHE_KEY, 0) - now()->getTimestamp();

        if ($secondsBlocked > 0) {
            $this->waitThenRetry($job, $next, $secondsBlocked);

            return;
        }

        try {
            $next($job);
        } catch (TooManyRequestsException $exception) {
            $retryAfter = (int) $exception->getResponse()->header('Retry-After');
            $retryAfter = $retryAfter > 0 ? $retryAfter : self::DEFAULT_RETRY_AFTER_SECONDS;

            Cache::put(self::BLOCKED_UNTIL_CACHE_KEY, now()->getTimestamp() + $retryAfter, $retryAfter);

            $this->waitThenRetry($job, $next, $retryAfter);
        }
    }

    private function waitThenRetry(ImportImageJob|DeleteCloudflareImageJob $job, Closure $next, int $seconds): void
    {
        if (app()->isProduction()) {
            $job->release($seconds);

            return;
        }

        Sleep::for($seconds)->seconds();

        Cache::forget(self::BLOCKED_UNTIL_CACHE_KEY);

        $this->handle($job, $next);
    }
}
