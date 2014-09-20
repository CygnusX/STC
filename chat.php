<?php 	

//ob_start();
require_once('functions_all.php');

//ensure a room is selected
if(empty($_GET['rid']))
{	
	$_SESSION['ErrorMsg'] = 'Invalid Room ID';
	header('Location: index.php');   
	exit;	
}

//set some vars
$RoomID = $db->sanitize($_GET['rid']);
$KeyCrypt = '';

if(isset($_SESSION[$RoomID]))
{	$KeyCrypt = $_SESSION[$RoomID];		}

if(isset($_COOKIE[$RoomID]))
{	$KeyCrypt = $_COOKIE[$RoomID];		}

ob_end_flush();

//record that user has logged out

$Page->Title = 'STC - Unoccupied';	
$Page->make_Header();

?>

<script>

//var socket    = io.connect('http://dppchat.jit.su:80');
var socket    = io.connect('http://dppchat.nodejitsu.com');
//var socket    = io.connect('http://localhost:8080');

//listing data//
var passport  = {};
var logindata = {};
logindata.username = ''; 
var myname    = 'you';

<?php

$Temp     = 'cast_'.$RoomID;
$RoomType = 'default';

//this overwrites listing information, if available
if(!empty($_SESSION[$Temp]))
{
	$RoomType = $_SESSION[$Temp];			
}

?>

//passport data//
passport.roomid   = '<?php echo $RoomID; ?>';
passport.keycrypt = '<?php echo $KeyCrypt; ?>';
roomtype          = '<?php echo $RoomType; ?>';

var apiKey = 20529252;
var sessionId;
var token;
var vc_enabled = 0;
var is_streaming = 0;
var friendname = '';

//opentok api//
var session;
var publisher;
var subscribers = {};
var VIDEO_WIDTH = 320;
var VIDEO_HEIGHT = 180;
var isStreaming  = 0;
///end of opentok api//

var iAmTyping         = 0;
var isOccupied        = 0;
var chatnotifications = 1;

var window_focus;

$(window).focus(function() {
    window_focus = true;
    
    if(isOccupied == 0)
	{	change_title("STC - Unoccupied");    	}
	else
	{	change_title("STC - Occupied");   }	
    
})
    .blur(function() {
        window_focus = false;
    });
	
<?php

$Temp = 'cast_'.$RoomID;

if(!empty($_SESSION['username']))
{
		
?>

var username = '<?php echo $_SESSION['username']; ?>';
var password = '<?php echo $_SESSION['password']; ?>';
var myname   = username;

<?php 
	if(!empty($_SESSION[$Temp]))
	{
?>


var roomtype = '<?php echo $_SESSION[$Temp]; ?>';


		
<?php
	}
?>

logindata.username   = username.toLowerCase();
logindata.password   = password;
logindata.loginlevel = 3;



// listener
socket.on('connect', function(){

	//login
	socket.emit('login', logindata, function(data){
		
		if(typeof data != 'object')
		{				
			window.location = "login.php";
			return;
		}	
		
		chatnotifications = data.chatnotifications;		
		
		// call the server-side function 'adduser'
		socket.emit('adduser', passport, roomtype);	

	});
		
});		
				
<?php
}
else
{
?>
				
// listener
socket.on('connect', function(){

	// call the server-side function 'adduser'
	socket.emit('adduser', passport, roomtype);	

});	
		
<?php
}
?>			
		
// listener
socket.on('updatechat', function (username, signal, data) {
	
	//this is a one-time check to capture the chat partners name
	if(username != logindata.username && username != 'you' && username != 'them')
	{	friendname = username;		}
	
	$("#chatbox").append('<b>' + username + ':</b> ' + bbcode_parser(data) + '<br>').scrollTop($("#chatbox")[0].scrollHeight);
	
	if(chatnotifications == 1)
	{
		if(window_focus == false)
		{
			change_title("New Message");
			var a = setTimeout(function() { change_title('<3<3<3'); }, 1000);
			var a = setTimeout(function() { change_title('New Message'); }, 2000);
		}				
	}	
	
});

