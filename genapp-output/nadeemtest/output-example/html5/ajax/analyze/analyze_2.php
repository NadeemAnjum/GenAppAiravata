<?php
header('Content-type: application/json');

include '../../airavata.php';

# setup php session
session_start();
if (!isset($_SESSION['count'])) {
  $_SESSION['count'] = 0;
} else {
  $_SESSION['count']++;
}

if ( !sizeof( $_REQUEST ) )
{
    $responseVar = array("error" => "PHP code received no \$_REQUEST?" );
    echo (json_encode($responseVar));
    exit();
}

$do_logoff = 0;

if ( $_REQUEST[ "_logon" ] != $_SESSION[ 'logon' ] )
{
   $do_logoff = 1;
   unset( $_SESSION[ 'logon' ] );
   $responseVar[ '_logon' ] = "";
}

// error_log( "analyze_2:\t", 3, "/tmp/mylog" );

// if ( isset( $_REQUEST[ '_logon' ] ) )
// {
//    error_log( "request: " . $_REQUEST[ '_logon' ], 3, "/tmp/mylog" );
// } else {
//    error_log( "request: not set", 3, "/tmp/mylog" );
// }

// if ( isset( $_SESSION[ 'logon' ] ) )
// {
//    error_log( "\tsession:" .  $_SESSION[ 'logon' ] . "\n", 3, "/tmp/mylog" );
// } else {
//   error_log( "\tsession: not set\n", 3, "/tmp/mylog" );
// }

$cmd = "/home/nadeem/nadeemtest/bin/analyze_2";
if ( !is_executable( $cmd ) )
{
    $responseVar[ "error" ] = "command not found or not executable";
    echo (json_encode($responseVar));
    exit();
}

// if user based, use alternate directory structure

if ( !isset( $uniquedir ) &&
     isset( $_SESSION[ 'logon' ] ) &&
     strlen( $_SESSION[ 'logon' ] ) > 1 )
{
   $dir = "/var/www/html/nadeemtest/results/users/" . $_SESSION[ 'logon' ] . "/";
   if ( isset( $_REQUEST[ '_project' ] ) &&
        strlen( $_REQUEST[ '_project' ] ) > 1 )
   {
      $dir .= $_REQUEST[ '_project' ];
   } else {
      $dir .= 'no_project_specified';
   }
} else {
   do {
       $dir = uniqid( "/var/www/html/nadeemtest/results/" );
   } while( file_exists( $dir ) );
}

if ( !file_exists( $dir ) )
{
   ob_start();

   if ( !mkdir( $dir, 0777, true ) )
   {  
      $cont = ob_get_contents();
      ob_end_clean();
      $responseVar[ "error" ] = "could not create directory " . $dir . " " . $cont;
      echo (json_encode($responseVar));
      exit();
   }
   chmod( $dir, 0775 );
   ob_end_clean();
}

$_REQUEST[ '_base_directory' ] = $dir;

if ( !isset( $_SESSION[ 'udpport' ] ) ||
     !isset( $_SESSION[ 'udphost' ] ) || 
     !isset( $_SESSION[ 'resources' ] ) )
{
   $appjson = json_decode( file_get_contents( "/home/nadeem/nadeemtest/appconfig.json" ) );
   $_SESSION[ 'udphost'         ] = $appjson->messaging->udphostip;
   $_SESSION[ 'udpport'         ] = $appjson->messaging->udpport;
   $_SESSION[ 'resources'       ] = $appjson->resources;
   $_SESSION[ 'resourcedefault' ] = $appjson->resourcedefault;
   $_SESSION[ 'submitpolicy'    ] = $appjson->submitpolicy;
}
session_write_close();

$_REQUEST[ '_udphost' ] =  $_SESSION[ 'udphost' ];
$_REQUEST[ '_udpport' ] =  $_SESSION[ 'udpport' ];
$_REQUEST[ 'resourcedefault' ] = $_SESSION[ 'resourcedefault' ];




