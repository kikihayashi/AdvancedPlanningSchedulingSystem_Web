<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Setting;
use App\Models\Signature;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    use BaseTool, ValidatorTool;
    /**
     * 報表內容管理
     */
    //讀取
    public function showContentPage()
    {
        $signatureArray = Signature::all()->toArray();

        $projectTypeArray = $this->projectTypeArray;

        foreach ($signatureArray as $signature) {
            switch ($signature['project_name']) {
                case 'PY':
                    $projectTypeArray[$signature['project_name']]['department'] =
                    array(
                        $signature['create_department'],
                        $signature['review_department'],
                        $signature['admit_department']);
                    $projectTypeArray[$signature['project_name']]['user'] =
                    array(
                        $signature['create_user'],
                        $signature['review_user'],
                        $signature['admit_user']);
                    break;
                case 'SY':
                    $projectTypeArray[$signature['project_name']]['department'] =
                    array(
                        $signature['create_department'],
                        $signature['review_department'],
                        $signature['review_department2'],
                        $signature['admit_department']);
                    $projectTypeArray[$signature['project_name']]['user'] =
                    array(
                        $signature['create_user'],
                        $signature['review_user'],
                        $signature['review_user2'],
                        $signature['admit_user']);
                    break;
                case 'PM':
                    $projectTypeArray[$signature['project_name']]['department'] =
                    array(
                        $signature['create_department'],
                        $signature['review_department']);
                    $projectTypeArray[$signature['project_name']]['user'] =
                    array(
                        $signature['create_user'],
                        $signature['review_user']);
                    break;
                case 'SM':
                    $projectTypeArray[$signature['project_name']]['department'] =
                    array(
                        $signature['create_department'],
                        $signature['review_department'],
                        $signature['review_department2'],
                        $signature['admit_department']);
                    $projectTypeArray[$signature['project_name']]['user'] =
                    array(
                        $signature['create_user'],
                        $signature['review_user'],
                        $signature['review_user2'],
                        $signature['admit_user']);
                    break;
            }
        }

        $data['title'] = "報表內容管理";
        $data['projectTypeArray'] = $projectTypeArray;
        $data['setting'] = Setting::orderBy('id')->get()->toArray();
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('system.maintainMenu.contents',
            ['selection' => 'system',
                'openMenu' => 'maintainMenu',
                'visitedId' => 'contents',
                'tableData' => $data]);
    }

    //新增&修改
    public function writeContent(Request $request)
    {
        $projectType = $request->selectSubPage;
        $signature = Signature::where('project_name', $projectType)->first() ?? new Signature();
        $signature->project_name = $projectType;
        $signature->create_department = $request->{$projectType . '-department'}[0];
        $signature->create_user = $request->{$projectType . '-user'}[0];
        $signature->review_department = $request->{$projectType . '-department'}[1];
        $signature->review_user = $request->{$projectType . '-user'}[1];
        switch ($projectType) {
            case 'PY':
                $signature->admit_department = $request->{$projectType . '-department'}[2] ?? "";
                $signature->admit_user = $request->{$projectType . '-user'}[2] ?? "";
                break;
            case 'SY':
                $signature->review_department2 = $request->{$projectType . '-department'}[2];
                $signature->review_user2 = $request->{$projectType . '-user'}[2];
                $signature->admit_department = $request->{$projectType . '-department'}[3] ?? "";
                $signature->admit_user = $request->{$projectType . '-user'}[3] ?? "";
                break;
            case 'PM':
                break;
            case 'SM':
                $signature->review_department2 = $request->{$projectType . '-department'}[2];
                $signature->review_user2 = $request->{$projectType . '-user'}[2];
                $signature->admit_department = $request->{$projectType . '-department'}[3] ?? "";
                $signature->admit_user = $request->{$projectType . '-user'}[3] ?? "";
                break;
        }

        if ($this->saveImage($projectType, $request->{$projectType . '-signBase64'})) {
            $message = '成功，已設定簽章！';
            $signature->save();
        } else {
            $message = '失敗，儲存圖片時發生錯誤！';
        }

        return redirect(route('ContentController.showContentPage'))
            ->with('message', $message) //顯示訊息
            ->with('selectPage', 'sign')
            ->with('selectSubPage', $projectType);
    }

    private function saveImage($projectType, $signBase64Array)
    {
        //設定儲存圖片的路徑
        $destinationPath = public_path() . '/img/' . $projectType . '/';
        //如果已經有此資料夾
        if (file_exists($destinationPath)) {
            //取得該路徑資料夾底下所有檔案
            $files = glob($destinationPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); //刪除每個檔案
                }
            }
            //刪除資料夾(此指令只能刪除空資料夾)
            rmdir($destinationPath);
        }
        //產生資料夾
        mkdir($destinationPath, 0777, true);

        //轉譯base64成圖片檔並儲存
        for ($i = 0; $i < count($signBase64Array); $i++) {
            if ($signBase64Array[$i] != null) {
                $signBase64 = str_replace('data:image/png;base64,', '', $signBase64Array[$i]);
                $signBase64 = str_replace(' ', '+', $signBase64);
                $file = base64_decode($signBase64);
                $fileName = $projectType . '-' . ($i + 1) . '.png';
                $success = file_put_contents($destinationPath . $fileName, $file);
                if (!$success) {
                    return false;
                }
            }
        }
        return true;
    }
}