// listener
socket.on('servermsg', function (data) {
	$("#chatbox").append('<i>' + data + '</i><br>').scrollTop($("#chatbox")[0].scrollHeight);;	
});

// listener that updates if your or guest is typing
socket.on('updatetyping', function (data) {
	
	if(data == 1)
	{				
		$("#typing").html('<i>Typing...</i>');						
		
		if(isOccupied == 0)
		{	
			isOccupied = 1;
			$("#roomstatus").html('<span class="green">Occupied</span>');		
			change_title("STC - Occupied");
			var a = setTimeout(function() { change_title('-A Guest Has Entered-'); }, 1000);
			var b = setTimeout(function() { change_title('STC - Occupied'); }, 2000);
		}				
	}
	else
	{	$("#typing").html('');	}
		
});	

// listener that loads the menu based on your login
socket.on('setmenuoptions', function (data) {
	
	$("#menu").load(data);
	$('#connectLink').show();
	$('#disconnectLink').hide();
	$('#publishLink').hide();
	$('#unpublishLink').hide();			
			
});		

//listener that updates room count
socket.on('updateroomcount', function(data){
		
	$("#roomcount").html(data);
			
});

//listener that updates if room is occupied or unoccupied
socket.on('roomstatus', function(data){
		
	if(data == '1')
	{			
		if(isOccupied == 0)
		{	
			playSnd();	
			change_title("STC - Occupied");
			var a = setTimeout(function() { change_title('-A Guest Has Entered-'); }, 1000);
			var b = setTimeout(function() { change_title('STC - Occupied'); }, 2000);
		}
				
		isOccupied = 1;
		$("#roomstatus").html('<span class="green">Occupied</span>');  								
	}
	else
	{	
		isOccupied = 0;
		$("#roomstatus").html('<span class="red">Unoccupied</span>');  
		document.title = "STC - Unoccupied";
	}
	
});

// socket.on('disconnect', function(){
// 
// 	$("#chatbox").append('<i>' + "Your connection to the server has been interupted" + '</i><br>').scrollTop($("#chatbox")[0].scrollHeight);;	
// 	socket.disconnect();
// 		
// });		

//listener that initializes web session
socket.on('opentok_initialize', function(data){
			
	sessionId = data.sessionId;
	token = data.token;
	vc_enabled = 1;
	
	TB.addEventListener("exception", exceptionHandler);
	
	//debug 1

	if (TB.checkSystemRequirements() != TB.HAS_REQUIREMENTS) {
		alert("You lack the minimum requirements to use our webcam feature. "
			  + "Please upgrade to the latest version of Flash.");
	} else {
		session = TB.initSession(sessionId);	// Initialize session

		// Add event listeners to the session
		session.addEventListener('sessionConnected', sessionConnectedHandler);
		session.addEventListener('sessionDisconnected', sessionDisconnectedHandler);
		session.addEventListener('connectionCreated', connectionCreatedHandler);
		session.addEventListener('connectionDestroyed', connectionDestroyedHandler);
		session.addEventListener('streamCreated', streamCreatedHandler);
		session.addEventListener('streamDestroyed', streamDestroyedHandler);
		//publisher.addEventLisntener("accessDenied", accessDeniedHandler);
	}
	
});

//to kick a guest
function removeguest(){
	
	socket.emit('removeguest');				
	
	//this should have a callback of a streamID
	
	//force opentok dc
	//session.forceDisconnect(subscribers[streamId].stream.connection);
	
}

//updates count of rooms
function update_roomcount()
{
	socket.emit('getroomcount');	
}

//for chat entry
$(document).ready(function(){
					
	//If user submits the form
	$('#mform').submit(function(event){				
		event.preventDefault();
		post_message();	
	});
	
	listInterval = setInterval (reload_ads, 120000);	
		
});

//used for new text area submission
function enter(evt)
{	
	var charCode = (evt.which) ? evt.which : window.event.keyCode; 
 	
    if (charCode == 13) 
    { 	
	    evt.preventDefault();
	    post_message();		
	} 
}

