<?php

namespace App\Mail\Concerns;

use Illuminate\Support\Facades\Log;

/**
 * Shared queue + retry behavior for every transactional mailable.
 *
 * Each mailable that implements ShouldQueue picks up tries / backoff / queue
 * from config('notifications.queue'). On a permanent failure (queue exhausted
 * its retries) we log enough context to debug bounces in production.
 */
trait SendsAsTransactional
{
    public function buildQueueable(): void
    {
        $cfg = config('notifications.queue');

        $this->tries   = $cfg['tries'] ?? 3;
        $this->backoff = $cfg['backoff'] ?? [60, 300, 900];

        if (!empty($cfg['connection'])) {
            $this->onConnection($cfg['connection']);
        }
        if (!empty($cfg['name'])) {
            $this->onQueue($cfg['name']);
        }
    }

    /**
     * Invoked by the queue worker after the last retry exhausts.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('Transactional email permanently failed', [
            'mailable'  => static::class,
            'recipient' => $this->recipientForLog(),
            'message'   => $e->getMessage(),
        ]);
    }

    /**
     * Best-effort: subclasses can override to surface a recipient.
     */
    protected function recipientForLog(): ?string
    {
        return $this->to[0]['address'] ?? null;
    }
}
