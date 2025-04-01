<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends BaseModel
{
    protected $table = 'roles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'active'
    ];

    protected static array $fields = [
        'id' => 'id',
        'name' => 'name',
        'active' => 'active'
    ];

}
