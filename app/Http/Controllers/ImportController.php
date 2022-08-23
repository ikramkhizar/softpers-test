<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileColumn;
use App\Models\FileData;
use Excel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\HeadingRowImport;

class ImportController extends Controller
{
    public function create()
    {
        return view('imports.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'file'  => 'required|mimes:xls,xlsx'
        ]);

        $data     = Excel::toArray([],$request->file, '', '');

        $filePath = $request->file->getRealPath();
        $fileName = pathinfo($request->file('file')->getClientOriginalName(),PATHINFO_FILENAME);

        $file_insert = File::create(['name' => $fileName]);
        if ($file_insert) 
        {
            $file_id = $file_insert->id;

            $heading_row = (new HeadingRowImport)->toArray($request->file, '', '');
            $headings    = $heading_row[0][0]; 
            
            for ($i = 0; $i < count($headings); $i++)
            {
                $file_column_insert = FileColumn::create([
                    'file_id' => $file_id,
                    'name'    => $headings[$i]
                ]);

                if ($file_column_insert)
                {
                    $column_id = $file_column_insert->id;

                    $new_data  = array_slice($data[0],1);

                    if(count($data) > 0)
                    {
                        for($k = 0; $k < count($new_data); $k++)
                        {
                            if (!is_null($new_data[$k][$i])) {
                                $column_data_insert = FileData::create([
                                    'column_id' => $column_id,
                                    'data'      => $new_data[$k][$i]
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('import.create');
    }
}
