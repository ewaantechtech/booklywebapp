<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
    ];

    public function providers()
    {
        return $this->belongsToMany(ServiceProvider::class, 'home_section_providers')
            ->where('service_providers.is_active', 1)
            ->where('service_providers.published', 1);
    }
}
