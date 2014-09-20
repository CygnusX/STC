<?php 	

require_once('functions_all.php');

//record that user has logged out
$Page->make_Header();

?>

<script>

var socket    = io.connect('http://dppchat.jit.su:80');
//var socket    = io.connect('http://localhost:8080');

var l_rating = 'sfw';
var l_type   = 'a';


$(document).ready(function(){	

	socket.on('connect', function(){
	
		load_listings();		
		listInterval = setInterval (reload_page, 45000);	
					
	});
		
});	
		
function load_listings()
{
	socket.emit('getlistings', function(listings){
		
		try{					
			
			var listarray        = [];				
			var listings4m       = 0;
			var listings4f       = 0;
			var listings4a       = 0;			
		    							
			$.each(listings, function(key, value){

				if(listings[key].g2 == 'm' && listings[key].isOccupied == "0")
				{	listings4m++;	}
				
				if(listings[key].g2 == 'f' && listings[key].isOccupied == "0")
				{	listings4f++;	}
				
				if(listings[key].g2 == 'a' && listings[key].isOccupied == "0")
				{	listings4a++;	}							
				
				var tempobj   = {}; 					

				if(listings[key].isOccupied == "0" && 
				   listings[key].isBanned == "0" &&
				   (listings[key].g1 == l_type || l_type == "a")){
				   							   														
					tempobj.roomid 		   = key;
					tempobj.currenttime    = listings[key].currenttime;
					tempobj.g1             = listings[key].g1;
					tempobj.g2             = listings[key].g2;
					tempobj.interests      = listings[key].interests;
					tempobj.rating         = listings[key].rating;
					tempobj.roomid         = listings[key].roomid;
					tempobj.age            = listings[key].age;
					tempobj.listingtype    = listings[key].listingtype;
					tempobj.chatpreference
													
					listarray.push(tempobj);
				}
			});
			
			$('#num4m').html(listings4m);
			$('#num4f').html(listings4f);
			$('#num4a').html(listings4a);			
			
			listarray.sort(function(a,b) { 			
				
				if(a.g1 < b.g1)
				{	return 1;	}
				else if(a.g1 > b.g1)  
				{	return -1;	}
				else
				{	return b.currenttime - a.currenttime;	}
										
			});
			
			var output      = '';
			var roomid      = '';
			var g1          = '';
			var g2          = '';
			var age         = '';
			var description = '';
			
			var count = 1;
			
			//i will eventually need something here to filter by language
			
			for (var i = 0; i < listarray.length && count <= 75; i++) {
															
				roomid            = listarray[i].roomid;						
				rating            = listarray[i].rating;
				listingtype       = listarray[i].listingtype;			
				g1                = listarray[i].g1;
				g2                = listarray[i].g2;
				age               = listarray[i].age;	
									
				description   = listarray[i].interests; 						
													
				if(g1 == "f")
				{	output += '<div class="A1">' + (count) + '.</div><div class="A2"><a href="chat.php?rid=' + roomid + '" title="' + g1 + '4" target="_blank" class="pink">' + age + ' ' + g1 + '4' + g2 + ' - ' + description + '</a></div>'; }
				else
				{	output += '<div class="A1">' + (count) + '.</div><div class="A2"><a href="chat.php?rid=' + roomid + '" title="' + g1 + '4" target="_blank">' + age + ' ' + g1 + '4' + g2 + ' - ' + description + '</a></div>'; }
					
				
				count++;
								
			}
					
			$('#listings').html(output);
		
		}
		catch(e)
		{	}

	});	
}//end of load_listings			

$("#listingfilter").live('submit', function(e){
	
	e.preventDefault();
	
	l_type   = $('#l_type').val();	
	load_listings();		
});

function reload_page()
{		
		
	load_listings();
	$('#sponsors').html('<center><iframe scrolling="no" style="border: 0; width: 250px; height: 250px;" src="https://coinurl.com/get.php?id=6528&SSL=1"></iframe></center>');
	
}



</script>



<div class="col-4">

	<span class="listings">

	<h2>About</h2><hr>		
	Registered users can create a room to add to the listings.
	Guests may join a room, but cannot create one.
	
	<br class="clear"><br>
	
	<h2>Filter</h2><hr>		
	
	<form id="listingfilter">
	
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
	
	<br class="clear"><br><br>
			
	<h2>Sponsors</h2><hr>
	
	<span id="sponsors">
		<center>
			<iframe scrolling="no" style="border: 0; width: 250px; height: 250px;" src="https://coinurl.com/get.php?id=6528&SSL=1"></iframe>
		</center>
	</span>
	
	</span>
</div>	

<div class="col-5">

	<h2>Open Rooms</h2><hr>
	<br>

	<span id = "listings" class="listings">
	</span>	

</div>	

                                                                                                  
<?php 

$Page->make_Footer(); 

?>

