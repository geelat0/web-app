<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarter_logs extends Model
{
    use HasFactory;

    protected $table = 'quarter_logs';

    protected $fillable = [
        'indicator_id',
        'Q1_target',
        'Q2_target',
        'Q3_target',
        'Q4_target',
        'created_by',
        'updated_by'
    ];

    public function indicator()
    {
        return $this->belongsTo(SuccessIndicator::class, 'quarter_logs_id', 'id');
    }
}
