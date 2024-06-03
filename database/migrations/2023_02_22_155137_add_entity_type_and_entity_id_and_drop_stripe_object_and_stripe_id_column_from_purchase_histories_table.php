<?php

use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\Models\PurchaseHistory;
use App\Models\StripePayment;
use Illuminate\Support\Carbon;
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
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_id')->after('user_id');
            $table->string('entity_type')->after('entity_id');
        });

        // $this->dumpStripeIntentToStripePayment();

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropColumn('stripe_id');
            $table->dropColumn('stripe_object');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->string('stripe_id')->after('user_id')->nullable();
            $table->string('stripe_object')->after('stripe_id')->nullable();
        });

        // $this->dumpStripeIntentToPurchaseHistory(new PurchaseHistory, new StripePayment, 'id As entity_id', 'entity_id');

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropColumn('entity_id');
            $table->dropColumn('entity_type');
        });
    }

    private function dumpStripeIntentToStripePayment()
    {
        $data = PurchaseHistory::select('stripe_id', 'stripe_object')->get()->toArray();
        foreach ($data as $key => $item) {
            $data[$key]['created_at'] = Carbon::now();
            $data[$key]['updated_at'] = Carbon::now();
        }
        StripePayment::insert($data);

        $data = StripePayment::select('id')->get()->toArray();
        $purchases = PurchaseHistory::all()->toArray();
        foreach ($data as $key => $item) {
            PurchaseHistory::where('id', $purchases[$key]['id'])->update(['entity_id' => $item['id'], 'entity_type' => PurchaseHistoryEntityData::ENTITY_STRIPE_PAYMENT]);
        }
    }

    private function dumpStripeIntentToPurchaseHistory($modelTo, $modelFrom, $selector, $index)
    {
        Batch::update(
            $modelTo,
            $modelFrom::select($selector, 'stripe_id', 'stripe_object')->get()->toArray(),
            $index
        );

        return StripePayment::truncate();
    }
};
