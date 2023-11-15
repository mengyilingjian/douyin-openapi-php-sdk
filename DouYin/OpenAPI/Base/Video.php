<?php
namespace DouYin\OpenAPI\Base;

use DouYin\OpenAPI\Base\Core\BaseApi;
use DouYin\OpenAPI\DouYin;
use Exception;
use Generator;
use think\db\exception\BindParamException;

class Video extends BaseApi
{
    //查询指定视频数据
    public function video_data($item_ids, $openid, $access_token)
    {
        $api = self::Douyin_Url . '/api/douyin/v1/video/video_data/';
        $params = [
            'open_id' => $openid,
            'access_token' => $this->access_token
        ];
        $api = $api . '?' . http_build_query($params);

        return $this->https_post($api, ["item_ids" => $item_ids]);
    }

    //查询授权账号视频列表
    public function video_list($openid, $access_token, $page = 0, $cursor = 10)
    {
        $params = [
            'open_id' => $openid,
            'access_token' => $this->access_token,
            'count' => $cursor,
            'cursor' => $page
        ];
        $url = self::Douyin_Url . '/api/douyin/v1/video/video_list/';
        return $this->https_get($url, $params);
    }

    //上传抖音视频
    public function video_upload($open_id, $file)
    {
        $url = self::Douyin_Url . '/api/douyin/v1/video/upload_video/?open_id=' . $open_id . '&access_token=' . $this->access_token;
        return $this->https_byte($url, $file);
    }

    //创建抖音视频
    public function video_create($open_id, $video_id, $text = '', $othes = [])
    {
        $url = self::Douyin_Url . '/api/douyin/v1/video/create_video/?open_id=' . $open_id . '&access_token=' . $this->access_token;
        $params = [
            'open_id' => $open_id,
            'access_token' => $this->access_token,
            'video_id' => $video_id,
            'text' => $text,
            'real_share' => !empty($othes['real_share']) ? $othes['real_share'] : '',
            'real_openid' => !empty($othes['real_openid']) ? $othes['real_openid'] : '',
        ];
        if (!empty($othes['real_openid'])) {
            $params['at_users'] = array(
                $othes['real_openid']
            );
        }
        if (empty($othes['aimingAt'])) {
            $params['poi_id'] = !empty($othes['poi_id']) ? $othes['poi_id'] : '';
            $params['poi_name'] = !empty($othes['poi_name']) ? $othes['poi_name'] : '';
            $params['poi_share'] = !empty($othes['poi_share']) ? $othes['poi_share'] : '';
        } else {
            $params['micro_app_id'] = !empty($othes['micro_app_id']) ? $othes['micro_app_id'] : '';
            $params['micro_app_title'] = !empty($othes['micro_app_title']) ? $othes['micro_app_title'] : '';
            $params['micro_app_url'] = !empty($othes['micro_app_url']) ? $othes['micro_app_url'] : '';
        }
        return $this->https_post($url, $params);
    }

    //初始化分片上传
    public function video_part_init($open_id)
    {
        $url = self::Douyin_Url . '/api/douyin/v1/video/init_video_part_upload/?open_id=' . $open_id;
        return $this->https_post($url, ['open_id' => $open_id]);
    }

    //上传视频分片到文件服务器
    public function video_part_upload($open_id, $upload_id, $part_number, $tempFilePath)
    {
        $params = [
            'open_id' => $open_id,
            'part_number' => $part_number,
            'upload_id' => $upload_id,
        ];
        $url = self::Douyin_Url . '/api/douyin/v1/video/upload_video_part/?' . http_build_query($params);
        return $this->https_byte($url, $tempFilePath);
    }

    /***
     * @param $videoUrl | 源视频地址
     * @param $open_id
     * @param $input
     * @param $output
     * @return int|mixed
     * @throws Exception
     */
    public function uploadVideo($videoId, $videoUrl, $open_id) {
        $videoByteSize = $this->getContentSizeByUrl($videoUrl); // 224499914
        if ($videoByteSize <= 0) {
            throw new Exception("文件大小获取异常", 400);
        }
        if ($videoByteSize > DouYin::Chip_Total_Video_Size) {
            throw new Exception("视频总大小超过4GB", 400);
        } elseif ($videoByteSize > DouYin::Chip_Upload_Size) {
            // 视频超过50M，强制分片上传
            // 初始化
            $chip_upload_init = $this->video_part_init($open_id);
            print_r("视频 {$videoId} 分片上传初始化..." . "\r\n");
            if ($chip_upload_init['data']['error_code'] !== 0) {
                // 请求接口初始化失败，跳出循环
                $msg = "分片上传初始化失败，原因：{$chip_upload_init['data']['description']}";
                throw new Exception($msg, 400);
            }

            $upload_id = $chip_upload_init['data']['upload_id'];
            $uploadGenerator = $this->uploadChunks($videoId, $videoByteSize, $videoUrl, $open_id, $upload_id);
            if (!$uploadGenerator->valid()) {
                throw new Exception("源视频{$videoId}分片失败,请联系管理员", 400);
            }
            foreach ($uploadGenerator as $part => $chip_upload_res) {
                // 处理每个分片上传的结果
                // 可以执行额外的操作，如记录日志、更新进度等
                print_r(json_encode(['code' => 0, 'message' => "视频 {$videoId} 第 {$part} 片上传结果", 'data' => $chip_upload_res], JSON_UNESCAPED_UNICODE) . "\r\n");
            }
            unset($chip_upload_init, $uploadGenerator);
            $data = $this->video_part_complete($open_id, $upload_id);
            print_r(json_encode(['code' => 0, 'message' => '分片上传完成', 'data' => $data], JSON_UNESCAPED_UNICODE) . "\r\n");
        } else {
            // 不符合分片要求,直接上传
            print_r("视频 {$videoId} 开始上传..." . "\r\n");
            $data = $this->video_upload($open_id, $videoUrl);
            print_r(json_encode(['code' => 0, 'message' => '视频上传完成', 'data' => $data], JSON_UNESCAPED_UNICODE));
        }
        return $data;
    }

