<?php

namespace library\mysmarty;

class FileSessionHandler implements \SessionHandlerInterface
{

    private $savePath = RUNTIME_DIR . '/session';

    /**
     * open 回调函数类似于类的构造函数， 在会话打开的时候会被调用。 这是自动开始会话或者通过调用 session_start() 手动开始会话 之后第一个被调用的回调函数。 此回调函数操作成功返回 TRUE，反之返回 FALSE。
     *
     * {@inheritdoc}
     *
     * @see SessionHandlerInterface::open()
     */
    public function open($save_path, $session_name)
    {
        $flag = true;
        if (!file_exists($this->savePath)) {
            $flag = mkdir($this->savePath, 0777, true);
        }
        return $flag;
    }

    /**
     * close 回调函数类似于类的析构函数。 在 write 回调函数调用之后调用。 当调用 session_write_close() 函数之后，也会调用 close 回调函数。 此回调函数操作成功返回 TRUE，反之返回 FALSE。
     *
     * {@inheritdoc}
     *
     * @see SessionHandlerInterface::close()
     */
    public function close()
    {
        return true;
    }

    /**
     * 如果会话中有数据，read 回调函数必须返回将会话数据编码（序列化）后的字符串。 如果会话中没有数据，read 回调函数返回空字符串。
     *
     * 在自动开始会话或者通过调用 session_start() 函数手动开始会话之后，PHP 内部调用 read 回调函数来获取会话数据。 在调用 read 之前，PHP 会调用 open 回调函数。
     *
     * read 回调返回的序列化之后的字符串格式必须与 write 回调函数保存数据时的格式完全一致。 PHP 会自动反序列化返回的字符串并填充 $_SESSION 超级全局变量。 虽然数据看起来和 serialize() 函数很相似， 但是需要提醒的是，它们是不同的。 请参考： session.serialize_handler。
     *
     * {@inheritdoc}
     *
     * @see SessionHandlerInterface::read()
     */
    public function read($session_id)
    {
        $file = $this->savePath . '/sess_' . $session_id;
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }

    /**
     * 在会话保存数据时会调用 write 回调函数。 此回调函数接收当前会话 ID 以及 $_SESSION 中数据序列化之后的字符串作为参数。 序列化会话数据的过程由 PHP 根据 session.serialize_handler 设定值来完成。
     *
     * 序列化后的数据将和会话 ID 关联在一起进行保存。 当调用 read 回调函数获取数据时，所返回的数据必须要和 传入 write 回调函数的数据完全保持一致。
     *
     * PHP 会在脚本执行完毕或调用 session_write_close() 函数之后调用此回调函数。 注意，在调用完此回调函数之后，PHP 内部会调用 close 回调函数。
     *
     * {@inheritdoc}
     *
     * @see SessionHandlerInterface::write()
     */
    public function write($session_id, $session_data)
    {
        return file_put_contents($this->savePath . '/sess_' . $session_id, $session_data) !== false;
    }

    /**
     * 当调用 session_destroy() 函数， 或者调用 session_regenerate_id() 函数并且设置 destroy 参数为 TRUE 时， 会调用此回调函数。此回调函数操作成功返回 TRUE，反之返回 FALSE。
     *
     * {@inheritdoc}
     *
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy($session_id)
    {
        $file = $this->savePath . '/sess_' . $session_id;
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

    /**
     * 为了清理会话中的旧数据，PHP 会不时的调用垃圾收集回调函数。 调用周期由 session.gc_probability 和 session.gc_divisor 参数控制。 传入到此回调函数的 lifetime 参数由 session.gc_maxlifetime 设置。 此回调函数操作成功返回 TRUE，反之返回 FALSE。
     *
     * {@inheritdoc}
     *
     * @see SessionHandlerInterface::gc()
     */
    public function gc($maxlifetime)
    {
        foreach (glob($this->savePath . '/sess_*') as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }
}