<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialGroup extends Model
{

    protected $table = 'group';

    protected $fillable = ['code', 'name', 'note', 'status'];

    protected $casts = ['status' => 'boolean'];
}