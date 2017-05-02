<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {
    public function index(){
        //获得参数 signature nonce token timestamp echostr
        $nonce     = $_GET['nonce'];
        $token     = 'jh2k15';
        $timestamp = $_GET['timestamp'];
        $echostr   = $_GET['echostr'];
        $signature = $_GET['signature'];
        //形成数组，然后按字典序排序
        $array = array();
        $array = array($nonce, $timestamp, $token);
        sort($array);
        //拼接成字符串,sha1加密 ，然后与signature进行校验
        $str = sha1( implode( $array ) );
        if( $str  == $signature && $echostr ){
            //第一次接入weixin api接口的时候
            echo  $echostr;
            exit;
        }else{
            $this->reponseMsg();
        }
    }//index end
    public function reponseMsg(){
        //1.获取到微信推送过来post数据（xml格式）
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
        //2.处理消息类型，并设置回复类型和内容
/*<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[FromUser]]></FromUserName>
<CreateTime>123456789</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[subscribe]]></Event>
</xml>*/
        $postObj = simplexml_load_string( $postArr );
        //$postObj->ToUserName = '';
        //$postObj->FromUserName = '';
        //$postObj->CreateTime = '';
        //$postObj->MsgType = '';
        //$postObj->Event = '';
        // gh_e79a177814ed
        //判断该数据包是否是订阅的事件推送
        if( strtolower( $postObj->MsgType) == 'event'){
            //如果是关注 subscribe 事件
            if( strtolower($postObj->Event == 'subscribe') ){
                //回复用户消息(纯文本格式)
                $content  = "欢迎关注jh2k15订阅号\n回复\n【1】我的个人主页\n【2】我的博客网址";
                $indexModel=new IndexModel();
                $indexModel->responseText($postObj,$content);
/*<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>12345678</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[你好]]></Content>
</xml>*/
            }
            if(strtolower($postObj->Event)=='click'){
                if(strtolower($postObj->EventKey)=='item1'){
                    $content="哈哈";
                }
                if(strtolower($postObj->EventKey)=='item2'){
                    $content="呵呵";
                }
                $indexModel=new IndexModel();
                $indexModel->responseText($postObj,$content);
            }
            if(strtolower($postObj->Event)=='view'){
                $content="sdfasdfsdf".$postObj->EventKey;
                $indexModel=new IndexModel();
                $indexModel->responseText($postObj,$content);
            }
        }

        if(strtolower($postObj->MsgType) == 'text'){
            switch( trim($postObj->Content) ){
                case 1:
                    $content = '<a href="http://www.jh2k15.online">www.jh2k15.online</a>';
                break;
                case 2:
                    $content = '<a href="http://dede.jh2k15.online">dede.jh2k15.online</a>';
                break;
                case "百度":
                    $content = '<a href="http://www.baidu.com">baidu</a>';
                break;
                case '佳豪':
                    $content = ':)你怎么知道我的名字';
                default:
                    $content="你话真多，不跟你聊了";
            }
            $indexModel=new IndexModel();
            $indexModel->responseText($postObj,$content);
        }
    }//reponseMsg end
    /**
     * [http_curl description]
     * @param  [type] $url  接口url
     * @param  string $type 请求类型
     * @param  string $res  返回数据类型
     * @param  string $arr  post请求参数
     * @return [type]       [description]
     */
    function http_curl($url,$type='get',$res='json',$arr=''){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($type=='post'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        $output=curl_exec($ch);
        curl_close($ch);
        if($res=='json'){
            if(curl_errno($ch)){
                return curl_error($ch);
            }else{
                return json_decode($output,true);
            }
        }
    }
    public function getWxAccessToken(){
        if($_SESSION['access_token'] && $_SESSION['expire_time']>time()){
            return $_SESSION['access_token'];
        }else{
            $appid='wx365ea7444c5c40af';
            $appsecret='0a24a383974a9ee95733061a4984a268';
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $res=$this->http_curl($url,'get','json');
            $access_token=$res['access_token'];
            $_SESSION['access_token']=$access_token;
            $_SESSION['expire_time']=time()+7000;
            return $access_token;
        }

    }
    //自定义菜单
    public function definedItem(){
        header('content-type:text/html;charset=utf-8');
        echo $access_token=$this->getWxAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $postArr=array(
                'button'=>array(
                    array(
                        'name'=>urlencode('主页'),
                        'type'=>'view',
                        'url'=>'http://www.jh2k15.online',
                        ),
                    array(
                        'name'=>urlencode('博客'),
                        'type'=>'view',
                        'url'=>'http://dede.jh2k15.online',
                        ),
                    array(
                        'name'=>urlencode('其他'),
                        'sub_button'=>array(
                                array(
                                    'name'=>urlencode('哈哈'),
                                    'type'=>'click',
                                    'key'=>'item1',
                                    ),
                                array(
                                    'name'=>urlencode('呵呵'),
                                    'type'=>'click',
                                    'key'=>'item2',
                                    ),
                            ),
                        ),
                ),
        );
        echo "<hr/>";
        echo $postJson=urldecode(json_encode($postArr));
        $res=$this->http_curl($url,'post','json',$postJson);
        echo "<hr/>";
        var_dump($res);
    }
    //群发接口
    function sendMsgAll(){
        header('content-type:text/html;charset=utf-8');
        echo $access_token=$this->getWxAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=".$access_token;
/*{
   "touser":"OPENID",
   "mpnews":{
            "media_id":"123dsdajkasd231jhksad"
             },
   "msgtype":"mpnews"
}*/
        $array=array(
                    'touser'=>'oClZGwEAnIE24Tu1cAco4_AAf8LA',
                    'mpnews'=>array(
                        'media_id'=>'123dsdajkasd231jhksad'
                        ),
                    'msgtype'=>'mpnews',
                    );
/*{
"touser":"OPENID",
"text":{
       "content":"CONTENT"
       },
"msgtype":"text"
}*/
//单文本
        /*$array=array(
            'touser'=>'oClZGwEAnIE24Tu1cAco4_AAf8LA',
            'text'=>array(
                'content'=>urlencode('呵呵')
                ),
            'msgtype'=>'text',
            );*/
        echo "<hr/>";
        $postJson=urldecode(json_encode($array));
        var_dump($postJson);
        echo "<hr/>";
        $res=$this->http_curl($url,'post','json',$postJson);
        var_dump($res);
    }
    function sendTemplateMsg(){
        header('content-type:text/html;charset=utf-8');
        echo $access_token=$this->getWxAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;

/*{
    "touser":"OPENID",
    "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
    "url":"http://weixin.qq.com/download",
    "miniprogram":{
     "appid":"xiaochengxuappid12345",
     "pagepath":"index?foo=bar"
    },
    "data":{
           "first": {
               "value":"恭喜你购买成功！",
               "color":"#173177"
           },
           "keynote1":{
               "value":"巧克力",
               "color":"#173177"
           },
           "keynote2": {
               "value":"39.8元",
               "color":"#173177"
           },
           "keynote3": {
               "value":"2014年9月22日",
               "color":"#173177"
           },
           "remark":{
               "value":"欢迎再次购买！",
               "color":"#173177"
           }
    }
}*/
    $array=array(
        "touser"=>"oClZGwEAnIE24Tu1cAco4_AAf8LA",
        "template_id"=>"OaJ8vo0WH-KDakdRgDko2mtnpOHbD99iniVFNub5yXI",
        "url"=>"http://www.jh2k15.online",
        "data"=>array(
                "name"=>array(
                        "value"=>"jh",
                        "color"=>"#173177"
                    ),
                "money"=>array(
                        "value"=>"1000",
                        "color"=>"#173177"
                    ),
                "date"=>array(
                        "value"=>date('Y-m-d H:i:s'),
                        "color"=>"#173177"
                    ),
            ),
        );
    $postJson=urldecode(json_encode($array));
    $res=$this->http_curl($url,'post','json',$postJson);
    var_dump($res);
    }//sendTemplateMsg end
    function getBaseInfo(){
        $appid='wx365ea7444c5c40af';
        $redirect_uri=urlencode('http://www.jh2k15.online/wechat.php/Index/getUserOpenId');
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
        header('location:'.$url);
    }
    function getUserOpenId(){
        $appid='wx365ea7444c5c40af';
        $appsecret='0a24a383974a9ee95733061a4984a268';
        $code=$_GET['code'];
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
        $res=$this->http_curl($url,'get');
        var_dump($res);
    }
    function getUserDetail(){
        $appid='wx365ea7444c5c40af';
        $redirect_uri=urlencode('http://www.jh2k15.online/wechat.php/Index/getUserInfo');
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=12345#wechat_redirect";
        header('location:'.$url);
    }
    function getUserInfo(){
        $appid='wx365ea7444c5c40af';
        $appsecret='0a24a383974a9ee95733061a4984a268';
        $code=$_GET['code'];
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
        $res=$this->http_curl($url,'get');
        var_dump($res);
        $access_token=$res['access_token'];
        $openid=$res['openid'];
        $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res=$this->http_curl($url);
        var_dump($res);
    }
    function getJsApiTicket(){
        if($_SESSION['jsapi_ticket_expire_time']>time() && $_SESSION['jsapi_ticket']){
            $jsapi_ticket=$_SESSION['jsapi_ticket'];
        }else{
            $access_token=$this->getWxAccessToken();
            $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
            $res=$this->http_curl();
            $jsapi_ticket=$res['ticket'];
            $_SESSION['jsapi_ticket']=$jsapi_ticket;
            $_SESSION['jsapi_ticket_expire_time']=time()+7000;
        }
        return $jsapi_ticket;
    }
    function getRandCode($num=16){
        $array=array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
            '0','1','2','3','4','5','6','7','8','9'
            );
        $tmpstr="";
        $max=count($array);
        for($i=1;$i<=$num;$i++){
            $key=rand(0,$max-1);
            $tmpstr.=$array[$key];
        }
        return $tmpstr;
    }
    function shareWx(){
        $jsapi_ticket=$this->getJsApiTicket();
        $timestamp=time();
        $noncestr=$this->getRandCode();
        $url="http://www.jh2k15.online/wechat.php/Index/shareWx";
        $signature="jsapi_ticket=".$jsapi_ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
        $signature=sha1($signature);
        $this->assign('name',"哈哈发的说法");
        $this->assign('timestamp',$timestamp);
        $this->assign('noncestr',$noncestr);
        $this->assign('signature',$signature);
        $this->display('share');
    }
}//class end
