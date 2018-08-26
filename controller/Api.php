<?php
namespace addons\couponPin\controller;
use think\facade\Cache;
/**
 * 拼多多查券机器人
 * qq:272984023
 * @author JYY
 */

class api{
	public function getConfig($mid = '')
	{
    	$addon = \think\Db::name('couponpin_config')->where(['mpid' => $mid])->find();
    	$addon['config'] = isset($addon['infos']) ? json_decode($addon['infos'], true) : [];
    	return $addon['config'];
	}
	public function message($msg = [], $param = [])
    {
       
		$mpset = $info = getAddonInfo('couponPin');
		
		require_once($mpset['path'].'Common.php');
		
		$set= getConfig(input('mid'));
		
        if(empty($set)) return replyText('未设置参数，无法查询！');
       
        if(($msg['MsgType'] != 'text') && ($msg['MsgType'] != 'event' )){
           	return replyText('对不起，系统支持文本内容查询。');
        }
        if($msg['MsgType'] == 'event')$msg['Content'] = $msg['EventKey'];   
        $content = $msg['Content'];	
		
		$find = array('[标题]','[在售价]','[优惠券]','[券后价]','[销量]','[文案]');
		$wxmb = '券后价：[在售价]元 [标题]';
		$d = array();
		preg_match("/(?<=goods_id=)\d+/", $content, $rr); 
		$gid = '';
		if(!empty($rr[0])){
			$pid = $set['pddpid'];
			$pidarr = explode('_',$pid);
			$gid = $rr[0];
			$owner = $set['owner'];
			$url = 'http://jyy.huiyuntk.cn/api/pddauth/url.html';
			$pdata = 'pid='.$pid.'&gid='.$gid.'&owner='.$owner;
			$cacheid = $pid.'jyy'.$gid.'jyyurl'.$owner;
			$result = Cache::get($cacheid);
				if(empty($result)){
					$jdata = jpost($pdata,$cacheid,$url,86400);
				}else{
					$jdata = $result['data'];
				}
			$jdata = json_decode($jdata,true);
			
			$kurl = $jdata['content']['goods_promotion_url_list'][0]['short_url'];
			$url = 'http://jyy.huiyuntk.cn/api/pddauth/detail.html';
			$pdata = 'pid='.$pid.'&gid='.$gid.'&owner='.$owner;
			$cacheid = $gid.'jyy';
			
			$result = Cache::get($cacheid);
				if(empty($result)){
					$jdata = jpost($pdata,$cacheid,$url,96400);
				}else{
					$jdata = $result['data'];
				}
			$jdata = json_decode($jdata,true);
			if($jdata['status'] >0 || empty($jdata['content']['goods_list'])) return replyText($kurl);
			$detail = $jdata['content']['goods_list'][0];
			if(!empty($detail)){
				$price = $detail['min_group_price']/100;
				$quan = $detail['coupon_discount']/100;
				$comm = $detail['promotion_rate']/10;
				$qprice = $price - $quan;
				$replace = array($detail['goods_name'],$price,$quan,$qprice,$detail['sold_quantity'],$detail['goods_desc']);
				$c = str_ireplace($find,$replace,$wxmb);
				if(strpos($wxmb,'[文案]') == false){
						$c.= PHP_EOL.$detail['goods_desc'];
				}
					return replyNews(array(
                        				array(
                            				'Title'       => $detail['goods_name'],
                            				'Description' => $c,
                            				'PicUrl'      => $detail['goods_image_url'],
                            				'Url'         => $kurl
                        					)
					));
			}else{
				return replyText("此商品暂无优惠券");
			}
		}
		$message = trim($content);
		$q = str_replace('amp;','',$message);
		$l=(mb_strlen($message,'utf8')+strlen($message))/2;
		if($l < 10){
			$pdata = 'psize=20&cid=全部&page=1&coupon=0&sort=&keyword='.$q;
			$cacheid = '20jyy全部jyy1jyyc1jyy'.$q;
		}else{
			$pdata = 'psize=20&cid=全部&page=1&coupon=1&sort=&keyword='.$q;
			$cacheid = '20jyy全部jyy1jyyc0jyy'.$q;
		}
		
		
		$url = 'http://jyys.huiyuntk.cn/api/index/ddjb.html';
		$result = Cache::get($cacheid);
		
				if(empty($result)){
					
					$jdata = jpost($pdata,$cacheid,$url,3600);
				}else{
					
					$jdata = $result['data'];
				}
		
		$data = json_decode($jdata,true);
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				if($data['status']!=0){
					return replyText("数据接口出错！");
				}else{
					if(empty($data['content']['goods_list'])) return replyText("此商品暂无优惠券");
					$total = $data['content']['total_count'];
					
					foreach($data['content']['goods_list'] as $k =>$v){
						if($k>=5) break;
						$price = ($v['min_group_price']-$v['coupon_discount'])/100;
						$replace = array($v['goods_name'],$v['min_group_price']/100,$v['coupon_discount']/100,$price,$v['sold_quantity'],'');
						$cc = str_ireplace($find,$replace,$wxmb);
						$comm = $v['promotion_rate']/10;
						$kurl = $http_type . $_SERVER['HTTP_HOST'].'/app/couponPin/index/view/mid/'.input('mid').'/gid/'.$v['goods_id'];
						$title = $cc;
						
						if($total == 1){
							return replyNews(array(
                        				array(
                            				'Title'       => $v['goods_name'],
                            				'Description' => $cc,
                            				'PicUrl'      => $v['goods_thumbnail_url'],
                            				'Url'         => $kurl
                        					)
								));
						}
						$detail=array(
                        	'Title'       => $title,
                        	'Description' => $cc,
                        	'PicUrl'      => $v['goods_thumbnail_url'],
                        	'Url'         => $kurl
						);
						
						if($gid == $v['goods_id']){
							return replyNews(array(
                        				array(
                            				'Title'       => $v['goods_name'],
                            				'Description' => $cc,
                            				'Picurl'      => $v['goods_thumbnail_url'],
                            				'Url'         => $kurl
                        					)
								));
						}
						$d[$k] = $detail;
						
					}
					$sourl = $http_type . $_SERVER['HTTP_HOST'].'/app/couponPin/index/index/mid/'.input('mid');
					$d[] = array(
							'Title'       => '点此查看【更多相关“'.$q.'”的优惠商品】',
                			'Description' => $cc,
                			'PicUrl'      => '',
                			'Url'         => $sourl
						);
					return replyNews($d);
				}
		
       
    }
	
	
	
}

?>