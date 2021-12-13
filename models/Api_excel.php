<?php
class Api_excel extends CI_Model{
   
    function __construct() {
        parent::__construct();
        $this->load->library('My_PHPExcel');

    }
   
    function export_Excel($name, $data=array(), $old_excel=TRUE ,$f_title,$detail) {
        if ( empty( $data ) ) 
            return; 
        else
            $rows = $data;
        // Set document properties
        $this->my_phpexcel->getProperties()->setCreator("")
                                     ->setLastModifiedBy("")
                                     ->setTitle("Office 2007 XLSX Document")
                                     ->setSubject("Office 2007 XLSX Document")
                                     ->setDescription("Document for Office 2007 XLSX")
                                     ->setCategory("orders result file");       
        //$this->my_phpexcel->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKGREEN ) );
    
        $this->my_phpexcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(12);    // Set default font

        $this->my_phpexcel->setActiveSheetIndex(0);     // // Set active sheet index to the first sheet, so Excel opens 

        $sheet = $this->my_phpexcel->getActiveSheet();

        //設定紙張尺寸 橫式
        $sheet->getPageSetup()
                    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()
                    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //設定列印邊界        
        $sheet->getPageSetup()->setFitToWidth(1); 
        $sheet->getPageMargins()->setTop(1);
        $sheet->getPageMargins()->setBottom(1.5);   
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);

        if($detail['sheetName']){
            $sheet->setTitle(mb_substr($detail['sheetName'],0, 31));     //name the worksheet
        }else{
            $sheet->setTitle(mb_substr($name,0, 31));     //name the worksheet
        }
        

        if(!$detail['titleLine']){//如果沒有設定表頭行->第三航
            $titleLine = "3";
        }else{
            $titleLine = $detail['titleLine'];
        }   
        if(!$detail['startLine']){//如果沒有設定資料開始行->第四航
            $detail['startLine'] = 4;
        }     

        if($detail['setThzNumber']){$setThzNumber = $detail['setThzNumber'];}
        if($detail['setPercentNumber']){$setPercentNumber = $detail['setPercentNumber'];}
        if($detail['setMathColor']){$setMathColor = $detail['setMathColor'];}
        if($detail['setTextField']){$setTextField = $detail['setTextField'];}
        if($detail['setTextField']){$setTextField = $detail['setTextField'];}
        if($detail['setSubTotalLine']){$setSubTotalLine = $detail['setSubTotalLine'];}
        
