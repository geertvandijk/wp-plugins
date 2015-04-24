<?php
include_once(ABSPATH . 'wp-config.php');
include_once(ABSPATH . 'wp-includes/wp-db.php');
include_once(ABSPATH . 'wp-includes/pluggable.php');


class g182_Validator {

  function g182_Validator() {
    
  }

  function none($text) {
    return true;
  } 

  function password($text) {
    return true;
  }

  function email($text) {
  	return filter_var($text, FILTER_VALIDATE_EMAIL);
  }

  function name($text) {
  	return preg_match('/^[A-Za-z ]/', $text);
  }

  function phone($text) {
  	return preg_match('/^[0-9\/-]+$/', $text);
  }

}

add_action("init", "g182_Validator_Init");
function g182_Validator_Init() { global $g182_Validator; $g182_Validator = new g182_Validator(); }
?>