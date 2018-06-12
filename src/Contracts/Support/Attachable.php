<?php
namespace Lawoole\Contracts\Support;

interface Attachable
{
    /**
     * Get all attachments.
     *
     * @return array
     */
    public function getAttachments();

    /**
     * Get an attachment by key.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttachment($key, $default = null);

    /**
     * Set attachments.
     *
     * @param array $attachments
     */
    public function setAttachments(array $attachments);

    /**
     * Set an attachment by key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttachment($key, $value);
}