function addfriend(){
		
	if(logindata.username == '')
	{
		alert("Only registered users can save friends");
		return;	
	}
		
	if(friendname != '')
	{	
		socket.emit('addfriend', friendname, function(data){
			
			if(data == true)
			{	
				alert("You are now friends with " + friendname);
			}
			else
			{	alert(data);	}			
			
		});	
	}
	else
	{
		alert("Your chat partner does not appear to be registered");
		return;			
	}
	
}


//function for posting a message
function post_message()
{	
	var clientmsg = $.trim($("#usermsg").val());
				
	if(clientmsg != '' && clientmsg != null)
	{
 		$("#chatbox").append('<b>' + myname + ':</b> ' + bbcode_parser(clientmsg) + '<br>').scrollTop($("#chatbox")[0].scrollHeight);	
		socket.emit('sendchat', clientmsg);		
	}
	
	//$("#usermsg").attr("value", "");  //removes all text from input field		
	$("#usermsg").focus();
	$("#usermsg").val('');
	
	update_roomcount();		
}

//listener
//this cycles to check if isTyping changes state, and if so, alerts the other users
function checkInput(){	
	var textbox_text = $("#usermsg").val(); 

	if((textbox_text !== '') && (iAmTyping == 0))
	{	  		
		iAmTyping = 1;					
		socket.emit('updatetyping', '1');	
	}
	else if((textbox_text == '') && (iAmTyping == 1))
	{					
	    iAmTyping = 0;
	    socket.emit('updatetyping', '0');		
	}		
}

function change_title(newtitle)
{
	document.title = newtitle;		
}


//bbcode client side
function bbcode_parser(str) {
	
str = $('<div/>').text(str).html();	
	
search = new Array(
      /\*\*(.*?)\*\*/g,  
      /\~~(.*?)\~~/g,
      /\^\^http:\/\/(.*?)\^\^/g,
      /\^\^(.*?)\^\^/g);

replace = new Array(
      "<b>$1</b>",
      "<i>$1</i>",
      "<a href=\"http:\/\/\$1\" target=\"_blank\">$1</a>",
      "<a href=\"http:\/\/\$1\" target=\"_blank\">$1</a>");

for (i = 0; i < search.length; i++) {
    str = str.replace(search[i], replace[i]);
}

return str;}

setInterval (checkInput, 333);	//Checks every .333 seconds to see if the user is typing	
setInterval (update_roomcount, 180000);	//Checks every 180 seconds to update number of room

//////////////////
///OPEN TOK API///
//////////////////


//--------------------------------------
//  LINK CLICK HANDLERS
//--------------------------------------

function connect() {
	
	if(vc_enabled == 1)
	{	
		session.connect(apiKey, token);
		vc_enable = 0;				
	}
}

function disconnect() {
	session.disconnect();
}

// Called when user wants to start publishing to the session
function startPublishing() {
	
	if (!publisher) {
		var parentDiv = document.getElementById("myCamera");
		var publisherDiv = document.createElement('div'); // Create a div for the publisher to replace
		publisherDiv.setAttribute('id', 'opentok_publisher');
		parentDiv.appendChild(publisherDiv);
		var publisherProps = {width: VIDEO_WIDTH, height: VIDEO_HEIGHT};
		publisher = TB.initPublisher(apiKey, publisherDiv.id, publisherProps);  // Pass the replacement div id and properties
		session.publish(publisher);
	}	
}

