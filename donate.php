<?php 	

require_once('functions_all.php');

// $my_address = '1EVvjyoQ7ujDwhuWWaYi1cgZPWrArYnhb5';
// 
// $root_url = 'http://blockchain.info/api/receive';
// 
// $parameters = 'method=create&address=' . $my_address .'&shared=false';
// 
// $response = file_get_contents($root_url . '?' . $parameters);
// 
// $object = json_decode($response);

// echo $object->input_address; 

//record that user has logged out
$Page->make_Header();

?>

	<div class="col-3">
	<?php $Page->display_messages(); ?>	
	
	<!--<div class="redhat">
		<b>Error!</b> Chat servers are temporarily down.</a>
	</div>		

	<br class="clear">--->	
	
	<div class="index">
	
		<h3>Donate Directly</h3>
		<hr>
		STC runs no ads and is a free to use service.  However, there are costs involved in making and 
		maintaining the site, and we are willing to accept the donations you are willing to make.  If you are
		interested in donating, we offer two options:
		
		<br><br>
		
		<b>To donate via bitcoins</b>, please send to address:  1EVvjyoQ7ujDwhuWWaYi1cgZPWrArYnhb5
	
		<br><br>
		
		<b>To donate via credit card</b>, please visit: https://www.wepay.com/donations/stc_10
		
		<br><br>
		
		<h3>Donate Indirectly</h3>
		<hr>
		
		Comming soon.
		
		
	</div>
	
	</div><!--end of col1-->
	                                                                                                    
<?php 

$Page->make_Footer(); 

?>