if ( !isset( $submitpolicy ) )
{
   if ( isset( $_SESSION[ 'submitpolicy' ] ) &&
        $_SESSION[ 'submitpolicy' ] == "login" &&
        ( !isset( $_SESSION[ 'logon' ] ) ||
          strlen( $_SESSION[ 'logon' ] ) == 0 ) )
   {
         $responseVar[ "error" ] = "You must be logged on to submit";
         echo (json_encode($responseVar));
         exit();
   }
} else {
   if ( $submitpolicy == "login" &&
        ( !isset( $_SESSION[ 'logon' ] ) ||
          strlen( $_SESSION[ 'logon' ] ) == 0 ) )
   {
         $responseVar[ "error" ] = "You must be logged on to submit";
         echo (json_encode($responseVar));
         exit();
   }
}

$cmdprefix = "";

if ( isset( $_SESSION[ 'resourcedefault' ] ) &&
     $_SESSION[ 'resourcedefault' ] == "disabled" )
{
      $responseVar[ "error" ] = "Job submission is currently disabled";
      echo (json_encode($responseVar));
      exit();
}

if ( isset( $useresource ) &&
     !isset( $_SESSION[ 'resources' ]->$useresource ) )
{
      $responseVar[ "error" ] = "module specified resource " . $useresource . " is not defined in appconfig";
      echo (json_encode($responseVar));
      exit();
}

if ( !isset( $_SESSION[ 'resources' ]->$_SESSION[ 'resourcedefault' ] ) &&
     !isset( $useresource ) )
{
     $responseVar[ "error" ] = "no default resource specified in appconfig and no resource defined in module";
     echo (json_encode($responseVar));
     exit();
} else {
   if ( isset( $useresource ) )
   {
      $cmdprefix = $_SESSION[ 'resources' ]->$useresource;
   } else {
      $cmdprefix = $_SESSION[ 'resources' ]->$_SESSION[ 'resourcedefault' ];
   }
   if ( strlen( $cmdprefix ) > 1 )
   {  
      $fileargs = 1;
   }
}


if ( sizeof( $_FILES ) )
{
   foreach ( $_FILES as $k=>$v )
   {
      if ( is_array( $v[ 'error' ] ) )
      {
         foreach ( $v[ 'error' ] as $k1=>$v1 )
         {
            if ( $v[ 'error' ][ $k1 ] )
            {
               if ( is_string( $v[ 'name' ][ $k1 ] ) && !strlen( $v[ 'name' ][ $k1 ] ) )
               {
                  $responseVar[ "error" ] = "missing file input for identifier " . $k;
               } else {
                  $responseVar[ "error" ] = "error uploading file " . $v[ 'name' ][ $k1 ] . " (possibly too large and, if so, php.ini and/or httpd.conf could be adjusted on the server)";
               }
               echo (json_encode($responseVar));
               exit();
            }
//            error_log( "move_uploaded_file( " . $v[ 'tmp_name' ][ $k1 ] . ',' .  $dir . '/' . $v[ 'name' ][ $k1 ] . "\n", 3, "/var/tmp/my-errors.log");
            if ( !move_uploaded_file( $v[ 'tmp_name' ][ $k1 ], $dir . '/' . $v[ 'name' ][ $k1 ] ) )
            {
               $responseVar[ "error" ] = "could not move file " . $v[ 'name' ][ $k1 ];
               echo (json_encode($responseVar));
               exit();
            }
            $_REQUEST[ $k ][] = $dir . '/' . $v[ 'name' ][ $k1 ];
         }
      } else {
         if ( $v[ 'error' ] )
         {
            if ( is_string( $v[ 'name' ] ) && !strlen( $v[ 'name' ] ) )
            {
               $responseVar[ "error" ] = "missing file input for identifier " . $k;
            } else {
               $responseVar[ "error" ] = "error uploading file " . $v[ 'name' ] . " (possibly too large and, if so, php.ini and/or httpd.conf could be adjusted on the server)";
            }
            echo (json_encode($responseVar));
            exit();
         }
//         error_log( "move_uploaded_file( " . $v[ 'tmp_name' ] . ',' .  $dir . '/' . $v[ 'name' ] . "\n", 3, "/var/tmp/my-errors.log");
         if ( !move_uploaded_file( $v[ 'tmp_name' ], $dir . '/' . $v[ 'name' ] ) )
         {
            $responseVar[ "error" ] = "could not move file " . $v[ 'name' ];
            echo (json_encode($responseVar));
            exit();
         }
         $_REQUEST[ $k ][] = $dir . '/' . $v[ 'name' ];
      }
   }
}

