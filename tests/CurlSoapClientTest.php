<?php
namespace Aaharu\Soap\CurlSoapClient\Tests;

use Aaharu\Soap\CurlSoapClient;

/**
 * @coversDefaultClass \Aaharu\Soap\CurlSoapClient
 */
class CurlSoapClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function soap1_1()
    {
        $obj = new CurlSoapClient(null, array(
            'location' => 'http://localhost:8000/tests/server.php',
            'uri' => 'http://test-uri/',
            'compression' => SOAP_COMPRESSION_ACCEPT,
            'connection_timeout' => 1
        ));

        $response = $obj->test('abc');
        $this->assertEquals('abc', $response);
    }

    /**
     * @test
     */
    public function soap1_2()
    {
        $obj = new CurlSoapClient(null, array(
            'location' => 'http://localhost:8000/tests/server.php?redirect=1',
            'uri' => 'http://test-uri/',
            'user_agent' => 'curlsoapclient',
            'soap_version' => SOAP_1_2,
            'compression' => SOAP_COMPRESSION_GZIP,
            'trace' => true
        ));

        $response = $obj->__soapCall('test', array(123));
        $this->assertEquals(123, $response);

        $last_request_headers = $obj->__getLastRequestHeaders();
        $this->assertTrue(stripos($last_request_headers, 'User-Agent: curlsoapclient') !== false);
    }

    /**
     * @test
     */
    public function overRedirectMax()
    {
        // no exception option
        $obj = new CurlSoapClient(null, array(
            'location' => 'http://localhost:8000/tests/server.php?redirect=2',
            'uri' => 'http://test-uri/',
            'redirect_max' => 1,
            'exceptions' => false
        ));

        $response = $obj->test(123);
        $this->assertInstanceOf('SoapFault', $response);
        $this->assertTrue(is_soap_fault($response));
    }

    /**
     * @test
     * @expectedException        \SoapFault
     * @expectedExceptionMessage Error Fetching http, 
     */
    public function curlSoapFault()
    {
        $obj = new CurlSoapClient(null, array(
            'location' => 'http://noexists',
            'uri' => 'http://test-uri/'
        ));
        $response = $obj->test('hoge');
    }

    /**
     * @test
     * @expectedException        \SoapFault
     * @expectedExceptionMessage Service Temporarily Unavailable
     */
    public function server503()
    {
        $obj = new CurlSoapClient(null, array(
            'location' => 'http://localhost:8000/tests/server.php?503=1',
            'uri' => 'http://test-uri/'
        ));
        $response = $obj->test('hoge');
    }

    /**
     * @test
     * @expectedException        \SoapFault
     * @expectedExceptionMessage message
     */
    public function testFault()
    {
        $obj = new CurlSoapClient(null, array(
            'location' => 'http://localhost:8000/tests/server.php',
            'uri' => 'http://test-uri/'
        ));
        $response = $obj->testFault();
    }
}