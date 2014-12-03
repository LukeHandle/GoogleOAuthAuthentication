<?php

class GoogleOAuthAuthentication {
    public $access_token;
    public $google_email;
    public static $oldsession;
    
    private static $oauth2_client_id;
    private static $oauth2_redirect;
    private static $oauth2_server_url;

    public function __construct($oauth2) {
        if(!empty(session_id())) { session_write_close(); }

        self::$oldsession = session_get_cookie_params ();
        self::$oldsession['name'] = session_name('Google_OAuth');
        session_set_cookie_params(0, '/', $oauth2['cookie_domain'], true, true);

        self::$oauth2_client_id = $oauth2['client_id'];
        self::$oauth2_redirect = $oauth2['redirect'];
        self::$oauth2_server_url = $oauth2['server_url'];
        session_start();
    }

    public function __destruct() {
        session_write_close();
        session_set_cookie_params(self::$oldsession['lifetime'], self::$oldsession['path'], self::$oldsession['domain'], self::$oldsession['secure'], self::$oldsession['httponly']);
        session_name(self::$oldsession['name']);
    }

    public function login($redirect = NULL) {
        if(isset($redirect)) {
            $_SESSION['auth_redirect'] = $redirect;
        }
        $query_params = array(
           'response_type' => 'code',
           'client_id' => self::$oauth2_client_id,
           'redirect_uri' => self::$oauth2_redirect,
           'scope' => 'email'
        );

        $forward_url = self::$oauth2_server_url . '?' . http_build_query($query_params);

        header('Location: ' . $forward_url);
    }

    public function logout($redirect = NULL) {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        if(isset($redirect)) {
            header('Location: ' . $redirect);
        }
    }

    public function checkToken($access_token) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://www.googleapis.com/plus/v1/people/me?fields=id%2Cemails',
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $access_token),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 5
        ));
        $peopleGet = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);
        if($status === 200) {
            $peopleGet = json_decode($peopleGet, 1);
            $userInfo = array(
                'access_token' => $access_token,
                'id' => $peopleGet['id'],
                'email' => $peopleGet['emails']['0']['value'],
                'username' => strstr($peopleGet['emails']['0']['value'], "@", true)
            );
            return $userInfo;
        } else {
            error_log(__FILE__.' '.__FUNCTION__.' '.$status.' '.$time.'s String: '.str_replace(array("\r", "\n"), "", $peopleGet));
            return false;
        }
    }

    public function authenticationCheck($strict = true) {
        if(!isset($_SESSION['userInfo'])) {
            return false;
        } else {
            if($strict === false) {
                return true;
            } else {
                return self::checkToken($_SESSION['userInfo']['access_token']);
            }
        }
    }

    public function cookieSet($userInfo) {
            $_SESSION['userInfo'] = $userInfo;
            if(isset($_SESSION['auth_redirect']) && $_SESSION['auth_redirect'] !== '') {
                $redirect = $_SESSION['auth_redirect'];
                $_SESSION['auth_redirect'] = '';
                return $redirect;
            }
    }

    public function cookieGet() {
            $userInfo = $_SESSION['userInfo'];
            return $userInfo;
    }

    public function echoTest() {
            $echo = $_SESSION['userInfo']['access_token'];
            return $echo;
    }

    public function returnTest() {
            return self::echoTest();
    }
}
?>
