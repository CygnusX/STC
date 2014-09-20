var io = require('socket.io');
var opentok = require('opentok');
var express = require("express");

var app = express()
  , server = require('http').createServer(app)
  , io = io.listen(server);
  

var opentok = require('opentok');
 
//FOR USE WITH SOCKET.IO 1.X
// var app = require('express')();
// var server = require('http').Server(app);
// var io = require('socket.io')(server);

//listen(8080);

server.listen(80);
//server.listen(8080);

io.configure('production', function(){

	io.enable('browser client minification');  // send minified client
	io.enable('browser client etag');          // apply etag caching logic based on version number
	io.enable('browser client gzip');          // gzip the file
	io.set('log level', 1);                    // reduce logging
	io.set('transports', [                     // enable all transports (optional if you want flashsocket)
	    'websocket'
	  , 'htmlfile'							   // I wonder if i should disable some of these
	  , 'xhr-polling'
	  , 'jsonp-polling'
	]);

});

var activeusers    = [];  //tracks everyone that is online
var chatinvites    = {};  //tracks chat invitations
var ipbans         = {};  //tracks IP bans by room
var listings       = {};  //tracks listing details of each room
var roomhosted     = {};  //tracks if host is in the room  (I may be able to replace this variable with a function)
var roomoccupied   = {};  //tracks if room is occupied (has 2 users)
var sessionIDs     = {};  //tracks sessionIDs by room (open tok)
var size           = 0;   //for determining #rooms
var usersinlobby   = [];  //for determining who wants to participate in the lobby

