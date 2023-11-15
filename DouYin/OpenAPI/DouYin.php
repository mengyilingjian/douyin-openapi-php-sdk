<?php
/**
 * GitHub:https://github.com/mengyilingjian/
 * Name: mengyilingjian
 * CreateTime:2023/8/28 16:36
 * IdeName:PhpStorm
 * FileName: 抖音商户php openapi sdk
 */
namespace DouYin\OpenAPI;

/**
 * @method static Video(array $uploadConfig)
 */
class DouYin{
    const Chip_Upload_Size = 50 * 1024 * 1024; // 建议超过50MB，则分片上传
    const Chip_Size = 20 * 1024 * 1024; // 分片大小设置：单片分片20MB
    const Chip_Min_Size = 5 * 1024 * 1024; // 最小分片大小设置5M.抖音接口限制
    const Chip_Total_Video_Size = 4 * 1024 * 1024 * 1024; // 视频可分片大小上限，不超过4G
    const Max_Video_Size = 128 * 1024 * 1024; // 视频超过128M，必须分片
    const Chunk_Cache_Dir = './temp/'; // 分片缓存目录
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
