<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entries extends Model
{
    use HasFactory;

    protected $table = 'entries';

        /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'indicator_id',
        'file',
        'months',
        'created_by',
    ];

    public function indicator()
    {
        return $this->belongsTo(SuccessIndicator::class);
    }
}
