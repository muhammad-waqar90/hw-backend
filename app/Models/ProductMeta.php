<?php
/**
 * TODO:
 * required to create a seperate structure for storing and generating the product meta keys wrt product type
 * required to store those keys seperately
 * once frontend request we required to provide those keys from backend to frontend to populate the fields wrt selection of product category
 * and on create request reqeuired to validate those meta keys against the product category
 *
 * which ultimately update product meta as well to store the relation rather than hard-coded eye-closed meta keys from frontend
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMeta extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
