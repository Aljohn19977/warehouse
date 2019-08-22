<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.warehouse.index');
    }

    
    public function get_warehouse_id()
    {
        $warehouse_prefix = 'WRH';
        
        $warehouse_id_not_clean = preg_replace("/[:-]/","", Carbon::now());
        $warehouse_id = preg_replace('/\s+/', '', $warehouse_prefix.'-'.$warehouse_id_not_clean);
        
        return response()->json(['warehouse_id'=>$warehouse_id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.warehouse.add');
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
            'warehouse_id' => 'required|max:255',
            'name' => 'required|max:255',
            'address' => 'required',
            'email' => 'required|email|max:255',
            'mobile_no' => 'required|max:255',
            'tel_no' => 'required|max:255',
            'details' => 'max:255',
            'remarks' => 'max:255',
            'photo' => 'image|max:5000',
        ]);

        $filePath = null;

        if($request->hasFile('photo')){
                $file = $request->file('photo');
                $filename =time().$file->getClientOriginalName();
                $file->move('images/warehouse/',$filename);
                $filePath ="images/warehouse/$filename";
        }



        $warehouse = new Warehouse;
        $warehouse->warehouse_id = $request->warehouse_id;
        $warehouse->name = $request->name;
        $warehouse->address = $request->address;
        $warehouse->email = $request->email;
        $warehouse->tel_no = $request->tel_no;
        $warehouse->mobile_no = $request->mobile_no;
        $warehouse->photo = $filePath;
        $warehouse->details = $request->details;
        $warehouse->remarks = $request->remarks;
        $warehouse->save();

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
        $warehouses = Warehouse::findOrFail($id);

        return view('admin.warehouse.view',compact('warehouses'));
    }


    public function api_show_info($id)
    {
        $warehouses = Warehouse::findOrFail($id);

        return response()->json([
            'warehouse_id'=> $warehouses->warehouse_id,
            'name'=> $warehouses->name,
            'address'=> $warehouses->address,
            'email'=> $warehouses->email,
            'tel_no'=> $warehouses->tel_no,
            'mobile_no'=> $warehouses->mobile_no,
            'photo'=> $warehouses->photo ,
            'remarks'=> $warehouses->remarks ,
            'details'=> $warehouses->details 
            ]);
    }

    public function api_upload_photo(Request $request, $id){

        $this->validate($request,[
            'photo' => 'image|max:5000',
        ]);

        $filePath = null;

        if($request->hasFile('photo')){
                $file = $request->file('photo');
                $filename =time().$file->getClientOriginalName();
                $file->move('images/warehouse/',$filename);
                $filePath ="images/warehouse/$filename";
        }

        $warehouses = Warehouse::findOrfail($id);
        $warehouses->photo = $filePath;
        $warehouses->update();

        return response()->json(['success'=>'Success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $warehouses = Warehouse::findOrFail($id);
        return view('admin.warehouse.edit',compact('warehouses'));
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
            'warehouse_id' => 'required|max:255',
            'name' => 'required|max:255',
            'address' => 'required',
            'email' => 'required|email|max:255',
            'mobile_no' => 'required|max:255',
            'tel_no' => 'required|max:255',
            'details' => 'max:255',
            'remarks' => 'max:255',
            'photo' => 'image|max:5000',
        ]);


        $warehouse = Warehouse::findOrfail($id);
        $warehouse->warehouse_id = $request->warehouse_id;
        $warehouse->name = $request->name;
        $warehouse->address = $request->address;
        $warehouse->email = $request->email;
        $warehouse->tel_no = $request->tel_no;
        $warehouse->mobile_no = $request->mobile_no;
        $warehouse->details = $request->details;
        $warehouse->remarks = $request->remarks;
        $warehouse->update();


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

    public function apiGetAllWarehouse(Request $request)
    {
      $columns = array(
        0 => 'warehouse_id',
        1 => 'photo',
        2 => 'name',
        3 => 'email'
      );
 
 
      // this will return the # of rows
 
      $totalData = Warehouse::all()->count();
     
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
 
          $warehouses = Warehouse::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get(['id','warehouse_id','photo','name','email']);
 
 
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
 
                $totalFiltered = Warehouse::all()->count();
      }
      else
      {
         $search = $request->input('search.value');
 
         // if search has a value (you can use inner join)
 
         $warehouses = Warehouse::WhereRaw("(warehouse_id AND name LIKE ?)", "%{$search}%")
                ->orWhereRaw("(Warehouser_id AND email LIKE ?)", "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get(['id','warehouse_id','photo','name','email']);
 
 
          //copy
 
         $totalFiltered = Supplier::WhereRaw("(warehouse_id AND name LIKE ?)", "%{$search}%")
                ->orWhereRaw("(warehouse_id AND email LIKE ?)", "%{$search}%")
                ->count();
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
        
      }
 
     
      //data to store the data's of the results
      $data = array();
 
 
      if ($warehouses)
      {
        foreach ($warehouses as $value) {
 
          //store the values here
          $nestedData['warehouse_id']  = $value->warehouse_id;
          $nestedData['photo']  ='<div class="text-center">
          <img class="img-fluid img-circle" src="/'.$value->photo.'" style="max-width:50px;" alt="User profile picture">
        </div>';
          $nestedData['fullname']  = $value->fullname;
          $nestedData['email']  = $value->email; 
          $nestedData['action']  = '<a class="btn btn-primary" href="warehouse/edit/'.$value->id.'" style="color:white;"><i class="fas fa-pen"></i></a>
                                   <a class="btn btn-success" href="warehouse/'.$value->id.'" style="color:white;"><i class="fas fa-eye"></i></a>
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
