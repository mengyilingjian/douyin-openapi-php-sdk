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

$douyin_video->video_upload($open_id, $VideoURL);
```
