<?php 
	session_start();
?>

	<div class="col-4">

		<span class = "home">

		<h2>Options</h2>
		<hr>		
		<?php require('include_menu.php'); ?>
		
		<br>
		
		<h2>Your Profile</h2>
		<hr>						
		Username: <?php echo $_SESSION['username']; ?><br>
		Age: <span id="dob">19  (6/12/18)</span><br>
		
		<br>
		
		<h2>Stats</h2>
		<hr>		
		Max Friends: <span id="maxfriends">3</span><br>
		<!--Upgrade!<br><br>-->
		
		<!--Listing Priority: +<span id="listingboost">0</span><br>-->
		<!--Upgrade!<br><br>-->
		
		</span>

	</div>
	
	<div class="col-5">
	
		<span class="home">

		<h2>Account Settings</h2><hr>		
				
		<form id="profileform">
		<div class="A1">Chat Preference</div>
		<div class="A2 t-right">
			<select style="width: 154px;" id="p_preference" class="select">
				<option value="m">A Man</option>
				<option value="f">A Woman</option>
				<option value="a">Anybody</option>				
			</select>					
		</div>
		
		<br>
		
		<div class="A1">Interests</div>
		<div class="A2 t-right" id="textcount">250</div>
				
		<br class="clear">
		
		<div class="A3"><textarea id="p_interests" name="p_interests" onKeyDown="textCounter(this.form.p_interests);" onKeyUp="textCounter(this.form.p_interests);" class="input"></textarea></div><br>
		
		<div class="A1">Participate in the Lobby</div>
		<div class="A2 t-right">
			<select style="width: 154px;" id="p_lobby" class="select">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>					
		</div>		
		
		<div class="A1">Enable Chat Notifications</div>
		<div class="A2 t-right">
			<select style="width: 154px;" id="p_chatnotifications" class="select">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>					
		</div>								
		
		<br>
		
		<div class="A3 t-right"><button class="button">Save</button></div>		
		</form>
		
		<br>
	
		<h2>About Settings</h2><hr>			
		Your 'interests' will be displayed to other users on this site.  Please use this feature to tell other users 
		about yourself, what you are here for and what you'd like to chat about.<br><br>
		
		Participating in the lobby will allow other users (even those you are not friends with) to see that you are online. 
		This will also signal that you wish to receive chat requests.<br><br>
		
		<br><br>
		
		</span>
	</div>	
