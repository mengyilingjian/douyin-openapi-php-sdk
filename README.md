# douyin-openapi-php-sdk
抖音开放平台的openapi
demo
```php
<?php

$cfg = [
    'client_key'=>$client_key,
    'client_secret'=>$client_secret,
    'access_token' => $access_token
];

$douyin_video = DouYin::Video($cfg);

/***
 * 上传视频，可根据配置的参数决定是否要分片
 * @param $videoId | 视频id,你自己数据库的视频id，用来区分日志输出
 * @param $MediaURL | 源视频,用来切割视频
 * @param $open_id
 */
$douyin_video->uploadVideo($videoId, $MediaURL, $open_id);
```
