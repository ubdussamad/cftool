<?php
function debugLog($alertMessage) {
      global $EN_DEBUG_ALERTS;
      if ($EN_DEBUG_ALERTS) {
        echo "<script> alert(\"Debug Alert: <br/> " . $alertMessage . "\");</script>";
      }
}


# POST data testing suite (API_DGB)
if ($EN_API_DBG) {
  $post_elements = array('job_name', 'usr_name' , 'search_only' , 'cancel_job');
  for ($x = 0; $x < count($post_elements) ; $x++) {
    if (isset( $_POST[ $post_elements[$x] ] )){
      echo "Var: " . $post_elements[$x] . " : " . $_POST[ $post_elements[$x] ] . "of type: " . gettype($_POST[$post_elements[$x]]) .  "\n";
    }
    else {
      echo "Var: " . $post_elements[$x] . " : " . "Not set/NULL\n";
    }
  }
  die();
}

?>

