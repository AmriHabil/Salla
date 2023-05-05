<?php
    
    
    namespace App\Traits;


    use Illuminate\Support\Facades\Storage;

    trait RetrievesCsvContent
    {
        /**
         * Retrieve the content of a CSV file from storage.
         *
         * @param string $filename
         * @return array|false
         */
        public static function getCsvContent(string $filename, int $length)
        {
            
            $path = storage_path($filename);
            $file = fopen($path, 'r');
            $headers = fgetcsv($file);
            $data = [];
    
            while (($row = fgetcsv($file)) !== false) {
                $line = array_slice($row, 0, $length); // Ensure that the two table have the same number of elements
                $data[] = array_combine($headers, $line);
            }
    
            fclose($file);
            return $data;
        }
    }