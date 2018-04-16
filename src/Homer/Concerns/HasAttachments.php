<?php
namespace Lawoole\Homer\Concerns;

use Illuminate\Support\Arr;

trait HasAttachments
{
    /**
     * 附加信息
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * 获得附加信息
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * 设置附加信息
     *
     * @param array $attachments
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * 获得附加信息
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttachment($key, $default = null)
    {
        return Arr::get($this->attachments, $key, $default);
    }

    /**
     * 设置附加信息
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttachment($key, $value)
    {
        $this->attachments[$key] = $value;
    }
}
