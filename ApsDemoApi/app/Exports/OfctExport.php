<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OfctExport implements FromView, WithEvents, WithStyles, WithTitle
{
    protected $data;
    protected $dataArray;

    public function __construct($jsonArray)
    {
        $this->dataArray = $jsonArray;
    }

    public function view(): View
    {
        $dataArray = $this->dataArray;

        $ofct = array();
        foreach ($dataArray as $dataInfo) {
            $lineArray = $dataInfo['OFCT']['Lines'];
            $date = $dataInfo['OFCT']['Code'];
            foreach ($lineArray as $lineInfo) {
                $ofct[] = array(
                    'lot_no' => $lineInfo['U_Lot'],
                    'item_code' => $lineInfo['ItemCode'],
                    'lot_total' => $lineInfo['Quantity'],
                    'material_date' => $lineInfo['U_MATA_DATE'],
                    'product_date' => $lineInfo['U_PP_DATE'],
                    'date' => explode('-', $date)[0] . '年' . explode('-', $date)[1] . '月',
                );
            }

        }
        $data['ofct'] = $ofct;
        $this->data = $data;
        return view('ofct_export', ['tableData' => $data]);
    }

    //標題設定-WithTitle
    public function title(): string
    {
        return '年度生產計劃SAP';
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
                $ofct = $this->data['ofct'];

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(25);
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(8);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(15);

                //標題置中設定
                $event->sheet->getDelegate()->getStyle('A1:F1')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //靠右設定
                $startRow = 2;
                $endRow = 1 + count($ofct);
                $event->sheet->getDelegate()->getStyle('A' . $startRow . ':' . 'A' . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('B' . $startRow . ':' . 'B' . $endRow)
                    ->getAlignment()->setHorizontal('left')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('C' . $startRow . ':' . 'C' . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('D' . $startRow . ':' . 'D' . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('E' . $startRow . ':' . 'E' . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('F' . $startRow . ':' . 'F' . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //邊框設定
                $event->sheet->getStyle('A1:F1')->applyFromArray([
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
                $endColumn = 'F';
                $startRow = 1;
                $endRow = 1 + count($ofct);

                $event->sheet->getDelegate()
                    ->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->getFont()->setName('PMingLiU');
            },
        ];
    }
}
