<?php

namespace App\Http\Resources;

use App\Auditing\LineDiff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Models\Audit;

/**
 * One entry of an audit trail, independent of the audited model.
 *
 * @mixin Audit
 */
class AuditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        // A fresh instance works even when the audited record has been deleted since
        $model = class_exists($this->auditable_type) ? new $this->auditable_type : null;
        $diffed = $model && method_exists($model, 'getAuditDiffed') ? $model->getAuditDiffed() : [];

        return [
            'id' => $this->id,
            'event' => $this->event,
            'createdAt' => $this->created_at,
            // Only id and name, other members' email addresses stay on the server
            'user' => $this->user?->only('id', 'name'),
            'changes' => collect(array_unique([...array_keys($old), ...array_keys($new)]))
                ->reject(fn (string $attribute) => $model && $attribute === $model->getKeyName())
                ->map(fn (string $attribute) => in_array($attribute, $diffed, true)
                    ? [
                        'attribute' => $attribute,
                        'type' => 'diff',
                        ...LineDiff::between($old[$attribute] ?? null, $new[$attribute] ?? null),
                    ]
                    : [
                        'attribute' => $attribute,
                        'type' => 'value',
                        'old' => $this->present($model, $attribute, $old[$attribute] ?? null),
                        'new' => $this->present($model, $attribute, $new[$attribute] ?? null),
                    ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Audits store attributes in their database form; decode them according to the model's casts.
     */
    private function present(?Model $model, string $attribute, mixed $value): mixed
    {
        if ($model === null || $value === null) {
            return $value;
        }

        if (is_string($value) && $model->hasCast($attribute, ['array', 'json', 'collection', 'object'])) {
            return json_decode($value, true);
        }

        // The old value comes from the database as 0/1, the new one as a PHP boolean
        if ($model->hasCast($attribute, ['bool', 'boolean'])) {
            return (bool) $value;
        }

        if ($model->hasCast($attribute, ['date', 'immutable_date'])) {
            return Carbon::parse($value)->toDateString();
        }

        return $value;
    }
}
