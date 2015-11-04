<?php
namespace Loula;
class Exception extends \Exception {
    private $status = null;
    private $header = null;
    private $body = null;
    private $request = null;
    public function __construct ($status, $header, $body, $request)
    {
        $this->status = $status;
        $this->header = $header;
        $this->body   = $body;
        $this->request = $request;
    }

    /**
     * @return \Exception|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int|null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return null|string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return null
     */
    public function getRequest()
    {
        return $this->request;
    }
}