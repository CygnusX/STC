<?php 	

require_once('functions_all.php');

//ensure a room is selected
if(empty($_SESSION['username']) || empty($_SESSION['password']))
{	
		
	$_SESSION['ErrorMsg'] = 'Your session has expired.  Please log back in.';
	header('Location: login.php');   
	exit;	
}

//record that user has logged out
$Page->make_Header();

?>

<script>

var socket  = io.connect('http://dppchat.jit.su:80');
//var socket    = io.connect('http://localhost:8080');

var username        = '<?php echo $_SESSION['username']; ?>';
var password        = '<?php echo $_SESSION['password']; ?>';

//set an ajax call here to refresh session
keepMeConnected = setInterval (stayConnected, 150000);

var gender          = '';
var numchatrequests = 0;
var numprevrequests = 0;

var listInterval = '';
username = username.toLowerCase();

var logindata = {};
logindata.username = username;
logindata.password = password;
logindata.loginlevel = 2;

//var l_rating = 'all';
var l_type   = 'a';

$(document).ready(function(){
	
	socket.on('connect', function(){
				
		//login
		socket.emit('login', logindata, function(data){
			
			if(typeof data != 'object')
			{				
				window.location = "login.php";
			}
			
			load_friends();		
			load_chatrequests();		
			//mood       = data.mood;       //for determining room type
			gender     = data.gender;
			maxfriends = data.maxfriends;
			age        = data.age;
		});		
	});

	//trying this as I seem to drop some user connections
	socket.on('reconnect', function(){
		
		//login
		socket.emit('login', logindata, function(data){
			
			if(typeof data != 'object')
			{				
				window.location = "login.php";
			}
			
			load_friends();		
			load_chatrequests();	
			//mood       = data.mood;       //for determining room type
			gender     = data.gender;
			maxfriends = data.maxfriends;
			age        = data.age;
		});	
	});
	
	
	//load friend list
	function load_friends()
	{
		socket.emit('getfriendlist', function(data){
			
			$(".friendsonline").empty();
			$(".friendsoffline").empty();
			
			var friendcount = 0;
			
			if(data == false)
			{	$(".friendsonline").html("<i>You have no saved friends</i>");		}
			else
			{
				var temponline = '';
				var tempoffline = '';	
				
			    for (key in data) {
				    
				    friendcount++;
				    
					temponline  = '<div class="frienddelete" id="' + key + '-on"> <div class="A4"><img src="images/user.png"><a href="script_getaroom.php?invite=' + key + '" target="_blank">' + key + '</a></div><div class="A5 t-right" id="cancel"><a href="#" id="friendremove" value="' + key + '"><img src="images/cancel.png"></a></div></div>';
					tempoffline	= '<div class="frienddelete" id="' + key + '-off"><div class="A4"><img src="images/user_offline.png"> <i>' + key + '</i></div><div class="A5 t-right" id="cancel"><a href="#" id="friendremove" value="' + key + '"><img src="images/cancel.png"></a></div></div>';										  
				    
				    if(data[key] == 1)
				    {						
						tempoffline = $(tempoffline).hide();						    		
						$(".friendsonline").append(temponline);															 													
						$(".friendsoffline").append(tempoffline);					    
				    }
				    else
				    {						
						temponline = $(temponline).hide();										  										  	
						$(".friendsonline").append(temponline);															 
						$(".friendsoffline").append(tempoffline);						   
				    }
			    }
			}
			
			var friendcountsummary = friendcount + '/' + maxfriends;
			$("#friendcountsummary").html(friendcountsummary);
			
		});	//end of socket.emit
	} //end of function load_friends		
	
	socket.on('updatefriends', function(){
		
		load_friends();		
	});
	
	socket.on('updatechatrequests', function(){
		
		load_chatrequests();		
	});	
		
	function load_chatrequests()
	{
		socket.emit('getchatrequests', function(requests){
				
			var r_name      = '';
			var r_interests = '';
			var r_age       = '';
			var r_gender    = '';
			
			try{				
				
				if(requests.length == 0 || typeof requests.length == 'undefined')
				{				
					$("#chatrequests").html('You currently have no chat requests');
					$("#invitealert").html('');
				}
				else
				{					
					var tempmsg = '(<b><span class="green">' + requests.length + '</span></b>)';
					$("#invitealert").html(tempmsg);
				}
				
												
				for(var i = 0; i < requests.length; i++)
				{			
					if(i == 0){
					
						$("#chatrequests").html('');
					}
					
					r_name      = requests[i].username;
					r_interests = requests[i].interests;
					r_age       = requests[i].age;
					r_gender    = requests[i].gender;
					r_roomid    = requests[i].roomid;
					
					if(i > 0)
					{	$("#chatrequests").append('<hr>');		}					
					
					$("#chatrequests").append('<div class="A1 bold top-offset">' + r_name + ' - ' + r_age + ' ' + r_gender + '</div>');
					$("#chatrequests").append('<div class="A2 t-right"><a href="chat.php?rid=' + r_roomid + '" target="_blank"><button class="button">Accept</button></a> <a href="#" id="declineinvite" value="' + r_name + '"><button class="button red">Decline</button></a></div>');
					$("#chatrequests").append('<div class="A3 small grey">' + r_interests + '</div>');
					$("#chatrequests").append('<br class="clear">');								
					
				}	
				
				numchatrequests = i;	
				
				if(numprevrequests != numchatrequests)
				{													
					if(numprevrequests == 0)
					{
						var chattitle = "(" + numchatrequests + ") Chat Requests";
						
						change_title(chattitle);
 						var a = setTimeout(function() { change_title('-Chat Request-'); }, 1000);
 						var b = setTimeout(function() { change_title(chattitle); }, 2000);							
					}					
					else
					{
						var chattitle = "(" + numchatrequests + ") Chat Requests";
						change_title(chattitle);						
					}
					
					numprevrequests = numchatrequests;
					
														
					if(numprevrequests == 0)
					{
						numprevrequests = -1;	
					}
					
				}
				
				//if number of requests has changed.....
				//and the previous number was 0....
				//blink
				//else, just update
				//and go back to -1 if current is 0
				
				
// 				change_title("STC - Occupied");
// 				var a = setTimeout(function() { change_title('-A Guest Has Entered-'); }, 1000);
// 				var b = setTimeout(function() { change_title('STC - Occupied'); }, 2000);				
				
										
			}
			catch(e)
			{	}
			
		});	//end of socket.emit
	} //end of function load_friends			
	
function load_listings()
	{
		socket.emit('getlistings', function(listings){
			
			try{					
				
				var listarray        = [];				
				var listings4m       = 0;
				var listings4f       = 0;
				var listings4a       = 0;			
				var listingsoccupied = 0;
			    							
				$.each(listings, function(key, value){

					if(listings[key].g2 == 'm' && listings[key].isOccupied == "0")
					{	listings4m++;	}
					
					if(listings[key].g2 == 'f' && listings[key].isOccupied == "0")
					{	listings4f++;	}
					
					if(listings[key].g2 == 'a' && listings[key].isOccupied == "0")
					{	listings4a++;	}							
					
					if(listings[key].isOccupied != "0")
					{	listingsoccupied++; }	
					
					if((listings[key].isOccupied == "0" && listings[key].isBanned == "0")){
						   
						//if(l_rating == listings[key].rating || l_rating == 'all'){
						   
							if((listings[key].g1 == l_type) || (l_type == 'a')){						
							
							var tempobj   = {}; 
							   
							tempobj.roomid 		= key;
							tempobj.currenttime = listings[key].currenttime;
							tempobj.g1          = listings[key].g1;
							tempobj.g2          = listings[key].g2;
							tempobj.interests   = listings[key].interests;
							tempobj.rating      = listings[key].rating;
							tempobj.roomid      = listings[key].roomid;
							tempobj.age         = listings[key].age;
							tempobj.listingtype = listings[key].listingtype;
															
							listarray.push(tempobj);
							
							}							
						//}							
					}
				});
				
				$('#num4m').html(listings4m);
				$('#num4f').html(listings4f);
				$('#num4a').html(listings4a);	
				$('#listingsoccupied').html(listingsoccupied);				
				
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
				
				var count = 0;
				
				//i will eventually need something here to filter by language
				
				for (var i = 0; i < listarray.length && count < 75; i++) {
																
					roomid            = listarray[i].roomid;						
					//rating            = listarray[i].rating;
					listingtype       = listarray[i].listingtype;			
					g1                = listarray[i].g1;
					g2                = listarray[i].g2;
					age               = listarray[i].age;	
									
					description = listings[roomid].interests;			
					
					if(g1 == "f")
					{	output += '<div class="A1">' + (i + 1) + '.</div><div class="A2"><a href="chat.php?rid=' + roomid + '" title="' + g1 + '4" target="_blank" class="pink">' + age + ' ' + g1 + '4' + g2 + ' - ' + description + '</a></div>';	}
					else
					{	output += '<div class="A1">' + (i + 1) + '.</div><div class="A2"><a href="chat.php?rid=' + roomid + '" title="' + g1 + '4" target="_blank">' + age + ' ' + g1 + '4' + g2 + ' - ' + description + '</a></div>';	}
						
					count++;
					
				}
						
				$('#listings').html(output);
			
			}
			catch(e)
			{	}
	
		});	
	}//end of load_listings
	
	function load_lobby()
	{
		socket.emit('getlobby', function(lobby){

			//lobby should return
			//socketusername (key)
			//g1
			//age
			//g2			
			//interests		
			//currenttime				
			//mood/rating		
			
			try{					
				
				var lobbyarray        = [];				
			    var usersinlobby      = 0;			
			    var form              = 0;
			    var forf			  = 0;
			    var fora              = 0;
							
				$.each(lobby, function(key, value){
					
					usersinlobby++;

					if(lobby[key].chatpreference == "m")
					{	form++;			}
					else if(lobby[key].chatpreference == "f")
					{	forf++;			}
					else
					{	fora++;			}										
					
					if((lobby[key].invitename != username) &&
					    (lobby[key].chatpreference == gender || lobby[key].chatpreference == "a") && 
					    (lobby[key].g1 == l_type || l_type == "a"))
					    //(lobby[key].mood == l_rating || l_rating == "all"))
					    {							   
							var tempobj   = {}; 
							   
							tempobj.invitename	= lobby[key].invitename;
							tempobj.currenttime = lobby[key].currenttime;
							tempobj.g1          = lobby[key].g1;						
							tempobj.interests   = lobby[key].interests;
							tempobj.age         = lobby[key].age;
																				
							lobbyarray.push(tempobj);
						}
				});
				
				$('#usersinlobby').html(usersinlobby);
				$('#form').html(form);
				$('#forf').html(forf);
				$('#fora').html(fora);
				
				lobbyarray.sort(function(a,b) { 			
					
						return b.currenttime - a.currenttime;												
				});
				
				var output      = '';
				var invitename  = '';
				var g1          = '';
				var age         = '';
				var interests   = '';
				
				var count = 0;
				
				//i will eventually need something here to filter by language
				
				if(lobbyarray.length == 0)
				{				
					output = "<i>No suitable users in the lobby could be found : (</i>";
				}
				
				for (var i = 0; i < lobbyarray.length && count < 75; i++) {					
								
						invitename  = lobbyarray[i].invitename;
						g1          = lobbyarray[i].g1;
						age         = lobbyarray[i].age;						
						interests   = lobbyarray[i].interests;
																	
						if(g1 == 'm')									
						{	output += '<div class="A1 bold top-offset"><span class="blue">' + invitename + ' - ' + age + ' ' + g1 + '</span></div><div class="A2 t-right"><a href="script_getaroom.php?invite=' + invitename + '" target="_blank"><button class="button">Invite to Chat</button></a></div><div class="A3 small grey">' + interests + '</div><br class="clear">';	}
						else
						{	output += '<div class="A1 bold top-offset"><span class="pink">' + invitename + ' - ' + age + ' ' + g1 + '</span></div><div class="A2 t-right"><a href="script_getaroom.php?invite=' + invitename + '" target="_blank"><button class="button">Invite to Chat</button></a></div><div class="A3 small grey">' + interests + '</div><br class="clear">';	}
						
						count++;
				}
						
				$('#lobby').html(output);
			
			}
			catch(e)
			{	}
	
		});	
	}//end of load_listings	
	
	
	$("#listingfilter").live('submit', function(e){
		
		e.preventDefault();
		
		//l_rating = $('#l_rating').val();
		l_type   = $('#l_type').val();
		
		load_listings();
			
	});
	
	$("#lobbyfilter").live('submit', function(e){
		
		e.preventDefault();
		
		//l_rating = $('#l_rating').val();
		l_type   = $('#l_type').val();
		
		load_lobby();					
	});		
		
	//add a friend handler
	$("#addfriend").live("click", function(e) {
		
		e.preventDefault();
		var friendname = prompt("Enter Friends Name:");
		
		if(!usernameIsValid(friendname))
		{
			alert("Invalid Name.  Names may only contain letters, numbers, underscore and hyphen");
			return;
		}		
		
		socket.emit('addfriend', friendname, function(data){
			
			if(data == true)
			{	load_friends();	}
			else
			{	alert(data);	}			
			
		});			
	});	//end of addfriend event
	
	//remove a friend click handler
	$(".frienddelete").live({
			
		mouseenter: function() { $(this).find("#cancel").show(); },
		mouseleave: function() { $(this).find("#cancel").hide(); }					
	});
	
	//remove a friend from your friend list
	$("#friendremove").live("click", function(e) {
				
		e.preventDefault();	
		var friendname = $(this).attr('value');
		
		socket.emit('removefriend', friendname, function(data){
		
			if(data == true)
			{	load_friends();	  }		
		});				
	});	

	//add a friend handler
	$("#openpublicroom").live("click", function(e) {
				
		e.preventDefault();	
		
		//URL = 'script_getaroom.php?type=' + mood;		
		URL = 'script_getaroom.php?type=l';		
		window.open(URL, '_blank');		
	});

	//remove a chat request
	$("#declineinvite").live("click", function(e) {
		
		e.preventDefault();	
		var invitename = $(this).attr('value');			
		
		socket.emit('declineinvite', invitename);
		
	});
		
	//add a friend handler
	$("#linky").live("click", function(e) {
		
		e.preventDefault();
		var location = $(this).attr('value') + ".php";			

		$.get(location, function(data) {
			
			clearInterval(listInterval);
						
			//$('#mainframe').html(data);
						
		  	$('#mainframe').load(location, function() {
		  				  			  
				if(location == 'p_myaccount.php')
				{
					socket.emit('loadprofile', function(data){
						
						$("#p_preference").val(data.chatpreference);
						$("#p_interests").val(data.interests);						
						$("#p_lobby").val(data.uselobby);
						$("#p_chatnotifications").val(data.chatnotifications);
						
						//determine age of user based on server date
						var date1     = new Date(data.serverdate);
						var date2     = new Date(data.dob.month + ', ' + data.dob.day + ', ' + data.dob.year);
						
						var trueday   = date1.getDate();
						var truemonth = date1.getMonth() + 1;
				
						var yearDiff = date1.getFullYear() - date2.getFullYear() - 1;			
						
						if((data.dob.month < truemonth) || (data.dob.month == truemonth && data.dob.day <= trueday))
						{	yearDiff++;	}
						
						var dateoutput = yearDiff + ' (' + data.dob.month + '/' + data.dob.day + '/' + data.dob.year + ')';
											
						$("#dob").html(dateoutput);
						$("#maxfriends").html(data.maxfriends);
						//$("#listingboost").html(data.listingboost);									
						
					});		
				}		
				else if(location == 'p_lobby.php')
				{
					load_lobby();					
					listInterval = setInterval (load_lobby, 45000);	    //Checks every 45 seconds to update lobby
			    }
				else if(location == 'p_home.php')
				{				
					load_friends();			
					load_chatrequests();
				}
				else if(location == 'p_listings.php');
				{				
					load_listings();			
					listInterval = setInterval (load_listings, 45000);	//Checks every 45 seconds to update listings	
				}
				
			});	//end of .load		  			 
			
		});	//end of get page event	
							
	});	//end of page change event	
	
	$("#profileform").live('submit', function(e){
		
		e.preventDefault();
		
		var chatpreference    = $('#p_preference').val();
		var interests         = $('#p_interests').val();	
		var uselobby          = $('#p_lobby').val();		
		var chatnotifications = $('#p_chatnotifications').val();		
				
		//make sure preference is ok m/f/a
		var validpreferences = ['m', 'f', 'a'];
		
		if(validpreferences.indexOf(chatpreference) < 0)
		{
			alert("Invalid chat preference");
			return;
		}
		
		//make sure interests don\'t exceed 250 characters
		if(interests.length > 250)
		{
			alert("Interests text must be 500 characters or less");
			return;
		}	
		
		var validlobbychoice = ["0", "1"];
		
		if(validlobbychoice.indexOf(uselobby) < 0)
		{
			alert("Invalid Lobby Choice");
			return;
		}
		
		var validchatnotifications = ["0", "1"];
		
		if(validchatnotifications.indexOf(chatnotifications) < 0)
		{
			alert("Invalid notification choice");
			return;
		}		
				
		var profiledata = {};
		profiledata.chatpreference    = chatpreference;
		profiledata.interests         = interests;
		profiledata.uselobby          = uselobby;
		profiledata.chatnotifications = chatnotifications;
		
		socket.emit('changeprofile', profiledata, function(data){
			
			if(data == true)
			{	alert("Profile Saved");	}
			else
			{	alert(data);			}			
			
		});
	});  //end of profile modify	
	
	
});	//end of document.ready

//helper functions
function usernameIsValid(username) {
    return /^[0-9a-zA-Z_-]+$/.test(username);
}

function stayConnected()
{
	$.get("stayconnect.php", function( data ) {
    
	});
}

function textCounter(field) 
{
	var maxlimit = 250;
	
	if (field.value.length > maxlimit) // if too long...trim it!
	{	field.value = field.value.substring(0, maxlimit);	}
	// otherwise, update 'characters left' counter
	else 
	{  
		var lcount = maxlimit - field.value.length;
		$("#textcount").html(lcount);	
	}
}

function change_title(newtitle)
{
	document.title = newtitle;		
}

</script>

	<div id="mainframe">
		
		<?php require('p_home.php'); ?>

	</div>

                                                                                                  
<?php 

$Page->make_Footer(); 

?>