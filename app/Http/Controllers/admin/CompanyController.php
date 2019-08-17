<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Company;
use DD;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.company.index');
    }

    public function get_company_id()
    {
        $company_prefix = 'CMP';
        
        $company_id_not_clean = preg_replace("/[:-]/","", Carbon::now());
        $company_id = preg_replace('/\s+/', '', $company_prefix.'-'.$company_id_not_clean);
        
        return response()->json(['company_id'=>$company_id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.company.add');
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
            'company_id' => 'required|max:255',
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
                $file->move('images/company/',$filename);
                $filePath ="images/company/$filename";
        }

        $company = new Company;
        $company->company_id = $request->company_id;
        $company->name = $request->name;
        $company->address = $request->address;
        $company->email = $request->email;
        $company->tel_no = $request->tel_no;
        $company->mobile_no = $request->mobile_no;
        $company->photo = $filePath;
        $company->details = $request->details;
        $company->remarks = $request->remarks;
        $company->save();

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
        $companies = Company::findOrFail($id);

        return view('admin.company.view',compact('companies'));
    }

    public function api_show_info($id)
    {
        $companies = Company::findOrFail($id);

        return response()->json([
            'company_id'=> $companies->company_id,
            'name'=> $companies->name,
            'address'=> $companies->address,
            'email'=> $companies->email,
            'tel_no'=> $companies->tel_no,
            'mobile_no'=> $companies->mobile_no,
            'photo'=> $companies->photo 
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
                $file->move('images/company/',$filename);
                $filePath ="images/company/$filename";
        }

        $company = Company::findOrfail($id);
        $company->photo = $filePath;
        $company->update();

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
        $companies = Company::findOrFail($id);
        return view('admin.company.edit',compact('companies'));
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
            'company_id' => 'required|max:255',
            'name' => 'required|max:255',
            'address' => 'required',
            'email' => 'required|email|max:255',
            'mobile_no' => 'required|max:255',
            'tel_no' => 'required|max:255',
            'details' => 'max:255',
            'remarks' => 'max:255',
            'photo' => 'image|max:5000',
        ]);


        $company = Company::findOrfail($id);
        $company->company_id = $request->company_id;
        $company->name = $request->name;
        $company->address = $request->address;
        $company->email = $request->email;
        $company->tel_no = $request->tel_no;
        $company->mobile_no = $request->mobile_no;
        $company->details = $request->details;
        $company->remarks = $request->remarks;
        $company->update();

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

    public function apiGetAllCompany(Request $request)
    {
      $columns = array(
        0 => 'company_id',
        1 => 'photo',
        2 => 'name',
        3 => 'email'
      );
 
 
      // this will return the # of rows
 
      $totalData = Company::all()->count();
     
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
 
          $companies = Company::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get(['id','company_id','photo','name','email']);
 
 
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
 
                $totalFiltered = Company::all()->count();
      }
      else
      {
         $search = $request->input('search.value');
 
         // if search has a value (you can use inner join)
 
         $companies = Company::WhereRaw("(company_id AND name LIKE ?)", "%{$search}%")
                ->orWhereRaw("(company_id AND email LIKE ?)", "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get(['id','company_id','photo','name','email']);
 
 
          //copy
 
         $totalFiltered = Company::WhereRaw("(company_id AND name LIKE ?)", "%{$search}%")
                ->orWhereRaw("(company_id AND email LIKE ?)", "%{$search}%")
                ->count();
                //return # of rows filtered (just copy the query of the post above and remove the get() and change to count() to return the # of rows)
        
      }
 
     
      //data to store the data's of the results
      $data = array();
 
 
      if ($companies)
      {
        foreach ($companies as $value) {
 
          //store the values here
          $nestedData['company_id']  = $value->company_id;
          $nestedData['photo']  ='<div class="text-center">
          <img class="img-fluid img-circle" src="/'.$value->photo.'" style="max-width:50px;" alt="User profile picture">
        </div>';
          $nestedData['name']  = $value->name;
          $nestedData['email']  = $value->email; 
          $nestedData['action']  = '<a class="btn btn-primary" href="company/edit/'.$value->id.'" style="color:white;"><i class="fas fa-pen"></i></a>
                                   <a class="btn btn-success" href="company/'.$value->id.'" style="color:white;"><i class="fas fa-eye"></i></a>
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
