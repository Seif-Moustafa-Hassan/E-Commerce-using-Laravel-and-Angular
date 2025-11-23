<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Columns that are allowed to be mass-assigned
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'out_of_stock',
    ];
}
