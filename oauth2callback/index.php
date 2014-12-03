<?php
require_once('../config.php');
require_once('../GoogleOAuthAuthentication.class.php');
require_once('../HttpPost.class.php');

# If Google returns an error redirect to index to query string set.
if(isset($_GET['error'])) {
    header('Location: '.$oauth2['redirect_domain'].'/?error='.$_GET['error']);
}

if(isset($_GET['code'])) {
    $code = $_GET['code'];
    $token_address = 'https://accounts.google.com/o/oauth2/token';
    // this will be our POST back to OAuth for token
    $post_data = array(
        "code" => $code,
        "client_id" => $oauth2['client_id'],
        "client_secret" => $oauth2['secret'],
        "redirect_uri" => $oauth2['redirect'],
        "grant_type" => "authorization_code"
    );

    $req = new HttpPost($token_address);
    $req->setPost($post_data);
    $req->send();

    $responseObj = json_decode($req->getResponse());
    $access_token = $responseObj->access_token;

    $auth_check = new BanxsiAuthenticate($oauth2);

    $userInfo = $auth_check->checkToken($access_token);
    if($userInfo) {
        if(strrchr($userInfo['email'], "@") === $oauth2['email_domain']) {
            $redirect = $auth_check->cookieSet($userInfo);
            header('Location: '.$oauth2['redirect_domain'].$redirect);
        } else {
            error_log(__FILE__.' '.__FUNCTION__.' invalid email: '.$userInfo['email']);
            header('Location: '.$oauth2['redirect_domain'].'/?error=invalid_email');
        }
    } else {
        header('Location: '.$oauth2['redirect_domain'].'/?error=check_token');
    }
}
?>
