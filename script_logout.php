<?php

require_once('functions_all.php');

if(!empty($_GET['username']))
{	unset($_SESSION['username']);	}

if(!empty($_GET['password']))
{	unset($_SESSION['password']);	}

header('Location: index.php');   


?>