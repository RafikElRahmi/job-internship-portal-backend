<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostulationsUser extends Model
{
    use HasFactory;
    protected $table = "postulations_users";
    protected $fillable = [
        'postid',
        'author',
        'userid',
        'filepath',
        'status',
    ];
}
