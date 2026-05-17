<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property string $image_type
 * @property string|null $image_blob
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereImageBlob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ttd withoutTrashed()
 * @mixin \Eloquent
 */
class Master_ttd extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'master_ttd';
    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }
}
