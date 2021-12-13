<?php

class Api extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_data');
        $this->Api_common->chkBlockIP();
        $this->Api_common->initLang();
        define('LANG',$this->Api_common->getCookie('lang'));
        $this->load->model('Lang');
        $this->Api_common->browserLog($user_detail,$nowPage);
    }

    // 主畫面
    function index(){
        exit;
    }

    function test22(){
        $url = 'https://www.instagram.com/explore/tags/%E5%A6%96%E7%B2%BE%E5%BA%B7%E6%99%AE%E8%8C%B6?__a=1';

        $load = json_decode($this->Api_common->cache('load','igCache',null,null),true)['data'];
        if($load){
            echo $this->Api_common->setFrontReturnMsg('200','',$load);exit;
        }
        
        $result = $this->getCurl($url);
        $resData = json_decode($result,true);
        //$this->Api_common->dataDump($resData);
        //echo $this->Api_common->setFrontReturnMsg('200','',$resData);
        //exit;
        $num = 0;
        $setRow = 1;
        //$this->Api_common->dataDump($resData['data']['top']['sections']);
        $igType = 'recent'; //top recent
        foreach ($resData['data'][$igType]['sections'] as $key => $value) {
            //$imguri = base64_encode($this->Api_common->getCurl($value['node']['display_url'],$postData,$header,$ckfile=null));
            //$img = $this->getImg($value['node']['display_url']);
            $str = '';
            //$this->Api_common->dataDump($value);
            foreach ($value['layout_content']['medias'] as $key2 => $value2) {                
                if($num%$setRow==0){
                    $row = 0;
                }else if($num%$setRow==1&&$setRow>=2){
                    $row = 1;
                }else if($num%$setRow==2&&$setRow>=3){
                    $row = 2;
                }else if($num%$setRow==3&&$setRow>=4){
                    $row = 3;
                }else if($num%$setRow==4&&$setRow>=5){
                    $row = 4;
                }else if($num%$setRow==5&&$setRow>=6){
                    $row = 5;
                }else if($num%$setRow==6&&$setRow>=7){
                    $row = 6;
                }
                //user
                if($value2['media']['caption']['user']['full_name']=='______D'){
                    //continue;
                }
                if($value2['media']['caption']['user']['full_name']=='日研專科'){
                    continue;
                }
                
                $retData[$row][$num]['full_name'] = $value2['media']['caption']['user']['full_name'];
                $retData[$row][$num]['text'] = str_replace("\n", "<br>", $value2['media']['caption']['text']);
                if($value2['media']['carousel_media'][0]['image_versions2']['candidates'][6]['height']>300){
                    $retData[$row][$num]['height'] = 300;
                }else{
                    $retData[$row][$num]['height'] = $value2['media']['carousel_media'][0]['image_versions2']['candidates'][6]['height'];
                }                
                $retData[$row][$num]['width'] = $value2['media']['carousel_media'][0]['image_versions2']['candidates'][6]['width'];
                //$retData[$row][$num]['profile_pic_url'] = 'https://localhost:8888/api/getImg/'.$this->Api_common->stringHash('encrypt',$value2['media']['caption']['user']['profile_pic_url']);
                //$retData[$row][$num]['img'] = 'https://localhost:8888/api/getImg/'.$this->Api_common->stringHash('encrypt',$value2['media']['carousel_media'][0]['image_versions2']['candidates'][4]['url']);
                $retData[$row][$num]['user_pic'] = $this->getImg($value2['media']['caption']['user']['profile_pic_url']);
                $retData[$row][$num]['img'] = $this->getImg($value2['media']['carousel_media'][0]['image_versions2']['candidates'][6]['url']);
                //echo $row.'---'.$num.'---'.$value2['media']['caption']['user']['full_name'].$value2['media']['caption']['text'].'<br>';
                $num++;
            }
        }
        //echo $num.'<hr>';
        $this->Api_common->cache('save','igCache',$retData);
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
    }

    function getImg2($hash){        
        $url = $this->Api_common->stringHash('decrypt',$hash);
        if(!$url){echo 'url';exit;}
        $pic = file_get_contents($url);
        if(!$pic){echo 'pic';exit;}
        $imguri = base64_encode($pic);
        //return '<img height="100" src="data:image/jpg;base64,'.$imguri.'" alt="img" style="image-rendering:-moz-crisp-edges;    image-rendering:-o-crisp-edges;image-rendering:-webkit-optimize-contrast;image-rendering: crisp-edges;-ms-interpolation-mode:nearest-neighbor;" />';
        return 'data:image/jpg;base64,'.$imguri.'';
    }

    function getImg($url){
        //return;
        /*
        $url = $this->Api_common->stringHash('decrypt',$hash);
        if(!$url){exit;}*/
        $pic = file_get_contents($url);
        $imguri = base64_encode($pic);
        //return '<img height="100" src="data:image/jpg;base64,'.$imguri.'" alt="img" style="image-rendering:-moz-crisp-edges;    image-rendering:-o-crisp-edges;image-rendering:-webkit-optimize-contrast;image-rendering: crisp-edges;-ms-interpolation-mode:nearest-neighbor;" />';
        return 'data:image/jpg;base64,'.$imguri.'';
    }

    function test3($hash=null){
        $url = $this->Api_common->stringHash('decrypt',$hash);
        echo $url.'<br>';
        echo $this->Api_common->stringHash('encrypt','https://instagram.ftpe7-4.fna.fbcdn.net/v/t51.2885-15/e35/p480x480/239380115_1915801275256597_54025231482952615_n.jpg?_nc_ht=instagram.ftpe7-4.fna.fbcdn.net&_nc_cat=101&_nc_ohc=FkTWZNhGRSAAX9SOkF8&tn=s44kR8gy-SQRIwQA&edm=ABZsPhsBAAAA&ccb=7-4&oh=eb3f3c2baffbb036774654a1b8c55a09&oe=612D828C&_nc_sid=4efc9f&ig_cache_key=MjY0MjAyNzU2OTYxNDc4MTM3Mw%3D%3D.2-ccb7-4');
        
    }

    function getCurl($url){
        $ckfile = DIR_SITE_FILE.'IGCOOKIE.txt';
        $header[0] = 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36';
        $header[1] = 'cookie: ig_did=14AC045A-8FF3-4F8E-B528-6E3F8905F78B; mid=X8SyFgALAAGdT99WTAvdlsO-2yqa; ig_nrcb=1; fbm_124024574287414=base_domain=.instagram.com; csrftoken=sSIP8dX8EpW6BA75bmBE0nm7drJ93gxy; ds_user_id=49151856322; sessionid=49151856322%3APMfIpqvOnoXjUx%3A23; fbsr_124024574287414=XX14BGefH9Gdp_mV4VdKo0W_2YGyp_lGvH0aaCt25q8.eyJ1c2VyX2lkIjoiMTAwMDAwMjMwOTkzMDc0IiwiY29kZSI6IkFRQzU4U3UxMkhHV1o0LWw3TE9XWXBKclZwdDYzQVZlMzllS01jRW1HdXpBa3VuVHpqNUc3bHZqUVFYNEtrdW5XaS0tamxXTUVudV9lY3l6UVJ3ajhfR1VGRWV6Q2lINUc4Yk81V2NwdFVwYVlQSTZVcXJzbVZCTkR2Y0dQa2ZsQjVjaEJEbnotRUZFLXE1Y3RkaDhubjNvX2ZfTHZYdFhFZl9KbE40Y1prX0toZllyakxlR2gxTjBKc3lnSGRPVE80cFo0UmxEYldPMC1pZmo4TDN2bDZNbEQyQmVRcFVJN3lDaWRJdjZnZjVLUjl0OWJiaUFXdUwwSXJmSk42QnIzNEh6ZEZoQUkxV3hXWFJnb25nRVgtcjl6elJjT0pMSG52bFoyNS1DMUN1NkE4QU1temN0dmRaUGNWU2dKd090UzVoRTYyYTk2T2Q4d0Q2U2dmZW9HaEw4Iiwib2F1dGhfdG9rZW4iOiJFQUFCd3pMaXhuallCQUtjbVpBVGhzQXpaQmhoNHNNQjFZdnkzczN2MEZUR2lDNFpCME43R2RtWkJaQ0dnbnM0dHk4bnlWTktmODcwdnhXYzNwc2k5TWNObllOZGE4MG42MUI0VlZzd3hWUjdLNGtUYXlBWkNxV0hvS2JJRmxNZndsc1pBOVpDMmNyc29zMUR6c3k1aDFUWTRidWFYcFhEWkNnaXVqa1pBYnVDeEs1cm1iWEpZQ0RvZFB6bnhwbFVzS3pvYmNaRCIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNjI5ODczMDIyfQ; rur="PRN\05449151856322\0541661409023:01f76c0dd7401579af9c0be40a16a74c6e008e65ec30b6424b06764d11d615eb0e804be9"';
        return $this->Api_common->getCurl($url,$postData,$header,$ckfile);
    }

}
