<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'surname', 'phone', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'wsr_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function images()
    {
        return $this->hasMany(Photo::class, 'owner_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function shared_to_me_images()
    {
        return $this->belongsToMany(Photo::class, 'shared_image_user', 'user_id', 'photo_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    function getAllAvailablePhotos()
    {
        return (collect($this->images)->concat($this->shared_to_me_images));
    }
}
