<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens ;
    protected $primaryKey = 'id';
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'email',
        'password',
        'role_id',
        'active'
    ];

    public function findForPassport($username)
    {
        return $this->where('user_name', $username)->first();
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    public function getRoleId()
    {
        return $this->role_id;
    }
}
