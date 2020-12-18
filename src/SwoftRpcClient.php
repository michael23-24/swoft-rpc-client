<?php
// +----------------------------------------------------------------------
// | 第三方应用请求swoft的rpc客户端
// +----------------------------------------------------------------------
// | Copyright (c) 义幻科技 http://www.mobimedical.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Michael23
// +----------------------------------------------------------------------
// | date: 2020-12-15
// +----------------------------------------------------------------------
namespace SwoftRpcClient;


class SwoftRpcClient
{
    use DataFormate;
    /**
     * rpc的host地址
     * @var null
     */
    protected $host = null;

    /**
     * rpc的端口
     * @var null
     */
    protected $port = null;

    /**
     * rpc的应用id
     * @var null
     */
    protected $appId = null;

    /**
     * rpc的服务版本
     * @var null
     */
    protected $version = null;

    const RPC_EOL = "\r\n\r\n";

    public function __construct($host, $port, $appId, $version = '1.0')
    {
        if (empty($host) || empty($port) || empty($appId) || empty($version)) {
            throw new \Exception("参数不合法");
        }
        $this->host    = $host;
        $this->port    = $port;
        $this->appId   = $appId;
        $this->version = $version;
    }

    /**
     * 请求rpc服务
     * @param $service 服务名称
     * @param $method 请求方法
     * @param $params 请求参数
     * @param null $version 服务版本
     * @param array $ext 扩展参数
     * @return array 格式为code,message,data,ext;code==1表示无错误，其他表示有错误
     */
    public function request($service, $method, $params, $version = null, $ext = [])
    {
        $version || $version = $this->version;
        $ext['appId'] = $this->appId;

        $address = "tcp://{$this->host}:{$this->port}";
        $fp      = stream_socket_client($address, $errno, $errstr);
        if (!$fp) {
            return $this->formateData(0, "stream_socket_client fail errno={$errno} errstr={$errstr}");
        }

        $service = "{$service}Interface";
        $class   = "App\Rpc\Lib\\{$service}";
        $req     = [
            "jsonrpc" => '2.0',
            "method"  => sprintf("%s::%s::%s", $version, $class, $method),
            'params'  => $params,
            'id'      => '',
            'ext'     => $ext,
        ];
        $data    = json_encode($req) . self::RPC_EOL;
        fwrite($fp, $data);

        $result = '';
        while (!feof($fp)) {
            $tmp = stream_socket_recvfrom($fp, 1024);

            if ($pos = strpos($tmp, self::RPC_EOL)) {
                $result .= substr($tmp, 0, $pos);
                break;
            } else {
                $result .= $tmp;
            }
        }

        fclose($fp);
        $result = json_decode($result, true);
        if (isset($result['error'])) {
            return $this->formateData($result['error']['code'], $result['error']['message']);
        }
        return $this->formateData($result['result']['code'], $result['result']['message'], $result['result']['data'], $result['result']['ext']);
    }
}

