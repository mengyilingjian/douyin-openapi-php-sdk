<?php
namespace DouYin\OpenAPI\Base;

use DouYin\OpenAPI\Base\Core\BaseApi;

class Init extends BaseApi
{
    // 抖音获取授权码 https://developer.open-douyin.com/docs/resource/zh-CN/dop/develop/openapi/account-permission/douyin-get-permission-code
    public function connect($scope, $redirect_url, $state = "")
    {

        $api_url = self::Douyin_Url . '/platform/oauth/connect/';
        $params = [
            'client_key' => $this->client_key,
            'response_type' => 'code',
            'scope' => implode(',', $scope),
            'redirect_uri' => $redirect_url,
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return $api_url . '?' . http_build_query($params);
    }
    // 获取 access_token https://developer.open-douyin.com/docs/resource/zh-CN/dop/develop/openapi/account-permission/get-access-token
    public function access_token($code)
    {
        $api_url = self::Douyin_Url . '/oauth/access_token/';
        $params = [
            'client_key' => $this->client_key,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];

        return $this->https_get($api_url, $params);

    }

    // 刷新 access_token https://developer.open-douyin.com/docs/resource/zh-CN/dop/develop/openapi/account-permission/refresh-access-token
    public function refresh_token($refresh_token)
    {
        $api_url = self::Douyin_Url . '/oauth/refresh_token/';
        $params = [
            'client_key' => $this->client_key,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ];
        return $this->https_get($api_url, $params);
    }

    // 刷新 refresh_token：https://developer.open-douyin.com/docs/resource/zh-CN/dop/develop/openapi/account-permission/refresh-token
    public function renew_refresh_token($refresh_token)
    {
        $api_url = self::Douyin_Url . '/oauth/renew_refresh_token/';
        $params = [
            'client_key' => $this->client_key,
            'refresh_token' => $refresh_token
        ];
        return $this->https_get($api_url, $params);
    }

    // 生成client_token https://open.douyin.com/platform/doc/6848806493387573256
    public function client_token()
    {
        $api_url = self::Douyin_Url . '/oauth/client_token/';
        $params = [
            'client_key' => $this->client_key,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credential'
        ];
        return $this->https_get($api_url, $params);
    }
}
