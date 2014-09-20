<?php 	

require_once('functions_all.php');

//record that user has logged out
$Page->make_Header();

// if(!empty($_SESSION['isListing']) && $_SESSION['isListing'] == 1)
// {	
// 	unset($_SESSION['isListing']);
// 	unset($_SESSION['gender1']);
// 	unset($_SESSION['gender2']);
// 	unset($_SESSION['age']);
// 	unset($_SESSION['description']);
// }

?>

<script>
var socket    = io.connect('http://dppchat.jit.su:80');

//var socket    = io.connect('http://localhost:8080');

socket.on('connect', function(){

	setInterval (update_roomcount, 120000);	//Checks every 120 seconds to update number of room
	
});

socket.on('updateroomcount', function(data)
{	
	$("#roomcount").html(data);			
});

function update_roomcount()
{
	socket.emit('getroomcount');	
}

$(document).ready(function(){	
	update_roomcount();
});		
	


</script>

	<div class="col-3">
	<?php $Page->display_messages(); ?>	
	
	<!--<div class="redhat">
		<b>Error!</b> Chat servers are temporarily down.</a>
	</div>		

	<br class="clear">--->	
	
	<div class="index">
	
	
		<div class="A1">				
			<center> <b class="small">Classic</b> | <a href="listings.php">Listings</a> | <a href="login.php">Login</a>	
		
			<br class="clear"><br>
			
			<center>												
				<form action="script_getaroom.php" method="post">
					<button class="button_pink">HOST A ROOM</button>
				</form>
	
				<br>
				
				Rooms in Use: 
				<span id = "roomcount">0</span>
				
				<br><br>
			
			</center>
		
		
			</div>

	</div>	
	
	</div><!--end of col1-->
	                                                                                                    
<?php 

$Page->make_Footer(); 

?>