<?php
require_once "Mail.php";

function mymail( $to, $subject, $body )
{
   $json = json_decode( file_get_contents( "__appconfig__" ) );

   $headers = array ('From' => '__application__@' . $json->smtpmail->from,
                     'To' => $to,
                     'Subject' => $subject );

   $smtp = Mail::factory('smtp',
           array (
             'host' => $json->smtpmail->host,
             'auth' => true,
             'username' => $json->smtpmail->user,
             'password' => base64_decode( $json->smtpmail->password ) ) );

   $mail = $smtp->send( $to, $headers, $body );

   return PEAR::isError( $mail );
}
?>
