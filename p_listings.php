
	<div class="col-4">
	
		<span class="listings">

		<h2>Options</h2>
		<hr>	
		<?php require('include_menu.php'); ?>
		
		<br>
		
		<h2>Filter</h2><hr>		
		
		<form id="listingfilter">
		<!--<div class="A1">Rating</div>
		<div class="A2 t-right">
			<select id="l_rating" class="select">
				<option value="sfw">SFW</option>
				<option value="nsfw">NSFW</option>
				<option value="all" selected>All</option>
			</select>		
		</div>
		
		<br class="clear">-->
		
		<div class="A1">Type</div>
		<div class="A2 t-right">
			<select id="l_type" class="select">
				<option value="m">M4</option>
				<option value="f">F4</option>
				<option value="a" selected>A4</option>
			</select>		
		</div>		
		
		<br class="clear">
		
		<div class="A3 t-right">
			<button class="button">Filter</button>
		</div>	
		</form><br>
		
		<br>
		
		<h2>Stats</h2><hr>
		
		<div class="A1">4M Listings:</div>
		<div class="A2 t-right" id="num4m"></div>
		
		<div class="A1">4F Listings:</div>
		<div class="A2 t-right" id="num4f"></div>
		
		<div class="A1">4A Listings:</div>
		<div class="A2 t-right" id="num4a"></div>

		
		</span>
	</div>	
	
	<div class="col-5">
	
		<h2>Listings</h2><hr>
		<br>

		<span id = "listings" class="listings">
		</span>	
	
	</div>	