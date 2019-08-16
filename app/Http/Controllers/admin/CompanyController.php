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
