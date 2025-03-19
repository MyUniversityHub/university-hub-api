<?php

namespace App\Models;

use App\Traits\HasFields;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasFields;
}
