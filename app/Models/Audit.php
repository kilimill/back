<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int id
 * @property string|null user_type
 * @property int|null user_id
 * @property string event
 * @property string auditable_type
 * @property int auditable_id
 * @property string|null old_values
 * @property string|null new_values
 * @property string|null url
 * @property string|null ip_address
 * @property string|null user_agent
 * @property string|null tags
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read User $user
 *
 * @see AuditObserver
 */

class Audit extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
