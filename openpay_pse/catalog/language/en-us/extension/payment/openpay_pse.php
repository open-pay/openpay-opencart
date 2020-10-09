<?php
$new_file = str_replace( '/catalog/', '/admin/', $file );
if( file_exists( $file ) )
	require_once( $new_file );
?>