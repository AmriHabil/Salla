<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessProductSync extends ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $product;
    public function __construct(array $product)
    {
        $this->product=$product;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();
        DB::table('products')
            ->where('id',$this->product['id'])
            ->update([
                'price' =>$this->product['price'],
                'name' =>$this->product['name'],
            ]);
        DB::table('product_quantities')
            ->insert(
                [
                    'product_id' =>$this->product['id'],
                    'quantity' =>$this->product['variations'][0]['quantity']??0,
                    'variations' => json_encode(
                        [
                            'color' => $this->product['variations'][0]['color']??0,
                            'material' => $this->product['variations'][0]['material']??0
                        ]
                    )
                ]
            );
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("File: " . $e->getFile() . " \nLine: " . $e->getLine() . " Error: " . $e->getMessage());
            Log::error($e->getLine());
            return ("File: " . $e->getFile() . " \nLine: " . $e->getLine() . " Error: " . $e->getMessage());
        }
    }
}
