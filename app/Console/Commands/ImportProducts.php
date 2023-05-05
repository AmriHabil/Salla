<?php
    
    namespace App\Console\Commands;
    
    
    use App\Jobs\ProcessProductImport;
    use App\Models\Product;
    use App\Traits\RetrievesCsvContent;
    use App\Traits\SynchronizesProducts;
    use Carbon\Carbon;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    
    class ImportProducts extends Command
    {
        use RetrievesCsvContent; // I used a trait cz this function can be used for others csv files
        
        use SynchronizesProducts; // I used a trait cz this function can be used also for Sync with the endpoint
        /**
         * @var string
         */
        protected $signature = 'import:products {file}';
        
        /**
         * @var string
         */
        protected $description = 'Imports products into database';
        
        /**
         * @return void
         */
        public function __construct()
        {
            parent::__construct();
        }
        
        /**
         * @return mixed
         */
        public function handle()
        {
            $bar = $this->output->createProgressBar();
            try {
                DB::beginTransaction();

                $products = RetrievesCsvContent::getCsvContent('app/' . $this->argument('file'), 8);
                
                
                $storedProducts = Product::withTrashed()->pluck('id')->toArray();
                $existingIds = $storedProducts;
                $productsToSoftDelete = array();
                $productsToCreate = array();
                $productsToUpdate = array();
                $productsAlreadyCreated = array();
                foreach ($products as $product) {
                    $productId = $product['id'];// I prefer using the name the coloumn instead of index more readable
                    
                    if (in_array($productId, $storedProducts)) {
                        
                        $existingIds = array_diff($existingIds, [$productId]);
                        
                        if ($product['status'] == 'deleted') {
                            $productsToSoftDelete[] = $productId;
                            
                        } else {
                            $product['deleted_at']=NULL;
                            $product['deleted_by_sync']=0;
                            $productsToUpdate[] = $product;
                        }
                        
                        
                    } else {
                        if ($product['status'] != 'deleted' && ! in_array($product['id'],$productsAlreadyCreated)) {
                            $productsAlreadyCreated[]=$product['id'];
                            // The product does not exist, create it
                            $rowToAdd = [
                                'id' => $product['id'], // Should I use this ?
                                'name' => $product['name'],
                                'sku' => $product['sku'],
                                'price' => floatval(max(0, min(999999.99, (float)$product['price']))),
                                'currency' => $product['currency'],
                                'variations' => $product['variations'],
                                'quantity' => (int)$product['quantity'],
                                'status' => $product['status'],
                            ];
                            $productsToCreate[] = $rowToAdd;
                        }
                        
                    }
                    $bar->advance();
                }
                
                
                // The product does not exist, create it
                $chunkSize = 5000; // depends on performance & table structure
                $productChunks = array_chunk($productsToCreate, $chunkSize);
                foreach ($productChunks as $products) {
                    ProcessProductImport::dispatch($products, 'create');
                }
                
                
                // The product already exists, update it as usual
                SynchronizesProducts::synchronizesProducts($productsToUpdate, $storedProducts, 'ProductImport');
                
                
                // Soft delete any products that were not in the file
                dispatch(function () use ($existingIds, $productsToSoftDelete) {
                    //using chunks to avoid the error of having a  prepared statement that contains too many placeholders
                    $chunkSize = 500; // depends on performance & table structure
                    $productChunks = array_chunk($existingIds, $chunkSize);
                    foreach ($productChunks as $products) {
                        DB::table('products')->whereIn('id', $products)->update([
                            'deleted_at' => Carbon::now(),
                            'deleted_by_sync' => true,
                        ]);
                    }
                    
                    // Separated in case that every type of delete should have a different hint
                    $chunkSize = 500; // depends on performance & table structure
                    $productChunks = array_chunk($productsToSoftDelete, $chunkSize);
                    foreach ($productChunks as $products) {
                        DB::table('products')->whereIn('id', $products)->update([
                            'deleted_at' => Carbon::now(),
                            'deleted_by_sync' => true,
                        ]);
                    }
                    
                })->afterResponse();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("File: " . $e->getFile() . " \nLine: " . $e->getLine() . " Error: " . $e->getMessage());
                Log::error($e->getLine());
                $this->info("Importing Products Failed : File: " . $e->getFile() . " \nLine: " . $e->getLine() . " Error: " . $e->getMessage());
                return;
            }
            $bar->finish();
            
            $this->info('Products imported successfully.');
        }
    }
