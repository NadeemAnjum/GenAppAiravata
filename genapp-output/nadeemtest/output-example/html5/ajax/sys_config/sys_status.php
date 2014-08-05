<?php
header('Content-type: application/json');

session_start(); 
if ( isset( $_SESSION[ 'logon' ] ) )
{ 
  $results[ '_logon' ] = $_SESSION[ 'logon' ];
} else {
  $results[ '_logon' ] = "";
}
echo (json_encode($results));
exit();
?>

