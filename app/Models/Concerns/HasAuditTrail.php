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
 * `$auditSummarized` for large attributes (e.g. long texts) whose values are
 * stored, but only shown as "changed" in the trail.
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
     * Attributes whose values are left out when the trail is displayed.
     *
     * @return array<int, string>
     */
    public function getAuditSummarized(): array
    {
        return $this->auditSummarized ?? [];
    }
}
