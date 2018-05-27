<?php
namespace Lawoole\Http;

use DateTime;
use DateTimeZone;
use RuntimeException;

class Respondent
{
    /**
     * Swoole 响应
     *
     * @var \Swoole\Http\Response
     */
    protected $response;

    /**
     * 是否开启分段发送
     *
     * @var bool
     */
    protected $chucked = false;

    /**
     * 创建响应发送器
     *
     * @param \Swoole\Http\Response $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * 发送响应头
     *
     * @param int $statusCode
     * @param \Symfony\Component\HttpFoundation\ResponseHeaderBag $headerBag
     */
    public function sendHeader($statusCode = 200, $headerBag = null)
    {
        if ($this->response->header !== null) {
            // 已经设置过响应头，就认为是已经发送过响应头
            return;
        }

        $this->response->status($statusCode);

        if ($headerBag) {
            // RFC2616 - 14.18 约定：所有的响应必须包含 Date 头
            if (!$headerBag->has('Date')) {
                $date = DateTime::createFromFormat('U', time(), new DateTimeZone('UTC'));

                $headerBag->set('Date', $date->format('D, d M Y H:i:s').' GMT');
            }

            foreach ($headerBag->allPreserveCaseWithoutCookies() as $name => $values) {
                $name = ucwords($name, '-');

                foreach ($values as $value) {
                    $this->response->header($name, $value);
                }
            }

            foreach ($headerBag->getCookies() as $cookie) {
                if ($cookie->isRaw()) {
                    $this->response->rawcookie(
                        $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(),
                        $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly()
                    );
                } else {
                    $this->response->cookie(
                        $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(),
                        $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly()
                    );
                }
            }
        }
    }

    /**
     * 发送响应体
     *
     * @param string $data
     */
    public function sendBody($data)
    {
        if ($this->chucked) {
            throw new RuntimeException('Cannot send data by sendBody() while the response has be chucked.');
        }

        $this->response->end($data);
    }

    /**
     * 发送分段数据
     *
     * @param string $data
     */
    public function sendChuck($data)
    {
        $this->chucked = true;

        if (strlen($data) > 0) {
            $this->response->write($data);
        }
    }

    /**
     * 结束分段响应发送
     */
    public function finishChuck()
    {
        $this->response->end();
    }
}
