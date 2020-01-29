<?php

namespace Fluidtheory\Filemanager\Models;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    //
    protected $dates = ['deleted_at'];

    protected $primaryKey = 'id';
    protected $table = 'directories';
    public $timestamps = false;
    protected $fillable = array('id', 'name', 'client_id', 'parent_id', 'created_at', 'deleted_at');
}
