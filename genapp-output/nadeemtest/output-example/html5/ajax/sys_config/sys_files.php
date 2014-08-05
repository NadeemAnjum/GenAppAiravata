<?php

session_start(); 
if ( !isset( $_SESSION[ 'logon' ] ) )
{
  echo '{}';
  exit();
}
session_write_close();

date_default_timezone_set("UTC");

$result_dirs = array();

function getDirectoryTree( $outerDir, $depth ) {
    global $result_dirs;

    $dirs = array_diff( scandir( $outerDir ), Array( ".", ".." ) );

    foreach( $dirs as $d ) 
    {
        $id = "$outerDir/$d";

        if( is_dir( $id ) )
        {
            array_push( $result_dirs, array( "id"     => $id,
                                             "parent" => ( $depth == 0 ? "#" : $outerDir ),
                                             "text"   => $d
                                             ) );
            getDirectoryTree( $id, $depth + 1 );
        } else {
            if ( substr( $d, -1 ) != "~" )
            {           
                $stat = stat( $outerDir."/".$d );

                $sz = $stat[ 'size' ];
                $sx = 'b';
                if ( $sz > 1024 )
                { 
                   $sz /= 1024;
                   $sx = "Kb";
                   if ( $sz > 1024 )
                   { 
                       $sz /= 1024;
                       $sx = "Mb";
                       if ( $sz >= 1024 )
                       { 
                           $sz /= 1024;
                           $sx = "Gb";
                           if ( $sz >= 1024 )
                           { 
                               $sz /= 1024;
                               $sx = "Tb";
                               if ( $sz >= 1024 )
                               { 
                                   $sz /= 1024;
                                   $sx = "Pb";
                               }
                           }
                       }
                   }
                   $sz = round( $sz, 2 );
                }
    
                $ss = "$d $sz$sx " . date( "Y M d H:i:s T", $stat[ 'mtime' ] );

                array_push( $result_dirs, array( "id"     => $id,
                                                 "parent" => ( $depth == 0 ? "#" : $outerDir ),
                                                 "text"   => $ss
                                                 ) );
            }
        }
    }
}

$dir = "/var/www/html/nadeemtest/results/users/";

ob_start();
if ( !chdir( $dir ) )
{
  ob_end_clean();
  echo '{}';
  exit();
}

getDirectoryTree( $_SESSION[ 'logon' ], 0 );
// print_r( $result_dirs );
ob_end_clean();
echo json_encode( $result_dirs );
?>
