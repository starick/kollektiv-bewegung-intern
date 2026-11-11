<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * Records every change of a model in the audit trail.
 *
 * Usage: implement OwenIt\Auditing\Contracts\Auditable and use this trait.
 * Optionally set `$auditExclude` for attributes that should not be recorded and
 * `$auditDiffed` for long text attributes, which the trail shows as a line diff
 * instead of the full old and new value.
 */
trait HasAuditTrail
{
    use Auditable;

    /**
     * Audits of this model, newest first, with the user who made the change.
     *
     * @return Collection<int, Audit>
     */
    public function auditTrail(): Collection
    {
        return $this->audits()->with('user')->latest()->latest('id')->get();
    }

    /**
     * Text attributes the trail shows as a line diff.
     *
     * @return array<int, string>
     */
    public function getAuditDiffed(): array
    {
        return $this->auditDiffed ?? [];
    }
}
