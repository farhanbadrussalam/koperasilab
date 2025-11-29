<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_jenisTld
 * @property string|null $name
 * @property string|null $order_jobs
 * @property int|null $status
 * @property-read mixed $jenis_tld_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereIdJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereOrderJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereStatus($value)
 * @mixin \Eloquent
 */
class Master_jenistld extends Model
{
    use HasFactory;
    protected $table = 'master_jenistld';

    protected $fillable = [
        'name',
        'status'
    ];

    protected $hidden = [
        'id_jenisTld'
    ];

    protected $appends = [
        'jenis_tld_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_jenisTld' => 'integer'
    ];

    public function getJenisTldHashAttribute()
    {
        return $this->id_jenisTld ? encryptor($this->id_jenisTld) : null;
    }

    public function kontrak(){
        return $this->hasMany(Kontrak::class, 'jenis_tld', 'id_jenisTld');
    }

}
