<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Weight_UOM;
use App\Models\Item_UOM;

class UOMController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.uom.index');
    }

    public function store_weight_uom(Request $request)
    {

        $this->validate($request,[
            'weight_uom_name' => 'required|max:255',
            'weight_uom_acronym' => 'required|max:255',
        ]);

        $weight_uom = new Weight_UOM;
        $weight_uom->name = $request->weight_uom_name;
        $weight_uom->acronym = $request->weight_uom_acronym;
        $weight_uom->save();

        return response()->json(['success'=>'Success']);

    }

    public function store_item_uom(Request $request)
    {

        $this->validate($request,[
            'item_uom_name' => 'required|max:255',
            'item_uom_acronym' => 'required|max:255',
        ]);

        $item_uom = new Item_UOM;
        $item_uom->name = $request->item_uom_name;
        $item_uom->acronym = $request->item_uom_acronym;
        $item_uom->save();

        return response()->json(['success'=>'Success']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function item_uom_list()
    {
        $item_uom = Item_UOM::get(['id','name','acronym']);
        
        $data = array();
 
        if ($item_uom)
        {
          foreach ($item_uom as $value) {
            $nestedData['id']  = $value->id;
            $nestedData['name']  = $value->name;
            $nestedData['acronym']  = $value->acronym;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }

    public function weight_uom_list()
    {
        $weight_uom = Weight_UOM::get(['id','name','acronym']);
        
        $data = array();
 
        if ($weight_uom)
        {
          foreach ($weight_uom as $value) {
            $nestedData['id']  = $value->id;
            $nestedData['name']  = $value->name;
            $nestedData['acronym']  = $value->acronym;
            $data[] = $nestedData;
          }
        }
        
        $json_data = array(
          "data" => $data,  
        );

        return json_encode($json_data);
    }


    public function destroy_weight_uom(Request $request)
    {
        $weight_uom = Weight_UOM::findOrfail($request->id);
        $weight_uom->delete();

        return response()->json(['success'=>'Success']);
    }

    public function destroy_item_uom(Request $request)
    {
        $item_uom = Item_UOM::findOrfail($request->id);
        $item_uom->delete();

        return response()->json(['success'=>'Success']);
    }


}

