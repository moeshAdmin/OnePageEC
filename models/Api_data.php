<?php
class Api_data extends CI_Model{
   
    function __construct() {
        parent::__construct();
        
    }

    function data($id,$detail=null){
        //$this->load->model('Users_auth');
        if($detail['postData']){
            $postData = $detail['postData'];
        }else{
            $postData = $this->input->post();
            $postData = $this->Api_common->cleanPostData($postData);
            if (!$this->input->is_ajax_request()) {exit;}
        }
        $id = strtolower($id);
        //load cache
        $return = json_decode($this->Api_common->cache('load',$postData['type'].'-'.$id,null,$detail),true);

        if($return['data']){
            return $return['data'];
        }

        if(!$postData['type']){exit;}

        if($postData['type']=='article'||$postData['type']=='event'){
            $resData = $this->Api_common->getDataCustom('*','cms_article','caSysID = "'.$id.'" OR caURL="'.$id.'"','caDate DESC');
            if(!$resData[0]['caTitle'.LANG]||!$resData[0]['caContent'.LANG]){
                $lang = 'TW';
            }else{
                $lang = LANG;
            }
            $retData['title'] = $resData[0]['caTitle'.$lang];
            $retData['articleData'][0]['title'] = $resData[0]['caTitle'.$lang];
            if($resData[0]['caBanner'.$lang]){
                if(str_replace('://', '', $resData[0]['caBanner'.$lang])==$resData[0]['caBanner'.$lang]){
                    $retData['articleData'][0]['banner'] = base_url().$resData[0]['caBanner'.$lang];
                }else{
                    $retData['articleData'][0]['banner'] = $resData[0]['caBanner'.$lang];
                }
            }
            $retData['articleData'][0] = $this->contentProcess($resData[0],$retData['articleData'][0],$lang);  
            $bamData = $this->getBreadAndMenu($lang,$resData[0]['caCateID'],$resData[0]['caSysID'],$resData[0]['caCateID']);
            $retData['articleData'][0]['date'] = $resData[0]['caDate'];
            $retData['articleData'][0]['type'] = $resData[0]['caType'.$lang];
            $retData['articleData'][0]['sys'] = $resData[0]['caSysID'];
            if($resData[0]['caMeta'.$lang]){
                $temp = explode(';', $resData[0]['caMeta'.$lang]);
                foreach ($temp as $key2 => $value2) {
                    if(!$temp[$key2]){continue;}
                    $temp2 = explode('||', $temp[$key2]);
                    $retData['articleData'][0]['meta'][$temp2[0]] = $temp2[1];
                }
            }else{
                $retData['articleData'][0]['meta'] = null;
            }
            //有設定slide圖片
            if($resData[0]['caSlider']){
                $temp = explode(';', $resData[0]['caSlider']);
                foreach ($temp as $key => $value) {
                    if(str_replace('://', '', $value)==$value){
                        $imgAry[$key] = base_url().$value;
                    }else{
                        $imgAry[$key] = $value;
                    }
                }
                $retData['articleData'][0]['slider'] = $imgAry;
            }
            //有related article顯示
            preg_match_all('/\[related\](.*?)\[\/related\]/', $retData['articleData'][0]['content'], $relatedID);
            foreach ($relatedID[1] as $key => $value) {
                $relatedArticle = $this->getRelatedArticle(strip_tags($relatedID[1][$key]),'normal');
                $retData['articleData'][0]['content'] = str_replace($relatedID[0][$key], $relatedArticle,$retData['articleData'][0]['content']);
            }
            preg_match_all('/\[related-sm\](.*?)\[\/related-sm\]/', $retData['articleData'][0]['content'], $relatedID);
            foreach ($relatedID[1] as $key => $value) {
                $relatedArticle = $this->getRelatedArticle(strip_tags($relatedID[1][$key]),'small');
                $retData['articleData'][0]['content'] = str_replace($relatedID[0][$key], $relatedArticle,$retData['articleData'][0]['content']);
            }
        }else if($postData['type']=='category'){
            $this->db->select('*');
            $this->db->from('cms_categorys');
            $this->db->join('cms_article', 'ccSysID = caCateID','left');            
            $this->db->where('ccSysID',$id);
            $this->db->or_where('ccParent',$id);
            $this->db->or_where('ccURL',$id);
            $this->db->or_where('caURL',$id);
            $this->db->order_by('caDate DESC');
            
            $query = $this->db->get();
            $resData = array();
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row){
                    array_push($resData, $row);
                }
            }
            foreach ($resData as $key => $value) {
                if(!$resData[$key]['ccName'.LANG]){
                    $lang = 'TW';
                }else{
                    $lang = LANG;
                }
                //找到指定分類
                if($resData[$key]['ccSysID']==$id||$resData[$key]['ccURL']==$id){
                    $retData['title'] = strip_tags($resData[$key]['ccName'.$lang]);
                    $retData['template'] = $resData[$key]['ccTemplate'.$lang];
                    $retData['banner'] = base_url().$resData[$key]['ccImageUrl'.$lang];
                    $cateID = $resData[$key]['ccSysID'];
                    $ppID = $resData[$key]['ccParent'];
                }
                if(!$resData[$key]['caTitle'.$lang]){continue;}
                //$retData['articleData'][$key]['title'] = $resData[$key]['caTitle'.$lang];
                //$retData['articleData'][$key]['banner'] = base_url().$resData[$key]['caBanner'];
                //$retData['articleData'][$key] = $this->contentProcess($resData[$key],$retData['articleData'][$key],$lang);  
            }