//creates an opentok object
var ot = new opentok.OpenTokSDK('PLACE OPENTOK CREDENTIALS HERE);

//now lets begin setting up mongodb and mongoose
var mongoose = require('mongoose');
//mongoose.connect('mongodb://localhost/st_chat');
mongoose.connect('PLACE MONGODB CREDENTIALS HERE');

var db = mongoose.connection;
db.on('error', console.error.bind(console, 'connection error:'));
db.once('open', function callback () {
	
	console.log('server reset?');
			
	console.log("db is open");
	
	//defining my database schema
	var userSchema = mongoose.Schema({
	    username: { type: String, unique: true, index: true },
	    password: String,
	    email: { address: String, isverified: Number },
	    dob: { day: Number, month: Number, year: Number },
	    language: String,
	    gender: String,
	    chatpreference: String,
	    interests: String,
	    friends: [],	   
	    donation_btc: [{ fromt: String, quantity: Number, date_day: Number, date_month: Number, date_year: Number }],  //from allows you to give friends to others
	    donation_usd: [{ from: String, quantity: Number, date_day: Number, date_month: Number, date_year: Number }],
	    behaviorreportedby: [],	    
	    listingreportedby: [],
	    banned: Number, 	   //banned users cannot log in.  give banned notice at login (not yet done?) only ban for underage or spam    
	    uselobby: { type: Number, default: "1" },     //0 for no, 1 for yes
	    chatnotifications: { type: Number, default: "0" }  //0 for no, 1 for yes

	});	
	
	//purchases, quantity and date?
	//chat karma?	
	
	var User = mongoose.model('User', userSchema);
		
	//io.sockets.on('connection', function (socket) {
	io.on('connection', function (socket) {
		
		
		var tempmsg = "Num Connections: " + io.sockets.clients().length;
		//var tempmsg = "Num Connections: " + io.clients().length;
		console.log(tempmsg);							

		//console.log('connection');
		
		socket.APPVARS = {};			
				
		//define aboutme here
		socket.APPVARS.username           = '';
		socket.APPVARS.email              = '';
		socket.APPVARS.emailisverified    = ''; //i don't verify emails, but maybe one day
		socket.APPVARS.interests          = '';
		socket.APPVARS.gender             = '';
		socket.APPVARS.chatpreference     = ''; //male, female, all
		socket.APPVARS.dob                = {};
		socket.APPVARS.dob.day            = '';  
		socket.APPVARS.dob.month          = '';
		socket.APPVARS.dob.year           = '';
		socket.APPVARS.friends            = [];
		socket.APPVARS.behaviorreportedby = []; //not yet used
		socket.APPVARS.banned             = 0;
		socket.APPVARS.listingreportedby  = []; //not yet used
		socket.APPVARS.listingboost       = 0;
		socket.APPVARS.loginlevel         = 0;  //1. no md5, 2. main, 3. chat (only remove active users on lvl 2)
		socket.APPVARS.roomtype           = ''; //options are default, listings, or username (the invite username)
		socket.APPVARS.isHost             = 0;
		socket.APPVARS.isTyping           = 0;
		socket.APPVARS.roomid   		  = '';
		socket.APPVARS.streamid           = '';	//for use with video							
		socket.APPVARS.maxfriends         = 0;	//just defining the variable.  this will be redefined at login
		socket.APPVARS.loginlevel         = 0;  //this is a shitty variable for stating if the user is a guest, registered, host, etc
		socket.APPVARS.uselobby           = 0;  //some people don't want their name in the lobby
		socket.APPVARS.chatnotifications  = 0;  //some people don't want flashing chat notifications
		
		
		//LETS START WITH THE REGISRTATION METHODS	
		socket.on('isNameTaken', function(username, fn) { 
			//checks to see if username exists in database
			
			if(typeof fn != 'function')
			{	return;		}	
			
			if(typeof username != 'string')
			{	
				fn(false);
				return;		
			}							
						
			//looks up username in mongodb and checks if it exists
			User.findOne({username: username}, function(err, result) {
    		
				if (err) 
				{	
					console.log(err);
					fn(false);
					return;
				};    
				
				if(result == null)
				{	
					fn(false);
					return;	
				}				
				
				fn(true);
					
			});
								
			return;	
		});
		
		socket.on('register', function(data, fn){
			
			var starttime = new Date().getTime();
			
			//need to update this to account for 2 other fields
			
			//verify input types are correct					
			if(typeof fn != 'function')
			{	return;		}	
			
			if(typeof data != 'object')
			{	return;		}	
			
			//veryify input data is within expected bounds
			if(!data.hasOwnProperty('username'))   //if data does not have a username property
			{					
				fn("Server Error: Username cannot be null");	
				return;	
			}	       

			if(!data.hasOwnProperty('password'))   //if data does not have a password property
			{	
				fn("Server Error: Password cannot be null");	
				return;	
			}
			
			if(!data.hasOwnProperty('dob_day'))   //if data does not have a dob_day property
			{	
				fn("Server Error: Day cannot be null");	
				return;	
			}

			if(!data.hasOwnProperty('dob_month'))   //if data does not have a dob_month property
			{	
				fn("Server Error: Month cannot be null");	
				return;	
			}
			
			if(!data.hasOwnProperty('dob_year'))   //if data does not have a dob_year property
			{	
				fn("Server Error: Year cannot be null");	
				return;	
			}						
			
			if(!data.hasOwnProperty('gender'))      //if data does not have a gender property	
			{	
				fn("Server Error: Gender cannot be null");	
				return;	
			}			
			
			//set up the vars we will be using
			var username  = data.username.toLowerCase();  //lowercase all usernames so i don't have to deal with capitalization checks
			var password  = data.password;
			var dob_day   = data.dob_day;
			var dob_month = data.dob_month;
			var dob_year  = data.dob_year;
			var gender    = data.gender;				
			
			//THE FOLLOWING SHOULD BE STOLEN DIRECTLY FROM LOGIN.PHP
			//don't let user take reserved names.... i should probably put all these in an array.. the way i'm doing it here is total shit				
			if(username == 'host' || username == 'guest' || username == 'default' || username == 'listing' || username == 'listings' || username == 'you' || username == 'them')
			{
				fn("Server Error: This username is reserved");
				return;
			}	
			
			//check if valid username
			if(!usernameIsValid(username) || username.length < 3 || username.length > 20)
			{
				fn("Server Error:  Usernames may only contain letters, numbers, underscore and hyphen and must be between 3-20 characters");
				return;
			}			
								
			//check if passwords match (VOID)

			//check if password is within the size range
			if(password < 5 || password > 20)
			{	
				fn("Server Error: Passwords must contain 5 to 20 characters");
				return;
			}
			
			var date = dob_day + '/' + dob_month + '/' + dob_year;
			
			//check if valid date
			if(!isValidDate(date))
			{	
				fn("Server Error: Invalid date of birth");
				return;	
			}		
			
			//check if valid month and user over 15
			var date1     = new Date();
			var date2     = new Date(dob_month + ', ' + dob_day + ', ' + dob_year);
			
			var trueday   = date1.getDate();
			var truemonth = date1.getMonth() + 1;
	
			var yearDiff = date1.getFullYear() - date2.getFullYear() - 1;			
			
			if((dob_month < truemonth) || (dob_month == truemonth && dob_day <= trueday))
			{	yearDiff++;	}
			
			if(yearDiff < 18)
			{	
				fn("Server Error: You must be 18 years of age or older to use this service");
				return;	
			}
									
			//check if valid sex
			if(data.gender != 'm' && data.gender != 'f')
			{	
				fn("Server Error: Invalid gender");
				return;	
			}
							
			//END OF LOGIN.PHP CODE
			
			//do a query to see if the name is already in the db			
			User.findOne({username: username}, function(err, result) {
    						
				if(err){
					
					console.log(err);
					return;
				}    
	
				if(result != null)
				{	
					fn("Server Error: That username is already taken");
					return;						
				}
				else
				{					
					password = MD5(password);
					var preference = 'm';
					
					if(gender == 'm')
					{	preference = 'f';	}
							
					//now i need to register
					var thisUser = new User({ username: username, 
					                          password: password, 
					                          email: { address: '', isverified: 0 },
					                          dob: { day: dob_day, month: dob_month, year: dob_year }, 
					                          language: '',
					                          gender: gender, 
					                          chatpreference: preference, 
					                          interests: '',			                          
					                          friends: [],										      
			    							  donation_btc: [{}],
			    							  donation_usd: [{}],
			    							  behaviorreportedby: [],
	    									  listingreportedby: [],
			    							  banned: 0,
			    							  uselobby: 1,
			    							  chatnotifications: 0
					                          });
					
					thisUser.save(function (err, thisUser) {
	
						if (err) // TODO handle the error
						{	
							console.log(err);
							fn(false);
							return;				
						}				 													
						
						fn(true);
						
			    	});					   
				}     				   
			});		
			
			//var endtime = new Date().getTime();
			//var difftime = endtime - starttime;
			//var timemsg = "isNameTaken " + difftime;
			//console.log(timemsg);								
			
			return;				
		});			
					
		socket.on('login', function(data, fn){
			
			var starttime = new Date().getTime();
			
			//check username/password
			//if name/password is valid combo, return true and set socket.APPVARS (mongodoID, array of friends, max friends, .....)
			//else, return false	
			
			if(typeof fn != 'function')
			{
				console.log("at login, fn not a function");
				return;		
			}
			
			if(typeof data != 'object')
			{	
				console.log("at login, data is not an object");
				return;		
			}				
			
			if(!data.hasOwnProperty('username'))   //if data does not have a username property
			{					
				fn("Server Error: Username cannot be null");	
				return;	
			}	 
			
			if(!data.hasOwnProperty('password'))   //if data does not have a username property
			{					
				fn("Server Error: Password cannot be null");	
				return;	
			}	
			
			if(!data.hasOwnProperty('loginlevel'))   //if data does not have a username property
			{				
				fn("Server Error:  Login level not defined");	
				return;	
			}				 			
				
			username = data.username.toLowerCase();
			password = data.password;
			
			//if loginlevel is from main login form, we will need to encrypt here
			if(data.loginlevel == 1)
			{	password = MD5(data.password);		}
						
			//do a query to see if the name is already in the db			
			User.findOne({username: username, password: password}, function(err, result) {
    		
				if (err) 
				{
					console.log(err);
					fn(false)
					return;						
				}    
	
				if(result == null)
				{	
					fn("Server Error: Invalid Username and Password");
					return;	
				}
				else
				{								
					socket.APPVARS.loginlevel     = data.loginlevel;
							
					if(data.loginlevel > 1)
					{																																														
						//define the user
						socket.APPVARS.username  	     = username;
						socket.APPVARS.interests         = result.interests;
						socket.APPVARS.gender            = result.gender;
						socket.APPVARS.chatpreference    = result.chatpreference;
						socket.APPVARS.dob = {};         
						socket.APPVARS.dob.day           = result.dob.day;
						socket.APPVARS.dob.month         = result.dob.month;
						socket.APPVARS.dob.year          = result.dob.year;
						socket.APPVARS.friends           = result.friends;
						socket.APPVARS.loginlevel        = data.loginlevel;				
						socket.APPVARS.age               = getAge(result.dob.month, result.dob.day, result.dob.year);												
						socket.APPVARS.maxfriends        = 6;  //I need a formula here for determining max											
						socket.APPVARS.listingboost      = 0;  //i need a scipt here for determining power rank
						socket.APPVARS.uselobby          = result.uselobby;
						socket.APPVARS.chatnotifications = result.chatnotifications;
						socket.APPVARS.publiclisting     = result.publiclisting;  //why did I add this!?!!?
						
						//notify users who have me on their friend list that I'm online
						io.sockets.clients().forEach(function (s) { 
						
							if(s.APPVARS.friends.indexOf(username));
							{							
								s.emit('updatefriends');
							}						
						});
																												
					    //ensure user is defined as being online				
						if(activeusers.indexOf(username) == -1 && data.loginlevel == 2)
						{								
							activeusers.push(username);								
						}
						
						//add user to the lobby if they are not already in the lobby
						if(containsObjKeyVal(usersinlobby, 'invitename', username) == -1 && socket.APPVARS.uselobby == 1 && data.loginlevel == 2)
						{													
							var tempobj            = {};
							tempobj.invitename     = socket.APPVARS.username;
							tempobj.g1             = socket.APPVARS.gender;
							tempobj.chatpreference = socket.APPVARS.chatpreference;
							tempobj.age            = socket.APPVARS.age;
							tempobj.interests      = socket.APPVARS.interests;
							tempobj.currenttime    = new Date().getTime();
													
							usersinlobby.push(tempobj);										
						}						
						
						var tempobj = {};
						tempobj.age               = socket.APPVARS.age;
						tempobj.dob               = socket.APPVARS.dob;
						tempobj.listingboost      = socket.APPVARS.listingboost;
						tempobj.maxfriends        = socket.APPVARS.maxfriends;
						tempobj.interests         = socket.APPVARS.interests;
						tempobj.chatpreference    = socket.APPVARS.chatpreference;
						tempobj.gender            = socket.APPVARS.gender;
						tempobj.chatnotifications = socket.APPVARS.chatnotifications;
												
						//doing this so I don't need to get listing data for the first time?
						fn(tempobj);  
												
						//var tempmsg = username + " has signed in";
						//console.log(tempmsg);
					}
					
					fn(true);								
				}			    				   
			});											
			
			//var endtime = new Date().getTime();
			//var difftime = endtime - starttime;
			//var timemsg = "login " + difftime;
			//console.log(timemsg);					
			
			return;
			
		});
				
		socket.on('addfriend', function(friendname, fn){			
			
			//var starttime = new Date().getTime();
			
			//do type validation to prevent crashing
			if(typeof friendname != 'string')
			{
				console.log("friendname not a string");
				return;				
			}			
			
			if(typeof fn != 'function')
			{
				console.log("fn not a function");
				return;		
			}			
			
			//get current number of saved friends
			var numfriends = socket.APPVARS.friends.length;					
			friendname = friendname.toLowerCase();					

			if(!usernameIsValid(friendname))
			{
				fn("Server Error: Invalid Name.  Names must contain only letters, numbers, underscore or hyphen");	
				return;	
			}			
			
			if(numfriends >= socket.APPVARS.maxfriends)
			{
				fn("Server Error: You have reached your friend limit");
				return;	
			}
			
			if(socket.APPVARS.friends.indexOf(friendname) >= 0)
			{
				fn("Server Error: You are already friends with this user");
				return;	
			}
			
			if(friendname == socket.APPVARS.username)
			{
				fn("Server Error: You cannot friend yourself");
				return;					
			}
			
			//now performe database lookup
			User.findOne({username: friendname}, function(err, result) {
    		
				if (err)
				{
					console.log(err);
					return;					
				}    
	
				if(result == null)
				{	
					fn("Server Error: No such user exists");
					return;	
				}
							
				var username = socket.APPVARS.username;	
		
				User.update({ username: username }, { $addToSet: { friends: friendname }}, { multi: false }, function(err, updated) {
					
					if (err) 
					{
						console.log(err);
						return;	
					}    				

					console.log("someone is adding a friend");
					
					///i need to loop through each socket here and push to each socket identified as me
					io.sockets.clients().forEach(function (s) { 
					
						var tempsocketname = s.APPVARS.username;
						
						if((tempsocketname == username) && (s.APPVARS.loginlevel == 2))
						{					
							var cmessage = s.APPVARS.username + " equals " + username;
							console.log(cmessage);
									
							s.APPVARS.friends.push(friendname);
						}						
					});
						
					fn(true);																																					
				});								
						
			});			
			
			//var endtime = new Date().getTime();
			//var difftime = endtime - starttime;
			//var timemsg = "AddFriend " + difftime;
			//console.log(timemsg);		

		});
		
		socket.on('removefriend', function(friendname, fn){
					
			//var starttime = new Date().getTime();
					
			//do type validation to prevent crashing
			if(typeof friendname != 'string')
			{
				console.log("friendname not a string");
				return;				
			}
			
			if(typeof fn != 'function')
			{
				console.log("fn not a function");
				return;		
			}			
			
			//trying to prevent sql injection		
			if(!usernameIsValid(friendname))
			{
				fn("Server Error: Invalid Name.  Names must contain only letters, numbers, underscore or hyphen");	
				return;	
			}						
						
			//get current number of saved friends
			friendname = friendname.toLowerCase();		
			username   = socket.APPVARS.username;
									
			User.update({ username: username }, { $pull: { friends: friendname }}, function(err, result) {
			
					if (err) 
					{
						console.log(err);
						return;	
					}
					
					//i need to update the socket variable also
					var index = socket.APPVARS.friends.indexOf(friendname);
				
					if(index >= 0)
					{ socket.APPVARS.friends.splice(index, 1); }										
										
					fn(true);					
			});				
			
// 			var endtime = new Date().getTime();
// 			var difftime = endtime - starttime;
// 			var timemsg = "FriendRemove " + difftime;
// 			console.log(timemsg);					
			
		});		
			
		socket.on('changeprofile', function(profiledata, fn){

			var starttime = new Date().getTime();
			
			if(typeof fn != 'function')
			{	return;		}	
						
			if(typeof profiledata != 'object')
			{	return;		}	
						
			if(!profiledata.hasOwnProperty('chatpreference'))   //if data does not have a username property
			{				
				fn("Server Error: Chat Preference not defined");	
				return;	
			}				
			
			if(!profiledata.hasOwnProperty('interests'))   //if data does not have a username property
			{				
				fn("Server Error: Chat Preference not defined");	
				return;	
			}
			
			var chatpreference     = profiledata.chatpreference;
			var interests          = profiledata.interests;	
			var uselobby           = profiledata.uselobby;	
			var chatnotifications  = profiledata.chatnotifications;
			
			//make sure preference is ok m/f/a
			var validpreferences = ['f', 'm', 'a'];
			
			if(validpreferences.indexOf(chatpreference) < 0)
			{
				fn("Server Error: Invalid chat preference");
				return;
			}
			
			//make sure interests don't exceed 250 characters
			if(interests.length > 250)
			{
				fn("Server Error: Interests text must be 250 characters or less");
				return;
			}
			
			//check if lobby choice == 0 || 1
			var validlobbychoice = ["0", "1"];
			
			if(validlobbychoice.indexOf(uselobby) < 0)
			{
				fn("Invalid Lobby Choice");
				return;
			}			
			
			//check if chatnotifications choice == 0 || 1
			var validchatnotifications = ["0", "1"];
			
			if(validchatnotifications.indexOf(chatnotifications) < 0)
			{
				fn("Invalid Notification Choice");
				return;
			}						
						
			//now, lets find the user name of the person making changes
			var username = socket.APPVARS.username;	
			
			//and update
			User.update({ username: username }, { $set: { chatpreference: chatpreference, interests: interests, uselobby: uselobby, chatnotifications: chatnotifications, listingreportedby: [] }}, { multi: false }, function(err, updated) {
				
				if(err){
					
					console.log(err);	
				}
				
				if(!updated){						//again, i'm not sure if this is needed								
					console.log("Could not update user profile");
					return;
				}
				
				socket.APPVARS.chatpreference    = chatpreference;
				socket.APPVARS.interests         = interests;
				socket.APPVARS.uselobby          = uselobby;
				socket.APPVARS.chatnotifications = chatnotifications;  
				socket.APPVARS.listingreportedby = [];
											
				if(uselobby == 1)
				{				
					index = containsObjKeyVal(usersinlobby, 'invitename', username);
												
					if(index >= 0)
					{ usersinlobby.splice(index, 1); }
																							
					var tempobj            = {};
					tempobj.invitename     = socket.APPVARS.username;
					tempobj.g1             = socket.APPVARS.gender;
					tempobj.chatpreference = socket.APPVARS.chatpreference;
					tempobj.age            = socket.APPVARS.age;
					tempobj.interests      = socket.APPVARS.interests;
					tempobj.currenttime    = new Date().getTime();
											
					usersinlobby.push(tempobj);		
				}				
					
				fn(true);																																					
			});	
			
			var endtime = new Date().getTime();
			var difftime = endtime - starttime;
			var timemsg = "ChangeProfile " + difftime;
			console.log(timemsg);					
			
									
		});	 //end of change profile
		
		socket.on('getfriendlist', function(fn){

			//var starttime = new Date().getTime();
			
			//ensure callback type			
			if(typeof fn != 'function')
			{
				console.log("fn not a function");
				return;		
			}											
			
			var friends = socket.APPVARS.friends;
			var friendlist = {};
			var count = friends.length;
			
			if(count == 0)
			{
				fn(false);	
				return;
			}
			
			for(var i=0; i<count; i++)
			{
				var tempname = friends[i];
				
				if(activeusers.indexOf(tempname) >= 0)
				{	friendlist[tempname] = 1;		}
				else
				{	friendlist[tempname] = 0;		}									
			}					
						
			fn(friendlist);
			
		    //double check that I am online (since I am asking for the friends list)		
		    var username   = socket.APPVARS.username;
		    var loginlevel = socket.APPVARS.loginlevel;
		    	
			if(activeusers.indexOf(username) == -1 && loginlevel == 2)
			{								
				activeusers.push(username);		
			}
			
			//double check that I am in the lobby (since I am asking for friends list)
			if(containsObjKeyVal(usersinlobby, 'invitename', username) == -1 && socket.APPVARS.uselobby == 1 && loginlevel == 2)
			{													
				var tempobj            = {};
				tempobj.invitename     = socket.APPVARS.username;
				tempobj.g1             = socket.APPVARS.gender;
				tempobj.chatpreference = socket.APPVARS.chatpreference;
				tempobj.age            = socket.APPVARS.age;
				tempobj.interests      = socket.APPVARS.interests;
				tempobj.currenttime    = new Date().getTime();
										
				usersinlobby.push(tempobj);										
			}

			//var endtime = new Date().getTime();
			//var difftime = endtime - starttime;
			//var timemsg = "getFriendList " + difftime;
			//console.log(timemsg);			
					
			return;
				
		});
				
		socket.on('getchatrequests', function(fn){

			//var starttime = new Date().getTime();
			
			//ensure callback type			
			if(typeof fn != 'function')
			{
				console.log("fn not a function");
				return;		
			}	
			
			//the connected socket's username is the key
			username = socket.APPVARS.username;			
			
			//if key does not exist, return false
			if(!chatinvites.hasOwnProperty(username))   //if data does not have a username property
			{					
				fn(false);	
				return;	
			}						
								
			//if key does exist, use it to return the array of requests
			var requests = chatinvites[username];
			
			fn(requests);
			
			//var endtime = new Date().getTime();
			//var difftime = endtime - starttime;
			//var timemsg = "getChatRequests " + difftime;
			//console.log(timemsg);					
			
			return;
				
		});			
		
		socket.on('loadprofile', function(fn){		
					
			//var starttime = new Date().getTime();
			
			if(typeof fn != 'function')
			{
				console.log("fn not a function at loadprofile");
				return;		
			}
			
			var tempobj = {};
			
			tempobj.interests         = socket.APPVARS.interests;
			tempobj.chatpreference    = socket.APPVARS.chatpreference;
			tempobj.dob               = socket.APPVARS.dob;
			tempobj.maxfriends        = socket.APPVARS.maxfriends;
			tempobj.listingboost      = socket.APPVARS.listingboost;
			tempobj.uselobby          = socket.APPVARS.uselobby;
			tempobj.chatnotifications = socket.APPVARS.chatnotifications;
			tempobj.serverdate        = new Date();
			
			fn(tempobj);
			
			//var endtime = new Date().getTime();
			//var difftime = endtime - starttime;;
			//var timemsg = "loadProfile " + difftime;
			//console.log(timemsg);					
			
			return;
		});
							
//======END OF DATABASE MANIPULATION METHODS==============//
//======END OF DATABASE MANIPULATION METHODS==============//
//======END OF DATABASE MANIPULATION METHODS==============//
//======END OF DATABASE MANIPULATION METHODS==============//
//======END OF DATABASE MANIPULATION METHODS==============//		
		
		//when the client emits 'adduser', this listens and executes
		socket.on('adduser', function(passport, roomtype){						
						
			var starttime = new Date().getTime();
			
			if(typeof passport != 'object')
			{
				console.log("passport is not an object at adduser");
				return;		
			}
			
			if(typeof roomtype != 'string')
			{
				console.log("roomtype is not a string at adduser");
				return;		
			}				
				
			var maxroomsize  = 2;	//max room size should be 2 users per room	(should probably be defined in global scope)	
			var roomid       = '';  //initialize roomid
			var keycrypt     = '';  //initialize keycrypt		
			var passportkey  = '';  //secondary check for password									
							
			if(passport.hasOwnProperty('roomid')) //check to prevent crashing
			{	roomid = passport.roomid;		}
			
			if(passport.hasOwnProperty('keycrypt'))	//check to prevent crashing
			{	keycrypt = passport.keycrypt;	}				
			
			if(passport.hasOwnProperty('key'))    //change if value is present in the passport
			{	passportkey = passport.key;		}		
													
		
			var currentroomcount = io.sockets.clients(roomid).length;												
			
			
			//disconnect user if IP is unresolved.  this causes the system to crash		
			//can't do this here..  auto reconnect goes back through 'adduser', and this can prevent re-entering your own room
// 			if(!socket.handshake.address.address)
// 			{
// 				socket.emit('disconnect');
// 				return;	
// 			}
	
			var myhash       = MD5(roomid);	  //a hash of the room id  (WRAP roomid WITH JUNK TEXT TO PREVENT HASH GUESSES			
			
			//code in comments immediately below is due to a bunch of fucked up trial on error on gett IP's and other connections to work correctly
			
			//var socket_ip = (socket.handshake != undefined && socket.handshake.address != undefined) ? socket.handshake.address.address : "SIP_ERROR";
			//var cmessage = "SIP: " + socket_ip;
			
			//SOMETHING NEW TO TRY
			
			//var client_ip = socket.request.connection.remoteAddress;
			client_ip = socket.handshake.headers['x-forwarded-for'] || socket.handshake.address.address;
			//var client_ip= (socket.connection != undefined && socket.connection.remoteAddress != undefined) ? socket.connection.remoteAddress : "CIP_ERROR";
			
			var dmessage = "CIP: " + client_ip;
			
			//console.log(cmessage);
			//console.log(dmessage);
			
			//console.log(socket.handshake.headers['x-forwarded-for']);
			//console.log(socket.handshake.headers.referer);
			//console.log(socket.handshake.headers['user-agent']);					
			
			if(currentroomcount >= maxroomsize)  	//check to see if room has space for more users
			{											
				socket.join('');   				 //send client to the void
													
				//prevent user from joining with message
				socket.emit('servermsg', 'Sorry, but this room is already occupied by another guest.  No new users may enter.');								
			}				
			else if(ipIsBanned(socket.handshake.address.address, roomid))
			{
				socket.join('');   				 //send client to the void
											
				//prevent user from joining with message
				socket.emit('servermsg', 'You have been banned from this room');		
			}
			else if(roomid.length < 5)  		 //this should catch null room keys, plus force enough entropy to prevent guessing of room ids
			{
				socket.join('');   				 //send client to the void
											
				//prevent user from joining if room id does not contain enough entropy
				socket.emit('servermsg', 'Invalid Room ID');		
			}
			else if((currentroomcount == 0 && myhash != keycrypt) || (roomhosted[roomid] == 0 && myhash != keycrypt))		
			{
				socket.join('');   				 //send client to the void
											
				//prevent user from joining if host has left (ie, keycrypt not present)
				socket.emit('servermsg', 'The room host has left.  Please try again when they return.');		
			}	
			else
			{	
				//prevent good users from having any connection with "the void"
				socket.leave('');
				socket.join(roomid);							
							
				socket.APPVARS.roomid = roomid; 			
				
				var tempmsg = socket.APPVARS.username + " has joined a room";
				//console.log(tempmsg);
																						
				if(myhash != keycrypt)   //due to previous else if above, if this is true, there must be a host present
				{						 		
					roomoccupied[roomid]   = 1;			   				//update the list of occupied rooms
					var tempusername       = socket.APPVARS.username;
					
					if(listings.hasOwnProperty(roomid))					//if this is a listing, update the listings
					{	listings[roomid]['isOccupied'] = 1;		}
										
					//check all chat requests with my name on it
					if(chatinvites.hasOwnProperty(tempusername))
					{								
						var temprequests = chatinvites[tempusername];
							
						//loop through each request and see if the roomid matches the one I'm about to join						
						for(var i = 0; i < temprequests.length; i++)
						{
							//and if we have a match, kill the request
							if(temprequests[i].roomid == roomid)
							{																
								//kill the chat request
								{ chatinvites[tempusername].splice(i, 1); }																																																
							}								
						}												
					}				
					
					//alert the host that a person has connected to their room
															
					if(client_ip != "IP_ERROR")
					{	var TempUniqueID = MD5(client_ip);  }
					else
					{   var TempUniqueID = client_ip;  }
					
					socket.broadcast.to(roomid).emit('servermsg', 'Guest (' + TempUniqueID + ') has entered the room');	
					
					io.sockets.in(roomid).emit('roomstatus', '1');   //(1 == occupied)					
					
					//alert the guest they are joining a room
					socket.emit('servermsg', 'You have joined a private room.');
					socket.emit('roomstatus', '1');   //(1 == occupied)					
					
					//give the guest their menu							
					socket.emit('setmenuoptions', 'rightcolumn_anonguest.php');									
									
					// Send initialization data back to the client		
					var data = {
		                sessionId: sessionIDs[roomid],
					    token: ot.generateToken({
					    sessionId: sessionIDs[roomid],
					    role:opentok.RoleConstants.PUBLISHER, 
					    connection_data:"userip:" + client_ip	                  	                  	                 
		                })};
									
		            socket.emit('opentok_initialize', data);							
				}
				else
				{	
					//no host present, which means what?  
											
					socket.APPVARS.isHost = 1; //you are not a guest, so you are the host
					roomhosted[roomid] = 1;	   //the room is now 'hosted'			
									
					//give the host their menu
					socket.emit('setmenuoptions', 'rightcolumn_anonhost.php');						
												
					if(currentroomcount == 0)													//if nobody is currently here
					{							
						//room has just started, nobody is here, so give the host a welcome message
						io.sockets.in(roomid).emit('servermsg', 'You are now hosting a private room.');							
					}  
										
					if(!roomoccupied.hasOwnProperty(roomid))   //if roomoccupied[roomid] does not exist	
					{	roomoccupied[roomid]   = 0;	}	       //add a new key to track occupied rooms			
															
					if(!sessionIDs.hasOwnProperty(roomid))	   //if sessionIDs[roomid] does not have a sessionID					
					{										   					
						//as host, start an opentok session as moderator
						ot.createSession('localhost',{'p2p.preference':'disabled'},function(result) {
							
						sessionId  				   = result;
						sessionIDs[roomid]         = sessionId;
							
						//var socket_ip = (socket.handshake != undefined && socket.handshake.address != undefined) ? socket.handshake.address.address : "IP_ERROR";   //old shit from IP issue days
												
						// Send initialization data back to the client		
						var data = {
						  sessionId: sessionId,
						  token: ot.generateToken({
						    sessionId: sessionId,
						    role:opentok.RoleConstants.MODERATOR, 
						    connection_data:"userip:" + client_ip
						  })};					  					
															
						socket.emit('opentok_initialize', data);
							
						});										
																
						//do basic security checks on listing, and add to the queue if valid
						if(roomtype == 'listing')
						{																															
						   	var currenttime = new Date().getTime();
					   	   	var tempobj = {};			
					   	   	
					   	   	tempobj.currenttime    = currenttime;
					   	   	tempobj.isOccupied     = 0;
					   	   	tempobj.g1             = socket.APPVARS.gender;
					   	   	tempobj.g2             = socket.APPVARS.chatpreference;
					   	   	tempobj.age            = socket.APPVARS.age;
					   	   	tempobj.interests      = socket.APPVARS.interests 
					   	   	tempobj.isBanned	   = 0;
					   	   	tempobj.roomid         = roomid;
					   	   			   	   																										
							//add listing to the array of listings
							listings[roomid] = tempobj;
							
							var listingtitle = tempobj.age + tempobj.g1 + '4' + tempobj.g2 + ' - ' + tempobj.interests;								
							
							io.sockets.in(roomid).emit('servermsg', 'Your room, "' + listingtitle + '", has been added to our listings');

						}		
						else if(roomtype != 'default')
						{
							
							//check if user is an activeuser and alert the client if not							
							if(activeusers.indexOf(roomtype) < 0)
							{
								io.sockets.in(roomid).emit('servermsg', roomtype + ' no longer appears to be online');
								return;	
							}
														
							if(!chatinvites.hasOwnProperty(roomtype))  //roomtype will be the invitees name if not a listing and if not default
							{		chatinvites[roomtype] = [];		}

							//since we are inviting a user to a room, lets take this time to review the chat invites logged for the invitee
							var temprequests = chatinvites[roomtype];  //this gets all chat requests for users 'roomtype'
														
							for(var i = 0; i < temprequests.length; i++)
							{
								if(temprequests[i].username	== socket.APPVARS.username && temprequests[i].roomid != roomid)
								{									
									var temproomid = temprequests[i].roomid;									
									io.sockets.in(temproomid).emit('servermsg', 'Duplicate chat invite detected.  The chat request associated with this room id has been deleted');
									
									//kill the old room
									chatinvites[roomtype].splice(i, 1);																																																
								}								
							}
							
							//load a temp object with user data
							var tempobj = {};													
							tempobj.roomid         = roomid;
							tempobj.username       = socket.APPVARS.username;
							tempobj.age            = socket.APPVARS.age;
							tempobj.gender         = socket.APPVARS.gender;
							tempobj.interests      = socket.APPVARS.interests;
							
							//add object to the list
							chatinvites[roomtype].push(tempobj);
							socket.APPVARS.roomtype = roomtype;		//save the room type so that if the room closes, we kill the chat request
																	//i can do thise because each chat page host a single user socket
							io.sockets.in(roomid).emit('servermsg', 'A chat invite has been sent to ' + roomtype);				
										
							//loop through and notify friend of the request
							io.sockets.clients().forEach(function (s) { 
																						
								if(s.APPVARS.username == roomtype && s.APPVARS.loginlevel == 2);
								{							
									s.emit('updatechatrequests');
								}						
							});																																							
						}																									
					}								
					else	//re-issue sessionID, and alert room that host has returned
					{			
						//get a new sessionId and Token
						var data = {
			                sessionId: sessionIDs[roomid],
						    token: ot.generateToken({
						    sessionId: sessionIDs[roomid],
						    role:opentok.RoleConstants.MODERATOR, 
						    connection_data:"userip:" + socket.handshake.address.address	                  	                  	                 
			                })};
										
			            //pass opentok data
			            socket.emit('opentok_initialize', data);		
									
			            //tell the socket they have regained the room
			            socket.emit('servermsg', 'You have reconnected as host of the room');
			            			
			            //tell everyone else in the room that the host has returned
						socket.broadcast.to(roomid).emit('servermsg', 'The host of this room has reconnected');		
						
						//set roomstatus for everyone in the room
						io.sockets.in(roomid).emit('roomstatus', '1');   //(1 == occupied)					
						socket.emit('roomstatus', '1');   //(1 == occupied)					
					}						
				}		
				
			}	//keep room connections inside this bracket	
			
			var endtime = new Date().getTime();
			var difftime = endtime - starttime;
			var timemsg = "addUser " + difftime;
			//console.log(timemsg);	
			
			
		});
	
		socket.on('getlistings', function(fn){
				
			var starttime = new Date().getTime();
			
			if(typeof fn != 'function')
			{
				console.log("fn not a function at getlistings");
				return;		
			}
			
			//note, i may need to do something here to reduce object size				
			fn(listings);
				
		});	
		
		socket.on('getlobby', function(fn){
				
			var starttime = new Date().getTime();
			
			if(typeof fn != 'function')
			{
				console.log("fn not a function at getlobby");
				return;		
			}
			
			//note, i may need to do something here to reduce object size				
			fn(usersinlobby);
								
		});			
		
		socket.on('declineinvite', function(invitename){
			
			//this takes in the name of a person sending a chat invite
			//and removes the request from the queue based on invitename
			//and based on the username of the socket
			
			var tempusername = socket.APPVARS.username;
			
			//check all chat requests with my name on it
			if(chatinvites.hasOwnProperty(tempusername))
			{								
				var temprequests = chatinvites[tempusername];
					
				//loop through each request and see if the roomid matches the one I'm about to join						
				for(var i = 0; i < temprequests.length; i++)
				{
					//and if we have a match, killt he request
					if(temprequests[i].username == invitename)
					{													
						//let user know they were rejected
						var temproomid = temprequests[i].roomid;
						io.sockets.in(temproomid).emit('servermsg', 'Your chat request was declined');							
									
						//kill the chat request
						{ chatinvites[tempusername].splice(i, 1); }																																																
						
					}								
				}
				
				socket.emit('updatechatrequests');
																
			}				
		});
		
		// when the client emits 'sendchat', this listens and executes
		socket.on('sendchat', function (data) {
			
// 			var starttime = new Date().getTime();
			
			var roomid = socket.APPVARS.roomid;  //define the room id to avoid confusion		
			
			if(roomid != '' && roomid !== undefined && roomid !== null)
			{					
											
				if(socket.APPVARS.username == '')
				{				
					//tell the user their message
					//socket.emit('updatechat', 'you', socket.APPVARS.isHost, data);  						  		
							
					 //tell everyone else in the channel the users message
					socket.broadcast.to(roomid).emit('updatechat', 'them', socket.APPVARS.isHost, data);
				}
				else
				{
					var tempusername = socket.APPVARS.username;
					
					//give the message to all users in the room
					socket.broadcast.to(roomid).emit('updatechat', tempusername, socket.APPVARS.isHost, data);	
					//io.sockets.in(roomid).emit('updatechat', tempusername, socket.APPVARS.isHost, data);	
				}
				
				if(io.sockets.clients(roomid).length <= 0)
				{
					//tell the user their message
					socket.emit('servermsg', 'You have no guests in the room.  Nobody can hear you.');  		
					socket.broadcast.to(roomid).emit('updatetyping', '0');
				}
				else
				{
					//reconfirm the room is occupied
					socket.emit('roomstatus', '1');   //(1 == occupied)					
				}					
				
				if(socket.APPVARS.isHost != '1')
				{	roomoccupied[roomid] = 1;	}
			}

// 			var endtime = new Date().getTime();
// 			var difftime = endtime - starttime;
// 			var timemsg = "sendChat " + difftime;
// 			console.log(timemsg);				
								
		});
		
		// when the client emits 'updatetyping', this listens and executes
		socket.on('updatetyping', function (data) {
										
			if(socket.APPVARS.roomid != '')
			{					
				//make sure data is either 1 or 0
				if(data == 0 || data == 1)
				{			
					 //tell everyone else in the channel the users message
					socket.broadcast.to(socket.APPVARS.roomid).emit('updatetyping', data);				
				}	
			}					
								
		});	
			
		//updates the statistic of number of open rooms	
		socket.on('getroomcount', function() {
			
			size = Object.size(roomoccupied);	//count all open rooms
			socket.emit('updateroomcount', size);				
			
		});
		
		// when the client emits 'updatetyping', this listens and executes
		socket.on('removeguest', function() {
						
			if(socket.APPVARS.isHost == 1)
			{						
				//store the room id so we don't mix things up
				var roomid = socket.APPVARS.roomid;
				
				if(!ipbans.hasOwnProperty(roomid))		   //if ipbans[roomid] does not exist
				{	ipbans[roomid] 		   = {};	}	   //create it and initialize an object
						
				//get each users in the room
			    var users        = io.sockets.clients(roomid);        //gets all users in the room			
			    var bansize      = 0;		                          //sets up a variable to hold the ban size
			    var guestremoved = 0;                                 //true or false to see if anyone was removed
			    var tempIP;			                                  //sets up a variable to hold the IP address
					
				//tell each guest to leave
			    for(var i = 0; i < users.length; i++) {
				    
				    //make sure the user isn't the host before we kick them
				    if(users[i].APPVARS.isHost != 1)
				    {			    
			        	users[i].leave(roomid);                       //force user out of room
						users[i].join('');                            //force user into the void
						users[i].APPVARS.roomid = '';	              //change stored room id to zero (void)
						                                              
						tempIP  = users[i].handshake.address.address; //get the users IP addres					                                            					 
						ipbans[roomid][tempIP] = '';				  //update the bans							
	
						roomoccupied[roomid] = 0;			  //update room status		
						
						//tell guests they have been asked to leave
						users[i].emit('servermsg', 'You have been removed from the room.');
						socket.emit('updateipban', tempIP);	
						
						guestremoved = 1;                             //to know that we removed someone					
					}							
												
			    }//end of for loop				
			    
			    //make sure room is unlocked (0 == unoccupied)
			    io.sockets.in(roomid).emit('roomstatus', '0');
			    
			    if(listings.hasOwnProperty(roomid))
				{	listings[roomid]['isOccupied'] = 0;		}
			    
				// send to current request socket client
				if(guestremoved == 1)
				{	
					io.sockets.in(roomid).emit('servermsg', "Your guest has been removed from the room");	
					roomoccupied[roomid] = 0;
				}
				else
				{	io.sockets.in(roomid).emit('servermsg', "There are no guest here to remove");	}		
			}						    		   
		});
				
		// when the user disconnects.. perform this
		socket.on('disconnect', function(){
			
			var starttime = new Date().getTime();
			
			//store current room id in a temporary var so i don't get consued							
			var roomid = socket.APPVARS.roomid;																			
							
			//store current room count
			var currentroomcount = io.sockets.clients(roomid).length;			
			
			//if currentroomcount <= 1, delete all traces of the room
			if(currentroomcount <= 1)
			{								
				delete ipbans[roomid];						//remove all IP bans from this room
				delete roomoccupied[roomid];				//remove room from occupied checklist
				delete listings[roomid];					//remove froom from listings
				delete sessionIDs[roomid];					//remove room from sessionIDs
				delete roomhosted[roomid];					//remove room from hosting array
			}		
						
			if(socket.APPVARS.loginlevel == 2)
			{								
				var username = socket.APPVARS.username;							
				var tempmsg = username + " has disconnected";
				//console.log(tempmsg);				
								
				var index = activeusers.indexOf(username);
			
				if(index >= 0)
				{ activeusers.splice(index, 1); }
				
				index = containsObjKeyVal(usersinlobby, 'invitename', username);
											
				if(index >= 0)
				{ usersinlobby.splice(index, 1); }										
						
				//notify all users that i'm leaving
				io.sockets.clients().forEach(function (s) { 
				
					if(s.APPVARS.friends.indexOf(username));
					{							
						s.emit('updatefriends');
					}						
				});
			}
			
			if(socket.APPVARS.roomtype != '')
			{			
				
				var temproomtype = socket.APPVARS.roomtype;
				var temprequests = chatinvites[temproomtype];
											
				for(var i = 0; i < temprequests.length; i++)
				{
					if(temprequests[i].username	== socket.APPVARS.username)
					{															
						//kill the old room
						{ chatinvites[temproomtype].splice(i, 1); }																																																
					}								
				}	
								
				//loop through and notify friend of the request
				io.sockets.clients().forEach(function (s) { 
																			
					if(s.APPVARS.username == temproomtype && s.APPVARS.loginlevel == 2);
					{							
						s.emit('updatechatrequests');
					}						
				});				
			}
		
			if(socket.APPVARS.isHost == 1)
			{											
				if(roomoccupied.hasOwnProperty(roomid))		//update room status if i didn't just delete it
				{	roomoccupied[roomid] = 0;	}
				
				if(roomhosted.hasOwnProperty(roomid))	    //turn room into 'unhosted' if i didn't just delete it
				{	roomhosted[roomid] = 0;		}
																			
				// echo to the room that the guest has left and that the room is closed
				io.sockets.in(roomid).emit('servermsg', 'The host of this room has left');
				io.sockets.in(roomid).emit('roomstatus', '0');   //(0 == Unoccupied)					   		    		    	
			}
			else
			{																			
				//if the user isn't in the void
				if(roomid != '' && roomid != undefined && roomid != null)
				{																									
					if(roomoccupied.hasOwnProperty(roomid))	//update room status if i didn't just delete it
					{	roomoccupied[roomid] = 0;	}				
							
					if(listings.hasOwnProperty(roomid)) 	//update the listing
					{	listings[roomid]['isOccupied'] = 0;		}
					
					io.sockets.in(roomid).emit('servermsg', 'Your guest has left the room');	
					io.sockets.in(roomid).emit('roomstatus', '0');   //(0 == unoccupied)
				}									
			}
							
			socket.leave(roomid);  //force user out as last step for good measure
									
			var endtime = new Date().getTime();
			var difftime = endtime - starttime;
			var timemsg = "disconnect " + difftime;
			//console.log(timemsg);				
			
		});
	});  //end of io.socket.on('connection'
});
	
//HELPER FUNCTIONS//

function getAge(month, day, year)
{
	
	//calculate the users age (this should probably be its own function)
	var date1     = new Date();
	var date2     = new Date(month + ', ' + day + ', ' + year);

	var trueday   = date1.getDate();
	var truemonth = date1.getMonth() + 1;

	var age = date1.getFullYear() - date2.getFullYear() - 1;			
	
	if((month < truemonth) || (month == truemonth && day <= trueday))
	{	age++;	}	
	
	return age;
	
}


//checks if an ip is banned based on room id
function ipIsBanned(ip, roomid) {
		
	if(ipbans.hasOwnProperty(roomid))
	{
	   if(ipbans[roomid].hasOwnProperty(ip))
	   {   return true;	   }
	    
	    return false;				
    }
    
    return false;			
}

//gets number of items in an object
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key) && key != undefined && key != '') size++;
    }
    return size;
};

