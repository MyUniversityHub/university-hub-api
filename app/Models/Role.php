<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends BaseModel
{
    protected $table = 'roles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'role_id',
        'role_name',
        'active'
    ];

    protected static array $fields = [
        'id' => 'role_id',
        'name' => 'role_name',
        'active' => 'active'
    ];

}
