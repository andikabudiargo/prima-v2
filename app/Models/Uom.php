<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    protected $fillable = ['code', 'name', 'category', 'note', 'status'];

    protected $casts = ['status' => 'boolean'];
}