function stopPublishing() {
	
	if (publisher) {				
		session.unpublish(publisher);			
	}	
}

	//--------------------------------------
	//  OPENTOK EVENT HANDLERS
	//--------------------------------------

	function sessionConnectedHandler(event) {
		// Subscribe to all streams currently in the Session
		for (var i = 0; i < event.streams.length; i++) {
			addStream(event.streams[i]);			
		}
		
		//callback for connect(); 
		//if this fires, obviously im connected
		
		//callback that hides the connectLink and only allows disconnect once any session is connected
		$('#connectLink').hide();
		$('#disconnectLink').show();		
		reload_ads();
		
		if(is_streaming == 0)
		{	$('#publishLink').show();	}
		
		vc_enable = 0;
	}

	function streamCreatedHandler(event) {
		// Subscribe to the newly created streams
		
		var foundme = 0;
		
		for (var i = 0; i < event.streams.length; i++) {
			addStream(event.streams[i]);
			
			if (event.streams[i].connection.connectionId == session.connection.connectionId)
			{	foundme = 1;	}									
		}

		if(foundme == 0)
		{
			$('#connectLink').hide();
			$('#disconnectLink').show();
			$('#publishLink').show();
			$('#unpublishLink').hide();
			is_streaming = 0;
			reload_ads();
		}		
		else
		{
			$('#connectLink').hide();
			$('#disconnectLink').show();
			$('#publishLink').hide();
			$('#unpublishLink').show();
			is_streaming = 1;
			reload_ads();
		}				
	}

	function streamDestroyedHandler(event) {
		// This signals that a stream was destroyed. Any Subscribers will automatically be removed.
		// This default behaviour can be prevented using event.preventDefault()					
		var foundme = 0;			
		
		for (var i = 0; i < event.streams.length; i++) {			
			if (event.streams[i].connection.connectionId == session.connection.connectionId)
			{	foundme = 1;	}		
		}
				
		if(foundme == 0)
		{
			$('#connectLink').hide();
			$('#disconnectLink').show();
			$('#publishLink').hide();
			$('#unpublishLink').show();
			is_streaming = 1;
			reload_ads();
		}		
		else
		{
			publisher = null;
			$('#connectLink').hide();
			$('#disconnectLink').show();
			$('#publishLink').show();
			$('#unpublishLink').hide();	
			is_streaming = 0;
			reload_ads();
		}	
	}

	function sessionDisconnectedHandler(event) {
		// This signals that the user was disconnected from the Session. Any subscribers and publishers
		// will automatically be removed. This default behaviour can be prevented using event.preventDefault()
		
		//**Im going to assume this only fires when the users session disconnects
		
		publisher = null;
		vc_enable = 1;
		is_streaming = 0;
		
		$('#connectLink').show();
		$('#disconnectLink').hide();
		$('#publishLink').hide();
		$('#unpublishLink').hide();		
		reload_ads();	
				
	}
	
	function accessDeniedHandler(event) {
		//do nothing?
	}

	function connectionDestroyedHandler(event) {
		// This signals that connections were destroyed
			
	}

	function connectionCreatedHandler(event) {
		// This signals new connections have been created.
			
	}

	function exceptionHandler(event) {
		if (event.code == 1013) {
			document.body.innerHTML = "This page is trying to connect a third client to a peer-to-peer session. "
				+ "Only two clients can connect to peer-to-peer sessions.";
		} else {
			alert("Exception: " + event.code + "::" + event.message);
		}
	}

	//--------------------------------------
	//  HELPER METHODS
	//--------------------------------------

	function addStream(stream) {
		// Check if this is the stream that I am publishing, and if so, do not publish.
		if (stream.connection.connectionId == session.connection.connectionId && stream.connection.connectionId != null) {
			
			//socket.emit('setstreamid', stream.streamId);			
			return;
		}
		else
		{
			var subscriberDiv = document.createElement('div'); // Create a div for the subscriber to replace
			subscriberDiv.setAttribute('id', stream.streamId); // Give the replacement div the id of the stream as its id.
			document.getElementById("subscribers").appendChild(subscriberDiv);
			var subscriberProps = {width: VIDEO_WIDTH, height: VIDEO_HEIGHT};
			subscribers[stream.streamId] = session.subscribe(stream, subscriberDiv.id, subscriberProps);
		}
	}

// 	function show(id) {
// 		document.getElementById(id).style.display = 'block';
// 	}
// 
// 	function hide(id) {
// 		document.getElementById(id).style.display = 'none';
// 	}


///=====BEEP FUNCTION========

