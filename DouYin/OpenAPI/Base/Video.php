<?php
namespace DouYin\OpenAPI\Base;

use DouYin\OpenAPI\Base\Core\BaseApi;

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

        return $this->https_post($api, ["item_ids" => $item_ids], true);
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
    public function video_part_upload($open_id, $upload_id, $part_number, $video)
    {
        $params = [
            'open_id' => $open_id,
            'access_token' => $this->access_token,
            'upload_id' => $upload_id,
            'part_number' => $part_number,
        ];
        $url = self::Douyin_Url . '/api/douyin/v1/video/upload_video_part/?' . http_build_query($params);

        return $this->https_byte($url, $video);
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
}
