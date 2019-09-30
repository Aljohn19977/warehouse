<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Purchase_Order;
use App\Models\Purchase_Order_Item;
use PDF;


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
            $nestedData['item_name']  = $item->name;
            $nestedData['item_uom'] = $purchase_order_item->item_uom;
            $nestedData['tax'] = $purchase_order_item->tax;
            $nestedData['tax_total'] = $purchase_order_item->tax_total;
            $nestedData['quantity']  = $purchase_order_item->quantity;
            $nestedData['price']  = $purchase_order_item->price;
            $nestedData['subtotal']  = $purchase_order_item->subtotal;
            $nestedData['line_total'] = $purchase_order_item->line_total;
            $data[] = $nestedData;
        }

        return response()->json([
            'purchase_order_id'=> $purchase_order->purchase_order_id,
            'transaction_id'=> $purchase_order->transaction_id,
            'supplier_id'=> $purchase_order->supplier->supplier_id,
            'supplier_name'=>$purchase_order->supplier->fullname,
            'supplier_company'=>$purchase_order->supplier->company->name,
            'order_date'=> $purchase_order->order_date,
            'deliver_to'=> $purchase_order->deliver_to,
            'total_volume'=> $purchase_order->total_volume,
            'total_weight'=> $purchase_order->total_weight,
            'total_tax'=> $purchase_order->total_tax,
            'subtotal'=> $purchase_order->subtotal,
            'total'=> $purchase_order->total,
            'status'=> $purchase_order->status,
            'comments'=> $purchase_order->comments,
            'purchase_order_items'=> $data
  
            ]);
    }


    public function get_supplier_item_info_via_id($id){
            
        $item = Item::findOrfail($id);


        return response()->json([
            'purchase_price'=> $item->purchase_price,
            'tax'=> $item->tax,
            'volume'=> $item->volume,
            'weight'=> $item->weight,
            'weight_uom'=> $item->weight_uom,
            'item_uom'=> $item->item_uom,
            'id'=> $id,
            'item_id'=> $item->item_id,
            'item_name'=> $item->name,
        ]);

    }  
     

    public function get_supplier_item_info_via_item_id($id){
            
        $get_id = Item::where('item_id','=',$id)->value('id');
        
        $item = Item::findOrfail($get_id);


        return response()->json([
            'purchase_price'=> $item->purchase_price,
            'tax'=> $item->tax,
            'volume'=> $item->volume,
            'weight'=> $item->weight,
            'weight_uom'=> $item->weight_uom,
            'item_uom'=>$item->item_uom,  
            'id'=> $get_id,
            'item_id'=> $item->item_id,
            'item_name'=> $item->name,
  
        ]);

    }  
    
    public function validation_add_item_table(Request $request){

            $this->validate($request,[
                'item_id_modal' => 'required|max:255',
                'item_name_modal' => 'required|min:0|max:255',
                'item_name' => 'required|max:255',
                'unit_price_modal' => 'required',
                'quantity_modal' => 'required|integer|min:1',
                'item_uom_modal' => 'required',
                'subtotal_modal' => 'required',
                'primary_id' => 'required|integer|min:1',
            ]);

            return $request->all();
    
    }

    public function api_supplier_list()
    {
        $suppliers = Supplier::get(['id','fullname','supplier_id']);
        
        $data = array();
 
        if ($suppliers)
        {
          foreach ($suppliers as $value) {
            $nestedData['id']  = $value->id;
            $nestedData['supplier_id']  = $value->supplier_id;
            $nestedData['name']  = $value->fullname;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }

    public function api_purchase_order_list($id)
    {
    
        
        if($id == 2){
            $purchase_order = Purchase_Order::where('status','=','Placed')->get(['id','purchase_order_id','transaction_id']);
        }elseif($id == 3){
            $purchase_order = Purchase_Order::where('status','=','Placed')->get(['id','purchase_order_id','transaction_id']);
        }
        elseif($id == 4){
            $purchase_order = Purchase_Order::get(['id','purchase_order_id','transaction_id']);
        }else{
            $purchase_order = Purchase_Order::where('status','=','Placed')->get(['id','purchase_order_id','transaction_id']);
        }

        

        $data = array();
 
        if ($purchase_order)
        {
          foreach ($purchase_order as $value) {
            $nestedData['purchase_order_id']  = $value->purchase_order_id;
            $nestedData['transaction_id']  = $value->transaction_id;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }

    public function cancel(Request $request)
    {
        $purchase_order = Purchase_Order::where('purchase_order_id','=',$request->purchase_order_no)->update(['status'=>'Canceled']);
    }

    public function store(Request $request)
    {

        $this->validate($request,[
            'supplier_id' => 'required',
            'purchase_order_id' => 'required',
            'transaction_id' => 'required',
            'total' => 'required',
            'subtotal' => 'required',
            'total_volume' => 'required',
            'total_weight' => 'required',
            'order_date' => 'required|min:1',
            'deliver_to' => 'required|min:1',

        ]);

        if($request->row_item_id == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_item_price == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_line_total == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_quantity == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_tax == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_tax_total == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_total == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_total_volume == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_total_weight == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_volume == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_weight == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }else if($request->row_item_uom == null){
            return response()->json(['error' => 'Invalid Input'], 422); // Status code here
        }

        $purchase_order = new Purchase_Order;
        $purchase_order->purchase_order_id = $request->purchase_order_id;
        $purchase_order->transaction_id = $request->transaction_id;
        $purchase_order->supplier_id = $request->supplier_id;
        $purchase_order->order_date = $request->order_date;
        $purchase_order->deliver_to = $request->deliver_to;
        $purchase_order->total_volume = $request->total_volume;
        $purchase_order->total_weight = $request->total_weight;
        $purchase_order->total_tax = $request->total_tax;
        $purchase_order->status = 'Placed';
        $purchase_order->subtotal = $request->subtotal;
        $purchase_order->total = $request->total;
        $purchase_order->comments = $request->comments;
        $purchase_order->save();

        for($count = 0; $count < count($request->row_item_id); $count++)
         {  
                $purchase_order_item = new Purchase_Order_Item;
                $purchase_order_item->purchase_order_id = $purchase_order->id;
                $purchase_order_item->item_id = $request->row_item_id[$count];
                $purchase_order_item->quantity = $request->row_quantity[$count];
                $purchase_order_item->price = $request->row_item_price[$count];
                $purchase_order_item->tax = $request->row_tax[$count];
                $purchase_order_item->volume = $request->row_volume[$count];
                $purchase_order_item->weight = $request->row_weight[$count];
                $purchase_order_item->item_uom = $request->row_item_uom[$count];
                $purchase_order_item->total_volume = $request->row_total_volume[$count];
                $purchase_order_item->total_weight = $request->row_total_weight[$count];
                $purchase_order_item->line_total = $request->row_line_total[$count];
                $purchase_order_item->tax_total = $request->row_tax_total[$count];
                $purchase_order_item->subtotal = $request->row_total[$count];
                $purchase_order_item->save();

         }
        
    }

    public function api_get_all_purchase_order(Request $request)
    {

      $columns = array(
        0 => 'purchase_order_id',
        1 => 'id',
        2 => 'transaction_id',
        3 => 'supplier_id',
        4 => 'order_date',
        5 => 'status',
        6 => 'total',
        7 => 'action'
      );


      $start_date = $request->start_date;
      $end_date = $request->end_date;
      $filter_status = $request->filter_status;
      $filter_supplier = $request->filter_supplier;

 
    //   if(!empty($request->filter_start_date && $request->filter_end_date))
 
      // this will return the # of rows
 
      $query = Purchase_Order::query();


      if(!empty($filter_status)){
        $query = $query->where('status','=', $filter_status);
      }
      if(!empty($filter_supplier)){
        $query = $query->where('supplier_id','=', $filter_supplier);
      }
      if(!empty($start_date)){
        $query = $query->whereDate('created_at', '>=', $start_date);
      }
      if(!empty($end_date)){
        $query = $query->whereDate('created_at', '<=', $end_date);
      }

      $totalData = $query->count();

      $limit = $request->length;
      $start = $request->start;
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');
 

      if (empty($request->input('search.value')))
      {
 

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
    
          $query = $query->offset($start)->limit($limit)->orderBy($order,$dir)
          ->select('purchase_order.id','purchase_order.purchase_order_id','purchase_order.transaction_id','suppliers.fullname AS supplier_id','purchase_order.order_date','purchase_order.status','purchase_order.total');
          
          $purchase_order = $query->get();
 
            $query = Purchase_Order::query();

            if(!empty($filter_status)){
                $query = $query->where('status','=', $filter_status);
            }
            if(!empty($filter_supplier)){
                $query = $query->where('supplier_id','=', $filter_supplier);
            }
            if(!empty($start_date)){
                $query = $query->whereDate('created_at', '>=', $start_date);
            }
            if(!empty($end_date)){
                $query = $query->whereDate('created_at', '<=', $end_date);
            }

            $totalFiltered = $query->count();

        }
        else
        {
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
         ->select('purchase_order.id','purchase_order.purchase_order_id','purchase_order.transaction_id','suppliers.fullname AS supplier_id','purchase_order.order_date','purchase_order.status','purchase_order.total');
      
         $totalFiltered = $query->get()->count();
        }
 
      $data = array();
 
 
      if ($purchase_order)
      {
        foreach ($purchase_order as $value) {

        
         if($value->status == 'Placed'){
             $action = '<button class="btn btn-primary table_print" id="table_print" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="fas fa-print"></i></button>
             <button class="btn btn-success table_view" id="table_view" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="far fa-eye"></i></button>
             <button class="btn btn-warning table_cancel" id="table_cancel" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="fas fa-ban"></i></button>
             <button class="btn btn-danger table_cancel" id="table_remove" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="fas fa-trash"></i></button>';
         }else if($value->status == 'Receiving'){
            $action = '<button class="btn btn-primary table_print" id="table_print" data-id="'.$value->purchase_order_id.'" style="color:white;" disabled><i class="fas fa-print"></i></button>
            <button class="btn btn-success table_view" id="table_view" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="far fa-eye"></i></button>
            <button class="btn btn-warning table_cancel" id="table_cancel" data-id="'.$value->purchase_order_id.'" style="color:white;" disabled><i class="fas fa-ban"></i></button>
            <button class="btn btn-danger table_cancel" id="table_remove" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="fas fa-trash"></i></button>';
        }else if($value->status == 'Received'){
            $action = '<button class="btn btn-primary table_print" id="table_print" data-id="'.$value->purchase_order_id.'" style="color:white;" disabled><i class="fas fa-print"></i></button>
            <button class="btn btn-success table_view" id="table_view" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="far fa-eye"></i></button>
            <button class="btn btn-warning table_cancel" id="table_cancel" data-id="'.$value->purchase_order_id.'" style="color:white;" disabled><i class="fas fa-ban"></i></button>
            <button class="btn btn-danger table_cancel" id="table_remove" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="fas fa-trash"></i></button>';
        }else if($value->status == 'Canceled'){
            $action = '<button class="btn btn-primary table_print" id="table_print" data-id="'.$value->purchase_order_id.'" style="color:white;" disabled><i class="fas fa-print"></i></button>
            <button class="btn btn-success table_view" id="table_view" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="far fa-eye"></i></button>
            <button class="btn btn-warning table_cancel" id="table_cancel" data-id="'.$value->purchase_order_id.'" style="color:white;" disabled><i class="fas fa-ban"></i></button>
            <button class="btn btn-danger table_cancel" id="table_remove" data-id="'.$value->purchase_order_id.'" style="color:white;"><i class="fas fa-trash"></i></button>';
        }
 
          $nestedData['purchase_order_id']  = $value->purchase_order_id;
          $nestedData['transaction_id']  = $value->transaction_id;
          $nestedData['supplier_id']  = $value->supplier_id; 
          $nestedData['order_date']  = $value->order_date; 
          $nestedData['status']  = $value->status; 
          $nestedData['total']  = number_format($value->total, 0 ); 
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

    public function print_and_email_po(Request $request)
    {

        $purchase_order = Purchase_Order::where('purchase_order_id','=',$request->nav2_purchase_order_no)->first();
        $purchase_order_items =  $purchase_order->purchase_order_items;
        $supplier =  $purchase_order->supplier;


        $customPaper = array(0,0,612,792);
        $pdf = PDF::loadView('admin.purchase_order.printable_po',compact('purchase_order','purchase_order_items','supplier'))->setPaper($customPaper, 'landscape');        
        return $pdf->stream('invoice.pdf');
        
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
