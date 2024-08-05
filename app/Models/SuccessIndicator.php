<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuccessIndicator extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'success_indc';

    protected $fillable = [
        'org_id',
        'target',
        'measures',
        'division_id',
        'alloted_budget',
        'months',
        'created_by',
        'updated_by',
        'Albay_target',
        'Camarines_Sur_target',
        'Camarines_Norte_target',
        'Catanduanes_target',
        'Masbate_target',
        'Sorsogon_target',
    ];

    protected $casts = [
        'division_id' => 'array', // Automatically decode JSON to array
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function org()
    {
        return $this->belongsTo(Organizational::class);
    }
}
