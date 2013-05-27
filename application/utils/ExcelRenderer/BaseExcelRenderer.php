<?php

namespace ExcelRenderer;

use \PHPExcel;
use \PHPExcel_Style_Border;
use \PHPExcel_Style_Fill;
use \PHPExcel_Style_Color;
use \PHPExcel_IOFactory;


abstract class BaseExcelRenderer
{
    public $row = 1;
    public $col = 0;
    public $sheet = null;
    public $fileName = '';
    public $phpExcel = null;
    public $dataMap = array();   
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->phpExcel = new PHPExcel();
        $this->phpExcel->setActiveSheetIndex(0);

        $this->sheet = $this->phpExcel->getActiveSheet();

        $this->sheet->getDefaultStyle()->getFont()
            ->setName('Arial')
            ->setSize(11);   
    }

    /**
     *
     * @param mixed $val
     * @param array $options 
     */
    public function formatRowElement($val, $options = array())
    {
        $this->sheet->setCellValueByColumnAndRow($this->col++, $this->row, $val);
        $style = $this->sheet->getStyleByColumnAndRow($this->col - 1, $this->row);
        $this->sheet->getColumnDimensionByColumn($this->col)->setAutoSize(true);
        $this->sheet->getRowDimension($this->row)->setRowHeight(20);

        $style->getBorders()->applyFromArray(
            array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            )
        );

        if (isset($options['bold'])) {

            $style->getFont()->setBold(true);
            $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->setStartColor(new PHPExcel_Style_Color('FFECECEC'));
        }

        if (isset($options['right']))
            $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    }
    
    /**
     * 
     */
    public function formHeaders()
    {
        $this->col = 0;    
        foreach ($this->dataMap as $value => $headerName)
        {
            $this->formatRowElement($headerName, array('bold' => true));
        }
        $this->row++;
        $this->col = 0;        
    }
    
    /**
     * 
     */
    public function formSubHeader($title)
    {
        $this->col = 0;    
        $this->formatRowElement($title, array('bold' => true));
        $this->sheet->mergeCellsByColumnAndRow(0, $this->row, count($this->dataMap) - 1, $this->row);
        $this->row++;
        $this->col = 0;        
    }    
    
    /**
     * 
     */
    public function fillData($data)
    {
        $this->col = 0;
        foreach ($data as $rowValues) {    
            foreach ($this->dataMap as $dataKey => $name) {
                $this->formatRowElement((isset($rowValues[$dataKey])) ? $rowValues[$dataKey] : '');
                $this->sheet->getColumnDimensionByColumn($this->col-1)->setAutoSize(true);      
            }
            $this->col = 0;
            $this->row++;
        }
    }

    /**
     * Forces download of rendered xls
     */
    public function download()
    {
        header("Content-Disposition: attachment; filename=" . $this->fileName);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }
}
