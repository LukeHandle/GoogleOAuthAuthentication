<?php

class HttpPost {
    public $token_address;
    public $postString;
    public $httpResponse;
    public $ch;

    public function __construct($token_address) {
        $this->token_address = $token_address;
        $this->ch = curl_init($this->token_address);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function setPost( $params ) {
        $this->postString = rawurldecode(http_build_query($params));
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postString);
    }

    public function send() {
        $this->httpResponse = curl_exec($this->ch);
    }

    public function getResponse() {
        return $this->httpResponse;
    }
}
?>
