<?php
namespace Loula;

class HttpResponse {
    private $info = null;
    private $content = null;
    private $header = null;
    private $body = null;
    private $httpCode = null;

    public function __construct ($content, $info)
    {
        $this->httpCode = $info["http_code"] - 0;
        $this->info = $info;
        $this->content = $content;
        $responses = explode("\r\n\r\n", $content);
        $this->body = array_pop($responses);
        $this->header = array_pop($responses);
    }

    /**
     * @return mixed|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed|null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return null
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return null
     */
    public function getInfo()
    {
        return $this->info;
    }

    public function __toString()
    {
        return (string)$this->body;
    }
}
