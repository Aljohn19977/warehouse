<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Item_Category;

class ItemCategoryController extends Controller
{
    public function store_category(Request $request)
    {

        $this->validate($request,[
            'category_name' => 'required|max:255',
        ]);

        $item_category = new Item_Category;
        $item_category->name = $request->category_name;
        $item_category->save();

        return response()->json(['success'=>'Success']);

    }

    public function api_categoy_list()
    {
        $item_category = Item_Category::get(['id','name']);
        
        $data = array();
 
        if ($item_category)
        {
          foreach ($item_category as $value) {
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

    
    public function destroy_category(Request $request)
    {
        $item_category = Item_Category::findOrfail($request->id);
        $item_category->delete();

        return response()->json(['success'=>'Success']);
    }


}
