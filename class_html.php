<?php

class Page
{

function __construct()
{
	//not quite sure why I'd want to define keywords here.... i guess its ok?  Need to set some defaults.
	$this->KeyWords = 'Talk to Strangers, Chat with Strangers';
	$this->Description = "Sexytimechat is a free service that lets you talk to strangers. We give you the chance to have private, personal conversations with strangers. Don't waste time being paired at random, choose your chat partner based on their profile.";
	$this->Forward = '';
	$this->Title = 'Talk to Strangers';	
}

function set_DB($db)
{
	$this->db = $db;		
}

function set_User($User)
{
	$this->User = $User;
}

function make_Header($Type)
{

	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo $this->Title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="description" content="<?php echo $this->Description; ?>" />
<meta name="keywords" content="<?php echo $this->KeyWords; ?>" />
<meta name="google-site-verification" content="rEIkJZIiEb3KB8rHf9NfimdQeodixy5_yn_pJk-JMPM" />
<link href="style.css" rel="stylesheet" type="text/css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
<script src="http://dppchat.nodejitsu.com/socket.io/socket.io.js"></script>

<?php
if($Type == '')
{
?>
	<script src="http://static.opentok.com/v0.91/js/TB.min.js"></script>
<?php
}
else
{
?>

	<script src="https://swww.tokbox.com/webrtc/v2.0/js/TB.min.js" type="text/javascript"></script>
<?php
}
?>

<script type="text/javascript" src="https://blockchain.info//Resources/wallet/pay-now-button.js"></script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-31982221-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</head>

<body>

	<div id="wrapper">
	<div id="tail-top">
	<div id="tail-bottom">
	
	
    <div id="main">
      <!-- header -->
      <div id="header">     
    	<div class="logo">
          	<a href="index.php">
          		<h1><img src="images/Logo.png" alt="Sexy Time Chat: Talk to Strangers"></h1>          		
          	</a>
        </div>
        
<?php

if(empty($_SESSION['user_id']))
{        
?>       
        <div class="nav">

        </div>
               
        <div class="login">
        </div>        
        
<?php
}
else
{
?>	
        <div class="nav">
   
        </div>

        <div class="account">     
        	<b><?php echo $this->User->UserName; ?></b>
        	(<a href="script_logout.php">Logout</a>)
        </div>           
        
		<br class="clear">

<?php

} 
?>       

	  </div> 
	  <br class="clear"><br>
	  
      <!-- content -->  
	  <div id="content">  
	  
<?php
}


function make_Footer()
{
			//clear any error messages that may have been generated
			unset($_SESSION['ErrorMsg']);
			unset($_SESSION['GoodNews']);
			unset($_SESSION['AlertMsg']);
	
?>

		      </div>
		      
		  <br class="clear">  		      
		      <!-- footer -->
		
		  <div id="footer">
		  
		    <div class="block"><a href="index.php">Home</a></div>
		  	<div class="block b-left"><a href="howitworks.php">Tutorial</a></div>
		  	<div class="block b-left"><a href="faq.php">FAQ</a></div>
		  	<div class="block b-left"><a href="#">Your Privacy</a></div>  				  	
		  	<div class="block b-left"><a href="login.php">Register</a></div>
		  	<div class="block b-left"><a href="http://www.reddit.com/message/compose?to=%2Fr%2Fsexytimechat" target="_blank">Contact Us</a></div>
		  	<div class="block b-left"><a href="http://www.reddit.com/r/sexytimechat" target="_blank">Visit r/SexyTimeChat (on Reddit)</a></div>
		  	
		  	<br class="clear">
		  	
		  	<div id="aboutus" class="aboutus">		  				  		
		  		<!--Talk to Strangers, Chat with Strangers-->
		  		We run on Bitcoin.  To donate: 1EVvjyoQ7ujDwhuWWaYi1cgZPWrArYnhb5
		  	</div>
		  	
			<br class="clear"><br>
		  	
		  				  	
		  </div>
		      	  		       
    </div>    
    </div>
    </div>
    </div>
    
</body>
</html>

<?php
}

function login()
{

	//disabeling for now
}


function secure_Page()
{
	//this needs to be redone
}


function display_messages()
{		
	$this->display_GoodNews(); 	
	$this->display_Error(); 			
	$this->display_Alert();
}


function display_Error()
{	
	if(!empty($_SESSION['ErrorMsg']))
	{			
?>				
		
	<div class="redhat">
		<b>Error!</b>  <?php echo $_SESSION['ErrorMsg']; ?>
	</div>		

	<br class="clear">	
		
<?php

	}
}

function display_Alert()
{
	
	if(!empty($_SESSION['AlertMsg']))
	{			
?>				
		
	<div class="alerthat">
		<b>Alert!</b>  <?php echo $_SESSION['AlertMsg']; ?>
	</div>		

	<br class="clear">	
		
<?php

	}
}

function display_GoodNews()
{
	
	if(!empty($_SESSION['GoodNews']))
	{			
?>				
		
	<div class="greenhat">
		<b>Success!</b> <?php echo $_SESSION['GoodNews']; ?>
	</div>		

	<br class="clear">	
		
<?php

	}
}

function make_li_menu()
{
?>	
	

	
<?php	
}


}//end of class			

function display($Var)
{
	 //this needs to be changed
	 echo htmlentities($Var);
}

function make_Key()
{	
	return substr(sha1(microtime(true).mt_rand(10000,90000)), 0, 12);		
}






?>