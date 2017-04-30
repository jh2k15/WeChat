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
                    $content="sdfasdfsdf";
                }
                if(strtolower($postObj->EventKey)=='item2'){
                    $content="sdfasdfsdf";
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
    public function definedItem(){
        header('content-type:text/html;charset=utf-8');
        echo $access_token=$this->getWxAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $postArr=array(
                'button'=>array(
                    array(
                        'name'=>urlencode('哈哈'),
                        'type'=>'click',
                        'key'=>'item1',
                        ),
                    array(
                        'name'=>urlencode('haha'),
                        'sub_button'=>array(
                                array(
                                    'name'=>urlencode('haha'),
                                    'type'=>'click',
                                    'key'=>'item2',
                                    ),
                                array(
                                    'name'=>urlencode('haha'),
                                    'type'=>'view',
                                    'url'=>'http://www.jh2k15.online',
                                    ),
                            ),
                        ),
                    array(
                        'name'=>urlencode('haha'),
                        'type'=>'view',
                        'url'=>'http://dede.jh2k15.online',
                        ),
                ),
        );
        echo "<hr/>";
        echo $postJson=urldecode(json_encode($postArr));
        $res=$this->http_curl($url,'post','json',$postJson);
        echo "<hr/>";
        var_dump($res);
    }

}//class end