if ( sizeof( $_REQUEST ) )
{
    $keys = preg_grep( "/-/", array_keys( $_REQUEST ) );
    foreach ( $keys as $k => $v )
    {
        $a = preg_split( "/-/", $v );
        $_REQUEST[ $a[ 0 ] ][ $a[ 1 ] - 1 ] = $_REQUEST[ $v ];
        unset( $_REQUEST[ $v ] );
    }

    $json = json_encode( $_REQUEST );
    $json = str_replace( "'", "_", $json );
    ob_start();
    if ( !chdir( $dir ) )
    {
      $cont = ob_get_contents();
      ob_end_clean();
      $responseVar[ "error" ] = "could not create directory " . $dir . " " . $cont;
      echo (json_encode($responseVar));
      exit();
    }
    ob_end_clean();
    if ( isset( $fileargs ) )
    {
      ob_start();
      if (!file_put_contents( "_args", $json ) )
      {
         $cont = ob_get_contents();
         ob_end_clean();
         $responseVar[ "error" ] = "could not write _args for remote submission " . $cont;
         echo (json_encode($responseVar));
         exit();
      }
      ob_end_clean();
      $cmd = "$cmdprefix 'cd $dir;$cmd \"\$(< $dir/_args )\"'";
    } else {
      $cmd .= " '$json'";
    }

    $cmd .= " 2> $dir/_stderr";    
    if($cmdprefix == "_airavata"){
	ob_start();

	$projId=createProject("nadeem", "analyze_2");
	$expId=createExperiment("nadeem", "exp1", $projId, "analyze_2", $json);	
	$res = launchExperiment($expId);	
	while(($status=getExperimentStatus($expId))!="COMPLETED"){		
		sleep(1);
	}
	$outputs = getOutput($expId);	

	foreach ($outputs as $output)
	{
			error_log("$output->type: $output->value      <br><br>");
			$results = $output->value ;
	}
    }
    else{
	$results = exec( $cmd );
    }
		
    $results = str_replace( "/var/www/html/nadeemtest/", "", $results );
    if ( $do_logoff == 1 )
    {   
        $results = substr( trim( $results ), 0, -1 ) + ",\"_login\":\"\"}";
    }

    ob_start();
    file_put_contents( "$dir/_stdout", $results );
    ob_end_clean();

    ob_start();
    $test_json = json_decode( $results );
    if ( $test_json == NULL )
    {   
        $cont = ob_get_contents();
        ob_end_clean();
        if ( strlen( $results ) )
        {
            $responseVar[ "error" ] = "Malformed JSON returned from executable $cont";
            if ( strlen( $results ) > 1000 )
            {
                $responseVar[ "executable_returned_end" ] = substr( $results, 0, 450  ) . " .... " . substr( $results, -450 );
                $responseVar[ "notice" ] = "The executable return string was greater than 1000 characters, so only the first 450 and the last 450 are shown above.  Check $dir/_stdout for the full output";
            } else {
                $responseVar[ "executable_returned" ] = substr( $results, 0, 1000 );
            }
        } else {
            $responseVar[ "error" ] = "Empty JSON returned from executable $cont";
        }


        ob_start();
        $stderr = trim( file_get_contents( "$dir/_stderr" ) );
        $cont = ob_get_contents();
        ob_end_clean();
        $responseVar[ "error_output" ] = ( strlen( $stderr ) > 0 ) ? $stderr : "EMPTY";
        if ( strlen( $cont ) )
        {
            $responseVar[ "error_output_issue" ] = "reading _stderr reported $cont";
        }           

        echo (json_encode($responseVar));
        exit();
    }
    ob_end_clean();
} else {
    $responseVar[ "error" ] = "PHP code received no \$_REQUEST?";
    echo (json_encode($responseVar));
    exit();
}

// cleanup CURRENTLY DISABLED!
if ( sizeof( $_FILES ) )
{
   $files = new RecursiveIteratorIterator(
       new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
       RecursiveIteratorIterator::CHILD_FIRST
   );

   foreach ($files as $fileinfo) {
      $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
//      $todo( $fileinfo->getRealPath() );
   }
//   rmdir( $dir );
}
ob_end_clean();
echo $results;
?>
