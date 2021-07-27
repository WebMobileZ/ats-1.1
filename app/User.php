<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */



    public function newQuery($excludeDeleted = true) {
        return parent::newQuery($excludeDeleted)
            ->whereIn('user_status',['A','B']);
    }
    protected $fillable = [
        'name', 'email', 'password','role','user_status','out_look_pass','isResume','performance'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function userlog(){
        return $this->hasMany('App\userLogs');
    }
    public function Jobs(){
        return $this->hasMany('App\Jobs','userId');
    }
    public function Consultants(){
        return $this->hasMany('App\Consultants','userId');
    }
    public function Submissions(){
        return $this->hasMany('App\Submissions','userId');
    }

    public function profile(){
        return $this->hasOne('App\Profile');
    }
    public function user_assign_jr(){
        return $this->hasMany('App\UserAssign','userId');
    }
}
