<?php

declare(strict_types=1);

namespace App\Tenancy;

use Closure;
use LogicException;

final class TenantContext
{
    private ?int $organizationId = null;

    public function id(): ?int
    {
        return $this->organizationId;
    }

    public function requireId(): int
    {
        return $this->organizationId ?? throw new LogicException('No tenant organization is active.');
    }

    public function set(?int $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function forget(): void
    {
        $this->organizationId = null;
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function run(int $organizationId, Closure $callback): mixed
    {
        $previous = $this->organizationId;
        $this->organizationId = $organizationId;

        try {
            return $callback();
        } finally {
            $this->organizationId = $previous;
        }
    }
}
