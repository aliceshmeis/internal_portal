<?php
/**
 * Google OAuth Configuration
 */

// YOUR Google OAuth credentials
define('GOOGLE_CLIENT_ID', '[565661564000-j0ps9jpaihepbeu4ashmajmeq3lar324.apps.googleusercontent.com](http://565661564000-j0ps9jpaihepbeu4ashmajmeq3lar324.apps.googleusercontent.com/)');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/internal_portal/auth/google-callback');

// Google OAuth URLs
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// OAuth scope
define('GOOGLE_SCOPE', 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');
?>