<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GenericName extends Model
{
    protected $table = 'generic_names';
    protected $fillable = ['description'];
    public $primaryKey = 'id';

    public function products(){
        return $this->hasMany('App\Products');
    }
}