<?php
session_start(); 
if (!isset($_SESSION['count'])) {
  $_SESSION['count'] = 0;
} else {
  $_SESSION['count']++;
}

if ( !sizeof( $_REQUEST ) )
{
    $result[ 'error' ] = "PHP code received no \$_REQUEST?";
    echo (json_encode($result));
    exit();
}

$do_logoff = 0;

if ( $_REQUEST[ "_logon" ] != $_SESSION[ 'logon' ] )
{
   $do_logoff = 1;
   unset( $_SESSION[ 'logon' ] );
   $responseVar[ '_logon' ] = "";
}

if ( !isset( $_SESSION[ 'logon' ] ) )
{
  echo '{}';
  exit();
}
session_write_close();
date_default_timezone_set("UTC");

if ( !isset( $_REQUEST[ 'compression' ] ) )
{
    $result[ 'error' ] = "No compression method received?";
    echo (json_encode($result));
    exit();
}
        
if ( !isset( $_REQUEST[ 'selectedfiles' ] ) )
{
  $result[ 'notice' ] = "No files selected";
  echo (json_encode($result));
  exit();
}

$userdir = "results/users/";
$dir     = "/var/www/html/nadeemtest/$userdir";

if ( $_REQUEST[ 'compression' ] == "no" )
{
// simply create a set of links 
   $result[ 'outfiles' ] = array();
   foreach ( $_REQUEST[ 'selectedfiles' ] as $k => $v )
   {
       if ( !is_dir( "$dir$v" ) )
       {
           if ( !file_exists( "$dir$v" ) )
           {
              if ( isset( $result[ 'missing files' ] ) )
              {
                  $result[ 'missing files' ] .= " $v";
              } else {
                  $result[ 'missing files' ] = $v;
              }
           } else {
              array_push( $result[ 'outfiles' ], "$userdir$v" );
           }
       }
   }
   echo json_encode( $result );
   exit();
}

// make an array of files

$outfiles = array();

$preg_remove_user = '/^' . $_SESSION[ 'logon' ] . '\//';

foreach ( $_REQUEST[ 'selectedfiles' ] as $k => $v )
{
    if ( !is_dir( "$dir$v" ) )
    {
        if ( !file_exists( "$dir$v" ) )
        {
           if ( isset( $result[ 'missing files' ] ) )
           {
               $result[ 'missing files' ] .= " $v";
           } else {
               $result[ 'missing files' ] = $v;
           }
        } else {
           array_push( $outfiles, preg_replace( $preg_remove_user, "", $v ) );
        }
    }
}

if ( !count( $outfiles ) )
{
   $result[ 'error' ] = 'no existing files found';
   echo json_encode( $result );
   exit();
}

$pkgname = "nadeemtest_" . $_SESSION[ 'logon' ] . "_" . date( "YmdHisT", time() );

// setup the command

$cmd = "";
switch( $_REQUEST[ 'compression' ] )
{
   case "gz" :
      $cmd = "tar zcf ";
      $suffix = ".tar.gz";
      break;
   case "bz2" :
      $cmd = "tar jcf ";
      $suffix = ".tar.bz2";
      break;
   case "xz" :
      $cmd = "tar Jcf ";
      $suffix = ".tar.xz";
      break;
   case "zip" :
      $cmd = "zip ";
      $suffix = ".zip";
      break;
   default :
      $result[ "error" ] = "unsupported compression method " . $_REQUEST[ 'compression' ];
      echo (json_encode($result));
      exit();
}


// need a temporary directory for output
do {
    $odir = uniqid( "/var/www/html/nadeemtest/results/" );
} while( file_exists( $odir ) );

if ( !file_exists( $odir ) )
{
   ob_start();

   if ( !mkdir( $odir, 0777, true ) )
   {  
      $cont = ob_get_contents();
      ob_end_clean();
      $result[ "error" ] = "could not create directory " . $odir . " " . $cont;
      echo (json_encode($result));
      exit();
   }
   chmod( $odir, 0775 );
   ob_end_clean();
}

$target = $odir . "/" . $pkgname . $suffix;
$cmd .= "$target " . join( " ", $outfiles );

// change into the user directory

ob_start();
if ( !chdir( $dir . "/" . $_SESSION[ 'logon' ] ) )
{
  $cont = ob_get_contents();
  ob_end_clean();
  $result[ "error" ] = "could not change to directory " . $dir . " " . $cont;
  echo (json_encode($result));
  exit();
}
ob_end_clean();

$cmd = escapeshellcmd( $cmd );

// $result[ 'tmpdir' ] = $odir;
// $result[ 'cmd'    ] = $cmd;
// $result[ 'cwd'    ] = getcwd();

ob_start();
exec ( $cmd );
$cont = ob_get_contents();
ob_end_clean();
if ( strlen( $cont ) )
{
   $result[ 'archiving reported' ] = $cont;
}

$result[ 'outfiles' ] = array( str_replace( "/var/www/html/nadeemtest/", "", $target ) );

// $result[ 'outfile_tag' ] = $result[ 'outfiles' ][ 0 ];
echo json_encode( $result );
?>
