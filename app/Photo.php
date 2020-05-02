<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'path'
    ];



    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function users_via_sharing()
    {
        return $this->belongsToMany(User::class, 'shared_image_user', 'photo_id', 'user_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    function getAllAlowedUsers()
    {
        $shared_ids = collect($this->users_via_sharing)->map(function ($item, $key) {
            return $item->id;
        });
        return $shared_ids->concat([$this->owner_id]);
    }
}
