<?php
    
    namespace App\Jobs;
    
    use App\Models\Product;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldBeUnique;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class ProcessProductImport implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
        
        
        protected $products;
        protected $action;
        
        /**
         * Create a new job instance.
         *
         * @param array $product
         * @param string $action
         * @return void
         */
        public function __construct(array $products, string $action)
        {
            $this->products = $products;
            $this->action = $action;
        }
        
        /**
         * Execute the job.
         */
        public function handle()
        {
        
            try {
                DB::beginTransaction();

                if ($this->action == 'create') {
                // Create new product
                DB::table('products')->insert($this->products);
            } else if ($this->action == 'update') {
                // Update existing product
    
                $this->products['price'] = floatval(max(0, min(999999.99, $this->products['price']))) ?? 0;
    
                $this->products['quantity'] = (float)$this->products['quantity'] ?? 0;

                $this->updateProduct($this->products);
                
//                /*sleep(2);*/ //Email notification to the warehouse about the new quantity
//                /*sleep(2);*/ //Email notifications to customers who requested updates when out-of-stock products become available
//                /*sleep(2);*/ //Requests to a third-party application to update product data

            }
    
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("File: " . $e->getFile() . " \nLine: " . $e->getLine() . " Error: " . $e->getMessage());
                Log::error($e->getLine());
                return ("File: " . $e->getFile() . " \nLine: " . $e->getLine() . " Error: " . $e->getMessage());
            }
        
        }
        
        /**
         * This Method will be used by extending this class in ProcessProductSync
         *
         */
        public function updateProduct(array $product){
        
            DB::table('products')
                ->where('id',$product['id'])
                ->update($product);
        }
    }
