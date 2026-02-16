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
        ];
        
        return GOOGLE_AUTH_URL . '?' . http_build_query($params);//builds a full url so when user clicks continue with google it redirect them to this url
    }
    
    /**
     * Exchange code for access token
     */
    public static function getAccessToken($code) {
        $params = [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ];// this tells google hers the code you give me please give me an acces token 
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);//Open connection to Google token endpoint
        curl_setopt($ch, CURLOPT_POST, true);//Send POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));//Send parameters
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Get user info from Google
     */
    public static function getUserInfo($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, GOOGLE_USERINFO_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
?>