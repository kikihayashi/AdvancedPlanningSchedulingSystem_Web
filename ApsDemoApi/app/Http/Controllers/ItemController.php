<?php

namespace App\Http\Controllers;

use App\Http\Traits\MesApiTool;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemController extends Controller
{
    use MesApiTool;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //取得機種清單(GET)
    public function index()
    {
        // $items = Item::get(['item_code', 'item_name', 'eternal_code', 'item_name_jp', 'item_name_en', 'image_code', 'stock_code', 'line', 'is_hidden'])
        //     ->toArray();

        // $data = array();
        // foreach ($items as $item) {
        //     $data[] = array(
        //         'ItemCode' => $item['item_code'],
        //         'ItemName' => $item['item_name'],
        //         '日京永久品番' => $item['eternal_code'],
        //         '機種日文名稱' => $item['item_name_jp'],
        //         '機種英文名稱' => $item['item_name_en'],
        //         '圖番' => $item['image_code'],
        //         '在庫品番' => $item['stock_code'],
        //         'Line' => $item['line'],
        //         '是否內藏' => $item['is_hidden'],
        //     );
        // }
        // return response(json_encode($data), Response::HTTP_OK);
    }

    //客製Method，取得機種清單(POST)
    public function getItemList(Request $request)
    {
        $items = Item::get([
            'item_code', 'item_name', 'eternal_code', 'item_name_jp',
            'item_name_en', 'image_code', 'stock_code', 'line', 'is_hidden'])
            ->toArray();

        $data = array();
        foreach ($items as $item) {
            $data[] = array(
                'ItemCode' => $item['item_code'],
                'ItemName' => $item['item_name'],
                '日京永久品番' => $item['eternal_code'],
                '機種日文名稱' => $item['item_name_jp'],
                '機種英文名稱' => $item['item_name_en'],
                '圖番' => $item['image_code'],
                '在庫品番' => $item['stock_code'],
                'Line' => $item['line'],
                '是否內藏' => $item['is_hidden'],
            );
        }
        $request->headers->set('Accept', 'application/json');
        return response(json_encode($data), Response::HTTP_OK);
    }

    //取得OrderNo，規則是隨意設置的
    public function getOrderNo(Request $request)
    {
        $itemCode = $request->itemcode;
        $lot = $request->lot;

        $items = Item::where('item_code', $itemCode)
            ->get()
            ->toArray();

        if (count($items) == 0 || !is_numeric($lot) || $lot == 0) {
            return response(json_encode(array("")), Response::HTTP_OK);
        } else {
            $id = $items[0]['id'];
            $orderNo1 = 'ETKT-' . abs(20220 - $id * $lot);
            $orderNo2 = 'ETKT-' . abs(20220 + $id * $lot);
            $data = array($orderNo1, $orderNo2);
            return response(json_encode($data), Response::HTTP_OK);
        }
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

    //儲存機種清單到資料庫
    public function store(Request $request)
    {
        //第一次存到資料庫需要，後續即可註解
        // foreach ($this->getEquipmentList() as $equipment) {
        //     $item = new Item();
        //     $item->item_code = $equipment['ItemCode'];
        //     $item->item_name = $equipment['ItemName'];
        //     $item->eternal_code = $equipment['日京永久品番'];
        //     $item->item_name_jp = $equipment['機種日文名稱'];
        //     $item->item_name_en = $equipment['機種英文名稱'];
        //     $item->image_code = $equipment['圖番'];
        //     $item->stock_code = $equipment['在庫品番'];
        //     $item->line = $equipment['Line'];
        //     $item->is_hidden = $equipment['是否內藏'];
        //     $item->save();
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //查詢單個機種
    public function show($id)
    {
        // $items = Item::where('id', $id)
        //     ->get(['item_code', 'item_name', 'eternal_code', 'item_name_jp', 'item_name_en', 'image_code', 'stock_code', 'line', 'is_hidden'])
        //     ->toArray();

        // $item = $items[0];

        // $data['ItemCode'] = $item['item_code'];
        // $data['ItemName'] = $item['item_name'];
        // $data['日京永久品番'] = $item['eternal_code'];
        // $data['機種日文名稱'] = $item['item_name_jp'];
        // $data['機種英文名稱'] = $item['item_name_en'];
        // $data['圖番'] = $item['image_code'];
        // $data['在庫品番'] = $item['stock_code'];
        // $data['Line'] = $item['line'];
        // $data['是否內藏'] = $item['is_hidden'];
        // return response(json_encode($data), Response::HTTP_OK);
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
