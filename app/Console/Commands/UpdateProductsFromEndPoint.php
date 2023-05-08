<?php

namespace App\Console\Commands;


use App\Models\Product;
use App\Traits\SynchronizesProducts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateProductsFromEndPoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:update-products-from-end-point';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try{
        DB::beginTransaction();
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products');
        $products = json_decode($response->getBody(), true);
    
    
        $existingIds = Product::pluck('id')->toArray();
        SynchronizesProducts::synchronizesProducts( $products,  $existingIds,'ProductSync');
        DB::commit();
        } catch (\Exception $e) {
                DB::rollBack();
                Log::error("File: " . $e->getFile() . " \nLine: " . $e->getLine() . " Error: " . $e->getMessage());
                Log::error($e->getLine());
                return;
        }
    }
}
