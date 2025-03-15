<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        try {
        return Company::select('Companies.*')->get();
    } catch (\Exception $e) {
        dd($e);
        report($e);
        return $this->errorResponse($e);
    }
    }

    public function show($id)
    {       
        $record = Company::find($id);
        
        return $record;
    }

    public function store(Request $request)
    {
        try{
            $model = new Company;
            $model->name = $request->name;
            $model->email = $request->email;
            $model->address = $request->address;
            $model->website = $request->website;
            
            $model->save();
            return response()->json([            
                'status' => 200,
                'data' => 'ok'
            ]);
           
        }
        catch(\Exception $e) {
            report($e);
            return response()->json([            
                'status' => 500,
                'data' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $model = Company::findOrFail($id);

            $model->update($request["data"]);
            
            return response()->json([            
                'status' => 200,
                'data' => 'ok'
            ]);
        }
        catch(\Exception $e) {
            report($e);
            return response()->json([            
                'status' => 500,
                'data' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try{
            $model = Company::findOrFail($id);

            $model->delete();

            return response()->json([            
                'status' => 200,
                'data' => 'ok'
            ]);
        }
        catch(\Exception $e) {
            report($e);
            return response()->json([            
                'status' => 500,
                'data' => $e->getMessage()
            ]);
            
        }
    }

    public function report($id)
    {
        try {
            $model = Company::select('Companies.*')
            ->where('Companies.id','=',$id)                
            ->first();
            
            return $model;
        }
        catch (\Exception $e) {  
            report($e);      
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
