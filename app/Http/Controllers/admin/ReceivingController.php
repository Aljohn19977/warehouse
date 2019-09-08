<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Purchase_Order;
use App\Models\Purchase_Order_Item;

class ReceivingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.receiving.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function api_transaction_list()
    {
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

        $transaction_items = $transaction->purchase_order_items;

        $data = array();

        foreach($transaction_items as $transaction_item){
            $item = Item::findOrFail($transaction_item->item_id);
            $nestedData['id']  = $transaction_item->id;
            $nestedData['item_id']  = $item->item_id;
            $nestedData['item_name']  = $item->name;
            $nestedData['item_uom'] = $item->uom_item->name;
            $nestedData['quantity']  = $transaction_item->quantity;
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

    public function receive_item(){
        
    }

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
        //
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
