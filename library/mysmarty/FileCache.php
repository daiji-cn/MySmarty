<?php

namespace library\mysmarty;

/**
 * 文件缓存
 * @package library\mysmarty
 */
class FileCache extends BaseCache
{
    // 缓存文件夹
    public string $cacheDir;
    private string $delimiter = '___@#@___';

    /**
     * 设置缓存文件夹
     * @param string $cacheDir
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * 写入缓存
     * @param string $cachekey key
     * @param string $content 内容
     * @param int $expire 过期时间，单位：秒
     * @return bool
     */
    public function write(string $cachekey, string $content, int $expire = 3600): bool
    {
        return file_put_contents($this->cacheDir . '/' . $cachekey . '.cache', (time() + $expire) . $this->delimiter . $content);
    }

    /**
     * 清空所有缓存
     */
    public function purge(): bool
    {
        $files = scandir($this->cacheDir);
        if (count($files) > 2) {
            foreach ($files as $v) {
                if ($v !== '.' && $v !== '..') {
                    unlink($this->cacheDir . '/' . $v);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 检查是否有缓存
     * @param string $cachekey 缓存key
     * @return bool
     */
    public function isCached(string $cachekey): bool
    {
        $data = $this->read($cachekey);
        if (!empty($data)) {
            return true;
        }
        return false;
    }

    /**
     * 读取缓存
     * @param string $cachekey key
     * @return mixed
     */
    public function read(string $cachekey)
    {
        $cacheFile = $this->cacheDir . '/' . $cachekey . '.cache';
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            $dataArr = explode($this->delimiter, $data);
            if (time() <= $dataArr[0]) {
                return $dataArr[1];
            }
            $this->delete($cachekey);
        }
        return false;
    }

    /**
     * 删除缓存
     * @param string $cachekey key
     * @return bool
     */
    public function delete(string $cachekey): bool
    {
        $cacheFile = $this->cacheDir . '/' . $cachekey . '.cache';
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        return false;
    }
}