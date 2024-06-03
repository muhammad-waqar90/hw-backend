<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_product_category_id_foreign');
            $table->dropColumn('product_category_id');
            $table->foreignId('category_id')
                ->after('course_module_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
        });

        $this->updateProductsCategoryId();

        Schema::dropIfExists('product_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_category_id_foreign');
            $table->dropColumn('category_id');
            $table->foreignId('product_category_id')
                ->after('course_module_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
        });
    }

    private function updateProductsCategoryId()
    {
        $booksCategory = Category::create([
            'parent_category_id' => null,
            'root_category_id' => null,
            'name' => 'Books',
        ]);

        Product::query()->update([
            'category_id' => $booksCategory->id,
        ]);
    }
};