    /***
     * 对视频进行切割分片
     * @param $videoId | 视频id,用来区分切片数据
     * @param $videoByteSize |视频大小
     * @param $MediaURL |源视频,用来切割视频
     * @param $open_id
     * @param $upload_id
     * @return Generator
     */
    public function uploadChunks($videoId, $videoByteSize, $MediaURL, $open_id, $upload_id): Generator
    {
        try {
            if ($videoByteSize <= 0) {
                $videoByteSize = $this->getContentSizeByUrl($MediaURL);
            }
            if ($videoByteSize < DouYin::Chip_Min_Size) {
                throw new Exception('视频切片字节不能小于5M', 400);
            }
            $fileCount = ceil($videoByteSize / DouYin::Chip_Size);
            $tempDir = DouYin::Chunk_Cache_Dir;
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            $chunkDataContainer = [];
            //  echo '当前视频切片数量为'. $fileCount . "\n";
            $videoByteData = file_get_contents($MediaURL);
            for ($i = 0; $i < $fileCount; $i++) {
                $part_number = $i + 1;
                $start = $i * DouYin::Chip_Size;
                $end = min(($i + 1) * DouYin::Chip_Size - 1, $videoByteSize - 1);
                $data = substr($videoByteData, $start, $end - $start + 1);
                // echo "第{$part_number}部分数据,i:{$i},大小为". strlen($data) . "\n";
                if ($i == $fileCount -1 && strlen($data) < DouYin::Chip_Min_Size) {
                    // 最后一片小于5M,则与上一片拼接,并替换到缓存容器中
                    $prvChunkData = $chunkDataContainer[$i - 1];
                    $chunkData = [
                        'data' => $prvChunkData['data'].$data,
                        'part' => $prvChunkData['part'],
                        'size' => strlen($prvChunkData['data'].$data)
                    ];
                    $chunkDataContainer[$i - 1] = $chunkData;
                    break;
                }
                $chunkData = [
                    'data' => $data,
                    'part' => $part_number,
                    'size' => strlen($data)
                ];
                $chunkDataContainer[] = $chunkData; // 将所有切片数据缓存
            }
            //  echo "开始进行分片上传, 分片数量为". count($chunkDataContainer);
            for ($j = 0; $j < count($chunkDataContainer); $j++) {
                $chunkData = $chunkDataContainer[$j];
            //  echo '开始切片上传第'. $chunkData['part'] . '部分,大小为' . $chunkData['size'] . "\n";
                $tempFilePath = $tempDir . "chunk_{$videoId}_part{$i}.mp4";
                file_put_contents($tempFilePath, $chunkData['data']); // 缓存分片的数据
                yield $this->video_part_upload($open_id, $upload_id, $chunkData['part'], $tempFilePath);
                unlink($tempFilePath); // 删除缓存文件
            }
            unset($chunkDataContainer, $videoByteData);
        } catch (Exception $e) {
            echo '视频切片报错了:'.$e->getMessage();
            return [];
        }
    }

    //分片完成上传
    public function video_part_complete($open_id, $upload_id)
    {
        $params = [
            'open_id' => $open_id,
            'upload_id' => $upload_id
        ];
        $url = self::Douyin_Url . '/api/douyin/v1/video/complete_video_part_upload/?' . http_build_query($params);
        return $this->https_post($url, $params);
    }

    //获取视频数据
    public function getUserDetailedData($openid,$date_type,$type){
        $api_path = self::Douyin_Url . '/data/external/user/';
        $api_url = '';
        switch ($type) {
            case 'like':
                $api_url = $api_path. 'like/';
                break;
            case 'comment':
                $api_url = $api_path. 'comment/';
                break;
            case 'profile':
                $api_url = $api_path. 'profile/';
                break;
            case 'share':
                $api_url = $api_path. 'share/';
                break;
            case 'fans':
                $api_url = $api_path. 'fans/';
                break;
            default:
                // code...
                break;
        }

        $params = [
            'access_token'  => $this->access_token,
            'open_id'       => $openid,
            'date_type'     => $date_type,
        ];
        return $this->https_get($api_url , $params);
    }

    public function getContentSizeByUrl($url): ?int
    {
        $url = parse_url($url);
        if ($fp = @fsockopen($url['host'], empty($url['port']) ? 80 : $url['port'], $error)) {
            fputs($fp, "GET " . (empty($url['path']) ? '/' : $url['path']) . " HTTP/1.1\r\n");
            fputs($fp, "Host:$url[host]\r\n\r\n");
            while (!feof($fp)) {
                $tmp = fgets($fp);
                if (trim($tmp) == '') {
                    break;
                } else if (preg_match('/Content-Length:(.*)/si', $tmp, $arr)) {
                    return trim($arr[1]);
                }
            }
            return 0;
        } else {
            return 0;
        }
    }
}
