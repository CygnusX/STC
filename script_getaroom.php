<?php

ob_start();
require_once('functions_all.php');

$NewKey  = uniqid();

//uncomment this if i upgrade servers

// $LastKey = apc_fetch('key');

// if(!$LastKey)  //failed to get a key
// {	
// 	apc_store('key', $NewKey);	
// }
// else
// {
// 	while($NewKey == $LastKey)
// 	{
// 		$NewKey  = uniqid();
// 		$LastKey = apc_fetch('key');	
// 	}		
// 	
// 	apc_store('key', $NewKey);	
// }

$Identifier = 's';
$CastType   = '';
	
//if type is set, this is a listing
if(!empty($_GET['type']))
{
	$Cast       = 'listing';
	$Identifier = 'e';
}
elseif(!empty($_GET['invite']))
{
	$Identifier = 'i';
	$Cast       = $_GET['invite'];
}

$RoomID   = $Identifier.$NewKey;
$KeyCrypt = MD5($RoomID);	//WRAP $RoomID with JUNK TEXT TO PREVENT HASH GUESSING

setcookie($RoomID, $KeyCrypt);
$_SESSION[$RoomID] = $KeyCrypt;

$Temp    	       = 'cast_'.$RoomID;
$_SESSION[$Temp]   = $Cast; 

header('Location: chat.php?rid='.$RoomID);   
ob_end_flush();
exit;	

?>