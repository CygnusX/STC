<?php

class dbCall
{
	
function __construct()
{	$this->db_connect();	}

function db_connect()
{
	
	$OnServer = 0;
	
	if($OnServer == 0)
	{	
		//establishes a database connection
		$Result = mysql_connect("ADD PRIVATE DATA HERE") or die ("Couldn't connect to server."); 
		
		if (!$Result)
		return false;
		if (!mysql_select_db("ADD PRIVATE DATA HERE"))
		return false;
		
		return $Result;	
	}
	else
	{
		$Result = mysql_connect ("ADD PRIVATE DATA HERE") or die;
		
		if (!$Result)
		{  return false;  }
		
		if (!mysql_select_db("ADD PRIVATE DATA HERE"))
		{  return false;  }
		
		return $Result;		
	}

  
}

function sanitize($Query)
{
	if(empty($Query))
	{	return;		}	
	
	$Query = mysql_real_escape_string($Query);
	return $Query;	
}

function query($Query)
{		
	$Result = mysql_query($Query) or die(mysql_error());
	
	return $Result;		
}

function fetch($Result)
{
	$Row = mysql_fetch_assoc($Result);
	
	return $Row;		
}

function num_Rows($Result)
{
	$Num = mysql_num_rows($Result);
	return $Num;	
}

function get_Last_Auto_ID()
{
	$LastID = mysql_insert_id();
	return $LastID;
	
}

}


?>