function containsObjKeyVal(arrayOfObj, objKey, keyValue) {
	
	//takes in an array of objects and an object key
	//checks to see if array contains an object with the key
	
    var l = arrayOfObj.length;
    
    for (var i = 0; i < l; i++) 
    {
        if (arrayOfObj[i][objKey] == keyValue) {
            return i;
        }
    }

    return -1;
}

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function usernameIsValid(username) {
    return /^[0-9a-zA-Z_-]+$/.test(username);
}

function isValidDate(s) {
  var bits = s.split('/');
  var d = new Date(bits[2], bits[1] - 1, bits[0]);
  return d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[0]);
} 

/**
*
*  MD5 (Message-Digest Algorithm)
*  http://www.webtoolkit.info/
*
**/
 
var MD5 = function (string) {
 
	function RotateLeft(lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}
 
	function AddUnsigned(lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			} else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		} else {
			return (lResult ^ lX8 ^ lY8);
		}
 	}
 
 	function F(x,y,z) { return (x & y) | ((~x) & z); }
 	function G(x,y,z) { return (x & z) | (y & (~z)); }
 	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }
 
	function FF(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function GG(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function HH(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function II(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function ConvertToWordArray(string) {
		var lWordCount;
		var lMessageLength = string.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	};
 
	function WordToHex(lValue) {
		var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			WordToHexValue_temp = "0" + lByte.toString(16);
			WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
		}
		return WordToHexValue;
	};
 
	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	};
 
	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;
 
	string = Utf8Encode(string);
 
	x = ConvertToWordArray(string);
 
	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
 
	for (k=0;k<x.length;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=AddUnsigned(a,AA);
		b=AddUnsigned(b,BB);
		c=AddUnsigned(c,CC);
		d=AddUnsigned(d,DD);
	}
 
	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
 
	return temp.toLowerCase();
}