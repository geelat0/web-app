<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entries extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'entries';

        /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'indicator_id',
        'user_id',
        'file',
        'months',
        'year',
        'accomplishment',
        'created_by',
        'updated_by',
    ];

    public function indicator()
    {
        return $this->belongsTo(SuccessIndicator::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
