<?php
/**
 * Date: 15/11/02
 * Time: 18:26
 */

namespace Loula;


abstract class OAuthClient extends HttpClient
{
    private $accessToken = null;
    private $refreshToken = null;
    private $observers = null;

    public function __construct($accessToken = null , $refreshToken = null, $observers = null)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->observers = $observers ?: array();
    }

    public function buildUrl ($url)
    {
        return $this->getEndPoint() . (strpos($url, '/') !== 0 ? '/' : '') . $url;
    }

    public abstract function getEndPoint();
    public abstract function exchangeCode($code);

    public function refreshToken ()
    {
        $this->refreshAccessToken();
        foreach ($this->observers as $observer) {
            $observer->changedAccessTokenAt();
        }
    }
    public abstract function refreshAccessToken();


    public function get ($url, $params =  null)
    {
        return parent::get($this->buildUrl($url), $params);
    }

    public function post ($url, $params = null, $files = null)
    {
        return parent::post($this->buildUrl($url), $params, null, $files);
    }

    public function delete ($url, $params = null)
    {
        return parent::delete($this->buildUrl($url), $params);
    }

    public function put ($url, $params = null, $files = null)
    {
        return parent::put($this->buildUrl($url), $params, null, $files);
    }

    public function sendOne(HttpRequest $request, $throwBadRequest = true)
    {
        $response = parent::sendOne($request, false);
        if ($response->getHttpCode() === 401 && preg_match('#invalid_token.+?access token#', $response->getHeader())) {
            $this->refreshAccessToken();
            $request->addParam('access_token', $this->accessToken);
            $response = parent::sendOne($request);
        } elseif ($throwBadRequest && ($response->getHttpCode() < 200 || $response->getHttpCode() > 300)) {
            throw new Exception ($response->getHttpCode(), $response->getHeader(), $response->getBody(), $response->getInfo());
        }
        return $response;
    }
}