<?php session_start();		
				unset( $_SESSION['wp_user_name'] );
				unset( $_SESSION['wp_user_id'] );
				echo 'logout';

?>