<?php

namespace library\mysmarty;

/**
 * 采集优化类
 */
class Query
{

    private $url = [];

    private $outputHeader = 0;

    private $followLocation = 1;

    private $returnTransfer = 1;

    private $timeOut = 20;

    private $userAgent = '';

    private $cookieFile = '';

    private $postFields = [];

    private $header = [];

    private $ip = '';

    private $referer = '';

    private $verifypeer = FALSE;

    private $sleepTime = 0;

    private $srcCharset = '';

    private $matchReg = [];

    private $proxyIp = '';
    private $proxyType = 0;

    /**
     * 初始化变量
     */
    private function init()
    {
        $this->url = [];
        $this->outputHeader = 0;
        $this->followLocation = 1;
        $this->returnTransfer = 1;
        $this->timeOut = 20;
        $this->userAgent = '';
        $this->cookieFile = '';
        $this->postFields = [];
        $this->header = [];
        $this->ip = '';
        $this->referer = '';
        $this->verifypeer = false;
        $this->sleepTime = 0;
        $this->srcCharset = '';
        $this->proxyIp = '';
        $this->proxyType = 0;
    }

    private function initMatchReg()
    {
        $this->matchReg = [];
    }

    private static $obj;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$obj)) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 设置请求url
     *
     * @param string $url
     *            一个网址
     * @return Query
     */
    public function setUrl($url)
    {
        $this->url = [
            $url
        ];
        return $this;
    }

    /**
     * 设置多个url
     *
     * @param string|array $urls
     *            逗号分隔的url或数组
     * @return Query
     */
    public function setUrls($urls)
    {
        if (!is_array($urls)) {
            $urls = explode(',', $urls);
        }
        $this->url = $urls;
        return $this;
    }

    /**
     * 启用时会将头文件的信息作为数据流输出
     *
     * @param int $outputHeader
     *            0 不输出 ，1 输出
     * @return Query
     */
    public function setOutputHeader($outputHeader)
    {
        $this->outputHeader = $outputHeader;
        return $this;
    }

    /**
     * TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。（注意：这是递归的，"Location: " 发送几次就重定向几次，除非设置了 CURLOPT_MAXREDIRS，限制最大重定向次数。）。
     *
     * @param int $followLocation
     *            0 不重定向，1 重定向
     * @return Query
     */
    public function setFollowLocation($followLocation)
    {
        $this->followLocation = $followLocation;
        return $this;
    }

    /**
     * 返回原生的（Raw）内容
     *
     * @param int $returnTransfer
     *            0 不返回，1 返回
     * @return Query
     */
    public function setReturnTransfer($returnTransfer)
    {
        $this->returnTransfer = $returnTransfer;
        return $this;
    }

    /**
     * 允许 cURL 函数执行的最长秒数
     *
     * @param int $timeOut
     *            多少秒
     * @return Query
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
        return $this;
    }

    /**
     * 设置原网页网页编码
     *
     * @param string $srcCharset
     */
    public function setSrcCharset($srcCharset)
    {
        $this->srcCharset = $srcCharset;
    }

    /**
     * 在HTTP请求中包含一个"Category-Agent: "头的字符串。
     *
     * @param string|integer $userAgent
     *            浏览器标识。
     *
     * @return Query
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * 包含 cookie 数据的文件名，cookie 文件的格式可以是 Netscape 格式，或者只是纯 HTTP 头部风格，存入文件。如果文件名是空的，不会加载 cookie，但 cookie 的处理仍旧启用。
     *
     * @param string $cookieFile
     *            cookie存放位置
     * @return Query
     */
    public function setCookieFile($cookieFile)
    {
        $this->cookieFile = $cookieFile;
        return $this;
    }

    /**
     * 全部数据使用HTTP协议中的 "POST" 操作来发送
     *
     * @param string|array $postFields
     *            可以是 urlencoded 后的字符串，类似'para1=val1&para2=val2&...'，也可以使用一个以字段名为键值，字段数据为值的数组。
     * @return Query
     */
    public function setPostFields($postFields)
    {
        $this->postFields = $postFields;
        return $this;
    }

    /**
     * 设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
     *
     * @param array $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * 设置请求模拟ip
     *
     * @param string $ip
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * 设置随机请求模拟ip
     *
     * @return Query
     */
    public function setRandIp()
    {
        $this->ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
        return $this;
    }

    /**
     * 在HTTP请求头中"Referer: "的内容。
     *
     * @param string $referer
     * @return Query
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * FALSE 禁止 cURL 验证对等证书（peer's certificate）
     *
     * @param bool $verifypeer
     * @return Query
     */
    public function setVerifypeer($verifypeer)
    {
        $this->verifypeer = $verifypeer;
        return $this;
    }

    /**
     * 设置在并发请求的休眠时间
     *
     * @param int $sleepTime
     *            单位，毫秒
     * @return $this
     */
    public function setSleepTime($sleepTime)
    {
        $this->sleepTime = $sleepTime;
        return $this;
    }

    /**
     * 获取原始数据
     *
     * @return string[]
     */
    public function getRawData()
    {
        $urls = array_unique($this->url);
        // 执行句柄数组对象
        $chs = [];
        for ($i = 0, $iMax = count($urls); $i < $iMax; $i++) {
            $chs[] = curl_init();
        }
        foreach ($urls as $k => $url) {
            curl_setopt($chs[$k], CURLOPT_URL, $url);
            curl_setopt($chs[$k], CURLOPT_HEADER, $this->outputHeader);
            curl_setopt($chs[$k], CURLOPT_FOLLOWLOCATION, $this->followLocation);
            curl_setopt($chs[$k], CURLOPT_RETURNTRANSFER, $this->returnTransfer);
            curl_setopt($chs[$k], CURLOPT_TIMEOUT, $this->timeOut);
            if (!empty($this->userAgent)) {
                curl_setopt($chs[$k], CURLOPT_USERAGENT, $this->userAgent);
            }
            if (!empty($this->cookieFile)) {
                if (!file_exists($this->cookieFile)) {
                    file_put_contents($this->cookieFile, '');
                }
                curl_setopt($chs[$k], CURLOPT_COOKIEFILE, $this->cookieFile);
                curl_setopt($chs[$k], CURLOPT_COOKIEJAR, $this->cookieFile);
            }
            if (!empty($this->postFields)) {
                // 判断文件
                if (is_array($this->postFields)) {
                    foreach ($this->postFields as $k2 => $v) {
                        if (preg_match('/^@/i', $v)) {
                            // 文件
                            $v = '@' . realpath(ltrim($v, '@'));
                            if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                                // php 版本 > 5.5,使用CURLFile传文件
                                $cfile = new \CURLFile(ltrim($v, '@'));
                                $this->postFields[$k2] = $cfile;
                            } else {
                                $this->postFields[$k2] = $v;
                            }
                        }
                    }
                }
                curl_setopt($chs[$k], CURLOPT_POST, 1);
                curl_setopt($chs[$k], CURLOPT_POSTFIELDS, $this->postFields);
            }
            if (!empty($this->ip)) {
                if (!empty($this->header)) {
                    $this->header[] = 'X-FORWARDED-FOR:' . $this->ip;
                    $this->header[] = 'CLIENT-IP:' . $this->ip;
                } else {
                    $this->header = array(
                        'X-FORWARDED-FOR:' . $this->ip,
                        'CLIENT-IP:' . $this->ip
                    );
                }
            }
            if (!empty($this->header)) {
                curl_setopt($chs[$k], CURLOPT_HTTPHEADER, $this->header);
            }
            if (!empty($this->referer)) {
                curl_setopt($chs[$k], CURLOPT_REFERER, $this->referer);
            }
            curl_setopt($chs[$k], CURLOPT_SSL_VERIFYPEER, $this->verifypeer);
            if (!empty($this->proxyIp)) {
                curl_setopt($chs[$k], CURLOPT_PROXY, $this->proxyIp);
                curl_setopt($chs[$k], CURLOPT_PROXYTYPE, $this->proxyType);
            }
        }
        $mh = curl_multi_init();
        foreach ($chs as $ch) {
            curl_multi_add_handle($mh, $ch);
        }
        $running = null;
        do {
            if ($this->sleepTime > 0) {
                usleep($this->sleepTime);
            }
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $data = [];
        foreach ($chs as $ch) {
            $content = curl_multi_getcontent($ch);
            if (!empty($this->srcCharset) && strtolower($this->srcCharset) != 'utf-8') {
                $content = mb_convert_encoding($content, 'utf-8', $this->srcCharset);
            }
            $data[] = $content;
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
        $this->init();
        return $data;
    }

    /**
     * 获取第一个结果
     *
     * @return string
     */
    public function getOne()
    {
        $data = $this->getRawData();
        if (isset($data[0])) {
            return $data[0];
        }
        return '';
    }

    /**
     * 获取所有结果
     *
     * @return string[]
     */
    public function getAll()
    {
        return $this->getRawData();
    }

    /**
     * 添加匹配规则
     *
     * @param string $regName
     *            定义一个获取名
     * @param string $reg
     *            正则表达式
     * @param integer $getIndex
     *            获取匹配的第几个
     * @param string|bool $isGetAll
     *            是否获取所有的匹配（使用preg_match_all）
     * @return $this
     */
    public function matchReg($regName, $reg, $getIndex = 1, $isGetAll = FALSE)
    {
        $this->matchReg[] = [
            'regName' => $regName,
            'reg' => $reg,
            'getIndex' => $getIndex,
            'isGetAll' => $isGetAll
        ];
        return $this;
    }

    /**
     * 匹配正则表达式的数据结果
     *
     * @return string[]
     * @throws \Exception
     */
    public function matchAll()
    {
        if (empty($this->matchReg)) {
            throw new \Exception('没有matchReg');
        }
        $data = $this->getAll();
        $result = [];
        foreach ($data as $v) {
            $tmp = [];
            foreach ($this->matchReg as $v2) {
                $content = '';
                if ($v2['isGetAll']) {
                    if (preg_match_all($v2['reg'], $v, $mat)) {
                        $content = $mat[$v2['getIndex']];
                    }
                } else {
                    if (preg_match($v2['reg'], $v, $mat)) {
                        $content = $mat[$v2['getIndex']];
                    }
                }

                $tmp[$v2['regName']] = $content;
            }
            $result[] = $tmp;
        }
        $this->initMatchReg();
        return $result;
    }

    /**
     * 匹配一个网页的数据
     *
     * @return array|string
     * @throws \Exception
     */
    public function matchOne()
    {
        $data = $this->matchAll();
        if (isset($data[0])) {
            return $data[0];
        }
        return [];
    }

    /**
     * 发送请求
     * @return string|string[]
     */
    public function send()
    {
        if (count($this->url) > 1) {
            return $this->getAll();
        }
        return $this->getOne();
    }

    /**
     * 发送body请求
     * @param array|string $body 发送的数据,json格式，数组会自动转为json格式
     * @return string|string[]
     */
    public function sendBody($body)
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }
        return $this->setHeader(array_merge($this->header, ['Content-Type: text/json; charset=utf-8', 'Content-Length:' . strlen($body)]))
            ->setPostFields($body)
            ->send();
    }

    /**
     * 设置谷歌浏览器useragent
     * @return $this
     */
    public function setPcUserAgent()
    {
        return $this->setUserAgent('Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.108 Safari/537.36');
    }

    /**
     * 设置手机浏览器useragent
     * @return $this
     */
    public function setMobileUserAgent()
    {
        return $this->setUserAgent('Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.108 Mobile Safari/537.36');
    }

    /**
     * 设置代理ip
     * @param string|array $ip 数组格式的会随机选择一个作为代理IP。形如 203.42.227.113:8080
     * @param int $proxyType 代理类型，0 http,2 https
     * @return $this
     */
    public function setProxyIp($ip, $proxyType = 0)
    {
        if (is_array($ip)) {
            shuffle($ip);
            $this->proxyIp = $ip[0];
        } else {
            $this->proxyIp = $ip;
        }
        $this->proxyType = $proxyType;
        return $this;
    }
}