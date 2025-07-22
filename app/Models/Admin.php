<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = [
        'firebase_uid',
        'first_name',
        'last_name',
        'email',
    ];

    public $timestamps = false; // Only use timestamps if you plan to persist locally

    // Optional: If you don’t want to store it at all in the database
    // you can just return a new instance without calling save()
}
