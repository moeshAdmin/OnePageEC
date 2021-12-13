<?php
class Api_table_generate extends CI_Model{
   
    function __construct() {
        parent::__construct();


    }
    function caclFormula($ary,$detail,$data){
        //預先計算
        foreach ($detail['cacl'] as $type => $value) {
            foreach ($detail['cacl'][$type] as $formulaKey => $formulaValue) {
                $temp = explode('||', $formulaValue);
                $formName = $temp[0];
                if($type=='countPercent'){//指定特定欄位為某兩欄位對除後取百分比
                    if(!preg_match('/\//', $temp[1])){echo '指令錯誤!';exit;}//排除沒有斜線的狀況
                    $temp2 = explode('/', $temp[1]);//拆出兩個對除欄位
                    //載入陣列資料開始計算
                    foreach ($ary as $key => $value) {

                        //如果除數為0或無資料就給-
                        if(!$ary[$key][$temp2[1]]||$ary[$key][$temp2[1]]==0){$ary[$key][$formName] = '-';continue;}
                        //除數保留兩位小數點+%
                        $ary[$key][$formName] = round(( str_replace(',', '',$ary[$key][$temp2[0]])/str_replace(',', '',$ary[$key][$temp2[1]])*100),2)."%";
                    }
                }
                if($type=='countAvg'){//指定特定欄位為某兩欄位對除後取百分比
                    if(!preg_match('/\//', $temp[1])){echo '指令錯誤!';exit;}//排除沒有斜線的狀況
                    $temp2 = explode('/', $temp[1]);//拆出兩個對除欄位
                    //載入陣列資料開始計算
                    foreach ($ary as $key => $value) {
                        //如果除數為0或無資料就給-
                        if(!$ary[$key][$temp2[1]]||$ary[$key][$temp2[1]]==0){$ary[$key][$formName] = '-';continue;}
                        //除數保留兩位小數點+%
                        $ary[$key][$formName] = round(( $ary[$key][$temp2[0]]/$ary[$key][$temp2[1]]))."";
                    }
                }
                if($type=='countPlus'){//指定特定欄位為某兩欄位對除後取百分比
                    if(strpos($temp[1], '+')>0){
                    $temp2 = explode('+', $temp[1]);
                        //載入陣列資料開始計算
                        foreach ($ary as $key => $value) {
                            //如果除數為0或無資料就給-
                            //if(!$ary[$key][$temp2[1]]||$ary[$key][$temp2[1]]==0){$ary[$key][$formName] = '-';continue;}
                            //除數保留兩位小數點+%
                            $ary[$key][$formName] = ($ary[$key][$temp2[0]]+$ary[$key][$temp2[1]])."";
                        }
                    }
                }
                //如果有小計列
                foreach ($detail['totalAry'] as $key4 => $value4) {
                    $totalValue = $value4;//定義分組，同分組key值資料計算 空白則為全部加總
                    $totalValuePreg = '/'.$value4.'/';
                    //echo "<br>".$formName."_";
                    if($type=='countPlusTotal'){//加總
                        if(strpos($temp[1], '+')>0){//有+號的情況

                        }else{//指令如果沒有加號->指定欄位直接加總
                            $ary[$totalValue.'小計'][$detail['title'][0]] = $totalValue.'小計';
                            foreach ($ary as $key3 => $value3) {
                                if(!preg_match($totalValuePreg, $key3)){continue;}
                                $plusCount += $ary[$key3][$temp[1]];
                                //echo $ary[$key3][$temp[1]]."+";
                            }
                            //echo "=".$plusCount;
                            $ary[$totalValue.'小計'][$formName] = $plusCount;
                            $plusCount = '';
                        }                    
                    }
                    if($type=='countPercentTotal'){//百分比
                        if(!preg_match('/\//', $temp[1])){echo '指令錯誤!';exit;}//排除沒有斜線的狀況
                        $temp2 = explode('/', $temp[1]);//拆出兩個對除欄位
                        foreach ($ary as $key3 => $value3) {
                            if(!preg_match($totalValuePreg, $key3)){continue;}
                            //echo $totalValue.'小計'."__".$temp2[1]."__".$ary[$totalValue.'小計'][$temp2[0]]."||".$ary[$totalValue.'小計'][$temp2[1]]."<br>";
                            $ary[$totalValue.'小計'][$formName] = round(($ary[$totalValue.'小計'][$temp2[0]]/$ary[$totalValue.'小計'][$temp2[1]]*100),2)."%";
                        }
                    }
                }
                
            }
        }
        foreach ($ary as $key => $value) {
            foreach ($ary[$key] as $formName => $value2) {
                if(preg_match('/金額|費/', $formName)&&!preg_match('/佔比/', $formName)){
                    if(!$detail['disableNumberFormat']){
                        $ary[$key][$formName] = number_format(round($ary[$key][$formName],0));
                    }
                }
            }
        }
        if($detail['disableKsort']){

        }else{
            ksort($ary);
        }
        
        return $ary;
    }
    function drawTable($ary,$detail,$data){
        if(!$detail['fontSize']){
            $detail['fontSize'] = 12;
        }
        if($detail['striped']!='N'){
            $striped = 'table-striped';
        }
        if($detail['overFlow']=='Y'){
            $overFlow = 'table table-condensed table-bordered table-hover table-striped';
            $detail['tableStyle'] .= 'table-layout:fixed;';
        }
        if(!$detail['tableWidth']){
            $detail['tableWidth'] = '100%';
        }
        //echo '<pre>' . var_export($ary, true) . '</pre>';
        //輸出表格
        $output .= '<table class="table table-sm table-hover table-rwd '.$striped.$overFlow.'" style="font-size:'.$detail['fontSize'].'pt;text-align:center;width:'.$detail['tableWidth'].';'.$detail['tableStyle'].'"><thead>';
        foreach ($detail['title'] as $key => $value) {   
            $style = '';  
            $style .= $this->setStyle($detail,$value,$style);    
            $output .= '<th style="text-align:center;'.$style.'">'.$value.'</th>';
        }
         $output .= '</thead><tbody>';
        foreach ($ary as $key => $value) {
            if($ary[$key]['trKeyName']){$trKeyName = 'id="'.$ary[$key]['trKeyName'].'"';}
            //if(!$ary[$key]['業務部門']){continue;}
            if( (preg_match('/小計/',$key)&&!$detail['disableFooter']) || in_array($key, $detail['setFooter'])){
                $outputFoot .= '<tr class="positive" '.$trKeyName.'>'; 
                foreach ($detail['title'] as $key2 => $value2) {
                    $style = '';
                    $style .= $this->setStyle($detail,$value2,$style);
                    if(in_array($value2, $detail['setNumber'])){
                        $outputFoot .='<td data-th="'.$value2.'" style="'.$style.'">'.number_format($ary[$key][$value2]).'</td>'; 
                    }else if($ary[$key][$value2]=="0"){
                         $outputFoot .='<td data-th="'.$value2.'" style="'.$style.'">0</td>'; 
                    }else if($ary[$key][$value2]){
                         $outputFoot .='<td data-th="'.$value2.'" style="'.$style.'">'.$ary[$key][$value2].'</td>'; 
                    }else{
                         $outputFoot .= '<td data-th="'.$value2.'" style="'.$style.'">-</td>'; 
                    }
                    
                }
                $outputFoot .= '</tr>';
            }else{
                if(preg_match('/Y/',$ary[$key]['醒目'])||preg_match('/小計/',$key)){
                    $output .= '<tr style="background:#ffeeba4d;" '.$trKeyName.'>'; 
                }else if(preg_match('/Y/',$ary[$key]['醒目2'])){
                    $output .= '<tr class="negative" '.$trKeyName.'>'; 
                }else if(preg_match('/Y/',$ary[$key]['醒目3'])){
                    $output .= '<tr class="warning" '.$trKeyName.'>'; 
                }else if(preg_match('/Y/',$ary[$key]['customH'])){
                     $output .= '<tr '.$ary[$key]['customHCSS'].' '.$trKeyName.'>';
                }else if(preg_match('/Y/',$ary[$key]['反灰'])){
                     $output .= '<tr class="disabled" '.$trKeyName.'>';
                }else{
                     $output .= '<tr '.$trKeyName.'>';
                }
                foreach ($detail['title'] as $key2 => $value2) {
                    $style = '';
                    $style .= $this->setStyle($detail,$value2,$style);
                    if(in_array($value2, $detail['setNumber'])){
                        $output .='<td data-th="'.$value2.'" style="'.$style.'">'.number_format($ary[$key][$value2]).'</td>'; 
                    }else if($ary[$key][$value2]=="0"){
                         $output .='<td data-th="'.$value2.'" style="'.$style.'">0</td>'; 
                    }else if($ary[$key][$value2]){
                         $output .='<td data-th="'.$value2.'" style="'.$style.'">'.$ary[$key][$value2].'</td>'; 
                    }else{
                         $output .= '<td data-th="'.$value2.'" style="'.$style.'">-</td>'; 
                    }
                    
                }
                $output .= '</tr>';
            }
        }
         $output .= '</tbody><tfoot>'.$outputFoot.'</tfoot></table><center><span class="table_page"></span></center>';
        return $output;
    }

