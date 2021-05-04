<?php
function debugLog($alertMessage) {
      global $EN_DEBUG_ALERTS;
      if ($EN_DEBUG_ALERTS) {
        echo "<script> alert(\"Debug Alert: <br/> " . $alertMessage . "\");</script>";
      }
}
?>