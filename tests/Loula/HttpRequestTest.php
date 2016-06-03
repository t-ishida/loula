<?php
/**
 * Date: 15/11/02
 * Time: 18:02
 */
namespace Loula;
class HttpRequestTest extends \PHPUnit_Framework_TestCase
{

    public function testBuildCurlOptionsGET()
    {
        $this->assertEquals(array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'Loula-WebClient',
            CURLOPT_URL => 'http://hoge/fuga.html?name=value',
            CURLOPT_HTTPHEADER => array(
                'header1',
                'header2',
            ),
            CURLOPT_FOLLOWLOCATION => 1,
        ), (new HttpRequest(
            'GET',
            'http://hoge/fuga.html',
            array('name' => 'value'),
            array(),
            array('header1', 'header2')
        ))->toArray());
    }

    public function testBuildCurlOptionsGETWithQueryString() 
    {
        $this->assertEquals(array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'Loula-WebClient',
            CURLOPT_URL => 'http://hoge/fuga.html?hogefuga=1&name=value',
            CURLOPT_HTTPHEADER => array(
                'header1',
                'header2',
            ),
            CURLOPT_FOLLOWLOCATION => 1,
        ), (new HttpRequest(
            'GET',
            'http://hoge/fuga.html?hogefuga=1',
            array('name' => 'value'),
            array(),
            array('header1', 'header2')
        ))->toArray());
    }
    public function testBuildCurlOptionsPOST() 
    {
        $this->assertEquals(array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'Loula-WebClient',
            CURLOPT_URL => 'http://hoge/fuga.html',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query(array('name' => 'value'), null, '&'),
            CURLOPT_FOLLOWLOCATION => 1,
        ), (new HttpRequest(
            'POST',
            'http://hoge/fuga.html',
            array('name' => 'value')
        ))->toArray());
    }
    
    public function testBuildCurlOptionsDELETE() 
    {
        $this->assertEquals(array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'Loula-WebClient',
            CURLOPT_URL => 'http://hoge/fuga.html',
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => http_build_query(array('name' => 'value'), null, '&'),
            CURLOPT_FOLLOWLOCATION => 1,
        ), (new HttpRequest(
            'DELETE',
            'http://hoge/fuga.html',
            array('name' => 'value')
        ))->toArray());
    }
    
    public function testBuildCurlOptionsPUT() 
    {
        $this->assertEquals(array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'Loula-WebClient',
            CURLOPT_URL => 'http://hoge/fuga.html',
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => http_build_query(array('name' => 'value'), null, '&'),
            CURLOPT_FOLLOWLOCATION => 1,
        ), (new HttpRequest(
            'PUT',
            'http://hoge/fuga.html',
            array('name' => 'value')
        ))->toArray());
    }
    
    public function testBuildCurlOptionsWithProxy() 
    {
        $this->assertEquals(array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'Loula-WebClient',
            CURLOPT_URL => 'http://hoge/fuga.html',
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => http_build_query(array('name' => 'value'), null, '&'),
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_PROXY => 'http://proxy.proxy.proxy',
        ), (new HttpRequest(
            'PUT',
            'http://hoge/fuga.html',
            array('name' => 'value'),
            null,
            null,
            null,
            'http://proxy.proxy.proxy'
        ))->toArray());
    }
    
    public function testBuildCurlOptionsWithFiles()
    {
        $result = (new HttpRequest('POST', 'https://hoge.fuga.com/file_upload', array('hoge' => 'fuga'), array(
            'script' => __FILE__,
        )))->toArray();
        preg_match('#boundary=-+(.+)#', $result[CURLOPT_HTTPHEADER][1], $boundary);
        $body = $result[CURLOPT_POSTFIELDS];
        $fileField = preg_split('#-+' . preg_quote($boundary[1], '#') . '-*#', $body);
        $file = $fileField[2];
        $array = explode("\r\n\r\n", trim($file));
        array_shift($array);
        $this->assertEquals(trim(file_get_contents(__FILE__)), implode("\n\n", $array));
    }
}