    function setStyle($detail,$fieldName,$style){
        if(in_array($fieldName, $detail['fatBorder'])){
            $style .= "border-right:2px solid #ccc;";
        }else if(in_array($fieldName, $detail['border'])){
            $style .= "border-right:1px solid #ccc;";
        }else if(in_array($fieldName, $detail['allBorder'])){
            $style .= "border:1px solid #ccc;";
        }
        
        if(in_array($fieldName, $detail['alignRight'])){
            $style .= "text-align:right;";
        }  
        if(in_array($fieldName, $detail['alignLeft'])){
            $style .= "text-align:left;";
        }  
        if(in_array($fieldName, $detail['alignCenter'])){
            $style .= "text-align:center;";
        }
        if(in_array($fieldName, $detail['verticalCenter'])){
            $style .= "vertical-align:middle;";
        }  
        if(in_array($fieldName, $detail['colorGreen'])){
            $style .= "background:#eeffe7!important;";
        }
        if(in_array($fieldName, $detail['colorOrange'])){
            $style .= "background:#fff9e0!important;";
        }
        if(in_array($fieldName, $detail['keepAll'])){
            $style .= "word-break:keep-all;";
        }
        if(in_array($fieldName, $detail['tdPadding'])){
            $style .= "padding:15px;";
        }
        if(in_array($fieldName, $detail['customV'])){
            $style .= $detail['customVCSS'][$fieldName];
        }
        
        return $style;
    }

   
}
?>