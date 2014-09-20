<?php 	

require_once('functions_all.php');

//record that user has logged out
$Page->make_Header();

?>

	<div class="col-3">
	<?php $Page->display_messages(); ?>
	
	<div style="min-height: 360px;">
		<span class="howitworks">
	
		<h2>Tutorial - Classic</h2><hr>

			The Classic version of Sexy Time Chat allows you to open your own private chatroom and invite other users to join it via a link.  
			As of March 2013, we now offer a full registration version that allows others to find you via the site and to save friends you've met.  
			Below is the tutorial for the Classic version of STC.  
		
			<br><br>
			
			<b>1</b> - Find the button below on the home page, and click it only after reading all the steps.<br><br>
			
			<form action="script_getaroom.php" method="post">
				<button class="blue_button" style="width: 150px; margin-left: 0px;">HOST A ROOM</button>
			</form>			
				
			<br class="clear">

			<b>2</b> - Once you click 'Host a Room', you will be placed in a private chatroom.  Copy the URL of that page and send it to a person you've met online in a community such as reddit so they may join you to chat.  The URL should look similar to the image below:<br><br> 
								
			<img src="images/step-2.png">
			
			<br class="clear"><br>
			
			<b>3</b> - Enjoy! You will be notified if a guest joins or leaves the room.  Room status will display as <span class="red">Unoccupied</span> if you are in it alone, or <span class="green">Occupied</span> if you have a guest.  Rooms may contain no more than 2 users at a time.  Hosts have the ability to banish users from their room if needed.<br><br>
					
			<img src="images/step-3.png">
			
			<br class="clear"><br>
			
		<h2>Tutorial - Using the Cam</h2><hr>
		
			<br>
			<img src="images/cam_on.png"> <b>Cam On</b> - This will allow you to view your partners stream as well as allow you to publish a stream.<br>
			<img src="images/stream_on.png"> <b>Stream On</b> - This publishes a video stream for your partner to view.<br>
			<img src="images/stream_off.png"> <b>Stream Off</b> - This terminates your video stream.  You will no longer be visible to your partner.<br>
			<img src="images/cam_off.png"> <b>Cam Off</b> - This stops you from viewing your partners stream.<br>
			
		<br>					
			
					
		</span>
		
		<br>
	</div>
	
	</div><!--end of col1-->
	                                                                                                    
<?php 

$Page->make_Footer(); 

?>