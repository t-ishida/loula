<?php
namespace Loula;

class HttpMultiClient
{
    private $requests = array();
    private $multiRequest = null;
    private $running = null;
    private $stat = null;
    public function __construct ()
    {
        $this->multiRequest = curl_multi_init();
    }

    public function addRequest (HttpRequest $request)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $request->toArray());
        curl_multi_add_handle($this->multiRequest, $ch);
        $request->setHandle($ch);
        $this->requests[] = $request;
    }


    public function run ()
    {
        $this->running = null;
        $this->stat = curl_multi_exec($this->multiRequest, $this->running);
        if (!$this->running || $this->stat !== CURLM_OK) {
            throw new \RuntimeException('unknown error');
        }
    }

    public function process ()
    {
        if (!$this->multiRequest) return ;
        $responses = array();
        do {$this->stat = curl_multi_exec($this->multiRequest, $this->running);} while ($this->running && $this->stat === CURLM_OK);
        foreach ($this->requests as $request) {
            $ch = $request->getHandle();
            $response = new HttpResponse(curl_multi_getcontent($ch), curl_getinfo($ch));
            $request->at($response);
            $responses[] = $response;
        }
        $this->close();
        return $responses;
    }

    public function close ()
    {
        if ($this->requests) {
            foreach ($this->requests as $request) {
                curl_multi_remove_handle ($this->multiRequest, $request->getHandle());
            }
        }
        if ($this->multiRequest) {
            curl_multi_close($this->multiRequest);
        }
        $this->requests = array();
        $this->multiRequest = null;
        $this->running = null;
        $this->stat = null;
    }


    public function __destruct ()
    {
        $this->process();
        $this->close();
    }
}


