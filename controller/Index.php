<?php
// +----------------------------------------------------------------------
// | [RhaPHP System] Copyright (c) 2017-2020 http://www.rhaphp.com/
// +----------------------------------------------------------------------
// | [RhaPHP] 并不是自由软件,你可免费使用,未经许可不能去掉RhaPHP相关版权
// +----------------------------------------------------------------------
// | Author: Geeson <qimengkeji@vip.qq.com>
// +----------------------------------------------------------------------
namespace addons\couponPin\controller;
use app\common\controller\Addon;
use think\Db;
use think\facade\Request;
use think\facade\Cache;
/**
 * 拼多多查券机器人
 * qq:272984023
 * @author JYY
 */
class Index extends Addon
{

    //public $onlyWexinOpen =false;
    //public $isWexinLogin =true;
    public $member;
	public $config;
    public function initialize()
    {

        parent::initialize(); // TODO: Change the autogenerated stub
        $this->member=getMember();
        $this->assign('app',$this->getAaddonConfigByMp);
		$this->config = getConfig($this->mid);
    }
	public function wxopen(){
		if(!empty($this->config['wxopen'])){
			if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') == false) {
                    $wxObj = getWechatActiveObj($this->mid);
                    $url = $wxObj->getOauthRedirect(getHostDomain(), 'state', $this->scope);
                    $this->redirect($url);
            }
		}
	}


    public function index(){
		$this->wxopen();
		$jyy = (empty(input('type')) && input('type') != 0)?3:input('type');
		if(empty($this->config)) exit('请先进行参数设置！');
		$owner = $this->config['owner'];
		$pid = $this->config['pddpid'];
		$urll = 'http://jyy.huiyuntk.cn/api/pddauth/cmsurl.html';
		$pdata = 'pid='.$pid.'&owner='.$owner.'&akey=jyy2018&type='.$jyy;
		$cacheid = $pid.'jyy'.$jyy.'jyy'.$owner;
		$result = Cache::get($cacheid); 
		if(empty($result)){
			$jdata = jpost($pdata,$cacheid,$urll,40000);
		}else{
			$jdata = $result['data'];
		}
		$urls = json_decode($jdata,true);
		//print_r($urls);
		if($urls['status']==0 && !empty($urls['content']['url_list'][0]['mobile_short_url'])) $url = $urls['content']['url_list'][0]['mobile_short_url'];
		$this->redirect($url);
		//echo addonUrl('getpid');
    }
	
	public function view(){
		$this->wxopen();
		$gid = input('gid');
		$owner = $this->config['owner'];
		$pid = $this->config['pddpid'];
		$urll = 'http://jyy.huiyuntk.cn/api/pddauth/url.html';
		$pdata = 'pid='.$pid.'&gid='.$gid.'&owner='.$owner;
		$cacheid = $pid.'jyy'.$gid.'jyyurl'.$owner;
		$result = Cache::get($cacheid);
		if(empty($result)){
			$jdata = jpost($pdata,$cacheid,$urll);
		}else{
			$jdata = $result['data'];
				
		}
		$urls = json_decode($jdata,true);
		if($urls['status']==0 && !empty($urls['content']['goods_promotion_url_list'][0]['short_url'])){
			$url = $urls['content']['goods_promotion_url_list'][0]['short_url'];
			$this->redirect($url);
		}else{
			echo '授权错误，请重新授权!';
		} 
	}

}