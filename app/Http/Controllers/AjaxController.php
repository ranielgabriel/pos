<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Product;
use App\GenericName;
use App\Inventory;
use App\DrugType;
use App\Manufacturer;
use App\Supplier;
use App\Batch;
use App\Cart;
use App\Customer;
use App\Sale;
use App\ProductSale;
use App\ReturnInventory;

class AjaxController extends Controller
{

    // This function is for searching products
    // This will return a list of product(s)
    public function searchProducts(Request $request){

        $output =
        '<table class="table table-striped table-bordered table-hover">'.
        '<thead><tr>'.
        '<th><small>Generic Name</small></th>'.
        '<th><small>Brand Name</small></th>'.
        '<th><small>Drug Type</small></th>'.
        '<th><small>Quantity</small></th>'.
        '<th><small>Status</small></th>'.
        '<th><small>Purchase Price</small></th>'.
        '<th><small>Special Price</small></th>'.
        '<th><small>Walk-In Price</small></th>'.
        '<th><small>Promo Price</small></th>'.
        '<th><small>Distributor\'s Prsmall></th>'.
        '</tr></thead>';

        if($request->name != ''){

            // get all products and order by brand name
            // first check if the brand name exist
            // then check if the generic name exist
            $products = Product::orderBy('brand_name','asc')
            ->where('brand_name', 'like', '%' . $request->name . '%')
            ->orWhereHas('genericNames', function($query) use ($request){
                $query->where('description', 'like', '%' . $request->name . '%');
            })
            ->get();

            foreach ($products->sortBy('genericNames.description') as $product) {

                // temporary variable for storing quantity

                $output.='<tr>'.

                '<td>' . $product->genericNames->description . '</td>'.

                '<td><a class="link-unstyled" href="/products/' . $product->id . '" class="">'.$product->brand_name.'</a></td>'.

                '<td>' . $product->drugTypes->description . '</td>'.

                '<td>' . ($product->inventories->sum('quantity') - $product->inventories->sum('sold')) . '</td>';

                if($product->status == 'In-stock'){
                    $output .='<td><p class="text-warning">' . $product->status . '</p></td>';
                }elseif($product->status == 'Out-of-stock'){
                    $output .='<td><p class="text-danger">' . $product->status . '</p></td>';
                }elseif($product->status == 'Selling'){
                    $output .='<td><p class="text-success">' . $product->status . '</p></td>';
                }

                $output.= '<td>&#8369 ' . $product->purchase_price . '</td>'.

                '<td>&#8369 ' . $product->special_price . '</td>'.

                '<td>&#8369 ' . $product->walk_in_price . '</td>'.

                '<td>&#8369 ' . $product->promo_price . '</td>'.

                '<td>&#8369 ' . $product->distributor_price . '</td>';

                if($product->status == 'Selling'){
                $output.= '<td>
                    <center>
                        <button class="btn btn-success modalSellClass" data-toggle="modal" data-target="#modalSell" data-product-id='. $product->id .'>
                            <span class="fa fa-cart-arrow-down"></span>
                        </button>
                    </center>
                </td>'.

                '</tr>';
                }else{
                    $output .= '</tr>';
                }
            }
            $output .= '</table>';
        }
        // return response($products);
        return response()->json([
            'code' => $output
            ]);
    }

    // This function is for searching Batch
    // This returns a batch
    public function searchBatch(Request $request){
        $batch = Batch::find($request->input('batchNumber'));

        if($batch === null){
            return response()->json([
                'batch' => $batch
                ]);
        }else{

            $products = array();

            foreach($batch->inventories as $inventory){
                array_push($products, $inventory->product);
            }

            return response()->json([
                'batch' => $batch,
                'inventories' => $batch->inventories,
                'products' => $products
                ]);
        };
    }

    // This function is for searching a specific product
    // This returns every information about the product.
    public function searchProductInfo(Request $request){
        if($request->id != null){

            $product = Product::find($request->id);
            $inventories = Inventory::where('product_id','=',$product->id)
            ->orderBy('expiration_date')
            ->get();
            return response()->json([
                'product' => $product,
                'genericNames' => $product->genericNames,
                'manufacturers' => $product->manufacturers,
                'drugTypes' => $product->drugTypes,
                'inventories' => $inventories
            ]);
        }
    }

    // This function is for searching a specific product sale
    // This returns every information about the product sale.
    public function searchProductSaleInfo(Request $request){
        if($request->id != null and $request->saleId != null){

            $productSale = ProductSale::where([
                ['sale_id','=',$request->saleId],
                ['product_id','=',$request->id]
            ])
            ->get()
            ->first();
            $product = Product::find($request->id);
            $inventory = Inventory::find($productSale->inventory_id);
            return response()->json([
                'product' => $product,
                'genericNames' => $product->genericNames,
                'manufacturers' => $product->manufacturers,
                'drugTypes' => $product->drugTypes,
                'inventory' => $inventory,
                'productSale' => $productSale
            ]);
        }
    }

    // This function is for searching a specific product quantity
    // This returns every information about the product quantity.
    public function searchProductQuantityInfo(Request $request){
        if($request->inventoryId != null){

            $inventories = Inventory::find($request->inventoryId);

            return response()->json([
                'inventories' => $inventories
            ]);
        }
    }
    
