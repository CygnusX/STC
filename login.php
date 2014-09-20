<?php 	

require_once('functions_all.php');

//record that user has logged out
$Page->make_Header();

if(!empty($_SESSION['isListing']) && $_SESSION['isListing'] == 1)
{	
	unset($_SESSION['isListing']);
	unset($_SESSION['gender1']);
	unset($_SESSION['gender2']);
	unset($_SESSION['age']);
	unset($_SESSION['description']);
}

?>

<script>
var socket    = io.connect('http://dppchat.jit.su:80');
//var socket    = io.connect('http://localhost:8080');

// setInterval (update_roomcount, 120000);	//Checks every 120 seconds to update number of room
			
//this submits the registration form
$(document).ready(function(){

	//register form event
	$("#registerform").submit(function(event){				
		event.preventDefault();
		
		var username  = $('#r_username').val();
		var password1 = $('#r_password1').val();
		var password2 = $('#r_password2').val();
		var dob_month = $('#r_dob_month').val();
		var dob_day   = $('#r_dob_day').val();
		var dob_year  = $('#r_dob_year').val();
		var gender    = $('#r_gender').val();
		
		if(username == 'host' || username == 'guest' || username == 'default')
		{
			alert("This username is reserved");
			return;
		}		
		
		//check if valid username
		if(!usernameIsValid(username) || username.length < 3 || username.length > 20)
		{
			alert('Error: Usernames may only contain letters, numbers, underscore and hyphen and must between 3-20 characters');
			return;	
		}

		//check if passwords match
		if(password1 != password2)
		{
			alert('Error: Passwords do not match');
			return;	
		}		
		
		//check if passwords match
		if(password1 < 5 || password1 > 20)
		{
			alert('Error: Passwords must contain 5 to 20 characters');
			return;	
		}				
		
		var date = dob_day + '/' + dob_month + '/' + dob_year;
		
		//check if valid date
		if(!isValidDate(date))
		{
			alert('Error: Invalid date of birth');
			return;	
		}		
		
		//check if valid month and user over 18
		var date1     = new Date();
		var date2     = new Date(dob_month + ', ' + dob_day + ', ' + dob_year);
		
		var trueday   = date1.getDate();
		var truemonth = date1.getMonth() + 1;

		var yearDiff = date1.getFullYear() - date2.getFullYear() - 1;			
		
		if((dob_month < truemonth) || (dob_month == truemonth && dob_day <= trueday))
		{	yearDiff++;	}
		
		if(yearDiff < 18)
		{
			alert('Error: You must be 18 years of age or older to use this service');
			return;	
		}
		
		//check if valid gender
		if(gender != 'm' && gender != 'f')
		{
			alert('Error: Invalid Gender');
			return;		
		}
		
		//pack everything into an object and send in to be registered... kick back any errors.  on success, set session and forward!
		
		var registrationdata = {};
		registrationdata.username  = username;
		registrationdata.password  = password1;
		registrationdata.dob_day   = dob_day;
		registrationdata.dob_month = dob_month;
		registrationdata.dob_year  = dob_year;		
		registrationdata.gender    = gender;		

		socket.emit("register", registrationdata, function(response){
		
			if(response == true)
			{				
				$.getJSON("script_login.php", { username: username, password: password1 }, function(data){
					
		          	if(data.result == true)
		          	{  	
			          	window.location = "home.php";      	
			          	return;		          	
		          	}			
		          	else
		          	{  	
			          	alert(data); 	       	
		          		return;
		          	}		
		        });
			}	
			else
			{
				alert(response);	
				return;
			}					
		});		
		
	});				


	//login form event
	$("#loginform").live('submit', function(event){		
		event.preventDefault();
		
		var username  = $('#l_username').val();
		var password  = $('#l_password').val();
		
		if(username == null || password == null)
		{
			alert("Username and Password fields cannot be null");
			return;	
		}
		
		var logindata = {};
		logindata.username   = username;
		logindata.password   = password;		
		logindata.loginlevel = 1;
		
		socket.emit("login", logindata, function(response){
		
			if(response == true)
			{				
				$.getJSON("script_login.php", { username: username, password: password }, function(data){
					
		          	if(data.result == true)
		          	{  	
			          	window.location = "home.php";      	
			          	return;		          	
		          	}			
		          	else
		          	{  	
			          	alert(data); 	       	
		          		return;
		          	}		
		        });
			}	
			else
			{
				alert(response);	
				return;
			}
		});		
	});
});
	



