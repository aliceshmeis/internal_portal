<?php
/**
 * Google OAuth Helper Class
 */

class GoogleOAuth {
    
    /**
     * Get Google login URL
     */
    //this class is the translator between my system and google
    public static function getLoginUrl() {
        $params = [
            'client_id' => GOOGLE_CLIENT_ID,//identifies your app to google 
            'redirect_uri' => GOOGLE_REDIRECT_URI,//where Google should send the user back after login
            'response_type' => 'code',//“After login, give me a temporary authorization code.”
            'scope' => GOOGLE_SCOPE,//what information you want (email, profile)
            'access_type' => 'online',
        ];//only builds a url(a special google login link)
        
        return GOOGLE_AUTH_URL . '?' . http_build_query($params);//builds a full url so when user clicks continue with google it redirect them to this url
    }
    
    /**
     * Exchange code for access token
     */
    public static function getAccessToken($code) {//the previous code they gave me prove that logs in successfully but it cannot give user info directly
        $params = [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,//is this request coming from the same app that requested the login
            'client_secret' => GOOGLE_CLIENT_SECRET,//this request is coming from my server not a hacker
            'redirect_uri' => GOOGLE_REDIRECT_URI,//is this the same redirct url that was used when requesting the code
            'grant_type' => 'authorization_code',//this tells google am excahanging an authorization code for an access token
        ];// this tells google hers the code you give me please give me an acces token 
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);//Open connection to Google token endpoint
        curl_setopt($ch, CURLOPT_POST, true);//Send POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));//converts param array into a url and sends it to google
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);//google checks eveyrthing,if correct google sends back jsonnlike(access;token , bearer token)
        curl_close($ch);
        
        return json_decode($response, true);//convert json to php
    }
    
    /**
     * Get user info from Google
     */ //now we have the access token 
    public static function getUserInfo($accessToken) {// here is the acces token give me user info
        $ch = curl_init();//curl session(like communication channel)
        curl_setopt($ch, CURLOPT_URL, GOOGLE_USERINFO_URL);//G endpoint that retuns id email name 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//give me the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken//sends the acces token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//this disable ssl verification for testing
        
        $response = curl_exec($ch);//sends the request to goole
        curl_close($ch);
        
        return json_decode($response, true);//convert jason to php
    }
}
?>