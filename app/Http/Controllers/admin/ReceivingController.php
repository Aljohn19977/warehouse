<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Purchase_Order;
use App\Models\Purchase_Order_Item;
use App\Models\Receiving_Item;
use App\Models\Receiving_Missing_Item;
use App\Models\Receiving_Damage_Item;
use App\Models\Receiving;
use App\Models\Inventory_Item;
use App\Models\Inventory_Batch_List;
use Redirect;
use PDF;

class ReceivingController extends Controller
{
    public function index(){
        return view('admin.receiving.index');
    }

    public function get_batch_id()
    {
        $batch_id_prefix = 'BA';
        
        $batch_id_not_clean = preg_replace("/[:-]/","", Carbon::now());
        $batch_id = preg_replace('/\s+/', '', $batch_id_prefix.'-'.$batch_id_not_clean);

        
        return response()->json([
                        'batch_id'=>$batch_id,
                        ]);
    }

    public function api_transaction_list(){

        $purchase_order = Purchase_Order::where('status','=','Placed')->get(['id','purchase_order_id','transaction_id']);


        
        $data = array();
 
        if ($purchase_order)
        {
          foreach ($purchase_order as $value) {
            $nestedData['purchase_order_id']  = $value->purchase_order_id;
            $nestedData['transaction_id']  = $value->transaction_id;
            $nestedData['id']  = $value->id;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }

    public function api_received_list(){

        $received_order = Receiving::where('status','=','Receiving')->get(['id','receiving_id','transaction_id']);
        
        $data = array();
 
        if ($received_order)
        {
          foreach ($received_order as $value) {
            $nestedData['receiving_id']  = $value->receiving_id;
            $nestedData['transaction_id']  = $value->transaction_id;
            $nestedData['id']  = $value->id;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }

    public function get_transaction_info($id){

        $transaction = Purchase_Order::findOrfail($id);

        $check_receiving = Receiving::where('transaction_id','=', $transaction->transaction_id)->first();


        if($check_receiving == null){

            $transaction_items = $transaction->purchase_order_items;

            $data = array();
    
            foreach($transaction_items as $transaction_item){
    
                $item = Item::findOrFail($transaction_item->item_id);
    
                $nestedData['id']  = $transaction_item->id;
                $nestedData['item_id']  = $item->item_id;
                $nestedData['item_name']  = $item->name;
                $nestedData['item_uom'] = $transaction_item->item_uom;
                $nestedData['quantity']  = $transaction_item->quantity;
                $nestedData['quantity_received']  = 0;
                $nestedData['quantity_missing']  = 0;
                $nestedData['quantity_damage']  = 0;
                $data[] = $nestedData;
            }
    
            return response()->json([
                'purchase_order_id'=> $transaction->purchase_order_id,
                'transaction_id'=> $transaction->transaction_id,
                'supplier_id'=> $transaction->supplier->supplier_id,
                'supplier_name'=>$transaction->supplier->fullname,
                'supplier_company'=>$transaction->supplier->company->name,
                'order_date'=> $transaction->order_date,
                'status'=> $transaction->status,
                'purchase_order_items'=> $data
      
                ]);

        }else{

            $transaction_items = $transaction->purchase_order_items;

          

            $data = array();
    
            foreach($transaction_items as $transaction_item){
                $item = Item::findOrFail($transaction_item->item_id);
    
                $quantity_received_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$transaction_item->item_id)->where('purchase_order_item_id','=',$transaction_item->id)->value('quantity');
                $quantity_received_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$transaction_item->item_id)->where('purchase_order_item_id','=',$transaction_item->id)->value('quantity');
                $quantity_received_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$transaction_item->item_id)->where('purchase_order_item_id','=',$transaction_item->id)->value('quantity');

                
                if ($quantity_received_item == null){
                    $quantity_received_item = 0;
                }
                if ($quantity_received_missing_item == null){
                    $quantity_received_missing_item = 0;
                }
                if ($quantity_received_damage_item == null){
                    $quantity_received_damage_item = 0;
                }

                    $nestedData['id']  = $transaction_item->id;
                    $nestedData['item_id']  = $item->item_id;
                    $nestedData['item_name']  = $item->name;
                    $nestedData['item_uom'] = $transaction_item->item_uom;
                    $nestedData['quantity']  = $transaction_item->quantity;
                    $nestedData['quantity_received']  = $quantity_received_item;
                    $nestedData['quantity_missing']  = $quantity_received_missing_item;
                    $nestedData['quantity_damage']  = $quantity_received_damage_item;
                    $data[] = $nestedData;
            }
    
            return response()->json([
                'purchase_order_id'=> $transaction->purchase_order_id,
                'transaction_id'=> $transaction->transaction_id,
                'supplier_id'=> $transaction->supplier->supplier_id,
                'supplier_name'=>$transaction->supplier->fullname,
                'supplier_company'=>$transaction->supplier->company->name,
                'order_date'=> $transaction->order_date,
                'status'=> $transaction->status,
                'purchase_order_items'=> $data
      
                ]);

        }

    }


    public function get_receiving_order_info($id){

        $receiving = Receiving::findOrfail($id);

        $purchase_order = Purchase_Order::where('transaction_id','=',$receiving->transaction_id)->first();

        $ordered_items = $purchase_order->purchase_order_items;
       
        $total_accepted_items = 0;
        $total_missing_items = 0;
        $total_damage_items = 0;
        $subtotal = 0;
        $tax = 0;

        foreach($ordered_items as $ordered_item){
    
            $item = Item::findOrFail($ordered_item->item_id);

            $quantity_received_item = Receiving_Item::where('receiving_id','=',$receiving->receiving_id)->where('item_id','=',$ordered_item->item_id)->where('purchase_order_item_id','=',$ordered_item->id)->value('quantity');
            $quantity_received_missing_item = Receiving_Missing_Item::where('receiving_id','=',$receiving->receiving_id)->where('item_id','=',$ordered_item->item_id)->where('purchase_order_item_id','=',$ordered_item->id)->value('quantity');
            $quantity_received_damage_item = Receiving_Damage_Item::where('receiving_id','=',$receiving->receiving_id)->where('item_id','=',$ordered_item->item_id)->where('purchase_order_item_id','=',$ordered_item->id)->value('quantity');

            if($quantity_received_item == null){
                $quantity_received_item = 0;
            }if($quantity_received_missing_item == null){
                $quantity_received_missing_item = 0;
            }if($quantity_received_damage_item == null){
                $quantity_received_damage_item = 0;
            }

            $subtotal += $ordered_item->price*$quantity_received_item;
            $tax +=  $ordered_item->price*$quantity_received_item*$ordered_item->tax/100;

            $nestedData['id']  = $ordered_item->id;
            $nestedData['item_id']  = $item->item_id;
            $nestedData['item_name']  = $item->name;
            $nestedData['item_uom'] = $ordered_item->item_uom;
            $nestedData['quantity']  = $ordered_item->quantity;
            $nestedData['quantity_received']  = $quantity_received_item;
            $nestedData['quantity_missing']  = $quantity_received_missing_item;
            $nestedData['quantity_damage']  = $quantity_received_damage_item;
            $nestedData['tax']  =  $ordered_item->tax;
            $nestedData['price']  =  $ordered_item->price;
            $nestedData['subtotal']  = ($ordered_item->price*$quantity_received_item) + ($ordered_item->price*$quantity_received_item*$ordered_item->tax/100);


            $total_accepted_items += $quantity_received_item;
            $total_missing_items += $quantity_received_missing_item;
            $total_damage_items += $quantity_received_damage_item;
            $data[] = $nestedData;
        }

        // if($total_accepted_items == null){
        //     $total_accepted_items = 0;
        // }else if($total_missing_items == null){
        //     $total_missing_items = 0;
        // }else if($total_damage_items == null){
        //     $total_damage_items = 0;
        // }


        return response()->json([
            'supplier_id'=> $purchase_order->supplier->supplier_id,
            'supplier_name'=>$purchase_order->supplier->fullname,
            'supplier_company'=>$purchase_order->supplier->company->name,
            'ordered_date'=> $receiving->purchase_order->order_date,
            'received_date'=> $receiving->updated_at->format('Y/m/d'),
            'status'=> $receiving->status,
            'total_accepted_items'=> $total_accepted_items,
            'total_missing_items'=> $total_missing_items,
            'total_damage_items'=> $total_damage_items,
            'subtotal'=> $subtotal,
            'tax'=> $tax,
            'total'=> $subtotal + $tax,
            'received_order_items'=> $data
            ]);

    }

    public function receive_item_info($id){

        $receive_item_info = Purchase_Order_Item::findOrfail($id);

        $check_receiving = Receiving::where('transaction_id','=', $receive_item_info->purchase_order->transaction_id)->first();

        if($check_receiving != null){
            $quantity_received_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');
            $quantity_received_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');
            $quantity_received_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity'); 
           
            $total_received_quantity = $quantity_received_item + $quantity_received_missing_item + $quantity_received_damage_item;
        
            $remaining_quantity = $receive_item_info->quantity - $total_received_quantity;

        }else{


            $quantity_received_item = 0;
            $quantity_received_missing_item = 0;
            $quantity_received_damage_item = 0;

            $total_received_quantity = $quantity_received_item + $quantity_received_missing_item + $quantity_received_damage_item;
        
            $remaining_quantity = $receive_item_info->quantity - $total_received_quantity;
        }


        return response()->json([
            'remaining_quantity'=> $remaining_quantity,
            'purchase_order_id'=> $receive_item_info->purchase_order_id
            ]);

    }

    public function receive_item(Request $request){
        
        $receive_item_info = Purchase_Order_Item::findOrfail($request->id);

        $check_receiving = Receiving::where('transaction_id','=', $receive_item_info->purchase_order->transaction_id)->first();

        $receiving_id_prefix = 'RO';
        $receiving_id_not_clean = preg_replace("/[:-]/","", Carbon::now());

        $receiving_id = preg_replace('/\s+/', '', $receiving_id_prefix.'-'.$receiving_id_not_clean);

        if($check_receiving == null){

                if($request->quantity <= 0){
                    return response()->json(['error' => 'Invalid Input'], 422); 
                }else if($request->quantity > $receive_item_info->quantity){
                    return response()->json(['error' => 'Invalid Input'], 422); 
                }
    
        
                $receiving = new Receiving;
                $receiving->receiving_id = $receiving_id;
                $receiving->transaction_id = $receive_item_info->purchase_order->transaction_id;
                $receiving->supplier_id = $receive_item_info->purchase_order->supplier_id;
                $receiving->order_date = $receive_item_info->purchase_order->order_date;
                $receiving->location = $request->location;
                $receiving->receiver_name = $request->receiver_name;
                $receiving->status = 'Ongoing';
                $receiving->save();   
                
        
                $receiving_item = new Receiving_Item;
                $receiving_item->receiving_id = $receiving_id;
                $receiving_item->purchase_order_item_id = $receive_item_info->id;
                $receiving_item->item_id = $receive_item_info->item_id;
                $receiving_item->quantity = $request->quantity;
                $receiving_item->location = $request->location;
                $receiving_item->bar_code = false;
                $receiving_item->price = ($receive_item_info->tax*$receive_item_info->price/100) + $receive_item_info->price;
                $receiving_item->save();   
        
                return response()->json([
                    'successs'=> 'Success',
                    ]);

        }else{

                $check_receive_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');

                if($check_receive_item != null){
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->update(['quantity'=>$check_receive_item + $request->quantity]);
                        return 'added-old-item';
                    }
                }else{
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        // Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->update(['quantity'=>$request->quantity]);

                        $receiving_item = new Receiving_Item;
                        $receiving_item->receiving_id = $check_receiving->receiving_id;
                        $receiving_item->purchase_order_item_id = $receive_item_info->id;
                        $receiving_item->item_id = $receive_item_info->item_id;
                        $receiving_item->quantity = $request->quantity;
                        $receiving_item->location = $request->location;
                        $receiving_item->bar_code = false;
                        $receiving_item->price = ($receive_item_info->tax*$receive_item_info->price/100) + $receive_item_info->price;
                        $receiving_item->save();   

                        return 'added-new-item';
                    }
                }

        }


    }

    public function receive_missing_item(Request $request){
        
        $receive_item_info = Purchase_Order_Item::findOrfail($request->id);

        $check_receiving = Receiving::where('transaction_id','=', $receive_item_info->purchase_order->transaction_id)->first();

        $receiving_id_prefix = 'RO';
        $receiving_id_not_clean = preg_replace("/[:-]/","", Carbon::now());

        $receiving_id = preg_replace('/\s+/', '', $receiving_id_prefix.'-'.$receiving_id_not_clean);

        if($check_receiving == null){

                if($request->quantity <= 0){
                    return response()->json(['error' => 'Invalid Input'], 422); 
                }else if($request->quantity > $receive_item_info->quantity){
                    return response()->json(['error' => 'Invalid Input'], 422); 
                }
    
        
                $receiving = new Receiving;
                $receiving->receiving_id = $receiving_id;
                $receiving->transaction_id = $receive_item_info->purchase_order->transaction_id;
                $receiving->supplier_id = $receive_item_info->purchase_order->supplier_id;
                $receiving->order_date = $receive_item_info->purchase_order->order_date;
                $receiving->location = $request->location;
                $receiving->receiver_name = $request->receiver_name;                
                $receiving->status = 'Ongoing';
                $receiving->save();   
        
                $receiving_item = new Receiving_Missing_Item;
                $receiving_item->receiving_id = $receiving_id;
                $receiving_item->purchase_order_item_id = $receive_item_info->id;
                $receiving_item->item_id = $receive_item_info->item_id;
                $receiving_item->quantity = $request->quantity;
                $receiving_item->save();   
        
                return response()->json([
                    'successs'=> 'Success',
                    ]);

        }else{

                $check_receive_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');
                $check_receive_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');
                $check_receive_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');

                if($check_receive_missing_item != null){
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->update(['quantity'=>$check_receive_item + $request->quantity]);
                        return 'added-old-missing_item';
                    }
                }
                else{
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        // Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->update(['quantity'=>$request->quantity]);

                        $receiving_item = new Receiving_Missing_Item;
                        $receiving_item->receiving_id = $check_receiving->receiving_id;
                        $receiving_item->purchase_order_item_id = $receive_item_info->id;
                        $receiving_item->item_id = $receive_item_info->item_id;
                        $receiving_item->quantity = $request->quantity;
                        $receiving_item->save();   

                        return 'added-new-missing_item';
                    }
                }

        }


    }

    public function receive_damage_item(Request $request){
        
        $receive_item_info = Purchase_Order_Item::findOrfail($request->id);

        $check_receiving = Receiving::where('transaction_id','=', $receive_item_info->purchase_order->transaction_id)->first();

        $receiving_id_prefix = 'RO';
        $receiving_id_not_clean = preg_replace("/[:-]/","", Carbon::now());

        $receiving_id = preg_replace('/\s+/', '', $receiving_id_prefix.'-'.$receiving_id_not_clean);

        if($check_receiving == null){

                if($request->quantity <= 0){
                    return response()->json(['error' => 'Invalid Input'], 422); 
                }else if($request->quantity > $receive_item_info->quantity){
                    return response()->json(['error' => 'Invalid Input'], 422); 
                }
    
        
                $receiving = new Receiving;
                $receiving->receiving_id = $receiving_id;
                $receiving->transaction_id = $receive_item_info->purchase_order->transaction_id;
                $receiving->supplier_id = $receive_item_info->purchase_order->supplier_id;
                $receiving->order_date = $receive_item_info->purchase_order->order_date;
                $receiving->location = $request->location;
                $receiving->receiver_name = $request->receiver_name;
                $receiving->status = 'Ongoing';
                $receiving->save();   
        
                $receiving_item = new Receiving_Damage_Item;
                $receiving_item->receiving_id = $receiving_id;
                $receiving_item->purchase_order_item_id = $receive_item_info->id;
                $receiving_item->item_id = $receive_item_info->item_id;
                $receiving_item->quantity = $request->quantity;
                $receiving_item->save();   
        
                return response()->json([
                    'successs'=> 'Success',
                    ]);

        }else{

                $check_receive_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');
                $check_receive_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');
                $check_receive_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->value('quantity');

                if($check_receive_damage_item != null){
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->update(['quantity'=>$check_receive_item + $request->quantity]);
                        return 'added-old-damage_item';
                    }
                }else{
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        // Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->where('purchase_order_item_id','=',$receive_item_info->id)->update(['quantity'=>$request->quantity]);

                        $receiving_item = new Receiving_Damage_Item;
                        $receiving_item->receiving_id = $check_receiving->receiving_id;
                        $receiving_item->purchase_order_item_id = $receive_item_info->id;
                        $receiving_item->item_id = $receive_item_info->item_id;
                        $receiving_item->quantity = $request->quantity;
                        $receiving_item->save();   

                        return 'added-new-damage_item';
                    }
                }

        }


    }

    public function get_received_item($id){
        
        $transaction = Purchase_Order::findOrfail($id);

        $check_receiving = Receiving::where('transaction_id','=', $transaction->transaction_id)->first();

        if($check_receiving == null){

        }else{

            $transaction_items = $check_receiving->received_items;

            $data = array();
    
            foreach($transaction_items as $transaction_item){


                $item = Purchase_Order_Item::findOrfail($transaction_item->purchase_order_item_id);

                $name = Item::findOrFail($transaction_item->item_id);

                $nestedData['id']  = $transaction_item->id;
                $nestedData['item_id']  = $name->item_id;
                $nestedData['item_name']  = $name->name;
                $nestedData['item_uom'] = $item->item_uom;
                $nestedData['quantity']  = $transaction_item->quantity;
                $nestedData['date_received']  = Carbon::parse($transaction_item->created_at)->format('Y/m/d');
                $data[] = $nestedData;
            }

            return response()->json([
                'receive_order_items'=> $data      
                ]);
    
        }
    }

    public function get_received_missing_item($id){
        
        $transaction = Purchase_Order::findOrfail($id);

        $check_receiving = Receiving::where('transaction_id','=', $transaction->transaction_id)->first();

        if($check_receiving == null){

        }else{

            $transaction_items = $check_receiving->received_missing_items;

            $data = array();
    
            foreach($transaction_items as $transaction_item){

                $item = Purchase_Order_Item::findOrfail($transaction_item->purchase_order_item_id);
                
                $name = Item::findOrFail($transaction_item->item_id);

                $nestedData['id']  = $transaction_item->id;
                $nestedData['item_id']  = $name->item_id;
                $nestedData['item_name']  = $name->name;
                $nestedData['item_uom'] = $item->item_uom;
                $nestedData['quantity']  = $transaction_item->quantity;
                $nestedData['date_received']  = Carbon::parse($transaction_item->created_at)->format('Y/m/d');
                $data[] = $nestedData;
            }

            return response()->json([
                'receive_order_items'=> $data      
                ]);
    
        }
    }

    public function get_received_damage_item($id){
        
        $transaction = Purchase_Order::findOrfail($id);

        $check_receiving = Receiving::where('transaction_id','=', $transaction->transaction_id)->first();

        if($check_receiving == null){

        }else{

            $transaction_items = $check_receiving->received_damage_items;

            $data = array();
    
            foreach($transaction_items as $transaction_item){

                $item = Purchase_Order_Item::findOrfail($transaction_item->purchase_order_item_id);
                
                $name = Item::findOrFail($transaction_item->item_id);

                $nestedData['id']  = $transaction_item->id;
                $nestedData['item_id']  = $name->item_id;
                $nestedData['item_name']  = $name->name;
                $nestedData['item_uom'] = $item->item_uom;
                $nestedData['quantity']  = $transaction_item->quantity;
                $nestedData['date_received']  = Carbon::parse($transaction_item->created_at)->format('Y/m/d');
                $data[] = $nestedData;
            }

            return response()->json([
                'receive_order_items'=> $data      
                ]);
    
        }
    }

    public function undo_receive_item_info($id){

        $received_item = Receiving_Item::findOrfail($id);

        $check_receiving = Receiving::where('receiving_id','=', $received_item->receiving_id)->first();

        $purchase_order_id = Purchase_Order::where('transaction_id','=',$check_receiving->transaction_id)->value('id');

            return response()->json([
                'quantity'=> $received_item->quantity,
                'purchase_order_id'=> $purchase_order_id
                ]);
            
    }

    public function undo_receive_missing_item_info($id){

        $received_missing_item = Receiving_Missing_Item::findOrfail($id);

        $check_receiving = Receiving::where('receiving_id','=', $received_missing_item->receiving_id)->first();

        $purchase_order_id = Purchase_Order::where('transaction_id','=',$check_receiving->transaction_id)->value('id');

            return response()->json([
                'quantity'=> $received_missing_item->quantity,
                'purchase_order_id'=> $purchase_order_id
                ]);

    }

    public function undo_receive_damage_item_info($id){

        $received_damage_item = Receiving_Damage_Item::findOrfail($id);

        $check_receiving = Receiving::where('receiving_id','=', $received_damage_item->receiving_id)->first();

        $purchase_order_id = Purchase_Order::where('transaction_id','=',$check_receiving->transaction_id)->value('id');

            return response()->json([
                'quantity'=> $received_damage_item->quantity,
                'purchase_order_id'=> $purchase_order_id
                ]);

    }

    public function undo_receive_item(Request $request){

        $quantity_received_item = Receiving_Item::where('id','=',$request->id)->first();

        if($quantity_received_item->quantity <= 0 ){
            return response()->json(['error' => 'Invalid Input'], 422); 
        }else if($request->quantity > $quantity_received_item->quantity){
            return response()->json(['error' => 'Invalid Input'], 422); 
        }else{

                Receiving_Item::where('id','=',$request->id)->decrement('quantity', $request->quantity);

                $quantity_received_items = Receiving_Item::where('id','=',$request->id)->first();
                $quantity_received_missing_item = Receiving_Missing_Item::where('receiving_id','=',$quantity_received_items->receiving_id)->value('quantity');
                $quantity_received_damage_item = Receiving_Damage_Item::where('receiving_id','=',$quantity_received_items->receiving_id)->value('quantity');
                
                if($quantity_received_items->quantity == 0){
                    Receiving_Item::where('id','=',$request->id)->delete();
                }
                
                $check_transaction = $quantity_received_items->quantity + $quantity_received_missing_item + $quantity_received_damage_item;

                if($check_transaction == 0){
                    Receiving::where('receiving_id','=', $quantity_received_items->receiving_id)->delete();
                }
                


            return response()->json([
                'successs'=> 'Success',
                ]);
        }


    }

    public function undo_receive_missing_item(Request $request){

        $quantity_received_missing_item = Receiving_Missing_Item::where('id','=',$request->id)->first();

        if($quantity_received_missing_item->quantity <= 0 ){
            return response()->json(['error' => 'Invalid Input'], 422); 
        }else if($request->quantity > $quantity_received_missing_item->quantity){
            return response()->json(['error' => 'Invalid Input'], 422); 
        }else{
                Receiving_Missing_Item::where('id','=',$request->id)->decrement('quantity', $request->quantity);

                $quantity_received_missing_items = Receiving_Missing_Item::where('id','=',$request->id)->first();
                $quantity_received_item = Receiving_Item::where('receiving_id','=',$quantity_received_missing_items->receiving_id)->value('quantity');
                $quantity_received_damage_item = Receiving_Damage_Item::where('receiving_id','=',$quantity_received_missing_items->receiving_id)->value('quantity');

                if($quantity_received_missing_items->quantity == 0){
                    Receiving_Missing_Item::where('id','=',$request->id)->delete();
                }
                
                $check_transaction = $quantity_received_missing_items->quantity + $quantity_received_item + $quantity_received_damage_item;

                if($check_transaction == 0){
                    Receiving::where('receiving_id','=', $quantity_received_missing_items->receiving_id)->delete();
                }
                


            return response()->json([
                'successs'=> 'Success',
                ]);
        }
    }

    public function undo_receive_damage_item(Request $request){

        $quantity_received_damage_item = Receiving_Damage_Item::where('id','=',$request->id)->first();

        if($quantity_received_damage_item->quantity <= 0 ){
            return response()->json(['error' => 'Invalid Input'], 422); 
        }else if($request->quantity > $quantity_received_damage_item->quantity){
            return response()->json(['error' => 'Invalid Input'], 422); 
        }else{

                Receiving_Damage_Item::where('id','=',$request->id)->decrement('quantity', $request->quantity);

                $quantity_received_damage_items = Receiving_Damage_Item::where('id','=',$request->id)->first();
                $quantity_received_item = Receiving_Item::where('receiving_id','=',$quantity_received_damage_items->receiving_id)->value('quantity');
                $quantity_received_missing_item = Receiving_Missing_Item::where('receiving_id','=',$quantity_received_damage_items->receiving_id)->value('quantity');

                if($quantity_received_damage_items->quantity == 0){
                    Receiving_Damage_Item::where('id','=',$request->id)->delete();
                }

                $check_transaction = $quantity_received_damage_items->quantity + $quantity_received_item + $quantity_received_missing_item;
                    
                if($check_transaction == 0){
                    Receiving::where('receiving_id','=', $quantity_received_damage_items->receiving_id)->delete();
                }
                


            return response()->json([
                'successs'=> 'Success',
                ]);
        }

    }

    public function receiving_order(Request $request){

        $transaction = Purchase_Order::findOrfail($request->id);

        $check_receiving = Receiving::where('transaction_id','=', $transaction->transaction_id)->first();


        $received_items = $transaction->purchase_order_items;
        $acceptable_items = $check_receiving->received_items;
        $damage_items = $check_receiving->received_damage_items;
        $missing_items = $check_receiving->received_missing_items;


        $acceptable_item_total = 0;
        $damage_item_total = 0;
        $missing_item_total = 0;
        $received_item_total = 0;

        foreach($acceptable_items as $acceptable_item){
            $acceptable_item_total += $acceptable_item->quantity;
        }
        
        foreach($damage_items as $damage_item){
            $damage_item_total += $damage_item->quantity;
        }

        foreach($missing_items as $missing_item){
            $missing_item_total += $missing_item->quantity;
        }

        foreach($received_items as $received_item){
            $received_item_total += $received_item->quantity;
        }

        $total_receive = $missing_item_total + $damage_item_total + $acceptable_item_total;
        if($total_receive == $received_item_total){

                Receiving_Item::where('receiving_id','=', $check_receiving->receiving_id)->update(['status'=>'Receiving']);
                Receiving::where('transaction_id','=', $transaction->transaction_id)->update(['status'=>'Receiving']);
                Purchase_Order::where('transaction_id','=', $transaction->transaction_id)->update(['status'=>'Receiving']);

                $receiving = Receiving::where('transaction_id','=', $transaction->transaction_id)->first();

                return response()->json([
                    'transaction_id'=> $receiving->transaction_id,
                    'receiving_id'=> $receiving->receiving_id,
                    'supplier_id'=> $transaction->supplier->supplier_id,
                    'supplier_name'=>$transaction->supplier->fullname,
                    'supplier_company'=>$transaction->supplier->company->name,
                    'order_date'=> $transaction->order_date, 
                    'received_date'=> Carbon::parse($receiving->updated_date)->format('Y/m/d')
                    ]);
                    

        }else{
            return response()->json(['error' => 'Incomplete Order Receiving Process'], 422); 
        }

    }

    public function receive_order(Request $request){

        if($request->id == null ){
            return response()->json(['error' => 'Invalid Input'], 422); 
        }
  
        $received = Receiving::findOrfail($request->id);

        $received_items = Receiving_Item::where('receiving_id','=',$received->receiving_id)->get();

        foreach($received_items as $received_item){

            $item = Item::where('id','=',$received_item->item_id)->first();

                for($count = 0; $count < $received_item->quantity; $count++)
                {  
                        $inventory_serialize_item = new Inventory_Item;
                        $inventory_serialize_item->receiving_item_id = $received_item->id;
                        $inventory_serialize_item->item_id = $item->id;
                        $inventory_serialize_item->price = $received_item->price;
                        $inventory_serialize_item->location = $received_item->location;
                        $inventory_serialize_item->bar_code = false;
                        $inventory_serialize_item->status = 'Active';
                        $inventory_serialize_item->save();  

                        Inventory_Item::where('id','=', $inventory_serialize_item->id)->update(['serialize_item_id'=>'S-STK-'.str_pad($inventory_serialize_item->id, 14, "0", STR_PAD_LEFT)]);
                }
        }

        Receiving_Item::where('receiving_id','=',$received->receiving_id)->update(['status'=>'Received']);
        Receiving::where('transaction_id','=', $received->transaction_id)->update(['status'=>'Received']);
        Purchase_Order::where('transaction_id','=', $received->transaction_id)->update(['status'=>'Received']);
        return response()->json(['success'=>'Success']);

    }



    public function api_get_all_received_item_barcoding(Request $request){

      $columns = array(
        0 => 'receiving_id',
        1 => 'item_id',
        2 => 'name',
        3 => 'quantity',
        4 => 'price',
        5 => 'item_uom',
        6 => 'type',
        7 => 'updated_at',
        8 => 'bar_code',
      );


      $start_date = $request->start_date;
      $end_date = $request->end_date;
      $filter_type = $request->filter_type;
      $filter_receiving_id = $request->filter_receiving_id;
 
      $query = Receiving_Item::query();

      $query = $query->join('purchase_order_item', 'receiving_item.purchase_order_item_id', '=', 'purchase_order_item.id');
      $query = $query->join('items', 'receiving_item.item_id', '=', 'items.id');
      $query = $query->where('receiving_item.status','=', 'Received');

      if(!empty($filter_type)){
        $query = $query->where('items.type','=', $filter_type);
      }
      if(!empty($filter_receiving_id)){
        $query = $query->where('receiving_item.receiving_id','=', $filter_receiving_id);
      }
      if(!empty($start_date)){
        $query = $query->whereDate('receiving_item.created_at', '>=', $start_date);
      }
      if(!empty($end_date)){
        $query = $query->whereDate('receiving_item.created_at', '<=', $end_date);
      }

      $totalData = $query->count();

      $limit = $request->length;
      $start = $request->start;
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');
 

      if (empty($request->input('search.value'))){
 

            $query = Receiving_Item::query();

            $query = $query->join('purchase_order_item', 'receiving_item.purchase_order_item_id', '=', 'purchase_order_item.id');
            $query = $query->join('items', 'receiving_item.item_id', '=', 'items.id');
            $query = $query->where('receiving_item.status','=', 'Received');

            if(!empty($filter_type)){
                $query = $query->where('items.type','=', $filter_type);
              }
              if(!empty($filter_receiving_id)){
                $query = $query->where('receiving_item.receiving_id','=', $filter_receiving_id);
              }
              if(!empty($start_date)){
                $query = $query->whereDate('receiving_item.created_at', '>=', $start_date);
              }
              if(!empty($end_date)){
                $query = $query->whereDate('receiving_item.created_at', '<=', $end_date);
              }
        
            $query = $query->offset($start)->limit($limit)->orderBy($order,$dir)
            ->select('receiving_item.bar_code AS bar_code','receiving_item.id AS id','receiving_item.receiving_id AS receiving_id','items.item_id AS item_id','items.name AS name','receiving_item.quantity AS quantity','receiving_item.price AS price','items.item_uom AS item_uom','items.type AS type','receiving_item.updated_at');
            
             $received_item = $query->get();
    
                $query = Receiving_Item::query();
                $query = $query->join('purchase_order_item', 'receiving_item.purchase_order_item_id', '=', 'purchase_order_item.id');
                $query = $query->join('items', 'receiving_item.item_id', '=', 'items.id');
                $query = $query->where('receiving_item.status','=', 'Received');
    
                if(!empty($filter_type)){
                    $query = $query->where('items.type','=', $filter_type);
                  }
                  if(!empty($filter_receiving_id)){
                    $query = $query->where('receiving_item.receiving_id','=', $filter_receiving_id);
                  }
                  if(!empty($start_date)){
                    $query = $query->whereDate('receiving_item.created_at', '>=', $start_date);
                  }
                  if(!empty($end_date)){
                    $query = $query->whereDate('receiving_item.created_at', '<=', $end_date);
                  }

                $totalFiltered = $query->count();

                

      }
      else{
         $search = $request->input('search.value');

         $query = Purchase_Order::query();
         
         $query = $query->join('suppliers', 'purchase_order.supplier_id', '=', 'suppliers.id');

            if(!empty($filter_status)){
                $query = $query->where('purchase_order.status','=', $filter_status);
            }
            if(!empty($filter_supplier)){
                $query = $query->where('purchase_order.supplier_id','=', $filter_supplier);
            }
            if(!empty($start_date)){
                $query = $query->whereDate('purchase_order.created_at', '>=', $start_date);
            }
            if(!empty($end_date)){
                $query = $query->whereDate('purchase_order.created_at', '<=', $end_date);
            }

            $query = $query->WhereRaw("(purchase_order.id AND purchase_order.purchase_order_id LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.transaction_id LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND suppliers.fullname LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.order_date LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.status LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.total LIKE ?)", "%{$search}%")
            ->offset($start)
            ->limit($limit)
            ->orderBy($order,$dir)
            ->select('purchase_order.id','purchase_order.purchase_order_id','purchase_order.transaction_id','suppliers.fullname AS supplier_id','purchase_order.order_date','purchase_order.status','purchase_order.total');
        
            $purchase_order = $query->get();

            $query = Purchase_Order::query();
            $query = $query->join('suppliers', 'purchase_order.supplier_id', '=', 'suppliers.id');
    
            if(!empty($filter_status)){
                $query = $query->where('purchase_order.status','=', $filter_status);
            }
            if(!empty($filter_supplier)){
                $query = $query->where('purchase_order.supplier_id','=', $filter_supplier);
            }
            if(!empty($start_date)){
                $query = $query->whereDate('purchase_order.created_at', '>=', $start_date);
            }
            if(!empty($end_date)){
                $query = $query->whereDate('purchase_order.created_at', '<=', $end_date);
            }
    
            $query = $query->WhereRaw("(purchase_order.id AND purchase_order.purchase_order_id LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.transaction_id LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND suppliers.fullname LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.order_date LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.status LIKE ?)", "%{$search}%")
            ->orWhereRaw("(purchase_order.id AND purchase_order.total LIKE ?)", "%{$search}%")
            ->offset($start)
            ->limit($limit)
            ->orderBy($order,$dir)
            ->select('purchase_order.id','purchase_order.purchase_order_id','purchase_order.transaction_id','suppliers.fullname AS supplier_id','purchase_order.updated_at','purchase_order.status','purchase_order.total');
        
            $totalFiltered = $query->get()->count();
      }
 
      $data = array();
 
 
      if ($received_item){
        foreach ($received_item as $value) {

        
         if($value->type == 'Serialize'){
            $action = '<button class="btn btn-primary table_print" id="table_print_serialize" data-id="'.$value->id.'" style="color:white;"><i class="fas fa-print"></i></button>';
         }else if($value->type == 'Batch Tracked'){
            $action = '<button class="btn btn-primary table_print" id="table_print_batch_tracked" data-id="'.$value->id.'" style="color:white;"><i class="fas fa-print"></i></button>';
         }

         if($value->bar_code == true){
            $bar_code = '<button class="btn btn-primary" style="color:white;">Completed</button>';
         }else if($value->bar_code == false){
            $bar_code = '<button class="btn btn-danger" style="color:white;">Incomplete</button>';
         }

          $nestedData['receiving_id']  = $value->receiving_id;
          $nestedData['item_id']  = $value->item_id;
          $nestedData['name']  = $value->name; 
          $nestedData['item_uom']  = $value->item_uom; 
          $nestedData['type']  = $value->type; 
          $nestedData['quantity']  = $value->quantity; 
          $nestedData['updated_at']  = $value->updated_at->format('y/m/d'); 
          $nestedData['price']  = $value->price; 
          $nestedData['bar_code']  = $bar_code; 
          $nestedData['action']  = $action;       

          $data[] = $nestedData;
        }
      }
 

      $json_data = array(
        "draw" => ($request->draw ? intval($request->draw):0), 
        "recordsTotal" => intval($totalData), 
        "recordsFiltered" => intval($totalFiltered), 
        "data" => $data, 
      );
 
      return json_encode($json_data);


    }

    public function api_get_selected_received_item_barcoding(Request $request){

        $columns = array(
          0 => 'serialize_item_id',
          1 => 'id',
          2 => 'bar_code',
        );
  
  
        $id = $request->id;
   
        $query = Inventory_Item::query();

        
        $query = $query->where('receiving_item_id','=', $id);
  
        $totalData = $query->count();
  
        $limit = $request->length;
        $start = $request->start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
   
  
        if (empty($request->input('search.value'))){
   
  
            $query = Inventory_Item::query();
  
            $query = $query->where('receiving_item_id','=', $id);
  
          
              $query = $query->offset($start)->limit($limit)->orderBy($order,$dir)
              ->select('id','serialize_item_id','bar_code');
              
               $items = $query->get();
      
                $query = Inventory_Item::query();
    
                $query = $query->where('receiving_item_id','=', $id);
  
                  $totalFiltered = $query->count();
  
                  
  
        }
        else{
           $search = $request->input('search.value');
  
           $query = Inventory_Item::query();
    
           $query = $query->where('receiving_item_id','=', $id);

    
              $query = $query->WhereRaw("(id AND serialize_item_id LIKE ?)", "%{$search}%")
              ->offset($start)
              ->limit($limit)
              ->orderBy($order,$dir)
              ->select('id','serialize_item_id','bar_code');

              $items = $query->get();
  
              $query = Inventory_Item::query();
    
              $query = $query->where('receiving_item_id','=', $id);
      
              $query = $query->WhereRaw("(id AND serialize_item_id LIKE ?)", "%{$search}%")
              ->offset($start)
              ->limit($limit)
              ->orderBy($order,$dir)
              ->select('id','serialize_item_id','bar_code');

              $totalFiltered = $query->get()->count();
        }
   
        $data = array();
   
   
        if ($items){
          foreach ($items as $value) {

  
  
            if($value->bar_code == false ){
                $bar_code = '<button class="btn btn-danger" style="color:white;">Unprinted</button>';
             }else if($value->bar_code == true){
                $bar_code = '<button class="btn btn-primary" style="color:white;">Printed</button>';
             }

            $nestedData['check_box']  = $value->id; 
            $nestedData['serialize_item_id']  = $value->serialize_item_id; 
            $nestedData['bar_code']  = $bar_code; 
        
            $data[] = $nestedData;
          }
        }
   
  
        $json_data = array(
          "draw" => ($request->draw ? intval($request->draw):0), 
          "recordsTotal" => intval($totalData), 
          "recordsFiltered" => intval($totalFiltered), 
          "data" => $data, 
        );
   
        return json_encode($json_data);
  
  
      }

    public function selected_serialize_item(Request $request)
    {

        $barcodes = array();

        foreach($request->id as $barcode){
            
            Inventory_Item::where('id','=', $barcode)->update(['expiration_date'=>$request->expiration_date]);
            $item = Inventory_Item::where('id','=', $barcode)->first();
           
            if($request->action == 'mark_printed'){
                Inventory_Item::where('id','=', $barcode)->update(['bar_code'=>true]);
            }
            if($request->action == 'unmark_printed'){
                Inventory_Item::where('id','=', $barcode)->update(['bar_code'=>false]);
            }

            $receiving_item_id = Inventory_Item::where('id','=', $barcode)->value('receiving_item_id');
            $bar_code_checker = Inventory_Item::where('receiving_item_id','=', $receiving_item_id)->where('bar_code','=',false)->count();

            if($bar_code_checker == 0){
                Receiving_Item::where('id','=',$receiving_item_id)->update(['bar_code'=>true]);
            }else{
                Receiving_Item::where('id','=',$receiving_item_id)->update(['bar_code'=>false]);
            }

            if($item->expiration_date == null){
                $expiration = '- No-Exp-Date-';
            }else{
                $expiration = 'Exp Date : '.$item->expiration_date;
            }
            
            $nestedData['id']  = $item->id; 
            $nestedData['serialize_item_id']  = $item->serialize_item_id;     
            $nestedData['item_name']  = $item->item->name;  
            $nestedData['expiration_date']  = $expiration;     
            $barcodes[] = $nestedData;
        }

        if($request->action == 'mark_printed' || $request->action == 'unmark_printed'){
            return response()->json([
                'successs'=> 'Success',
            ]);
        }

        $customPaper = array(0,0,85.04,113.39);
        $pdf = PDF::loadView('admin.receiving.serialize_item_barcode_label',compact('barcodes'))->setPaper($customPaper, 'landscape');        
        return $pdf->stream('invoice.pdf');
    }

    public function selected_serialize_item_template()
    {
        return view('admin.receiving.serialize_item_barcode_label');

    }
    
    public function add_batch(Request $request)
    {
        $this->validate($request,[
            'batch_name' => 'required',
            'batch_id' => 'required',
        ]);
        
        $inventory_batch_list = new Inventory_Batch_List;
        $inventory_batch_list->batch_id = $request->batch_id;
        $inventory_batch_list->name = $request->batch_name;
        $inventory_batch_list->expiration_date = $request->expiration_date;
        $inventory_batch_list->save();   

        return response()->json([
            'successs'=> 'Success',
        ]);

    }

    public function api_get_all_batch(Request $request){

        $columns = array(
          0 => 'batch_id',
          1 => 'name',
          2 => 'expiration_date',
        );
  
  
        $id = $request->id;
   
        $query = Inventory_Batch_List::query();
  
        $totalData = $query->count();
  
        $limit = $request->length;
        $start = $request->start;
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
   
  
        if (empty($request->input('search.value'))){
   
  
            $query = Inventory_Batch_List::query();
  
            $query = $query->offset($start)->limit($limit)->orderBy($order,$dir)
              ->select('id','batch_id','name','expiration_date');
              
               $items = $query->get();
      
            $query = Inventory_Batch_List::query();
    
            $totalFiltered = $query->count();
  
                  
  
        }
        else{
           $search = $request->input('search.value');
  
           $query = Inventory_Batch_List::query();
    
              $query = $query->WhereRaw("(id AND batch_id LIKE ?)", "%{$search}%")
              ->offset($start)
              ->limit($limit)
              ->orderBy($order,$dir)
              ->select('id','batch_id','name','expiration_date');

              $items = $query->get();
  
              $query = Inventory_Batch_List::query();
          
              $query = $query->WhereRaw("(id AND batch_id LIKE ?)", "%{$search}%")
              ->offset($start)
              ->limit($limit)
              ->orderBy($order,$dir)
              ->select('id','batch_id','name','expiration_date');

              $totalFiltered = $query->get()->count();
        }
   
        $data = array();
   
   
        if ($items){
          foreach ($items as $value) {

  
  
            if($value->expiration_date == null ){
                $value->expiration_date = '-';
            }

            $nestedData['batch_id']  = $value->batch_id; 
            $nestedData['name']  = $value->name; 
            $nestedData['expiration_date']  = $value->expiration_date;
            $nestedData['action']  = $value->expiration_date; 
        
            $data[] = $nestedData;
          }
        }
   
  
        $json_data = array(
          "draw" => ($request->draw ? intval($request->draw):0), 
          "recordsTotal" => intval($totalData), 
          "recordsFiltered" => intval($totalFiltered), 
          "data" => $data, 
        );
   
        return json_encode($json_data);
  
  
    }


    public function get_batch_quantity_remaining($id)
    {
        $receiving_item = Receiving_Item::where('id','=',$id)->first();
        $inventory_item = Inventory_Item::where('receiving_item_id','=',$id)->where('bar_code','=',true)->count();
        $quantity_remaining =  $receiving_item->quantity - $inventory_item;

        return response()->json([
            'quantity_remaining'=>$quantity_remaining,
            ]);
    }



    public function create()
    {
        
    }

    public function store(Request $request)
    {
        
    }

    public function show($id)
    {
        
    }

    public function edit($id)
    {
        
    }

    public function update(Request $request, $id)
    {
        
    }

    public function destroy($id)
    {
        
    }
}