    // This function is for searching suppliers
    // This returns a list of supplier(s).
    public function searchSupplier(Request $request){

        $output =
        '<table class="table table-striped table-bordered table-hover" id="tableProducts">'.
        '<thead><tr>'.
        '<th><center><small>Name</small></center></th>'.
        '<th><center><small>Address</small></center></th>'.
        '<th><center><small>LTO Number</small></center></th>'.
        '<th><center><small>Expiration Date</small></center></th>'.
        '<th><center><small>Contact Person</small></center></th>'.
        '<th><center><small>Contact Number</small></center></th>'.
        '<th><center><small>Email Address</small></center></th>'.
        '</tr></thead>';

        if($request->name != ''){

            // get all products and order by brand name
            // first check if the brand name exist
            // then check if the generic name exist
            $suppliers = Supplier::orderBy('name','asc')
            ->where('name', 'like', '%' . $request->name . '%')
            ->orWhere('contact_person', 'like', '%' . $request->name . '%')
            ->get();

            // temporary array for inventory
            // $invent = array();

            // loop to every product
            foreach ($suppliers as $supplier) {

                $output.= '<tr>'.

                '<td>'. $supplier->name .'</td>'.

                '<td>'. $supplier->address .'</td>'.

                '<td>'. $supplier->lto_number .'</td>'.

                '<td>'. $supplier->expiration_date .'</td>'.

                '<td>'. $supplier->contact_person .'</td>'.

                '<td>'. $supplier->contact_number .'</td>'.

                '<td>'. $supplier->email_address .'</td>'.

            '</tr>';
            }
        }

        $output .= '</table>';

        return response()->json([
            'code' => $output
            ]);
    }

    // This function is for searching a specific supplier
    // This returns every information about the supplier.
    public function searchSupplierInfo(Request $request){
        if($request->name != null){
            $supplier = Supplier::where('name', '=', $request->name)
            ->get()
            ->first();
            
            if($supplier != null){
                return response()->json([
                    'supplier' => $supplier
                ]);
            }
        }
    }

    // This function is for searching a specific supplier
    // This returns every information about the supplier.
    public function searchSupplierInfoById(Request $request){
        if($request->id != null){
            $supplier = Supplier::find($request->id);
            
            return response()->json([
                'supplier' => $supplier
            ]);
        }
    }

    // This function is for searching a specific sale
    // This returns every information about the sale.
    public function searchBySaleId(Request $request){
        if($request->saleId != null){
            $sales = ProductSale::where('sale_id','=',$request->saleId)
            ->with('product')
            ->get();

            return response()->json([
                'sales' => $sales,
            ]);
        }
    }

    // This function gets the list of all the Drug types from the database.
    // This returns a list of drug types
    public function getDrugTypes(){
        $drugTypes = DrugType::orderBy('description','asc')
        ->select('description')
        ->get();
        return response($drugTypes);
    }

    // This function gets the list of all the generic names from the database.
    // This returns a list of generic names
    public function getGenericNames(){
        $genericNames = GenericName::orderBy('description','asc')
        ->select('description')
        ->get();
        return response($genericNames);
    }

    // This function gets the list of all the brand names from the database.
    // This returns a list of brand names
    public function getBrandNames(){
        $brandNames = Product::orderBy('brand_name','asc')
        ->select('brand_name')
        ->get();
        return response($brandNames);
    }

    // This function gets the list of all the manufcturers from the database.
    // This returns a list of manufcturers
    public function getManufacturers(){
        $manufacturers = Manufacturer::orderBy('name','asc')
        ->select('name')
        ->get();
        return response($manufacturers);
    }

    // This function gets the list of all the suppliers from the database.
    // This returns a list of suppliers
    public function getSuppliers(){
        $suppliers = Supplier::orderBy('name','asc')
        ->select('name')
        ->get();
        return response($suppliers);
    }

    // This function insert sale(s) from the database.
    public function insertSale(Request $request){

        $object = $request->input('inventoryList');
        // $size = 0;
        // $inventoryList = array();
        // $productList = array();

        $customer = Customer::firstOrCreate([
            'name' => $request->input('customerName')
        ]);

        $sale = Sale::create([
            'sale_date' => date('Y-m-d'),
            'customer_id' => $customer->id
        ]);

        foreach($object as $item){
            
            $inventory = Inventory::find($item['inventoryId']);
            if($inventory->quantity >= $inventory->sold + $item['inventorySold']){
                $inventory->sold = $inventory->sold + $item['inventorySold'];
                $inventory->save();
            }

            $cart = Cart::find($item['cartId']);
            $cart->delete();

            $product = Product::find($item['productId']);
            if(($product->inventories->sum('quantity') - $product->inventories->sum('sold')) == 0){
                $product->status = 'Out-of-stock';
                $product->save();
            }

            $productSale = ProductSale::firstOrCreate([
                'product_id' => $product->id,
                'sale_id' => $sale->id,
                'inventory_id' => $inventory->id,
                'quantity' => $item['inventorySold'],
                'price' => $item['priceSold']
            ]);

            // $size++;
            // $inventoryList[$size] = $inventory;
            // $inventoryList[$size]["cart"] = $cart;

            // $productList[$size] = $product;
        }

        // $inventory = new Inventory();
        // $inventory = Inventory::find($request->input('id'));
        // $inventory->sold = $inventory->sold + $request->input('sold');
        // $inventory->save();

        return response()->json([
            // 'inventoryList' => $inventoryList
            // 'productList' => $productList,
            'message' => 'Purchase successful.'
        ]);
    }

}