//helper functions

function usernameIsValid(username) {
    return /^[0-9a-zA-Z_-]+$/.test(username);
}

function isValidDate(s) {
  var bits = s.split('/');
  var d = new Date(bits[2], bits[1] - 1, bits[0]);
  return d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[0]);
} 


</script>


	<?php $Page->display_messages(); ?>	
	
	<!--<div class="redhat">
		<b>Error!</b> Chat servers are temporarily down.</a>
	</div>		

	<br class="clear">--->	
	
	<div class="col-1" style="min-height: 100px">
		
		<div class="login">
				
			<h2>Register (For New Users)</h2><hr>
			
			<form id="registerform">
			<div class="A1">Username:</div>
			<div class="A2 t-right"><input type="text" id="r_username" class="input"></div>
			
			<div class="A1">Password:</div>
			<div class="A2 t-right"><input type="password" id="r_password1" class="input"></div>
			
			<div class="A1">Confirm Password:</div>
			<div class="A2 t-right"><input type="password" id="r_password2" class="input"></div>			
			
			<div class="A1">Date of Birth:</div>
			<div class="A2 t-right">
			
				<select id="r_dob_month" style="width: 51px;" class="select">
					<option value="1">Jan</option>
					<option value="2">Feb</option>
					<option value="3">Mar</option>
					<option value="4">Apr</option>
					<option value="5">May</option>
					<option value="6">Jun</option>
					<option value="7">Jul</option>
					<option value="8">Aug</option>
					<option value="9">Sep</option>
					<option value="10">Oct</option>
					<option value="11">Nov</option>
					<option value="12">Dec</option>	
				</select>		
				
				<select id="r_dob_day" style="width: 42px;" class="select">

<?php
				for($i = 1; $i <= 31; $i++)
				{	echo '<option value="'.$i.'">'.$i.'</option>';		}
?>
		
				</select>		
				
				<select id="r_dob_year" style="width: 59px;" class="select">
				
				
<?php
				$date = date('Y');

				for($i = 1; $i <= 100; $i++)
				{				
					$display = $date - $i;	
					echo '<option value="'.$display.'">'.$display.'</option>';		
				}
?>
				
				</select>										
			
			</div>
			<div class="A1">Gender:</div>
			<div class="A2 t-right">			
				<select style="width: 160px;" id="r_gender" class="select">
					<option value="m">Male</option>
					<option value="f">Female</option>				
				</select>					
			</div>	
			
			<div class="A3 t-right"><button class="button">Submit</button></div>		
			</form>
			
			<br class="clear"><br>
					
			<h2>Terms of Service</h2><hr>		
			
			<b>1)</b> This service may not be used for any illegal activity.<br>
			<b>2)</b> By using this service, you agree not to use bots, scripts or other similar
			   software to interact in any way with the site.<br>
			<b>3)</b> By using this service, you agree not to post any content that may
			   be viewed by a reasonable person as spam.<br>			   
			<b>4)</b> By using this serivce, you agree not to do harm to our servers or to the content 
			   of our servers<br>
			<b>5)</b> By using this serivce, you agree to accept any harm done to your computer system<br>
			<b>6)</b> Terms of service may change at any time without notification.<br>
			   					
			<br clear="clear"><br>			
			
		
		</div><!--end of col1-->	
	
		
		
	</div>
	
	
	<div class="col-2" style="min-height: 100px">
	
		<span class="login">
		
			<form id="loginform">
			<h2>Login</h2><hr>
			<div class="A1">Username:</div>
			<div class="A2 t-right"><input type="text" class="input" id="l_username"></div>
			<br class="clear">
			
			<div class="A1">Password:</div>
			<div class="A2 t-right"><input type="password" class="input" id="l_password"></div>	
			<br class="clear">
								
			<div class="A3 t-right"><button class="button">Submit</button></div>		
			</form>
			<br class="clear"><br><br>			


			
		</span>
	</div>	

	<br class="clear">
				
	                                                                                                    
<?php 

$Page->make_Footer(); 

?>