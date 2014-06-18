<?php
header('Content-type: application/json');
session_unset(); 
session_destroy(); 
session_write_close(); 
setcookie(session_name(),'',0,'/'); 
session_regenerate_id(true);
$results[ '_logon' ] = "";
$results[ '-close' ] = 1;
echo (json_encode($results));
exit();
?>
