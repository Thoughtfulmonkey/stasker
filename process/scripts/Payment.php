<?php

// Most of the parameters come from the php page that includes this one.

require_once 'payment-class.php';


$payment = new Payment;
	
$equation =  swapParamsForValues($params[0], $targetGroup);
$paymentAmount = calculate_string($equation);

if ($paymentAmount>0){

	// Set parameters
	$payment->setTaskDetails("Payment", "<p>You have received a payment for £$paymentAmount.</p>");
	$payment->setDaysToClaim(1);
	$payment->setOptions($paymentAmount);
		
	$payment->generate($targetGroup);
		
		
	echo "<p>Payment for £$paymentAmount scheduled</p>";

} else echo "<p>Paymente was for zero (or less), so ignored</p>";


?>