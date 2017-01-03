<?php

namespace My\Example;

require '../vendor/autoload.php';

class A {
    /**
     * @return int
     */
    function getStatuscode() {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://api.github.com/repos/guzzle/guzzle');
        return $res->getStatusCode();
    }
}
