<?php
@session_start();
require_once("adminsecurity.php");

$uid				=	$_GET['id']; 
$view				=	$_GET['view'];
$respond			=	$_GET['respond'];
$response_id		=	$_GET['response_id'];
$active_attr_email	=	$_SESSION['loggedin_email'];

$discoveryDetails	=	$AdminDAO->getrows('discoveries d,cases c,system_addressbook a,forms f',
											'c.case_title 	as case_title,
											c.case_number 	as case_number,
											c.jurisdiction 	as jurisdiction,
											c.judge_name 	as judge_name,
											c.county_name 	as county_name,
											c.court_address as court_address,
											c.department 	as department, 
											d.case_id 		as case_id,
											d.id 			as discovery_id,
											d.uid,
											d.type,
											d.discovery_instrunctions,
											c.plaintiff,
											c.defendant,
											d.send_date,
											d.propounding,
											d.responding,
											d.form_id 		as form_id,
											d.set_number 	as set_number,
											d.discovery_introduction as introduction,
											f.form_name	 	as form_name,
											f.short_form_name as short_form_name,
											a.firstname 	as atorny_fname,
											a.lastname 		as atorny_lname,
											d.attorney_id	as attorney_id,
											d.discovery_name,
											d.conjunction_setnumber,
											d.interogatory_type,
											a.email,
											(CASE WHEN (form_id = 1 OR form_id = 2) 
											 THEN
												  f.form_instructions 
											 ELSE
												  d.discovery_instrunctions 
											 END)
											 as instructions 
											',
											/*(d.responding_uid 			= :uid OR d.propounding_uid = :uid) AND */
											"d.uid 			= :uid AND  
											
											d.case_id 		= c.id AND  
											d.form_id		= f.id AND
											d.attorney_id 	= a.pkaddressbookid",
											array(":uid"=>$uid)
										);



$discovery_data					=	$discoveryDetails[0];
$case_title						=	$discovery_data['case_title'];//$discovery_data['plaintiff']." V ".$discovery_data['defendant'];
$discovery_id					=	$discovery_data['discovery_id'];
$case_number					=	$discovery_data['case_number'];
$jurisdiction					=	$discovery_data['jurisdiction'];
$judge_name						=	$discovery_data['judge_name'];
$county_name					=	$discovery_data['county_name'];
$court_address					=	$discovery_data['court_address'];
$department						=	$discovery_data['department'];
$case_id						=	$discovery_data['case_id'];
$form_id						=	$discovery_data['form_id'];
$set_number						=	$discovery_data['set_number'];
$atorny_name					=	$discovery_data['atorny_fname']." ".$discovery_data['atorny_lname'];
$attorney_id					=	$discovery_data['attorney_id'];
$form_name						=	$discovery_data['form_name']." [Set ".$set_number."]";
$short_form_name				=	$discovery_data['short_form_name'];
$send_date						=	$discovery_data['send_date'];
$email							=	$discovery_data['email'];
$instructions					=	$discovery_data['discovery_instrunctions'];
$type							=	$discovery_data['type'];
$introduction					=	$discovery_data['introduction'];
$propounding					=	$discovery_data['propounding'];
$responding						=	$discovery_data['responding'];
$discovery_name					=	$discovery_data['discovery_name']." [Set ".$set_number."]";
/*
//Responding Party
$respondingdetails		=	$AdminDAO->getrows("clients","*","id = :id",array(":id"=>$responding));
$responding_name		=	$respondingdetails[0]['client_name'];
$responding_email		=	$respondingdetails[0]['client_email'];
$responding_type		=	$respondingdetails[0]['client_type'];
$responding_role		=	$respondingdetails[0]['client_role'];

//Propondoing Party
$propondingdetails		=	$AdminDAO->getrows("clients","*","id = :id",array(":id"=>$propounding));
$proponding_name		=	$propondingdetails[0]['client_name'];
$proponding_email		=	$propondingdetails[0]['client_email'];
$proponding_type		=	$propondingdetails[0]['client_type'];
$proponding_role		=	$propondingdetails[0]['client_role'];


if($response_id > 0)
{
	$discovery_name		=	"RESPONSE TO ".$discovery_name;
} 
$PDFname	=	strtoupper($discovery_name).".pdf";
//if($type == 2)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,DOMAIN."makepdf.php?id=".$uid."&downloadORwrite=1&view={$view}&active_attr_email={$active_attr_email}&response_id={$response_id}");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec($ch);
	curl_close ($ch);	
	
}
$urlPDF	=	UPLOAD_URL."documents/".$uid."/".$PDFname;*/
?>
<div id="screenfrmdiv" style="display: block;">
    <div class="col-lg-12">
        <div class="hpanel">
            <div class="panel-heading">
            <h3 align="center"><strong><?php echo $case_title;?></strong></h3></div>
            <div class="panel-body">
            <div class="panel panel-primary">
            <div class="panel-heading">
            <div class="row">
                <div class="col-md-12">
                    <span style="font-size:18px; font-weight:600"><?php echo $discovery_name;?></span>
                </div>
            </div>
            </div>
            <div class="panel-body">
                <div class="text-center">
            <a style="margin-bottom:10px" href="javascript:void(0);" onclick="selecttab('45_tab','discoveries.php?pid=<?php echo $case_id; ?>','45');" class="btn btn-danger buttonid">
				<i class="fa fa-close"></i>
				Close
			</a>
            
            <div class="col-md-12" style="min-height:500px">
            	<iframe id="loadIFrame" style="height:800px" src="" frameborder="0" height="100%" width="100%"></iframe>
            </div>
            <div class="text-center">
            <a style="margin-bottom:10px" href="javascript:void(0);" onclick="selecttab('45_tab','discoveries.php?pid=<?php echo $case_id; ?>','45');" class="btn btn-danger buttonid">
				<i class="fa fa-close"></i>
				Close
			</a>
        	</div>
            </div>
            </div>
            </div>
            
        </div> 
    </div>
</div>
<script>
$(document).ready(function() 
{
	<?php /*?>loadinstructions('<?php echo $form_id ?>','<?php echo $discovery_id ?>');	<?php */?>
	$.LoadingOverlay("show");
	$.get( "generatePDF_IFrame.php?id=<?=$uid?>&downloadORwrite=1&view=<?=$view?>&active_attr_email=<?=$active_attr_email?>&response_id=<?=$response_id?>", function( data ) 
	{
		$("#loadIFrame").attr("src",data);
		setTimeout(function()
		{
			$.LoadingOverlay("hide");
			//alert(data);
		}, 2000);
		
	});
});
</script>
    					
                   