            $bamData = $this->getBreadAndMenu($lang,$cateID,$cateID,$ppID);
            //$bamData['breadcrumb'] = substr($bamData['breadcrumb'], 0,strrpos($bamData['breadcrumb'], ' > '));
        }
        $retData['breadcrumb'] = $bamData['breadcrumb'];
        $retData['leftmenu'] = $bamData['leftmenu'];
        $retData['parentCate'] = $bamData['parentCate'];
        $retData['type'] = $postData['type'];
        //$this->Api_common->dataDump($resData);
        //save cache
        if(!$retData['title']){
            echo $this->Api_common->setFrontReturnMsg('401','',null);
            exit;
        }else{
            $this->Api_common->cache('save',$postData['type'].'-'.$id,$retData);
        }

        if($detail['return']){
            return $retData;
        }else{
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        }
        
        exit; 
    }

    function getItemCardElements($allItemData){
        foreach ($allItemData as $key => $value) {
            $allItemData[$key]['eiSetting'] = json_decode($allItemData[$key]['eiSetting'],true);
            $allItemData[$key]['eiImg'] = explode(';', $allItemData[$key]['eiImg']);
        }
        foreach ($allItemData as $key => $value) {
              $itemName = $value['eiName'];
              if($_GET['utm_source']){
                $utm = '&utm_source='.$_GET['utm_source'].'&utm_medium='.$_GET['utm_medium'].'&utm_campaign='.$_GET['utm_campaign'].'&utm_term='.$_GET['utm_term'].'&utm_content='.$_GET['utm_content'];
              }
              $url = base_url().'ec/EC_Order?itemID='.$value['eiSysID'].$utm;
              $value['eiImg'][0] = str_replace('../', base_url(), $value['eiImg'][0]);
              $imgEl = '<img class="card-img-top" src="'.$value['eiImg'][0].'" alt="'.$itemName.'">';
              $itemData = array_shift($value['eiSetting']);
              if(!$value['eiImg'][0]){
                $imgEl = '<div class="card-img-top" style="background:url('.base_url().'assets/images/logo.png);background-repeat: no-repeat;background-position: center;"></div>';
              }
              $str .= '<div class="card col-md-4">
                      <a href="'.$url.'">'.$imgEl.'</a>
                      <div class="card-body">
                        <a href="'.$url.'"><p class="card-text">'.$itemName.'</p>
                        <p class="card-text">
                          <del>$'.number_format($itemData['nprice']).'</del><br>
                          <price>$'.number_format($itemData['price']).'</price>
                        </p></a>
                      </div>
                    </div>';
        }
        $return['cardElement'] = $str;
        return $return;
    }

    function getMenu($type){
        if(!LANG){
            $lang = 'TW';
        }else{
            $lang = LANG;
        }
        if(preg_match('/topMenu|footMenu|front|footMeta/', $type)){
            $target = str_replace('_return', '', $type);
        }else{
            exit;
        }
        //load cache        
        if(preg_match('/return/', $type)){
            $return = json_decode($this->Api_common->cache('load',$target,null,['return'=>true]),true);
            if($return){return $return['data'];}            
        }else{
            $this->Api_common->cache('load',$target);
        }

        $spNum = date('m')*date('d');
        $resData = $this->Api_common->getDataCustom('*','cms_menu','cmMenuType = "'.$target.$lang.'"','cmOrder');
        if(!$resData){
            $resData = $this->Api_common->getDataCustom('*','cms_menu','cmMenuType = "'.$target.'"','cmOrder');
        }
        
        foreach ($resData as $key => $value) {
            if(preg_match('/_ignore/', $resData[$key]['cmName'.$lang])){
                continue;
            }
            $element[$key]['id'] = $resData[$key]['cmSysID']*$spNum;
            $element[$key]['parent'] = $resData[$key]['cmParent']*$spNum;
            $element[$key]['template'] = $resData[$key]['cmTemplate'.$lang];
            $element[$key]['targetType'] = $resData[$key]['cmTargetType'];
            $element[$key]['targetID'] = $resData[$key]['cmTargetID'];
            if($resData[$key]['cmImgurl']){
                if(preg_match('/http/', $resData[$key]['cmImgurl'])){
                    $element[$key]['imgurl'] = $resData[$key]['cmImgurl'];
                }else{
                    $element[$key]['imgurl'] = base_url().$resData[$key]['cmImgurl'];
                }
            }
            
            $element[$key]['tab1'] = $resData[$key]['cmTab1']*$spNum;
            $element[$key]['tab2'] = ($resData[$key]['cmTab2']*$spNum).'-1';
            $element[$key]['meta'] = str_replace('[base_url]', base_url(), $resData[$key]['cmMeta'.$lang]);
            if($resData[$key]['cmTargetURL']){
                if(preg_match('/http/', $resData[$key]['cmTargetURL'])){
                    $element[$key]['url'] = $resData[$key]['cmTargetURL'];
                }else{
                    $element[$key]['url'] = base_url().$resData[$key]['cmTargetURL'];
                }
            }else{
                if($resData[$key]['ccURL']){
                    $element[$key]['url'] =  base_url().'pages/'.$resData[$key]['cmTargetType'].'/'.$resData[$key]['ccURL'];
                }else{
                    $element[$key]['url'] =  base_url().'pages/'.$resData[$key]['cmTargetType'].'/'.$resData[$key]['cmTargetID'];
                }
            }
            if($resData[$key]['cmTargetType']=="category"){                
                if($resData[$key]['ccName'.$lang]){
                   $element[$key]['name'] = $resData[$key]['ccName'.$lang]; 
                   $element[$key]['desc'] = $resData[$key]['ccDesc'.$lang]; 
                }else{
                   $element[$key]['name'] = $resData[$key]['ccNameTW']; 
                   $element[$key]['desc'] = $resData[$key]['ccDescTW']; 
                }                
            }else if($resData[$key]['cmTargetType']=="article"){                
                if($resData[$key]['caTitle'.$lang]){
                   $element[$key]['name'] = $resData[$key]['caTitle'.$lang]; 
                   $element[$key]['desc'] = mb_substr($resData[$key]['caContent'.$lang], 0,400); 
                }else{
                   $element[$key]['name'] = $resData[$key]['caTitleTW']; 
                   $element[$key]['desc'] = mb_substr($resData[$key]['caContentTW'], 0,400); 
                }                
            }
            if(!$element[$key]['name']){
                $element[$key]['name'] = $resData[$key]['cmName'.$lang]; 
            }
            if(preg_match('/_ignore/', $element[$key]['name'])){
                unset($element[$key]);
                continue;
            }
        }

        $retData['parentOptionData'][$type] = $element;
        $treeData = $this->Api_common->buildTree($element,0);
        if($target=="front"){
            foreach ($treeData as $key => $value) {
                if($treeData[$key]['targetType']=="category"){
                    $findID = array();
                    $targetID = $treeData[$key]['targetID'];
                    $resData = $this->Api_common->getDataCustom('ccParent,ccSysID','cms_categorys','ccParent = "'.$targetID.'" OR ccSysID = "'.$targetID.'"');
                    foreach ($resData as $key2 => $value2) {
                        array_push($findID, $resData[$key2]['ccParent']);
                        array_push($findID, $resData[$key2]['ccSysID']);
                    }
                    $resData = $this->Api_common->getDataInCustom('*','cms_article','caCateID',$findID,'caDate DESC','in');
                    $acData = array();
                    $num = 0;
                    foreach ($resData as $key2 => $value2) {
                        if($num>2){continue;}
                        if(preg_match('/_ignore/', $resData[$key]['caTitle'.$lang])){
                            continue;
                        }
                        $acData[$key2]['name'] = $resData[$key2]['caTitle'.$lang];
                        $acData[$key2]['meta'] = mb_substr(strip_tags($resData[$key2]['caContent'.$lang]),0,80).'...';
                        $acData[$key2]['date'] = $resData[$key2]['caDate'];
                        if($resData[$key2]['caIcon']){
                            $acData[$key2]['imgurl'] = base_url().$resData[$key2]['caIcon'];
                        }
                        $acData[$key2]['url'] = base_url().$resData[$key2]['caURL'];
                        if($resData[$key2]['caURL']){
                            $acData[$key2]['url'] = base_url().'pages/article/'.$resData[$key2]['caURL'];
                        }else{
                            $acData[$key2]['url'] = base_url().'pages/article/'.$resData[$key2]['caSysID'];
                        }
                        $num++;
                    }
                    $treeData[$key]['children'] = $acData;
                }
            }
        }
        
        //save cache
        $this->Api_common->cache('save',$target,$treeData);

        if(preg_match('/return/', $type)){
            return $treeData;
        }else{
            echo $this->Api_common->setFrontReturnMsg('200','',$treeData);
            exit;
        }
    }

    private function getProducts(){
        $resData = $this->Api_common->getDataCustom('*','cms_categorys','all');
        foreach ($resData as $key => $value) {
            $lang=LANG;
            if(strlen($resData[$key]['ccName'.$lang])==0){$lang='TW';}
            $cateID = $resData[$key]['ccSysID'];
            $cateData[$cateID]['name'] = strip_tags($resData[$key]['ccName'.$lang]);
            if(preg_match('/_ignore/', $cateData[$cateID]['name'])){unset($cateData[$cateID]);continue;}
            $cateData[$cateID]['desc'] = $resData[$key]['ccDesc'.$lang];
            $cateData[$cateID]['parentID'] = $resData[$key]['ccParent'];
            $cateData[$cateID]['url'] = base_url().'pages/category/'.$resData[$key]['ccURL'];
            $cateData[$cateID]['related'] = $resData[$key]['ccRelated'];
            $cateData[$cateID]['order'] = $resData[$key]['ccOrder'];
            if($resData[$key]['ccImageUrl'.$lang]){
                $cateData[$cateID]['imgurl'] = base_url().$resData[$key]['ccImageUrl'.$lang];
            }
            $cateData[$cateID]['sys'] = $cateID;
            if($cateID==35){
                $data['title'] = strip_tags($cateData[$cateID]['name']);
                $data['template'] = $resData[$key]['ccTemplate'.LANG];
                $data['banner'] =  base_url().$resData[$key]['ccImageUrl'.LANG];
            }
        }
        foreach ($cateData as $cateID => $value) {
            $parentID = $cateData[$cateID]['parentID'];
            $newParentID = $parentID+($cateData[$parentID]['order']*1000);  
            $retData[$newParentID] = $cateData[$parentID];
        }

        $data['parentCate'] = $cateData[35];
        
        $resData = $this->Api_common->getDataCustom('*','cms_article','caType'.$lang.' = "product"');
        foreach ($resData as $key => $value) {
            $lang=LANG;
            if(strlen($resData[$key]['caTitle'.$lang])==0){$lang='TW';}
            $cateID = $resData[$key]['caCateID'];
            if(!$cateData[$cateID]){continue;}
            $parentID = $cateData[$cateID]['parentID'];
            $newParentID = $cateData[$cateID]['parentID']+($cateData[$parentID]['order']*1000);  
            $num = count($retData[$newParentID]['children']);
            $retData[$newParentID]['children'][$num]['name'] = strip_tags($resData[$key]['caTitle'.$lang]);
            $article = $this->contentProcess($resData[$key],null,$lang);
            $retData[$newParentID]['children'][$num]['keyfeature'] = str_replace('&nbsp;','',strip_tags(mb_substr($article['keyfeature'], 0,200)).'...');
            if(preg_match('/_ignore/', $retData[$newParentID]['children'][$num]['name'])){unset($retData[$newParentID]['children'][$num]);continue;}
            if($resData[$key]['caURL']){
                $retData[$newParentID]['children'][$num]['url'] = base_url().'pages/article/'.$resData[$key]['caURL'];
            }else{
                $retData[$newParentID]['children'][$num]['url'] = base_url().'pages/article/'.$resData[$key]['caSysID'];
            }

            $retData[$newParentID]['children'][$num]['sys'] = $resData[$key]['caSysID'];
            $retData[$newParentID]['children'][$num]['imgurl'] = base_url().$resData[$key]['caBanner'.$lang];
            $retData[$newParentID]['children'][$num]['iconurl'] = base_url().$resData[$key]['caIcon'];
            $retData[$newParentID]['children'][$num]['parentID'] = $resData[$key]['caCateID'];
            $retData[$newParentID]['children'][$num]['parentName'] = $cateData[$resData[$key]['caCateID']]['name'];
            if($resData[$key]['caMeta'.$lang]){
                $temp = explode(';', $resData[$key]['caMeta'.$lang]);
                foreach ($temp as $key2 => $value2) {
                    if(!$temp[$key2]){continue;}
                    $temp2 = explode('||', $temp[$key2]);
                    $retData[$newParentID]['children'][$num]['meta'][$temp2[0]] = $temp2[1];
                }
            }else{
                $retData[$newParentID]['children'][$num]['meta'] = null;
            }
            

        }
        //$this->Api_common->dataDump($retData);exit;
        foreach ($retData as $newParentID => $value) {
            if(!$retData[$newParentID]['children']||!$retData[$newParentID]['name']){unset($retData[$newParentID]);}
            if(count($retData[$newParentID]['children'])>8){
                //shuffle($retData[$newParentID]['children']);
            }
        }

        $data['parentCate']['children'] = $retData;
        $data['leftmenu'][0] = $data['parentCate'];
        $data['type'] = 'category';
        
        //$this->Api_common->dataDump($data);exit;
        return $data;
    }

    private function getBreadAndMenu($lang,$parentID,$nowID,$ppID=null){

        $resData = $this->Api_common->getDataCustom('*','cms_categorys','ccIsDel = "N"');
        foreach ($resData as $key => $value) {
            //取得所有分類
            $id = $resData[$key]['ccSysID'];
            if(!$resData[$key]['ccName'.LANG]){
                $lang = 'TW';
            }else{
                $lang = LANG;
            }
            $menuData[$id]['name'] = strip_tags($resData[$key]['ccName'.$lang]);
            $menuData[$id]['desc'] = $resData[$key]['ccDesc'.$lang];
            $menuData[$id]['template'] = $resData[$key]['ccTemplate'.$lang];
            $menuData[$id]['related'] = $resData[$key]['ccRelated'];
            if($resData[$key]['ccImageUrl'.$lang]){
                $menuData[$id]['imgurl'] = base_url().$resData[$key]['ccImageUrl'.$lang];
            }
            if($resData[$key]['ccIconUrl']){
                $menuData[$id]['iconurl'] = base_url().$resData[$key]['ccIconUrl'];
            }
            $menuData[$id]['parentID'] = $resData[$key]['ccParent'];
            if ($resData[$key]['ccURL']) {
                $menuData[$id]['url'] = base_url().'pages/category/'.$resData[$key]['ccURL'];
            }else{
                $menuData[$id]['url'] = base_url().'pages/category/'.$resData[$key]['ccSysID'];
            }
            
            if($id==$parentID){
                //找到母分類後，尋找同層文章
                $cateArticle = $this->Api_common->getDataCustom('*','cms_article','caCateID = "'.$parentID.'"',$resData[$key]['ccOrderBy']);
                $menuData = $this->getArticleMeta($cateArticle,$menuData,$nowID,$lang,$id);
                if(!$cateArticle){//不是文章的情況下 尋找本階層子項目
                    if($ppID=='67'){
                        $odc = 'ccNameEN DESC';
                    }else{
                        $odc = '';
                    }
                    $cateData = $this->Api_common->getDataCustom('*','cms_categorys','ccParent = "'.$parentID.'" AND ccIsDel = "N"',$odc);
                    foreach ($cateData as $key2 => $value2) {
                        if(!$cateData[$key2]['ccName'.$lang]){
                            $lang = 'TW';
                        }
                        $menuData[$id]['children'][$key2]['name'] = strip_tags($cateData[$key2]['ccName'.$lang]);
                        if ($cateData[$key2]['ccURL']) {
                            $menuData[$id]['children'][$key2]['url'] = base_url().'pages/category/'.$cateData[$key2]['ccURL'];
                        }else{
                            $menuData[$id]['children'][$key2]['url'] = base_url().'pages/category/'.$cateData[$key2]['ccSysID'];
                        }
                        $menuData[$id]['children'][$key2]['sys'] = $cateData[$key2]['ccSysID'];
                        if($cateData[$key2]['ccImageUrl'.$lang]){
                            $menuData[$id]['children'][$key2]['imgurl'] = base_url().$cateData[$key2]['ccImageUrl'.$lang];
                        }
                        if($cateData[$key2]['ccIconUrl']){
                            $menuData[$id]['children'][$key2]['iconurl'] = base_url().$cateData[$key2]['ccIconUrl'];
                        }
                        if($cateData[$key2]['ccSysID']==$nowID){
                            $menuData[$id]['children'][$key2]['active'] = true;
                        }

                        $cateArticle = $this->Api_common->getDataCustom('*','cms_article','caCateID = "'.$cateData[$key2]['ccSysID'].'"',$resData[$key]['ccOrderBy']);
                        $menuData[$id]['children'] = $this->getArticleMeta($cateArticle,$menuData[$id]['children'],'',$lang,$key2);
                    }
                }
                $menuData[$id]['active'] = true;
                $leftMenu[$id] = $menuData[$id];
                $parentUpID = $menuData[$id]['parentID'];
                
            }
        }
        //逐層推出麵包削
        $temp = $this->getParent($menuData,$parentID,$resultData);
        $retData['breadcrumb'] = $temp['breadcrumb'];
        $menuData = $temp['menuData'];

        //第二層
        foreach ($menuData as $id => $value) {
            //平行階層
            if($menuData[$id]['parentID']==$parentUpID||$id==$ppID){
                $leftMenu[$id] = $menuData[$id];
            }
            //品牌
            if($menuData[$id]['parentID']&&$menuData[$id]['active']==true){
                $leftMenu[$menuData[$id]['parentID']] = $menuData[$menuData[$id]['parentID']];
            }
            //空白層(避免無資料選單頁面載入錯誤)
            if($menuData[$id]['parentID']==0&&$menuData[$id]['active']==true){
                $parentCate = $menuData[$id];
            }
        }

        //頂層
        $count = 0;
        foreach ($leftMenu as $id => $value) {
            if($leftMenu[$id]['parentID']==0){
                $count++;
            }
        }
        //如果是第一層 把最頂層移除
        if($count==1){
            foreach ($leftMenu as $id => $value) {
                if($leftMenu[$id]['parentID']==0){
                    unset($leftMenu[$id]);
                }
            }
        }
        if($parentCate['name']=='Media Center'){
            foreach ($leftMenu as $key => $value) {
                $year = (int)$leftMenu[$key]['name'];
                if($year>2000){
                    $new[$year] = $leftMenu[$key];
                    unset($leftMenu[$key]);
                }
            }
            if($leftMenu&&$new){
                $leftMenu = array_merge($leftMenu,$new);
            }
        }
        
        $retData['leftmenu'] = $leftMenu;
        $retData['parentCate'] = $parentCate;
        return $retData;
    }

    private function getParent($menuData,$parentID,$resultData){
        if($parentID==0){
            $retData['breadcrumb'] = '<a href="'.base_url().'">Home</a> > '.$resultData;
            $retData['menuData'] = $menuData;
            return $retData;
        }
        if(preg_match('/logo|none/', $menuData[$parentID]['template'])){
            $base = $menuData[$parentID]['name'];
        }else{
            $base = '<a href="'.base_url().'pages/category/'.$parentID.'">'.$menuData[$parentID]['name'].'</a>';
        }
        
        if(!$resultData){
            $resultData = $base;
        }else{
            $resultData = $base.' > '.$resultData;
        }
        $menuData[$parentID]['active'] = true;
        return $this->getParent($menuData,$menuData[$parentID]['parentID'],$resultData);
    }

    private function getArticleMeta($cateArticle,$menuData,$nowID,$lang,$id){
        foreach ($cateArticle as $key2 => $value2) {
            if(!$cateArticle[$key2]['caTitle'.$lang]){$lang='TW';}
            $menuData[$id]['children'][$key2]['name'] = $cateArticle[$key2]['caTitle'.$lang];
            if($cateArticle[$key2]['caURL']){
                $menuData[$id]['children'][$key2]['url'] = base_url().'pages/article/'.$cateArticle[$key2]['caURL'];
            }else{
                $menuData[$id]['children'][$key2]['url'] = base_url().'pages/article/'.$cateArticle[$key2]['caSysID'];
            }
            $menuData[$id]['children'][$key2]['cateID'] = $cateArticle[$key2]['caCateID'];
            $menuData[$id]['children'][$key2]['iconurl'] = base_url().$cateArticle[$key2]['caIcon'];
            $menuData[$id]['children'][$key2]['date'] = $cateArticle[$key2]['caDate'];
            $menuData[$id]['children'][$key2]['year'] = date('Y',strtotime($cateArticle[$key2]['caDate']));
            $menuData[$id]['children'][$key2] = $this->contentProcess($cateArticle[$key2],$menuData[$id]['children'][$key2],$lang);  

            if($menuData[$id]['children'][$key2]['keyfeature']){
                if(strlen(strip_tags($menuData[$id]['children'][$key2]['keyfeature']))>200){
                    $menuData[$id]['children'][$key2]['keyfeature'] = mb_substr(strip_tags($menuData[$id]['children'][$key2]['keyfeature']), 0,200).'...';
                }else{
                    $menuData[$id]['children'][$key2]['keyfeature'] = strip_tags($menuData[$id]['children'][$key2]['keyfeature']);
                }
            }else{
                if(strlen(strip_tags($cateArticle[$key2]['caContent'.$lang]))>200){
                    $menuData[$id]['children'][$key2]['desc'] = mb_substr(strip_tags($cateArticle[$key2]['caContent'.$lang]),0,200).'...';
                }else{
                    $menuData[$id]['children'][$key2]['desc'] = strip_tags($cateArticle[$key2]['caContent'.$lang]);
                }
            }
            if($cateArticle[$key2]['caSysID']==$nowID){
                $menuData[$id]['children'][$key2]['active'] = true;
            }

            //有額外設定文章參數
            if($cateArticle[$key2]['caMeta'.$lang]){
                $temp = explode(';', $cateArticle[$key2]['caMeta'.$lang]);
                foreach ($temp as $key2 => $value2) {
                    if(!$temp[$key2]){continue;}
                    $temp2 = explode('||', $temp[$key2]);
                    $menuData[$id]['children'][$key2]['meta'][$temp2[0]] = $temp2[1];
                }
            }else{
                $menuData[$id]['children'][$key2]['meta'] = null;
            }
        }
        return $menuData;
    }

    private function contentProcess($resData,$retData,$lang){
        $tagAry = array('keyfeature','specification','setup','download','faq','support');
        $str = str_replace('/ttiweb', '', $resData['caContent'.$lang]);
        if($resData['caType'.$lang]=="product"){
            //產品資訊
            foreach ($tagAry as $key => $tag) {
                $start = strpos(strtolower($str), '['.$tag.']');
                $end = strpos(strtolower($str), '[/'.$tag.']');
                $retData[$tag] = substr($str, $start,($end-$start));
                foreach ($tagAry as $key2 => $rpTag) {
                    $retData[$tag] = str_replace(array('['.$rpTag.']','[/'.$rpTag.']'), array('',''),$retData[$tag]);
                }
            } 
        }else{
            //一般頁面
            $retData['content'] = $str;
        }
        
        return $retData;  
    }

    private function getRelatedArticle($articleAry,$type){

        $data = $this->Api_common->getDataInCustom('eiName,eiSysID,eiStatus,eiSetting,eiImg','ec_item','eiSysID',explode(';', $articleAry),'none','in');

        $return = $this->getItemCardElements($data);
        return '<div class="container"><div class="row">'.$return['cardElement'].'</div></div>';
    }

   
}
?>