        $totalLine = $detail['totalLine']+$titleLine;
        $tba = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
            'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
            'CA','CB','CC','CD','CE','CF','CG','CH','CI','CJ','CK','CL','CM','CN','CO','CP','CQ','CR','CS','CT','CU','CV','CW','CX','CY','CZ',
            'DA','DB','DC','DD','DE','DF','DG','DH','DI','DJ','DK','DL','DM','DN','DO','DP','DQ','DR','DS','DT','DU','DV','DW','DX','DY','DZ',
            'EA','EB','EC','ED','EE','EF','EG','EH','EI','EJ','EK','EL','EM','EN','EO','EP','EQ','ER','ES','ET','EU','EV','EW','EX','EY','EZ',
            'FA','FB','FC','FD','FE','FF','FG','FH','FI','FJ','FK','FL','FM','FN','FO','FP','FQ','FR','FS','FT','FU','FV','FW','FX','FY','FZ',
            'GA','GB','GC','GD','GE','GF','GG','GH','GI','GJ','GK','GL','GM','GN','GO','GP','GQ','GR','GS','GT','GU','GV','GW','GX','GY','GZ',
            'HA','HB','HC','HD','HE','HF','HG','HH','HI','HJ','HK','HL','HM','HN','HO','HP','HQ','HR','HS','HT','HU','HV','HW','HX','HY','HZ',
            'IA','IB','IC','ID','IE','IF','IG','IH','II','IJ','IK','IL','IM','IN','IO','IP','IQ','IR','IS','IT','IU','IV','IW','IX','IY','IZ',
            'JA','JB','JC','JD','JE','JF','JG','JH','JI','JJ','JK','JL','JM','JN','JO','JP','JQ','JR','JS','JT','JU','JV','JW','JX','JY','JZ',
            'KA','KB','KC','KD','KE','KF','KG','KH','KI','KJ','KK','KL','KM','KN','KO','KP','KQ','KR','KS','KT','KU','KV','KW','KX','KY','KZ',
            'LA','LB','LC','LD','LE','LF','LG','LH','LI','LJ','LK','LL','LM','LN','LO','LP','LQ','LR','LS','LT','LU','LV','LW','LX','LY','LZ',
            'MA','MB','MC','MD','ME','MF','MG','MH','MI','MJ','MK','ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ');

        $ts = explode("_", $name);
        $sheet->fromArray(array($ts[0]), null, 'A1');
        //$sheet->fromArray(array("統計日期區間：".$ts[1]), null, 'A2');
        //$sheet->getStyle('A1')->getFont()->setName('微軟正黑體')->setSize(24);
        

        for($i=0;$i<count($data['A'.$detail['startLine']][0]);$i++){
            $sheet->getStyle($tba[$i].$titleLine)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffdc31'); 
            $sheet->getStyle($tba[$i].$titleLine)->getFont()->setBold(true);
            $sheet->getStyle($tba[$i].$titleLine)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
        }

        //畫框線區域
        $styleArray = array('borders' => array('allborders' => array("style" => PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '000000'),),),);
        $sheet->getStyle('A'.$titleLine.':'.$tba[count($data['A'.$detail['startLine']][0])-1].$totalLine)->applyFromArray($styleArray);
        
        if($setRightBorder){
            foreach ($setRightBorder as $key => $value) {
                 //加粗
                $styleArray = array('borders' => array('right' => array("style" => PHPExcel_Style_Border::BORDER_MEDIUM,'color' => array('argb' => '000000'),),),);
                $sheet->getStyle($value.($titleLine).':'.$value.($totalLine))->applyFromArray($styleArray);
            }
        }

        if($setTextField){
            foreach ($setTextField as $key => $value) {
                    // 設定指定欄位為文字格式
                $sheet->getStyle($value.($titleLine+1).':'.$value.($totalLine+2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
            }
            
        }

        if($setThzNumber){
            foreach ($setThzNumber as $key => $value) {
                    // 格式化千位金額
                $sheet->getStyle($value.($titleLine+1).':'.$value.($totalLine+2))->getNumberFormat()->setFormatCode("#,##0"); 
            }
        }
        if($setPercentNumber){
            foreach ($setPercentNumber as $key => $value) {
                    // 格式化百分比
                $sheet->getStyle($value.($titleLine+1).':'.$value.($totalLine+2))->getNumberFormat()->setFormatCode("0.00%");        
            }
        }
        if($setMathColor){ 
            foreach ($setMathColor as $key => $value) {
                    //公式底色
                $sheet->getStyle($value.($titleLine+1).':'.$value.$totalLine)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('c3dab3');      
            }
        }

        if($setSubTotalLine){ 
            foreach ($setSubTotalLine as $key => $value) {
                //echo 'A'.$setSubTotalLine[$key].':'.$tba[count($data['A'.$detail['startLine']][0])-1].$setSubTotalLine[$key]."<br>";
                $sheet->getStyle('A'.$setSubTotalLine[$key].':'.$tba[count($data['A'.$detail['startLine']][0])-1].$setSubTotalLine[$key])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('a4eaff');
            }
        }

        $titles = $f_title;
        // Header
        $sheet->fromArray( $titles, null, 'A'.$titleLine);    // From A1
        // Data
        $num=0;
        $total = count($rows);        
        foreach ($rows as $key => $value) {
            //$this->users_auth->saveMsg('產生Excel中...'.($num).'/'.$total,$detail['pageName'],'w+');
            $sheet->fromArray( $rows[$key], null, $key);
            $num++;
        }
        //$sheet->fromArray( $rows, null, 'A2');    // From A2
        //$sheet->fromArray( $rows );   // read data to active sheet
        if(!$detail['downloadName']){
            ob_end_clean();
        }
        if ( $old_excel ) {
            //$filename = urlencode($name.'.xls'); //save it as this file name
            $filename = iconv('UTF-8','Big5//IGNORE',trim($name)).".xls";
            header("Content-type: text/html; charset=utf-8");
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment;filename=".$filename);
            header('Cache-Control: max-age=1'); // If you're serving to IE 9, then the following may be needed
            
            //$this->users_auth->saveMsg('寫入資料中...這會花較多時間',$detail['pageName'],'w+');
            $objWriter=PHPExcel_IOFactory::createWriter($this->my_phpexcel, 'Excel5');
        }
        else {
            $filename = iconv('UTF-8','Big5//IGNORE',trim($name)).".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
            
            //$this->users_auth->saveMsg('寫入資料中...這會花較多時間',$detail['pageName'],'w+');
            $objWriter = PHPExcel_IOFactory::createWriter($this->my_phpexcel, 'Excel2007');
        }

        if($detail['downloadName']){
            if($old_excel){
                 $objWriter->save(DIR_SITE_FILE."report/".$detail['downloadName'].".xls");
            }else{
                 $objWriter->save(DIR_SITE_FILE."report/".$detail['downloadName'].".xlsx");
            }
           
        }else{
            $objWriter->save('php://output');
        }   
        //$this->users_auth->saveMsg('',$detail['pageName'],'w+');
        //exit;
        
    }

    function readExcel($filename) {
        $fileExcel = $filename;
        
        if (!file_exists($fileExcel)) {
            exit("Can't read ".$fileExcel.", please check it first.\n");
        }

        //error_reporting(E_ALL);
        error_reporting (E_ALL ^ E_NOTICE);
        set_time_limit(0);
        ini_set("memory_limit","2048M");

        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '2048MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        // 以上用以解決 PHPExcel Fatal error: Allowed memory size 的記憶體不足的錯誤

        $objPHPExcel = PHPExcel_IOFactory::load($fileExcel);
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet()->toArray(null,true,false,true);
        
        return $sheet;
    }

    function readCSV($filename){
        $inputFileType = 'CSV';
        $inputFileName = $filename;
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
        return $objPHPExcel;
    }

    function readMultipleExcel($filename) {
        $fileExcel = $filename;
        
        if (!file_exists($fileExcel)) {
            exit("Can't read ".$fileExcel.", please check it first.\n");
        }

        //error_reporting(E_ALL);
        error_reporting (E_ALL ^ E_NOTICE);
        set_time_limit(0);
        ini_set("memory_limit","2048M");

        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '2048MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        // 以上用以解決 PHPExcel Fatal error: Allowed memory size 的記憶體不足的錯誤

        $objPHPExcel = PHPExcel_IOFactory::load($fileExcel);
        $totalCount =$objPHPExcel->getSheetCount();

        for ($i=0; $i < $totalCount; $i++) {             
            $objPHPExcel->setActiveSheetIndex($i);
            $sheetName = str_replace([' ','（','）','(',')'], '', $objPHPExcel->getActiveSheet()->getTitle());
            //if($sheetName=='關鍵字文案表現CPA'){
                $sheet[$sheetName] = $objPHPExcel->getActiveSheet()->toArray(null,false,false,true);
            //}
        }
        
        return $sheet;
    }


}



?>