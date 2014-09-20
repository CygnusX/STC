<?php

require_once('functions_all.php');

//check if get differs from session

if(!empty($_GET['username']) AND !empty($_GET['password']))
{
	$_SESSION['username'] = $_GET['username'];
	$_SESSION['password'] = MD5($_GET['password']);
	
	$arr = array('result' => true);
	echo json_encode($arr);	
	exit;	
}
elseif(!empty($_SESSION['username']) && !empty($_SESSION['password']))
{	
	$arr = array('result' => true);
	echo json_encode($arr);	
	exit;	
}

	$arr = array('result' => false);
	echo json_encode($arr);	
	exit;	

?>