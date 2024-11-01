<?php
require_once('../lib/smartfilter.class.php');

header('Content-Type: application/json');

if (isset($_GET['apikey'])) {
  $api_key = trim($_GET['apikey']);
  if (strlen($api_key) > 0) {
    $client = new SmartFilterClient($api_key);
    try {
      if ($client->verify()) {
        echo 1;
      }
    }
    catch (Exception $e) {
      echo 0;
    }
  }
}
else {
  echo 0;
}