function reload_ads()
{			
	if(is_streaming == 0)
	{
		$('#sponsors').html('<center><iframe scrolling="no" style="border: 0; width: 468px; height: 60px;" src="//coinurl.com/get.php?id=24376&SSL=1"></iframe></center>');	
	}
	else
	{	
		$('#sponsors').html('<center><iframe scrolling="no" style="border: 0; width: 468px; height: 60px;" src="//coinurl.com/get.php?id=24376&SSL=1"></iframe></center>');	
	}
}

function playSnd() {
    sfx.load(); //stops it playing, if it already is. quite lame.
    sfx.play();
}

function Init() {
    if (!!(document.createElement('audio').canPlayType)) {
        sfx = document.createElement('audio');
        if (sfx.canPlayType('audio/mpeg')) {
            sfx.setAttribute('src', 'blip.mp3');
        } else if (sfx.canPlayType('audio/wav')) {
            sfx.setAttribute('src', 'blip.wav');
        }
        sfx.load();

    }
}

window.onload = Init();

</script>	
	
	<div style="min-height: 360px;">
		
		<div class="col-7">
		<span class="chat">
		
			<div class="A1">
				<h2>Room Status</h2>							
			</div>
			<div class="A2 t-right">
				<!--<img src="images/Rooms-2.png" title="Rooms in Use"> <span id = "roomcount">0</span>-->
				<div id = "roomstatus" class="A2 t-right"><span class="red">Unoccupied</span></div>
				
			</div>
			<br class="clear"><hr>
		
			<div id="chatbox"></div>
			
			<br class="clear">
			
			<div id="typing" class="small"></div>
			
			<form name="message" id="mform" autocomplete="off">
			<button name="submitmsg" id="submitmsg" class="blue_button" style="width: 70px;">Send</button>
				<textarea name="usermsg" type="text" id="usermsg" size="63" autocomplete="off" onKeyPress="enter(event);"></textarea>											
			</form>
	
		</span>
		
		<br class="clear">
		
		</div><!--end of col1-->
		
		<div id="rightcolumn" class="col-8">	
		<span class="chat">
			
			<h2>Options</h2>	
			<hr>		
			
			<div id="menu" class="menu"></div>	
				
			<!--<div class="A1"><img src="images/Status.png"> Room Status:<br></div>
			<div id = "roomstatus" class="A2 t-right"><span class="red">Unoccupied</span></div>-->
			
			<div style="padding-top: 5px; padding-bottom: 5px; width: 100%">
				<span id="sponsors">
					<center>
						<iframe scrolling="no" style="border: 0; width: 468px; height: 60px;" src="//coinurl.com/get.php?id=24376&SSL=1"></iframe>
					</center>
				</span>
			</div>
							
			<br>	

			<h2>Web Cam</h2>	
			<hr>	
			<div id="opentok_console"></div>
			<div id="links" style="float: left">
		       	<div value="Connect" id ="connectLink" style="width: 200px;">
		       		<a href="#" onClick="javascript:connect()" />
		       			<img src="images/cam_on.png"> View Their Cam (BETA)
		       		</a>
				</div>
		       	
		       	<div value="Leave" id ="disconnectLink" style="width: 200px;">
		       		<a href="#" onClick="javascript:disconnect()" />
		       			<img src="images/cam_off.png"> Cam Off
		       		</a>
		       	</div>

		       	<div value="Start Publishing" id ="publishLink" style="width: 200px;">
		       		<a href="#" onClick="javascript:startPublishing()" />
		       			<img src="images/stream_on.png"> Stream Your Cam
		       		</a>
		       	</div>

		       	<div value="Stop Publishing" id ="unpublishLink" style="width: 200px;">
		       		<a href="#" onClick="javascript:stopPublishing()" />
		       			<img src="images/stream_off.png"> Stop Streaming
		       		</a>
		       	</div>		   
		       	
			</div>
			<div id="myCamera" class="publisherContainer"></div>
			<div id="subscribers"></div>
	
		</span>
		</div>	
		
	</div>
                                                                                                    
<?php 

$Page->make_Footer(); 

?>