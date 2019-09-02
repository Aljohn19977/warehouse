<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Purchase_Order;
use App\Models\Purchase_Order_Item;


class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.purchase_order.index');
    }

    public function get_purchase_order_id()
    {
        $purchase_order_id_prefix = 'PO';
        
        $purchase_order_id_not_clean = preg_replace("/[:-]/","", Carbon::now());
        $purchase_order_id = preg_replace('/\s+/', '', $purchase_order_id_prefix.'-'.$purchase_order_id_not_clean);
        $order_date = Carbon::now()->format('Y/m/d');

        $transaction_id_prefix = 'TR';
        
        $transaction_id_not_clean = preg_replace("/[:-]/","", Carbon::now());
        $transaction_id = preg_replace('/\s+/', '', $transaction_id_prefix.'-'.$transaction_id_not_clean.$transaction_id_not_clean);
        
        return response()->json([
                        'purchase_order_id'=>$purchase_order_id,
                        'transaction_id'=>$transaction_id,
                        'order_date'=>$order_date,
                        ]);
    }

    public function get_supplier_info($id){

        $supplier = Supplier::findOrfail($id);

        $supplier_id = $supplier->supplier_id;
        $supplier_company = $supplier->company->name;

        return response()->json([
            'supplier_id'=> $supplier_id,
            'supplier_company'=> $supplier_company,
            'supplier_item'=>$supplier->item
  
            ]);
    }

    public function get_purchase_order_info($id){

        $get_id = Purchase_Order::where('purchase_order_id','=',$id)->value('id');

        $purchase_order = Purchase_Order::findOrfail($get_id);

        $purchase_order_items = $purchase_order->purchase_order_items;

        $data = array();

        foreach($purchase_order_items as $purchase_order_item){
            $item = Item::findOrFail($purchase_order_item->item_id);
            $nestedData['item_id']  = $item->item_id;
            $nestedData['item_uom'] = $item->uom_item->name;
            $nestedData['quantity']  = $purchase_order_item->quantity;
            $nestedData['price']  = $purchase_order_item->price;
            $nestedData['subtotal']  = $purchase_order_item->subtotal;
            $data[] = $nestedData;
        }


        return response()->json([
            'purchase_order_id'=> $purchase_order->purchase_order_id,
            'transaction_id'=> $purchase_order->transaction_id,
            'supplier_id'=> $purchase_order->supplier->supplier_id,
            'supplier_name'=>$purchase_order->supplier->fullname,
            'order_date'=> $purchase_order->order_date,
            'deliver_to'=> $purchase_order->deliver_to,
            'total'=> $purchase_order->total,
            'purchase_order_items'=> $data
  
            ]);
    }


    public function get_supplier_item_info_via_id($id){
            
        $item = Item::findOrfail($id);

        $item->uom_weight->acronym;

        return response()->json([
            'unit_price'=> $item->unit_price,
            'id'=> $id,
            'item_id'=> $item->item_id,
            'item_name'=> $item->name,
            'item_uom'=>$item->uom_item->acronym,    
        ]);

    }  
     

    public function get_supplier_item_info_via_item_id($id){
            
        $get_id = Item::where('item_id','=',$id)->value('id');
        
        $item = Item::findOrfail($get_id);

        $item->uom_weight->acronym;

        return response()->json([
            'unit_price'=> $item->unit_price,
            'id'=> $get_id,
            'item_id'=> $item->item_id,
            'item_name'=> $item->name,
            'item_uom'=>$item->uom_item->acronym,    
        ]);

    }  
    
    public function validation_add_item_table(Request $request){

            $this->validate($request,[
                'item_id_modal' => 'required|max:255',
                'item_name_modal' => 'required|min:0|max:255',
                'item_name' => 'required|max:255',
                'unit_price_modal' => 'required|numeric|min:1',
                'quantity_modal' => 'required|integer|min:1',
                'item_uom_modal' => 'required',
                'subtotal_modal' => 'required|regex:/^[\d\s,]*$/|min:1',
                'primary_id' => 'required|integer|min:1',
            ]);

            return $request->all();
    
    }

    public function api_supplier_list()
    {
        $suppliers = Supplier::get(['id','fullname']);
        
        $data = array();
 
        if ($suppliers)
        {
          foreach ($suppliers as $value) {
            $nestedData['id']  = $value->id;
            $nestedData['name']  = $value->fullname;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }

    public function api_purchase_order_list()
    {
        $purchase_order = Purchase_Order::get(['id','purchase_order_id']);
        
        $data = array();
 
        if ($purchase_order)
        {
          foreach ($purchase_order as $value) {
            $nestedData['purchase_order_id']  = $value->purchase_order_id;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
     
        $this->validate($request,[
            'supplier_id' => 'required',
            'purchase_order_id' => 'required',
            'transaction_id' => 'required',
            'total' => 'required',
            'order_date' => 'required|min:1',
            'deliver_to' => 'required|min:1',
        ]);

        if($request->row_item_price == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_subtotal == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_item_id == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_quantity == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }

        $purchase_order = new Purchase_Order;
        $purchase_order->purchase_order_id = $request->purchase_order_id;
        $purchase_order->transaction_id = $request->transaction_id;
        $purchase_order->supplier_id = $request->supplier_id;
        $purchase_order->order_date = $request->order_date;
        $purchase_order->deliver_to = $request->deliver_to;
        $purchase_order->status = 'open';
        $purchase_order->total = $request->total;
        $purchase_order->save();

        for($count = 0; $count < count($request->row_item_id); $count++)
         {  
                $purchase_order_item = new Purchase_Order_Item;
                $purchase_order_item->purchase_order_id = $purchase_order->id;
                $purchase_order_item->item_id = $request->row_item_id[$count];
                $purchase_order_item->quantity = $request->row_quantity[$count];
                $purchase_order_item->price = $request->row_item_price[$count];
                $purchase_order_item->subtotal = $request->row_subtotal[$count];
                $purchase_order_item->save();
         }

         return "good";
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
