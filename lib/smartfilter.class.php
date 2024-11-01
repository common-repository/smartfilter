<?php
require_once('Requests.php');
Requests::register_autoloader();

class SmartFilterNetworkException extends Exception { }
class SmartFilterBadInputParameter extends Exception { }
class SmartFilterBadAPIKey extends Exception { }
class SmartFilterRequestTooLarge extends Exception { }
class SmartFilterInternalError extends Exception { }
class SmartFilterAccountQuotaExceeded extends Exception { }

class SmartFilterClient {
    private $key, $base;
    
    function __construct($key) {
        $this->key = $key;
        $this->base = 'https://api.prevoty.com/1';
    }

    // Endpoint: /key/verify
    function verify() {
        try {
            $response = Requests::get(
                $this->base . '/key/verify?api_key=' . $this->key, 
                array('Accept' => 'application/json')
            );
        }
        catch (Requests_Exception $e) {
            throw new SmartFilterNetworkException($e);
        }
        if ($response->status_code == 200) { return true; }
        if ($response->status_code == 400) { throw new SmartFilterBadInputParameter(); }
        if ($response->status_code == 403) { throw new SmartFilterBadAPIKey(); }
        if ($response->status_code == 500) { throw new SmartFilterInternalError(); }
        return false;
    }

    // Endpoint: /key/info
    function info() {
        try {
            $response = Requests::get(
                $this->base . '/key/info?api_key=' . $this->key, 
                array('Accept' => 'application/json')
            );
        }
        catch (Requests_Exception $e) {
            throw new SmartFilterNetworkException($e);
        }
        if ($response->status_code == 200) { return json_decode($response->body, true); }
        if ($response->status_code == 400) { throw new SmartFilterBadInputParameter(); }
        if ($response->status_code == 403) { throw new SmartFilterBadAPIKey(); }
        if ($response->status_code == 500) { throw new SmartFilterInternalError(); }
        return array();
    }

    // Endpoint: /rule/verify
    function verify_rule($rule_key) {
        try {
            $response = Requests::get(
                $this->base . '/rule/verify?api_key=' . $this->key . '&rule_key=' . $rule_key, 
                array('Accept' => 'application/json')
            );
        }
        catch (Requests_Exception $e) {
            throw new SmartFilterNetworkException($e);
        }
        if ($response->status_code == 200) { return true; }
        if ($response->status_code == 400) { throw new SmartFilterBadInputParameter(); }
        if ($response->status_code == 403) { throw new SmartFilterBadAPIKey(); }
        if ($response->status_code == 500) { throw new SmartFilterInternalError(); }
        return false;
    }

    // Endpoint: /xss/filter
    function filter($input, $rule_key) {
        try {
            $response = Requests::post(
                $this->base . '/xss/filter',
                array('Accept' => 'application/json'),
                array('api_key' => $this->key, 'input' => $input, 'rule_key' => $rule_key)
            );
        }
        catch (Requests_Exception $e) {
            throw new SmartFilterNetworkException($e);
        }
        if ($response->status_code == 200) { return json_decode($response->body, true); }
        if ($response->status_code == 400) { throw new SmartFilterBadInputParameter(); }
        if ($response->status_code == 403) { throw new SmartFilterBadAPIKey(); }
        if ($response->status_code == 413) { throw new SmartFilterRequestTooLarge(); }
        if ($response->status_code == 500) { throw new SmartFilterInternalError(); }
        if ($response->status_code == 507) { throw new SmartFilterAccountQuotaExceeded(); }
        return array();
    }
}
