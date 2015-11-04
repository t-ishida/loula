<?php
/**
 * Date: 15/11/02
 * Time: 18:03
 */

namespace Loula;
class HttpResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse ()
    {
        $response = new HttpResponse("HTTP/1.1 401 Unauthorized\r\n" .
        'WWW-Authenticate: Bearer realm="ReFUEL4 API",error="invalid_token", error_description="The access token invalid or expired"' . "\r\n" .
        "\r\n" .
        json_encode(array(
            'error' => 'invalid token',
            'error_description' => 'access_token invalid or expired',
        )), array('http_code' => 401));
        $this->assertEquals(401, $response->getHttpCode());
        $this->assertEquals(
            "HTTP/1.1 401 Unauthorized\r\n" .
            'WWW-Authenticate: Bearer realm="ReFUEL4 API",error="invalid_token", error_description="The access token invalid or expired"' ,
            $response->getHeader()
        );

        $this->assertEquals(
            json_encode(array(
                'error' => 'invalid token',
                'error_description' => 'access_token invalid or expired',
            )),
            $response->getBody()
        );
    }
}