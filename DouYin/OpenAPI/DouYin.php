<?php
/**
 * GitHub:https://github.com/mengyilingjian/
 * Name: mengyilingjian
 * CreateTime:2023/8/28 16:36
 * IdeName:PhpStorm
 * FileName: 抖音商户php openapi sdk
 */
namespace DouYin\OpenAPI;

class DouYin{
    const Chip_Upload_Size = 10 * 1024 * 1024; // 分片上传大小设置为10M
    const Douyin_Upload_Part_Video_Total_Size = 4 * 1024 * 1024 * 1024; // 分片上传视频总大小 4G

    public static function __callStatic($name , $arguments)
    {
        $name = ucfirst(strtolower($name));
        $class = "\\DouYin\\OpenAPI\\Base\\{$name}";
        if (class_exists($class)) {
            $option = array_shift($arguments);
            $config =  $option;
            return new $class($config);
        }
        return "找不到对应的函数或类";
    }
}
