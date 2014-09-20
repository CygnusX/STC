<?php

session_start();

global $_SESSION;
global $_POST;
global $_GET;

require_once('class_html.php');
require_once('class_db.php');


$db   = new dbCall;
$Page = new Page();
$Page->set_DB($db);



?>