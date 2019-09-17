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

class ReceivingController extends Controller
{
    public function index(){
        return view('admin.receiving.index');
    }

    public function api_transaction_list(){
        $purchase_order = Purchase_Order::where('status','=','open')->get(['id','purchase_order_id','transaction_id']);
        
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
                $nestedData['item_uom'] = $item->uom_item->name;
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
    
                $quantity_received_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$transaction_item->item_id)->value('quantity');
                $quantity_received_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$transaction_item->item_id)->value('quantity');
                $quantity_received_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$transaction_item->item_id)->value('quantity');

                
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
                    $nestedData['item_uom'] = $item->uom_item->name;
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

    public function receive_item_info($id){

        $receive_item_info = Purchase_Order_Item::findOrfail($id);

        $check_receiving = Receiving::where('transaction_id','=', $receive_item_info->purchase_order->transaction_id)->first();

        if($check_receiving != null){
            $quantity_received_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');
            $quantity_received_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');
            $quantity_received_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity'); 
           
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
                $receiving->status = 'Ongoing';
                $receiving->save();   
        
                $receiving_item = new Receiving_Item;
                $receiving_item->receiving_id = $receiving_id;
                $receiving_item->item_id = $receive_item_info->item_id;
                $receiving_item->quantity = $request->quantity;
                $receiving_item->save();   
        
                return response()->json([
                    'successs'=> 'Success',
                    ]);

        }else{

                $check_receive_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');

                if($check_receive_item != null){
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->update(['quantity'=>$check_receive_item + $request->quantity]);
                        return 'added-old-item';
                    }
                }else{
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->update(['quantity'=>$request->quantity]);

                        $receiving_item = new Receiving_Item;
                        $receiving_item->receiving_id = $check_receiving->receiving_id;
                        $receiving_item->item_id = $receive_item_info->item_id;
                        $receiving_item->quantity = $request->quantity;
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
                $receiving->status = 'Ongoing';
                $receiving->save();   
        
                $receiving_item = new Receiving_Missing_Item;
                $receiving_item->receiving_id = $receiving_id;
                $receiving_item->item_id = $receive_item_info->item_id;
                $receiving_item->quantity = $request->quantity;
                $receiving_item->save();   
        
                return response()->json([
                    'successs'=> 'Success',
                    ]);

        }else{

                $check_receive_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');
                $check_receive_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');
                $check_receive_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');

                if($check_receive_missing_item != null){
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->update(['quantity'=>$check_receive_item + $request->quantity]);
                        return 'added-old-missing_item';
                    }
                }
                else{
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->update(['quantity'=>$request->quantity]);

                        $receiving_item = new Receiving_Missing_Item;
                        $receiving_item->receiving_id = $check_receiving->receiving_id;
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
                $receiving->status = 'Ongoing';
                $receiving->save();   
        
                $receiving_item = new Receiving_Damage_Item;
                $receiving_item->receiving_id = $receiving_id;
                $receiving_item->item_id = $receive_item_info->item_id;
                $receiving_item->quantity = $request->quantity;
                $receiving_item->save();   
        
                return response()->json([
                    'successs'=> 'Success',
                    ]);

        }else{

                $check_receive_item = Receiving_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');
                $check_receive_missing_item = Receiving_Missing_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');
                $check_receive_damage_item = Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->value('quantity');

                if($check_receive_damage_item != null){
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->update(['quantity'=>$check_receive_item + $request->quantity]);
                        return 'added-old-damage_item';
                    }
                }else{
                    if($request->quantity <= 0){
                        return response()->json(['error' => 'Invalid Input'], 422); 
                    }else if($check_receive_item + $check_receive_missing_item + $check_receive_damage_item + $request->quantity > $receive_item_info->quantity){
                        return response()->json(['error' => 'Exceeded to quantity to be receive'], 422); 
                    }else{
                        Receiving_Damage_Item::where('receiving_id','=',$check_receiving->receiving_id)->where('item_id','=',$receive_item_info->item_id)->update(['quantity'=>$request->quantity]);

                        $receiving_item = new Receiving_Damage_Item;
                        $receiving_item->receiving_id = $check_receiving->receiving_id;
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
                $item = Item::findOrFail($transaction_item->item_id);
                $nestedData['id']  = $transaction_item->id;
                $nestedData['item_id']  = $item->item_id;
                $nestedData['item_name']  = $item->name;
                $nestedData['item_uom'] = $item->uom_item->name;
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
                $item = Item::findOrFail($transaction_item->item_id);
                $nestedData['id']  = $transaction_item->id;
                $nestedData['item_id']  = $item->item_id;
                $nestedData['item_name']  = $item->name;
                $nestedData['item_uom'] = $item->uom_item->name;
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
                $item = Item::findOrFail($transaction_item->item_id);
                $nestedData['id']  = $transaction_item->id;
                $nestedData['item_id']  = $item->item_id;
                $nestedData['item_name']  = $item->name;
                $nestedData['item_uom'] = $item->uom_item->name;
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

    public function receive_order(Request $request){

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

                Receiving::where('transaction_id','=', $transaction->transaction_id)->update(['status'=>'completed']);

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
