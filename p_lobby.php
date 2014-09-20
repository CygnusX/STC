

		<div class="col-4">
			
			<span class="lobby">
	
			<h2>Options</h2>
			<hr>		
			<?php require('include_menu.php'); ?>
			
			<br class="clear">
				
			<h2>Filter</h2><hr>		
			
			<form id="lobbyfilter">
			<!--<div class="A1">Rating</div>
			<div class="A2 t-right">
				<select id="l_rating" class="select">
					<option value="sfw">SFW</option>
					<option value="nsfw">NSFW</option>
					<option value="all" selected>All</option>
				</select>		
			</div>
			
			<br class="clear">-->
			
			<div class="A1">Gender</div>
			<div class="A2 t-right">
				<select id="l_type" class="select">
					<option value="m">Male</option>
					<option value="f">Female</option>
					<option value="a" selected>Anyone</option>
				</select>		
			</div>	
	
			<br class="clear">
			
			<div class="A3 t-right">
				<button class="button">Filter</button>
			</div>	
			</form><br>
			
			<br>
			
			<h2>Stats</h2><hr>
			<div class="A1">Users In Lobby:</div> 
			<div class="A2 t-right" id="usersinlobby">0</div>
			
			<div class="A1">4M:</div> 
			<div class="A2 t-right" id="form">0</div>
			<div class="A1">4F:</div> 
			<div class="A2 t-right" id="forf">0</div>
			<div class="A1">4A:</div> 
			<div class="A2 t-right" id="fora">0</div>			
									

			</span>
	
		</div>
		
		
		<div class="col-5">
											
			<h2>Lobby</h2>
			<hr>			
			
			<span class="lobby">
			
			<div id="lobby"></div>
				
			</span>
			
		</div>	
