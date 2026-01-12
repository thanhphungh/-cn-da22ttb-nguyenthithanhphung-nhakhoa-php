<?php
require_once '../includes/config.php';

$config = require '../includes/config.php';
$google = $config['google'];

$params = [
  'client_id'     => $google['client_id'],
  'redirect_uri'  => $google['redirect_uri'],
  'response_type' => 'code',
  'scope'         => $google['scopes'],
  'access_type'   => 'offline',
  'prompt'        => 'consent'
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
