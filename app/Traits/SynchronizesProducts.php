<?php
    
    
    namespace App\Traits;


    use App\Jobs\ProcessProductImport;
    use App\Jobs\ProcessProductSync;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;

    trait SynchronizesProducts
    {
        /**
         * Retrieve the content of a CSV file from storage.
         *
         * @param string $filename
         * @return array|false
         */
        public static function synchronizesProducts(array $products, array $existingIds,string $type)
        {
    
           
            foreach ($products as $product) {
    
             
                if (in_array($product['id'], $existingIds)) {
                        if($type=='ProductImport'){
                            ProcessProductImport::dispatch($product, 'update');
                           
                        }elseif($type=='ProductSync'){
                            ProcessProductSync::dispatch($product, 'update');
                        }
                        
                }
            }
    
            
        }
    }