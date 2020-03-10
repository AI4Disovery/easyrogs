<?php
@session_start();
require_once("adminsecurity.php"); 
$response_id			=	$_POST['response_id'];
$discoverydetails		=	$AdminDAO->getrows('responses,discoveries',"*","responses.fkdiscoveryid = discoveries.id AND  responses.id = :id",array(":id"=>$response_id));
if(sizeof($discoverydetails) > 0)
{
	$case_id		=	$discoverydetails[0]['case_id'];
	$discovery_id	=	$discoverydetails[0]['id'];
	
	$fields				=	array('isserved','servedate',"submit_date",'is_submitted','verification_signed_by','verification_by_name','discovery_verification','verification_state','verification_city','verification_datetime',"posstate","poscity","postext","posupdated_at","posupdated_by");
	$values				=	array("0","0000-00-00","0000-00-00",'0',"","","","","","0000-00-00","","","","0000-00-00","");
	$AdminDAO->updaterow("responses",$fields,$values,"id ='$response_id'");
	echo $case_id;
}