<?php
function getConfig($mid = '')
{
    if ($mid == '') {
        $mid = session('mid') ? session('mid') : input('mid');
    }
    if ($mid == '') {
        exit('参数不完整：公众号标识不存在');
    }
    $addon = \think\Db::name('couponpin_config')->where(['mpid' => $mid])->find();
    $addon['config'] = isset($addon['infos']) ? json_decode($addon['infos'], true) : [];
    return $addon['config'];

}

function execcurl($url,$ispost=false,$data='',$cookie=''){
    	$fn = curl_init();
    	curl_setopt($fn, CURLOPT_URL, $url);
    	curl_setopt($fn, CURLOPT_TIMEOUT, 60);
    	curl_setopt($fn, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($fn, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
		curl_setopt($fn, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
    	curl_setopt($fn, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    	curl_setopt($fn, CURLOPT_REFERER, $url);
    	curl_setopt($fn, CURLOPT_HEADER, 0);
    	if($cookie)
       		curl_setopt($fn,CURLOPT_COOKIE,$cookie);
    	if($ispost){
      		curl_setopt($fn, CURLOPT_POST, TRUE);
      		curl_setopt($fn, CURLOPT_POSTFIELDS, $data);   
    	}
    	$fm = curl_exec($fn);
		curl_close($fn);
    	
    	return $fm;
	}

function jpost($pdata,$cacheid,$url,$ctime = 3600){
		$jdata = execcurl($url,true,$pdata);
		$r['ctime'] = time();
		$r['data']= $jdata;
		Cache::set($cacheid,$r,$ctime);
		return $jdata;
	}