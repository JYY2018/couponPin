<?php
return array(
    'name' => '拼多多查券机器人',
    'addon' => 'couponPin',
    'desc' => '拼多多优惠券查询系统，拼客',
    'version' => '1.0.1',
    'author' => 'JYY',
    'logo' => 'logo.png',
    'entry_url' => 'couponPin/index/index',
    'install_sql' => 'install.sql',
    'upgrade_sql' => '',
    'menu' => [
        [
            'name' => '参数设置',
            'url' => 'couponPin/coupon/config',
            'icon' => ''
        ],
    ],
    


);