<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\ValidatorTool;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    use BaseTool, ValidatorTool;

    public function showFilePage()
    {
        $destinationPath = public_path() . '/file/';
        $dataArray = array();
        if (file_exists($destinationPath)) {
            $files = File::allFiles($destinationPath);
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $fileSize = $file->getSize();
                $fileFullName = pathinfo($file)['basename'];
                $uploadDate = explode("_", $fileFullName)[0];
                $fileName = str_replace($uploadDate . '_', "", $fileFullName);
                $dataArray[] = array(
                    $fileFullName,
                    ($i+1),
                    $fileName,
                    date('Y-m-d H:i:s', strtotime($uploadDate) + 8 * 3600),
                    $this->getSizeUnit($fileSize),
                );
            }
        }

        $data['title'] = '檔案管理';
        $data['file'] = $dataArray;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('system.maintainMenu.file',
            ['selection' => 'system',
                'openMenu' => 'maintainMenu',
                'visitedId' => 'file',
                'tableData' => $data]);
    }

    //取得檔案大小
    private function getSizeUnit($fileSize): string
    {
        if ($fileSize <= 1024) {
            return $fileSize . ' byte';
        }
        if (1024 < $fileSize && $fileSize <= (1024 * 1024)) {
            return round($fileSize / 1024, 2) . ' KB';
        }
        if ((1024 * 1024) < $fileSize && $fileSize <= (1024 * 1024 * 1024)) {
            return round($fileSize / (1024 * 1024), 2) . ' MB';
        }
        if ((1024 * 1024 * 1024) < $fileSize && $fileSize <= (1024 * 1024 * 1024 * 1024)) {
            return round($fileSize / (1024 * 1024 * 1024), 2) . ' GB';
        }
        return '大於1GB';
    }

    //上傳檔案
    public function uploadFile(Request $request)
    {
        $errorMessage = '錯誤，無檔案上傳！';
        if ($request->hasFile('excelFiles')) {
            $isFilesValid = true;
            //驗證格式
            foreach ($request->file('excelFiles') as $file) {
                $columnArray['name'] = 'file'; //名稱
                $columnArray['value'] = $file; //值
                $columnArray['name2'] = 'extension';
                $columnArray['value2'] = strtolower($file->getClientOriginalExtension());
                $validator = $this->checkInputValid('file', $columnArray);
                if ($validator->fails()) {
                    $isFilesValid = false;
                    $errorMessage = $validator->errors()->all()[0];
                    break;
                }
            }
            //驗證通過才可以上傳檔案
            if ($isFilesValid) {
                //設定儲存檔案的路徑
                $destinationPath = public_path() . '/file/';
                //如果沒有資料夾，產生一個
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                foreach ($request->file('excelFiles') as $file) {
                    $fileName = $file->getClientOriginalName();
                    $date = date('YmdHis');
                    $file->move($destinationPath, $date . '_' . $fileName);
                }
                return redirect(route('FileController.showFilePage'))
                    ->with('message', '成功，已上傳檔案！'); //顯示成功訊息
            }
        }
        return redirect()->back()
            ->with('errorMessage', $errorMessage); //顯示錯誤訊息
    }

    //下載檔案
    public function downloadFile($fileFullName)
    {
        $destinationPath = public_path() . '/file/';
        $file = $destinationPath . $fileFullName;
        $headers = array('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        return Response::download($file, $fileFullName, $headers);
    }

    //刪除檔案
    public function deleteFile($fileFullName)
    {
        $destinationPath = public_path() . '/file/';
        $file = $destinationPath . $fileFullName;
        if (unlink($file)) {
            return redirect(route('FileController.showFilePage'))
                ->with('message', '成功，已刪除檔案！'); //顯示成功訊息
        } else {
            return redirect(route('FileController.showFilePage'))
                ->with('errorMessage', '錯誤，刪除檔案失敗！'); //顯示錯誤訊息
        }

    }
}
