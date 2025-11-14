<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name_lab
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $lab_hash
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab query()
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereNameLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class tbl_lab extends Model
{
    use HasFactory;

    protected $table = 'tbl_lab';

    protected $fillable = [
        'name_lab'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'lab_hash'
    ];

    public function getLabHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }
}
