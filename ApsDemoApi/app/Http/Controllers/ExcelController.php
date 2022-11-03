<?php

namespace App\Http\Controllers;

use App\Exports\OfctExport;
use App\Exports\OrdrExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    private $EXCEL_NAME_OFCT = '年度生產計畫SAP.xlsx';
    private $EXCEL_NAME_ORDR = '月度生產計畫SAP.xlsx';

    //產生Excel檔案，並轉為二進制回傳
    public function getSapExcel(Request $request, $type)
    {
        $data = array(
            "Msg" => "Success",
            "IsSuccess" => true,
            'base64' => base64_encode($this->createExcelFile($request, $type)),
        );
        return response(json_encode($data), Response::HTTP_OK);
    }

    //產生excel檔案
    private function createExcelFile(Request $request, $type)
    {
        //設定儲存檔案的路徑
        $destinationPath = public_path() . '/file/';
        //如果沒有此資料夾
        if (!file_exists($destinationPath)) {
            //產生資料夾
            mkdir($destinationPath, 0777, true);
        }
      
        switch ($type) {
            case 'OFCT':
                $export = new OfctExport($request->all());
                break;

            case 'ORDR':
                $export = new OrdrExport($request->all());
                break;
        }
        //設定檔案名稱
        $fileName = $this->{'EXCEL_NAME_' . $type};
        //儲存Excel檔至public/file底下
        Excel::store($export, $fileName, 'export');
        //取得Excel檔
        return file_get_contents($destinationPath . $fileName);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
