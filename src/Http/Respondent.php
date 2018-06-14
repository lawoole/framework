<?php
namespace Lawoole\Http;

use DateTime;
use DateTimeZone;
use RuntimeException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Respondent
{
    /**
     * The Swoole response instance.
     *
     * @var \Swoole\Http\Response
     */
    protected $response;

    /**
     * Split response into multiple parts.
     *
     * @var bool
     */
    protected $chucked = false;

    /**
     * Create a respondent instance.
     *
     * @param \Swoole\Http\Response $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Send the response headers.
     *
     * @param int $statusCode
     * @param \Symfony\Component\HttpFoundation\ResponseHeaderBag $headerBag
     */
    public function sendHeader($statusCode = 200, $headerBag = null)
    {
        if ($this->response->header !== null) {
            // If any headers has been set in response, we will consider the
            // response headers has been sent.
            return;
        }

        $this->response->status($statusCode);

        $headerBag = $headerBag ?? new ResponseHeaderBag;

        // RFC2616 - 14.18
        // Origin servers MUST include a Date header field in all responses.
        $this->addDateHeaderIfNecessary($statusCode, $headerBag);

        foreach ($headerBag->allPreserveCaseWithoutCookies() as $name => $values) {
            $this->setHeaderInResponse($name, (array) $values);
        }

        foreach ($headerBag->getCookies() as $cookie) {
            $this->setCookieInResponse($cookie);
        }
    }

    /**
     * Add the Date header if it's missing.
     *
     * @see https://tools.ietf.org/html/rfc2616#section-14.18
     *
     * @param int $statusCode
     * @param \Symfony\Component\HttpFoundation\ResponseHeaderBag $headerBag
     */
    protected function addDateHeaderIfNecessary($statusCode, $headerBag)
    {
        if ($statusCode >= 200 && $statusCode < 500 && !$headerBag->has('Date')) {
            $date = DateTime::createFromFormat('U', time(), new DateTimeZone('UTC'));

            $headerBag->set('Date', $date->format('D, d M Y H:i:s').' GMT');
        }
    }

    /**
     * Set response header.
     *
     * @param string $name
     * @param array $values
     */
    protected function setHeaderInResponse($name, array $values)
    {
        $name = ucwords($name, '-');

        foreach ($values as $value) {
            $this->response->header($name, $value);
        }
    }

    /**
     * Set a cookie in response header.
     *
     * @param \Symfony\Component\HttpFoundation\Cookie $cookie
     */
    protected function setCookieInResponse(Cookie $cookie)
    {
        $method = $cookie->isRaw() ? 'rawcookie' : 'cookie';

        $this->response->$method(
            $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(),
            $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly()
        );
    }

    /**
     * Return whether the response body has been split.
     *
     * @return bool
     */
    public function isChucked()
    {
        return $this->chucked;
    }

    /**
     * Send response body.
     *
     * @param string $body
     */
    public function sendBody($body)
    {
        if ($this->chucked) {
            throw new RuntimeException('Cannot send data by sendBody() while the response is chucked.');
        }

        $this->response->end($body);
    }

    /**
     * Send response body in chucked.
     *
     * @param string $data
     * @param bool $last
     */
    public function sendChuck($data, $last = false)
    {
        $this->chucked = true;

        if ($last == true) {
            $this->response->end($data);
        } elseif (strlen($data) > 0) {
            $this->response->write($data);
        }
    }

    /**
     * Finish the chuck sending.
     */
    public function finishChuck()
    {
        $this->response->end();
    }
}
