<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tesData extends Model
{
    use HasFactory;
    protected $table = 'tesDataNama';
    protected $primaryKey = 'no';
    protected $fillable = [
        'nama','email'
    ];
}
