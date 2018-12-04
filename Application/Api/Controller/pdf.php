<?php
/**
 * Created by PhpStorm.
 * User: morganzhao
 * Date: 16/2/26
 * Time: 下午1:33
 */


function createHead($country,$nationalFlag,$imageX,$imageY,$goldTotal,$silverTotal,$copperTotal){
    $this->setFont('Arial','B','24');//定义一个字体及样式
    $this->cell(40,20,$country,15);//显示各个国家的信息
    $this->image($nationalFlag,$imageX,$imageY);//国旗的位置
    $this->setX('70');//设置国旗的坐标
    $this->setTextColor(200,160,12);//设置金牌的颜色
    $this->cell(40,20,$goldTotal);//显示金牌数量
    $this->setX('100');//设置金牌数量的显示位置
    $this->setTextColor(170,162,138);//设置银牌的颜色
    $this->cell(40,20,$silverTotal);//显示银牌数量
    $this->setX('130');//设置银牌数量的显示位置
    $this->setTextColor(187,120,68);//设置铜牌的颜色
    $this->cell(40,20,$copperTotal);//显示银牌的位置
    $this->ln();//进行换行
    $this->setTextColor(0,0,0);//因为设置的颜色能在整个页面都有效，所以这里每次输入一个国家的信息就恢复初始的颜色
}