<?php  
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once DIR_API.'MSOffice/PHPExcel-1.8/PHPExcel.php';
 
class MY_PHPExcel extends PHPExcel {
    public function __construct() {
        parent::__construct();
		
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '2048MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        // �H�W�ΥH�ѨM PHPExcel Fatal error: Allowed memory size ���O���餣�������~
    }
	
	function loadExcel($excel_file, $with_title='') {
		//try {
			$objPHPExcel = PHPExcel_IOFactory::load($excel_file);
		//} catch(Exception $e) {
			//die('Error loading file "'.pathinfo($excel_file,PATHINFO_BASENAME).'": '.$e->getMessage());
		//}

		$sheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

		if ( empty($sheet) ) return NULL;
		
		$i=0;
		$rows = array();
		foreach( $sheet as $key => $row ) {
			foreach( $row as $colkey => $colval ) {
				if ( $with_title == '' ) 
					$rows['value'][$i+1][$colkey] = $colval; // �����
				else {
					if ( !empty($colval) && $i < 1 )
						$rows['title'][$colval] = $colkey; // �����D
					else 
						$rows['value'][$i][$colkey] = $colval; // �����
				}
			}
			$i++;
		}
		//echo "<br>�����D:"; var_dump($rows['title']);
		//echo "<br>�����:"; var_dump($rows['value']);	
		return $rows;
    }
}