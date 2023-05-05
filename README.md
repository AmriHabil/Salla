# Documentation
This coding challenge is part of the technical recruitment process for joining Salla as a Senior Backend Engineer role
## Installation
* Clone Project & Run this command
> composer install

* Run this command 
> cp .env.example .env

* Then configure it ( DB & QUEUE ) then
Run this command
> php artisan key:generate

* Run this command
> php artisan migrate

## Test Import
* To import all the products in first time you have to run this command
> php artisan import:product products.csv
   
   I used the old way of __fgetcsv__ since it gives us a better performance than the famous __maatwebsite/excel__ 
   package and I used __array_combine__ to be able to use __keys__ instead of __indexs__


* To update all the products and soft delete the deleted products or any products that were not in the file run this command
> php artisan import:product products2.csv

## Sync products using response of the endpoint
* To update all the products and its variations run this command
> php artisan sync:update-products-from-end-point   

# Explanation 
## Migrations
I added __quantity__ since I’ve found this column in the csv file also __deleted_by_sync__
for a hint of soft deleting after importing files, for now it’s a bool but if we must explain the reason with details 
we have to change the type.
I created a table name __product_quantities__ to store the quantity of products and its avalabilty. Regarding the response
 of the endpoint, the majority of products quantities depends on _color & material_ or _color & size_ and it may even 
 depend only on a _single attribute_
 __SO THIS SHOULD BE FLEXIBLE__. I prefer keeping quantity of each variation in separated column so it should be better 
 for counting the total number of pieces of each product.
 
 _Maybe we can also add index to the column  `name` since this can radically accelerate the search & SELECT queries._
 ## Models
 When creating the models of products I didn’t add the __relations with product_quantities__ since I do not need it for now in this test.
 I didn’t rely on __Eloquent__ so I haven't given time for models since using the __DB facade__ gives us __better performance__ and it  is __much faster__.

## Commands
* To use the command I Created two classes : 
  * __ImportProducts__ : to load from csv (has argument to choose the file that we use to load data) 
  * __UpdateProductsFromEndPoint__ : to load data from the endpoint ( executed daily at 12:00 )
  
  
## Traits
  
 * I created a trait for retrieving csv files and I tried to make it flexible since I can use the header of csv and pass the number of columns to ensure that the two tables have the same number of elements.
 * I created another trait for Synchronizing products which I used for both classes ImportProducts  & UpdateProductsFromEndPoint 

## Jobs
Regarding __the huge number__ of records in the csv file I tried to __optimize performance__ and avoiding the __timeout execution__ by using __jobs__ and __queue__

## Validations
I have not find any description about how to deal with products if the validation fails, for example if the rule of unique sku
is not respected should I update the other fields or skip the row.

## Test
You will find a feature test that simulate the craetion of a csv file and then pass this file to the command that import products and see if the result is the same as expected to see if everything goes right 