<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'zoho_id',
        'supplier',
    ];

    /**
     * Get the comments for the blog post.
     */
    public function pins()
    {
        return $this->hasMany('App\Models\Pin');
    }

}
