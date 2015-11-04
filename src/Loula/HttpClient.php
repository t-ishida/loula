<?php
namespace Loula;
class HttpClient {

    public function get ($url, $params =  null)
    {
        return json_decode($this->sendOne(new HttpRequest('GET', $url, $params)));
    }

    public function post ($url, $params = null, $files = null)
    {
        return json_decode($this->sendOne(new HttpRequest('POST', $url, $params, null, $files)));
    }

    public function delete ($url, $params = null)
    {
        return json_decode($this->sendOne(new HttpRequest('DELETE', $url, $params)));
    }

    public function put ($url, $params = null, $files = null)
    {
        return json_decode($this->sendOne(new HttpRequest('PUT', $url, $params, $files)));
    }

    public function sendOne(HttpRequest $request, $throwBadRequest = true)
    {
        $curl = curl_init();
        curl_setopt_array($curl, $request->toArray());
        $response = new HttpResponse(curl_exec($curl), curl_getinfo($curl));
        curl_close($curl);
        if ($throwBadRequest && ($response->getHttpCode() < 200 || $response->getHttpCode() > 300)) {
            throw new Exception ($response->getHttpCode(), $response->getHeader(), $response->getBody(), $response->getInfo());
        }
        $request->at($response);
        return $response;
    }
}
