<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\Supplier;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.item.index');
    }

    public function api_show_info($id)
    {
        $item = Item::findOrFail($id);

        return response()->json([
            'item_id'=> $item->item_id,
            'unit_price'=> $item->unit_price,
            'name'=> $item->name,
            'low_stock'=> $item->low_stock,
            'weight'=> $item->weight,
            'description'=> $item->description,
            ]);
    }


    public function get_item_id()
    {
        $supplier_prefix = 'ITM';
        
        $supplier_id_not_clean = preg_replace("/[:-]/","", Carbon::now());
        $supplier_id = preg_replace('/\s+/', '', $supplier_prefix.'-'.$supplier_id_not_clean);
        
        return response()->json(['item_id'=>$supplier_id]);
    }

    public function api_upload_photo(Request $request, $id){

        $this->validate($request,[
            'photo' => 'image|max:5000',
        ]);

        $filePath = null;

        if($request->hasFile('photo')){
                $file = $request->file('photo');
                $filename =time().$file->getClientOriginalName();
                $file->move('images/item/',$filename);
                $filePath ="images/item/$filename";
        }

        $item = Item::findOrfail($id);
        $item->photo = $filePath;
        $item->update();

        return response()->json(['data'=>$filePath]);
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

    public function api_selected_supplier($id)
    {
        $items = Item::findOrFail($id);

        $selected_supplier = $items->supplier;  

        $selected = array();
 
    
          foreach ($selected_supplier as $value) {
                array_push($selected,$value->id);
          }
        
        $json_data = array(
          "data" => $selected,  
        );

        return json_encode($json_data);
    }

    public function api_selected_category($id)
    {
        $items = Item::findOrFail($id);

        $selected_category = $items->category->id;  

        $json_data = array(
          "data" => $selected_category,  
        );

        return json_encode($json_data);
    }

    public function api_selected_weight_uom($id)
    {
        $items = Item::findOrFail($id);

        $selected_uom_weight = $items->uom_weight->id;  

        
        $json_data = array(
          "data" => $selected_uom_weight,  
        );

        return json_encode($json_data);
    }

    public function api_selected_item_uom($id)
    {
        $items = Item::findOrFail($id);

        $selected_uom_item = $items->uom_item->id;  

        $json_data = array(
          "data" => $selected_uom_item,  
        );

        return json_encode($json_data);
    }

    public function api_selected_item_setting($id){

        $items = Item::findOrFail($id);

        $selected_category = $items->category->id;   
        $selected_uom_weight = $items->uom_weight->id;
        $selected_uom_item = $items->uom_item->id;
        
        $json_data = array(
            "selected_categoy" => $selected_category, 
            "selected_uom_weight" => $selected_uom_weight, 
            "selected_uom_item" => $selected_uom_item,  
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
        return view('admin.item.add');
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
            'item_id' => 'required|max:255',
            'unit_price' => 'required|min:0|max:255',
            'category_id' => 'required|max:255',
            'name' => 'required|max:255',
            'low_stock' => 'required|integer|min:0|max:255',
            'supplier' => 'required',
            'item_uom' => 'required|max:255',
            'weight_uom' => 'required|min:0|max:255',
            'weight' => 'required|max:255',
            'description' => 'max:255',
            'photo' => 'image|max:5000',
        ]);

        $filePath = null;

        if($request->hasFile('photo')){
                $file = $request->file('photo');
                $filename =time().$file->getClientOriginalName();
                $file->move('images/item/',$filename);
                $filePath ="images/item/$filename";
        }



        $item = new Item;
        $item->item_id = $request->item_id;
        $item->category_id = $request->category_id;
        $item->unit_price = $request->unit_price;
        $item->name = $request->name;
        $item->low_stock = $request->low_stock;
        $item->item_uom_id = $request->item_uom;
        $item->weight_uom_id = $request->weight_uom;
        $item->weight = $request->weight;
        $item->description = $request->description;
        $item->photo = $filePath;
        $item->save();


                  
        $item->supplier()->sync($request->supplier);
 
        

        return response()->json(['success'=>'Success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $items = Item::findOrFail($id);

        return view('admin.item.view',compact('items'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $items = Item::findOrFail($id);

        return view('admin.item.edit',compact('items'));
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

        $this->validate($request,[
            'category_id' => 'required|max:255',
            'name' => 'required|max:255',
            'unit_price' => 'required|min:0|max:255',
            'low_stock' => 'required|integer|min:0|max:255',
            'supplier' => 'required',
            'item_uom' => 'required|max:255',
            'weight_uom' => 'required|min:0|max:255',
            'weight' => 'required|max:255',
            'description' => 'max:255',
        ]);


        $item = Item::findOrfail($id);
        $item->category_id = $request->category_id;
        $item->name = $request->name;
        $item->unit_price = $request->unit_price;
        $item->low_stock = $request->low_stock;
        $item->item_uom_id = $request->item_uom;
        $item->weight_uom_id = $request->weight_uom;
        $item->weight = $request->weight;
        $item->description = $request->description;
        $item->update();
                   
        $item->supplier()->sync($request->supplier);

        return response()->json(['success'=>'Success']);
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

    public function apiGetAllItem(Request $request)
    {
      $columns = array(
        0 => 'item_id',
        1 => 'photo',
        2 => 'name',
        3 => 'category_id'
      );
 
 
      // this will return the # of rows
 
      $totalData = Item::all()->count();
     
      //static requests
 
      $limit = $request->length;
      $start = $request->start;
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');
 
      //end of static requests
     
 
      //this enables the search function on the datatables blade view
      if (empty($request->input('search.value')))
      {
 
        //query if no values on search text
 
          $items = Item::offset($start)
                ->join('item_category', 'items.category_id', '=', 'item_category.id')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('items.id','items.item_id','items.name','items.photo','item_category.name AS category_id')
                ->get();
 
 
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
 
                $totalFiltered = Item::all()->count();
      }
      else
      {
         $search = $request->input('search.value');
 
         // if search has a value (you can use inner join)
 
         $items = Item::join('item_category', 'items.category_id', '=', 'item_category.id')
                ->WhereRaw("(items.id AND items.name LIKE ?)", "%{$search}%")
                ->orWhereRaw("(items.id AND item_category.name LIKE ?)", "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('items.id','items.item_id','items.name','items.photo','item_category.name AS category_id')
                ->get();
 
 
          //copy
 
         $totalFiltered = Item::join('item_category', 'items.category_id', '=', 'item_category.id')
                ->WhereRaw("(items.id AND items.name LIKE ?)", "%{$search}%")
                ->orWhereRaw("(items.id AND item_category.name LIKE ?)", "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('items.id','items.item_id','items.name','items.photo','item_category.name AS category_id')
                ->get()
                ->count();
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
        
      }
 
     
      //data to store the data's of the results
      $data = array();
 
 
      if ($items)
      {
        foreach ($items as $value) {
 
          //store the values here
          $nestedData['item_id']  = $value->item_id;
          $nestedData['photo']  ='<div class="text-center">
          <img class="img-fluid img-circle" src="/'.$value->photo.'" style="max-width:50px;" alt="User profile picture">
        </div>';
          $nestedData['name']  = $value->name;
          $nestedData['category_id']  = $value->category_id; 
          $nestedData['action']  = '<a class="btn btn-primary" href="item/edit/'.$value->id.'" style="color:white;"><i class="fas fa-pen"></i></a>
                                   <a class="btn btn-success" href="item/'.$value->id.'" style="color:white;"><i class="fas fa-eye"></i></a>
                                   <a class="btn btn-danger" style="color:white;"><i class="fas fa-trash"></i></a>';       
    
          //pass to data
          $data[] = $nestedData;
        }
      }
 
 
      //return this json encoded!
      $json_data = array(
        "draw" => ($request->draw ? intval($request->draw):0), //draw for pagination
        "recordsTotal" => intval($totalData), //total records
        "recordsFiltered" => intval($totalFiltered), //results of filter
        "data" => $data, //data
 
      );
 
      //like this
      return json_encode($json_data);


    }
}
