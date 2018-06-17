<?php
namespace Lawoole\Support\Concerns;

use Illuminate\Support\Arr;

trait HasAttachments
{
    /**
     * All attachments.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Get all attachments.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Get an attachment by key.
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
     * Set attachments.
     *
     * @param array $attachments
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * Set an attachment by key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttachment($key, $value)
    {
        $this->attachments[$key] = $value;
    }
}
