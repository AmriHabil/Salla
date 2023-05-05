<?php
    
    namespace Tests\Feature;
    
    
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Storage;
    use Tests\TestCase;
    
    class ImportProductsCommandTest extends TestCase
    {
        
        
        /** @test */
        public function it_can_import_products_from_csv()
        {
            // Create a test CSV file
            
            Storage::fake('csv');
            
            // Create a CSV file with products data
            $file = UploadedFile::fake()->createWithContent('fake.csv', "id,name,sku,price,currency,variations,quantity,status\n291193,Product 1,,100,SAR,,10,sale"
            );
            
            $this->artisan('import:products', [
                'file' => $file->path(),
            ]);
            
            // Check if products were imported to the database
            $this->assertDatabaseHas('products', [
                'name' => 'Product 1',
                'id' => '291193',
                'price' => '100.00',
            ]);
            
            
        }
    }