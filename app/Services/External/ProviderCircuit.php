<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Cache;

/**
 * Stops calling an index that is clearly down.
 *
 * Without this, a provider outage adds its full timeout (up to 6s) to every
 * single chat turn that reaches the external path — so an optional secondary
 * source makes the whole chatbot feel broken. After a few failures the breaker
 * opens and the feature behaves as if it were disabled, which is exactly the
 * right degradation for something that was never load-bearing.
 *
 * Cache-backed rather than in-memory: the app runs on Vercel, where each
 * request may be a fresh process, so per-process state would never accumulate
 * enough failures to trip.
 */
class ProviderCircuit
{
    public function isOpen(string $provider): bool
    {
        return Cache::get($this->openKey($provider)) !== null;
    }

    public function recordSuccess(string $provider): void
    {
        Cache::forget($this->failureKey($provider));
    }

    public function recordFailure(string $provider): void
    {
        $threshold = (int) config('external_literature.circuit.failure_threshold', 3);
        $window = (int) config('external_literature.circuit.window_seconds', 300);

        $key = $this->failureKey($provider);
        $failures = (int) Cache::get($key, 0) + 1;

        // Re-put with the window TTL rather than increment(): a rolling window
        // is what makes "3 failures ever" different from "3 failures recently",
        // and only the latter should trip a breaker.
        Cache::put($key, $failures, $window);

        if ($failures >= $threshold) {
            Cache::put(
                $this->openKey($provider),
                true,
                (int) config('external_literature.circuit.open_seconds', 300),
            );
            Cache::forget($key);
        }
    }

    private function failureKey(string $provider): string
    {
        return "extlit:circuit:failures:{$provider}";
    }

    private function openKey(string $provider): string
    {
        return "extlit:circuit:open:{$provider}";
    }
}
