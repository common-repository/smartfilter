<?php
require_once('../lib/smartfilter.class.php');

header('Content-Type: application/json');

if (isset($_GET['apikey']) && isset($_GET['rulekey'])) {
  $api_key = trim($_GET['apikey']);
  $rule_key = trim($_GET['rulekey']);
  if (strlen($api_key) > 0 && strlen($rule_key) > 0) {
    $client = new SmartFilterClient($api_key);
    try {
      if ($client->verify_rule($rule_key)) {
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