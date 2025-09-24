<?php

namespace App\Models;

use App\Models\UserHasPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description','module_name'
    ];

    public function userPermissions() : HasMany
    {
        return $this->hasMany(UserHasPermission::class);
    }
}
