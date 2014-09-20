<?php

//all i'm giong to do here is get a uniqid(), put it in the url and forward

ob_start();

require_once('functions_all.php');

//get 'who i am'
if(empty($_POST['gender1']) || ($_POST['gender1'] <> 'M' && $_POST['gender1'] <> 'W'))
{
	$_SESSION['ErrorMsg'] = 'Invalid Selection Gender 1';
	header('Location: listings.php');   	
	exit;
} 

//get 'who i want'
if(empty($_POST['gender2']) || ($_POST['gender2'] <> 'M' && $_POST['gender2'] <> 'W' && $_POST['gender2'] <> 'A'))
{
	$_SESSION['ErrorMsg'] = 'Invalid Selection Gender 2';
	header('Location: listings.php');   	
	exit;
}

//get age
if(empty($_POST['age']) || !is_numeric($_POST['age']) || $_POST['age'] < '18' || $_POST['age'] > '65')
{
	$_SESSION['ErrorMsg'] = 'Invalid Age';
	header('Location: listings.php');   	
	exit;
}

//get description
if(empty($_POST['description']))
{	
	$_SESSION['ErrorMsg'] = 'Invalid Description';
	header('Location: listings.php');   	
	exit;
}	

//make sure description length is ok
if(strlen($_POST['description']) < 6 || strlen($_POST['description']) > 180)
{	
	$_SESSION['ErrorMsg'] = 'Invalid description length.  Description must be between 6 and 180 characters';
	header('Location: listings.php');   	
	exit;
}	

///open a room and send post data
$_SESSION['isListing'] = 1;
$_SESSION['gender1'] = $_POST['gender1'];
$_SESSION['gender2'] = $_POST['gender2'];
$_SESSION['age'] = $_POST['age'];
$_SESSION['description'] = mysql_real_escape_string($_POST['description']);

header('Location: script_getaroom.php');   
ob_end_flush();
exit;	

?>