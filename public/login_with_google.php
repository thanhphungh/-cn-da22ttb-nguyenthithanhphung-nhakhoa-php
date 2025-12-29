// public/login_with_google.php
require_once '../includes/config.php';

$params = [
  'client_id' => GOOGLE_CLIENT_ID,
  'redirect_uri' => GOOGLE_REDIRECT_URI,
  'response_type' => 'code',
  'scope' => GOOGLE_SCOPES,
  'access_type' => 'offline',
  'prompt' => 'consent'
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
