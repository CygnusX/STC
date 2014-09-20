<?php 	

require_once('functions_all.php');

$my_address = '15PxsHo4MuCvh178BAVwBBsCbEXMYvCbK7';

$root_url = 'http://blockchain.info/api/receive';

$parameters = 'method=create&address=' . $my_address .'&shared=false';

$response = file_get_contents($root_url . '?' . $parameters);

$object = json_decode($response);


//record that user has logged out
$Page->make_Header();

?>

	<div class="col-3">
	<?php $Page->display_messages(); ?>
	
	<div style="min-height: 360px;">
			
		<h2>FAQ - Classic</h2><hr>
		1. <a href="#qc1">I've read 'how this site works', but I still don't understand the concept</a><br>
		2. <a href="#qc2">Once I start a room, what do I do next?</a><br>
		3. <a href="#qc3">How many people can be in a room?</a><br>
		4. <a href="#qc4">Can I remove a guest if I do not want them in my room?</a><br>
		5. <a href="#qc5">What should I talk about with my guest?</a><br>
		6. <a href="#qc6">Can I format text in chat?</a><br>
		7. <a href="#qc7">How old must I be to use this service?</a><br>		
		8. <a href="#qc8">Can you recommend a webcam?</a><br>		
		9. <a href="#qc9">This site doesn't work for me.  Help!</a><br>		
		10. <a href="#qc10">The Cam feature doesn't work for me.</a><br>		
		11. <a href="#qc11">How can I send a picture to my chat partner?</a><br>		
		12. <a href="#qc12">Is there anyway I can donate to the site?</a><br>	
		13. <a href="#qc13">I have other questions / comments.  How can I get a hold of someone?</a><br>		
		
		<br>
		
		<h2>FAQ - Registered Users</h2><hr>
		1. <a href="#qr1">I've registered.  Now what?</a><br>
	    2. <a href="#qr2">How do listings work?</a><br>
	    2. <a href="#qr3">How does the lobby work?</a><br>
	    3. <a href="#qr4">What does my profile do?</a><br>
	    4. <a href="#qr5">How do I add a friend?</a><br>
	    5. <a href="#qr6">Why can I only have 3 friends?</a><br>
	    6. <a href="#qr7">How do I remove a friend?</a><br>
	    8. <a href="#qr8">What is a rating of SFW vs. NSFW?</a><br>
			    
	    <br>		

		<h2>Answers</h2><hr>			
		
		<a name="qc1"></a>
			<b>I've read 'how this site works', but I still don't understand the concept</b><br>
			
			Have you ever used Omegle?  Or Chatroulette? Or are you at least familiar with how those sites 
			work?  Sexy Time Chat is a conversation app that works in any modern browser.  It requires no downloads 
			and uses modern socket technology.  But, unlike Omegle or Chatroulette, you will not be joined
			at random with strangers.  Rather, you must solitict a partner through a community such as 
			<a href="http://www.reddit.com/r/sexytimechat">this one</a>, or by <a href="login.php">registering</a> 
			and opening a public room.		
					
			<br><br>
		
		<a name="qc2"></a>
			<b>Once I start a room, what do I do next?</b><br>
			
			There are two options.  If you do not register, you will be responsible for finding a guest interested in 
			chatting with you.  How you find such a guest is up to you, but to have them join you in chat, 
			simply share with them the full URL of the website once you are in a room.  See <a href="howitworks.php">The Tutorial</a> 
			for more details.  If you decide register, you may set your interests under 'my account' and then open
			a 'public room' to be added to the listening for others to contact you.
				
			<br><br>
	
		<a name="qc3"></a>	
			<b>How many people can be in a room?</b><br>
			
			Rooms are limited to 2 persons.  There is a host and a guest.  No more than these 2 people
			can ever occupy a room at a given time.  This is by design to allow for private conversations.<br><br>
			
			<b>How will I know if a guest has joined?</b><br>
			
			There are 2 key indicators for a guest being present.  First, the server will give you a
			message stating a guest has joined.  Second, there is a room status indicator to the right.
			It will read <span class="red">Unoccupied</span> if you do not have a guest present, and
			will read <span class="green">Occupied</span> if there is a guest in the room with you.
			Once the room is occupied, no other guest will be allowed to join.  Guest attempting
			to join a full room will receive a message stating that the room is already full.
		
			<br><br>
		
		<a name="qc4"></a>		
			<b>Can I remove a guest if I do not want them in my room?</b><br>
		
			Yes.  There is a 'remove guest' link to the right that is available to room hosts.  It will
			ban users based on IP address.  If you accidently ban a guest, simply refresh your browser
			and all bans for your room will be lifted.
						
			<br><br>
		
		<a name="qc5"></a>	
			<b>What should I talk about with my guest?</b><br>
		
			Anything, but keep it legal.  Also, we highly recommend talking with your chat partner and
			getting a feel for what they're interested in before requestings pics or to get on cam.  
			The quickest way to make a guest leave is to make request they do not want to fullfil.
								
			<br><br>			
		
		<a name="qc6"></a>		
			<b>Can I format text in chat?</b><br>
		
			Yes.  Formatting conventions are as follows:<br><br>
		
			**Bold** - makes enclosed text bold<br>
			~~Italic~~ - makes enclosed text italic<br>
			^^URL^^ - make a url clickable (does not work with ftp:// or https://)
					
			<br><br>							
		
		<a name="qc7"></a>	
			<b>How old must I be to use this service?</b><br>
		
			All users must be 16 or older to register and 18 or older to use or view the NSFW listings.
					
			<br><br>							
		
		<a name="qc8"></a>		
			<b>Can you recommend a webcam?</b><br>
			
			Yes.  If you would like to consider 
			<a href="http://www.amazon.com/gp/product/B002DW0VT8/ref=as_li_qf_sp_asin_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B002DW0VT8&linkCode=as2&tag=stc041-20" target="_blank">This One</a>,
			purchasing it through this link will cost you no additional money and will support our website.
					
			<br><br>				
		
			
		<a name="qc9"></a>			
			<b>This site doesn't work for me.  Help!</b><br>
		
			Please check the following.  1) You are useing the latest
			version of your prefered web browser.  2) You have javascript enabled.  3) You do not have
			any firewalls or antivirus programs that block port 80.  If you still need help,
			please contact us for further assistance.
		
			<br><br>					
		
		<a name="qc10"></a>	
			<b>The Cam feature doesn't work for me.</b><br>
		
			The most common reason the Cam doesn't work is because it requires version 11 or higher of
			flash.  Please try upgrading flash if the feature fails.  Also, the cam feature is not
			currently supported for smartphones nor for AIR on iOS.
						
			<br><br>		
			
		<a name="qc11"></a>					
			<b>How can I send a picture to my chat </b><br>
			There is no direct picture share on this site.  However, we can recommend <a href="www.imgur.com">imgur.com</a> for uploading and sharing photos.
			
			<br><br>	
			
			
		<a name="qc12"></a>	
			<b>Is there anyway I can donate to the site?</b><br>
			Yes.  We proudly accept bitcoins!  To donate, please use this address: <?php echo $object->input_address; ?>
			
		
			<br><br>	
			
			
		<a name="qc13"></a>	
			<b>I have other questions / comments.  How can I get a hold of someone?</b><br>
		
			Just <a href="http://www.reddit.com/r/sexytimechat">click here</a> and use the 'message the moderators' 
			feature.  
		
		<br><br>
	
			
		<a name="qr1"></a>	
			<b>I've registered.  Now what?</b><br>
		
			Now you will need to find someone to chat with.  Yon can either do this through the listings, by participating
			in the lobby, or by giving others your usename so they can chat request you directly. 
			<br><br>
			
		<a name="qr2"></a>	
			<b>How do listings work?</b><br>
		
			Opening a room while logged in puts that room on a public listing.  From here, all users (including non-registered 
			user) can find and join your room.  Once another user joins your room, it will be removed from the listing.  Rooms are
			sorted by most recently opened room on top, so if nobody joins your room for a long period of time,
			you may need to open a new room.
						
			<br><br>			
			
		<a name="qr3"></a>	
			<b>How does the lobby work?</b><br>
		
			If you choose to participate in the lobby, then other users can find you by profile description to invite you to chat.  
			This feature is currently being tested in hopes to replace the former logged-in listing system.
						
			<br><br>									
			
		<a name="qr4"></a>	
			<b>What does my profile do?</b><br>
		
			Profiles help other users determine who you are.  What you place in your profile provides the description for chat 
			requests, the lobby and listings..  As other users browse the listings or they lobby, what they will actually read 
			is your profile description.
						
			<br><br>	
			
		<a name="qr5"></a>	
			<b>How do I add a friend?</b><br>
		
			From the left menu, click chat request, then find the text "add" next to the column header for friends.  
			Click this, and type the name of your friend into the box.  At this time, there is no 'friend' button
			on the chat page.
						
			<br><br>									
				
		<a name="qr6"></a>	
			<b>Why can I only have 6 friends?</b><br>
		
			I am still not sure how much of a strain saving friend lists will place on my server, so for this portion of the
			beta, friends are limited to 6 per account.
						
			<br><br>				
			
		<a name="qr7"></a>	
			<b>How do I remove a friend?</b><br>
		
			Hover over the friends name on the chat requests page, and a red X should appear.  Click on this to remove
			your friend.
						
			<br><br>													
			
		<a name="qr8"></a>	
			<b>What is a rating of SFW vs. NSFW?</b><br>
		
			This stands for Safe for Work and Not Safe for Work.  This is to help seperate request for casual chat from
			those seeking more exotic talk.
						
			<br><br>						
			
			
	</div>
	
	</div><!--end of col1-->
	                                                                                                    
<?php 

$Page->make_Footer(); 

?>