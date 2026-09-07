<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * @template TModel of Model
 *
 * @extends Builder<TModel>
 */
final class TenantOwnedBuilder extends Builder
{
    /** @param array<string, mixed> $values */
    public function update(array $values): int
    {
        $this->guardOwnershipMutation($values);

        return parent::update($values);
    }

    /**
     * @param  array<int, array<string, mixed>>|array<string, mixed>  $values
     * @param  array<int, string>|string  $uniqueBy
     * @param  array<int, string>|null  $update
     */
    public function upsert(array $values, $uniqueBy, $update = null): int
    {
        if ($values === []) {
            return 0;
        }

        $tenantId = app(TenantContext::class)->requireId();
        $rows = isset($values[0]) && is_array($values[0]) ? $values : [$values];

        foreach ($rows as &$row) {
            $rowOrganizationId = $row['organization_id'] ?? $tenantId;

            if ((int) $rowOrganizationId !== $tenantId) {
                throw new LogicException('Upsert rows must belong to the active tenant.');
            }

            $row['organization_id'] = $tenantId;
        }
        unset($row);

        $uniqueColumns = is_array($uniqueBy) ? $uniqueBy : [$uniqueBy];
        if (! in_array('organization_id', $uniqueColumns, true)) {
            throw new LogicException('Tenant upserts require organization_id in the unique key.');
        }

        if (is_array($update) && in_array('organization_id', $update, true)) {
            throw new LogicException('Tenant ownership cannot be changed by an upsert.');
        }

        return parent::upsert(count($rows) === 1 ? $rows[0] : $rows, $uniqueBy, $update);
    }

    /** @param array<string, mixed> $extra */
    public function increment($column, $amount = 1, array $extra = []): int
    {
        $this->guardOwnershipMutation([$column => $amount, ...$extra]);

        return parent::increment($column, $amount, $extra);
    }

    /** @param array<string, mixed> $extra */
    public function decrement($column, $amount = 1, array $extra = []): int
    {
        $this->guardOwnershipMutation([$column => $amount, ...$extra]);

        return parent::decrement($column, $amount, $extra);
    }

    /**
     * @param  array<string, int|float>  $columns
     * @param  array<string, mixed>  $extra
     */
    public function incrementEach(array $columns, array $extra = []): int
    {
        $this->guardOwnershipMutation([...$columns, ...$extra]);

        return parent::incrementEach($columns, $extra);
    }

    /**
     * @param  array<string, int|float>  $columns
     * @param  array<string, mixed>  $extra
     */
    public function decrementEach(array $columns, array $extra = []): int
    {
        $this->guardOwnershipMutation([...$columns, ...$extra]);

        return parent::decrementEach($columns, $extra);
    }

    /** @param array<string, mixed> $values */
    private function guardOwnershipMutation(array $values): void
    {
        if (array_key_exists('organization_id', $values)) {
            throw new LogicException('Tenant ownership cannot be changed by a bulk operation.');
        }
    }
}
