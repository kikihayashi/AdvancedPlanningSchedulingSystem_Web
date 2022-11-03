<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdrExport implements FromView, WithEvents, WithStyles, WithTitle
{
    protected $data;
    protected $dataArray;

    public function __construct($jsonArray)
    {
        $this->dataArray = $jsonArray[0];
    }

    public function view(): View
    {
        $lineArray = $this->dataArray['ORDR']['Lines'];

        $ordr = array();
        foreach ($lineArray as $line) {
            $ordr[] = array(
                'lot_no' => $line['U_Lot'],
                'item_code' => $line['ItemCode'],
                'cost' => $line['Price'],
                'this_month_number' => $line['Quantity'],
                'shippingDate' => $line['ShipDate'],
            );
        }
        $data['ordr'] = $ordr;
        $this->data = $data;
        return view('ordr_export', ['tableData' => $data]);
    }

    //標題設定-WithTitle
    public function title(): string
    {
        return '月度生產計劃SAP';
    }

    //字體設定-WithStyles
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    //Cell大小設定-WithEvents
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ordr = $this->data['ordr'];

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(25);
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(8);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);

                //標題置中設定
                $event->sheet->getDelegate()->getStyle('A1:E1')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //靠右設定
                $startRow = 2;
                $endRow = 1 + count($ordr);
                $event->sheet->getDelegate()->getStyle('A' . $startRow . ':' . 'A' . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('B' . $startRow . ':' . 'B' . $endRow)
                    ->getAlignment()->setHorizontal('left')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('C' . $startRow . ':' . 'C' . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('D' . $startRow . ':' . 'D' . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('E' . $startRow . ':' . 'E' . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //邊框設定
                $event->sheet->getStyle('A1:E1')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                //凍結窗格-------------------------------------------------------------------------------------------------------------------
                $event->sheet->getDelegate()->freezePane('A2');

                //新細明體---------------------------------------------------------------------------------------------------------------------
                $startColumn = 'A';
                $endColumn = 'E';
                $startRow = 1;
                $endRow = 1 + count($ordr);

                $event->sheet->getDelegate()
                    ->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->getFont()->setName('PMingLiU');
            },
        ];
    }
}
