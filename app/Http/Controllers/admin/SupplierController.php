<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\Company;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.supplier.index');
    }

    public function get_supplier_id()
    {
        $supplier_prefix = 'SPL';
        
        $supplier_id_not_clean = preg_replace("/[:-]/","", Carbon::now());
        $supplier_id = preg_replace('/\s+/', '', $supplier_prefix.'-'.$supplier_id_not_clean);
        
        return response()->json(['supplier_id'=>$supplier_id]);
    }

    public function api_company_list()
    {
        $companies = Company::get(['id','name']);
        
        $data = array();
 
        if ($companies)
        {
          foreach ($companies as $value) {
            $nestedData['id']  = $value->id;
            $nestedData['name']  = $value->name;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }

    public function api_selected_company($id){

        $suppliers = Supplier::findOrFail($id);

        $selected_company = $suppliers->company;

        $selected = array();


        foreach($selected_company as $value) {

            array_push($selected,$value->id);

        }

        $json_data = array(
            "data" => $selected,  
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
        return view('admin.supplier.add');
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
            'supplier_id' => 'required|max:255',
            'fullname' => 'required|max:255',
            'address' => 'required',
            'email' => 'required|email|max:255',
            'mobile_no' => 'required|max:255',
            'tel_no' => 'required|max:255',
            'details' => 'max:255',
            'remarks' => 'max:255',
            'photo' => 'image|max:5000',
            'company' => 'required',
        ]);

        $filePath = null;

        if($request->hasFile('photo')){
                $file = $request->file('photo');
                $filename =time().$file->getClientOriginalName();
                $file->move('images/supplier/',$filename);
                $filePath ="images/supplier/$filename";
        }



        $supplier = new Supplier;
        $supplier->supplier_id = $request->supplier_id;
        $supplier->fullname = $request->fullname;
        $supplier->address = $request->address;
        $supplier->email = $request->email;
        $supplier->tel_no = $request->tel_no;
        $supplier->mobile_no = $request->mobile_no;
        $supplier->photo = $filePath;
        $supplier->details = $request->details;
        $supplier->remarks = $request->remarks;
        $supplier->save();


                  
        $supplier->company()->sync($request->company);
 
        

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
        $suppliers = Supplier::findOrFail($id);

        return view('admin.supplier.view',compact('suppliers'));
    }

    public function api_show_info($id)
    {
        $suppliers = Supplier::findOrFail($id);

        return response()->json([
            'supplier_id'=> $suppliers->supplier_id,
            'fullname'=> $suppliers->fullname,
            'address'=> $suppliers->address,
            'email'=> $suppliers->email,
            'tel_no'=> $suppliers->tel_no,
            'mobile_no'=> $suppliers->mobile_no,
            'photo'=> $suppliers->photo, 
            'remarks'=> $suppliers->remarks ,
            'description'=> $suppliers->description 
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
                $file->move('images/supplier/',$filename);
                $filePath ="images/supplier/$filename";
        }

        $supplier = Supplier::findOrfail($id);
        $supplier->photo = $filePath;
        $supplier->update();

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
        $suppliers = Supplier::findOrFail($id);

        return view('admin.supplier.edit',compact('suppliers'));
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
            'supplier_id' => 'required|max:255',
            'fullname' => 'required|max:255',
            'address' => 'required',
            'email' => 'required|email|max:255',
            'mobile_no' => 'required|max:255',
            'tel_no' => 'required|max:255',
            'details' => 'max:255',
            'remarks' => 'max:255',
            'photo' => 'image|max:5000',
            'company' => 'required',
        ]);


        $supplier = Supplier::findOrfail($id);
        $supplier->supplier_id = $request->supplier_id;
        $supplier->fullname = $request->fullname;
        $supplier->address = $request->address;
        $supplier->email = $request->email;
        $supplier->tel_no = $request->tel_no;
        $supplier->mobile_no = $request->mobile_no;
        $supplier->details = $request->details;
        $supplier->remarks = $request->remarks;
        $supplier->update();

                   
           $supplier->company()->sync($request->company);
        

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

    public function apiGetAllsupplier(Request $request)
    {
      $columns = array(
        0 => 'supplier_id',
        1 => 'photo',
        2 => 'fullname',
        3 => 'email'
      );
 
 
      // this will return the # of rows
 
      $totalData = Supplier::all()->count();
     
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
 
          $suppliers = Supplier::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get(['id','supplier_id','photo','fullname','email']);
 
 
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
 
                $totalFiltered = supplier::all()->count();
      }
      else
      {
         $search = $request->input('search.value');
 
         // if search has a value (you can use inner join)
 
         $suppliers = Supplier::WhereRaw("(supplier_id AND fullname LIKE ?)", "%{$search}%")
                ->orWhereRaw("(supplier_id AND email LIKE ?)", "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get(['id','supplier_id','photo','fullname','email']);
 
 
          //copy
 
         $totalFiltered = Supplier::WhereRaw("(supplier_id AND fullname LIKE ?)", "%{$search}%")
                ->orWhereRaw("(supplier_id AND email LIKE ?)", "%{$search}%")
                ->count();
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
        
      }
 
     
      //data to store the data's of the results
      $data = array();
 
 
      if ($suppliers)
      {
        foreach ($suppliers as $value) {
 
          //store the values here
          $nestedData['supplier_id']  = $value->supplier_id;
          $nestedData['photo']  ='<div class="text-center">
          <img class="img-fluid img-circle" src="/'.$value->photo.'" style="max-width:50px;" alt="User profile picture">
        </div>';
          $nestedData['fullname']  = $value->fullname;
          $nestedData['email']  = $value->email; 
          $nestedData['action']  = '<a class="btn btn-primary" href="supplier/edit/'.$value->id.'" style="color:white;"><i class="fas fa-pen"></i></a>
                                   <a class="btn btn-success" href="supplier/'.$value->id.'" style="color:white;"><i class="fas fa-eye"></i></a>
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
