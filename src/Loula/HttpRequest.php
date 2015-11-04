<?php
namespace Loula;
class HttpRequest
{
    private $method = null;
    private $url = null;
    private $params = null;
    private $files = null;
    private $observers = array();
    private $handle = null;
    private $headers = null;
    private static $CURL_OPTIONS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => 1,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'Loula-WebClient',
        CURLOPT_FOLLOWLOCATION => 1,
    );

    public function __construct ($method, $url, $params = null, $files = null, $headers = null, $observers = null)
    {
        $method = strtoupper($method);
        if ($method !== 'GET' && $method !== 'POST' && $method !== 'DELETE' && $method !== 'PUT') {
            throw new \RuntimeException('invalid method:' . $method);
        }
        if ($params && !is_array($params)) {
            throw new \InvalidArgumentException('$params wants array');
        }

        $this->method = $method;
        $this->url = $url;
        $this->params = $params ?: array();
        $this->files = $files ?: array();
        $this->observers = $observers ?: array();
        $this->headers = $headers ?: array();
    }

    public function addParam($key, $value) {
        $this->params[$key] = $value;
    }
    public function setHandle ($handler)
    {
        $this->handle = $handler;
    }

    public function getHandle ()
    {
        return $this->handle;
    }

    public function at (HttpResponse $response)
    {
        foreach ($this->observers as $observer) {
            $observer->at($response);
        }
    }

    public function toArray()
    {
        $method = $this->method;
        $url = $this->url;
        $params = $this->params;
        $files = $this->files;
        $headers = $this->headers;
        $options = self::$CURL_OPTIONS;
        if (is_array($options)) {
            foreach ($options as $key => $val) {
                if (!is_numeric($key)) continue;
                if (!isset($options[$key])) $options[$key] = $val;
            }

        }
        if ($method == 'GET') {
            $query = null;
            if (is_array($params)) {
                $query = http_build_query($params, null, '&');
            } elseif (is_scalar($params)) {
                $query = $params;
            } elseif (is_null($params)) {
                $query = '';
            } else {
                throw new \InvalidArgumentException ('Invalid params');
            }
            if ($query) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
            }
        } else {
            if ($files)  {
                static $disallow = array("\0", "\"", "\r", "\n");
                $body = array();
                if (is_array($params)) {
                    foreach ($params as $key => $value) {
                        $key = str_replace($disallow, "_", $key);
                        $body[] = implode("\r\n", array("Content-Disposition: form-data; name=\"{$key}\"", "", filter_var($value)));
                    }
                }
                foreach ($files as $name => $path) {
                    $path = realpath(filter_var($path));
                    if (!is_file($path) || !is_readable($path)) {
                        throw new \InvalidArgumentException($path . ' is not file.');
                    }
                    $data = file_get_contents($path);
                    $fileName = call_user_func("end", explode(DIRECTORY_SEPARATOR, $path));
                    list($name, $fileName) = str_replace($disallow, "_", array($name, $fileName));
                    $body[] = implode("\r\n", array(
                        "Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$fileName}\"",
                        "Content-Type: application/octet-stream",
                        "",
                        $data,
                    ));
                }
                do {
                    $boundary = "---------------------" . md5(mt_rand() . microtime());
                } while (preg_grep("/{$boundary}/", $body));
                array_walk($body, function (&$part) use ($boundary) {
                    $part = "--{$boundary}\r\n{$part}";
                });
                $body[] = "--{$boundary}--";
                $body[] = "";
                $options[CURLOPT_POSTFIELDS] = implode("\r\n", $body);
                $tmp = array(
                    "Expect: 100-continue",
                    "Content-Type: multipart/form-data; boundary={$boundary}",
                );
                if ($headers) $headers = array_merge($headers, $tmp);
                else          $headers = $tmp;
            } elseif (is_array($params)) {
                $options[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
            } elseif (is_file($params)) {
                $options[CURLOPT_POSTFIELDS] = file_get_contents(realpath($params));
            } elseif ($params) {
                $options[CURLOPT_POSTFIELDS] = $params;
            }

            if ($method == 'POST') {
                $options[CURLOPT_POST] = 1;
            } elseif (in_array($method, array('DELETE', 'PUT'))) {
                $options[CURLOPT_CUSTOMREQUEST] = $method;
            }
        }

        if ($headers && is_array($headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        } elseif ($headers) {
            throw new \InvalidArgumentException ('Invalid Header:' . var_export($headers, true));
        }
        $options[CURLOPT_URL] = $url;
        return $options;
    }
}


