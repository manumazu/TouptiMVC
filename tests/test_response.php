<?php

class TestResponse extends UnitTestCase
{
    public function testSetStatus()
    {
        $response = new TouptiResponse();
        $response->set_status(200);
        $headers = $response->get_headers();
        $this->assertEqual(count($headers), 1);
        $this->assertEqual($headers['Status'], 'HTTP/1.1 200 OK');
    }

    public function testSetStatus500()
    {
        $response = new TouptiResponse();
        $response->set_status(500);
        $headers = $response->get_headers();
        $this->assertEqual(count($headers), 1);
        $this->assertEqual($headers['Status'], 'HTTP/1.1 500 Internal Server Error');
    }

    public function testSetHeader()
    {
        $response = new TouptiResponse();
        $response->set_header('Content-Type', 'text/xml');
        $headers = $response->get_headers();
        $this->assertEqual(count($headers), 1);
        $this->assertEqual($headers['Content-Type'], 'text/xml');
        $this->assertEqual($response->get_header('Content-Type'), 'text/xml');
    }

    public function testHeaderNull()
    {
        $response = new TouptiResponse();
        $this->assertNull($response->get_header('Content-Type'));
    }

}
