<?php
/************************************************************
//DATE: 09/10/2013
//CLASS NAME: eway
//PURPOSE: Eway Recuring Payments
//Author: Khandad Niazi (kdniazi@gmail.com)
//Developed by: Gumption Technologies.
// CLASS USE Eway soap method to create profile and events.
*************************************************************/
class eway 
{	
	function profilexml()
	{
	/*********************************************************************************/
	// RECURRING PAYMENT FUNCTIONS
	/********************************************************************************/
		global  $customerTitle,
				$customerFirstName,
				$customerAddress,
				$customerLastName,
				$customerSuburb,
				$customerState,
				$customerCompany,
				$customerPostCode,
				$customerCountry,
				$customerEmail,
				$customerFax,
				$customerPhone1,
				$customerPhone2,
				$customerRef,
				$customerJobDesc,
				$customerComments,
				$eWAYCustomerID,
				$Username,
				$Password,
				$customerURL;
				$makeXml	=	'<CreateRebillCustomer xmlns="http://www.eway.com.au/gateway/rebill/manageRebill">
									<customerTitle>'.$customerTitle.'</customerTitle>
									<customerFirstName>'.$customerFirstName.'</customerFirstName>
									<customerLastName>'.$customerLastName.'</customerLastName>
									<customerAddress>'.$customerAddress.'</customerAddress>
									<customerSuburb>'.$customerSuburb.'</customerSuburb>
									<customerState>'.$customerState.'</customerState>
									<customerCompany>'.$customerCompany.' Con</customerCompany>
									<customerPostCode>'.$customerPostCode.'</customerPostCode>
									<customerCountry>'.$customerCountry.'</customerCountry>
									<customerEmail>'.$customerEmail.'</customerEmail>
									<customerFax>'.$customerFax.'</customerFax>
									<customerPhone1>'.$customerPhone1.'</customerPhone1>
									<customerPhone2>'.$customerPhone2.'</customerPhone2>
									<customerRef>'.$customerRef.'</customerRef>
									<customerJobDesc>'.$customerJobDesc.'</customerJobDesc>
									<customerComments>'.$customerComments.'</customerComments>
									<customerURL>'.$customerURL.'</customerURL>
								</CreateRebillCustomer>';
			return $makeXml;
	}
	
	function eventexml($RebillCustomerID)
	{
		
		//return 'The id is'.$RebillCustomerID;
		//exit;
		if(!is_numeric($RebillCustomerID))
		{
		  return 'RebillCustomerID can not empty';
		  exit;	
		}
		global  $RebillInvRef,
				$RebillInvDes,
				$RebillCCName,
				$RebillCCNumber,
				$RebillCCExpMonth,
				$RebillCCExpYear,
				$RebillInitAmt,
				$RebillInitDate,
				$RebillRecurAmt,
				$RebillStartDate,
				$RebillInterval,
				$RebillIntervalType,
				$RebillEndDate,
				$RebillInvRef;
				$makeXml	=	'<CreateRebillEvent xmlns="http://www.eway.com.au/gateway/rebill/manageRebill">
									<RebillCustomerID>'.$RebillCustomerID.'</RebillCustomerID>
									<RebillInvRef>'.$RebillInvRef.'</RebillInvRef>
									<RebillInvDes>'.$RebillInvDes.'</RebillInvDes>
									<RebillCCName>'.$RebillCCName.'</RebillCCName>
									<RebillCCNumber>'.$RebillCCNumber.'</RebillCCNumber>
									<RebillCCExpMonth>'.$RebillCCExpMonth.'</RebillCCExpMonth>
									<RebillCCExpYear>'.$RebillCCExpYear.'</RebillCCExpYear>
									<RebillInitAmt>'.$RebillInitAmt.'</RebillInitAmt>
									<RebillInitDate>'.$RebillInitDate.'</RebillInitDate>
									<RebillRecurAmt>'.$RebillRecurAmt.'</RebillRecurAmt>
									<RebillStartDate>'.$RebillStartDate.'</RebillStartDate>
									<RebillInterval>'.$RebillInterval.'</RebillInterval>
									<RebillIntervalType>'.$RebillIntervalType.'</RebillIntervalType>
									<RebillEndDate>'.$RebillEndDate.'</RebillEndDate>
									<RebillInvRef>'.$RebillInvRef.'</RebillInvRef>
								</CreateRebillEvent>';
			return $makeXml;
	}
	
	function prepareRequest($xml)
	{
			global	$eWAYCustomerID,$Username,$Password;
			$post_string = '<?xml version="1.0" encoding="utf-8"?>
			<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
				<soap:Header>
					<eWAYHeader xmlns="http://www.eway.com.au/gateway/rebill/manageRebill">
				<eWAYCustomerID>'.$eWAYCustomerID.'</eWAYCustomerID>
				<Username>'.$Username.'</Username>
				<Password>'.$Password.'</Password>
				</eWAYHeader>
				</soap:Header>
				<soap:Body>
					'.$xml.'
				</soap:Body>
			</soap:Envelope>';
			$header  = "POST /gateway/rebill/test/manageRebill_test.asmx HTTP/1.1 \r\n";
			$header .= "Host: www.eway.com.au \r\n";
			$header .= "Content-Type: text/xml; charset=utf-8 \r\n";
			$header .= "Content-Length: ".strlen($post_string)." \r\n";
			//$header .= 'SOAPAction: "http://www.eway.com.au/gateway/rebill/manageRebill/CreateRebillCustomer"'. "\r\n";
			$header .= "Connection: close \r\n\r\n";
			return $header .= $post_string;
	}
	
	function creatprofile()
	{
		global 	$url;
		$profileXml		=	$this->profilexml();
		$request		=	$this->prepareRequest($profileXml);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 36000);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
		$data = curl_exec($ch);        
		if(curl_errno($ch))
		{		$response	=	curl_error($ch);
				return 'error: : Problem in account creation';
		}
		else
		{
				$Result					=	$this->getTextBetweenTags($data,'Result');
				if($Result=='Success')
				{
					$RebillCustomerID	=	$this->getTextBetweenTags($data,'RebillCustomerID');
					return array("Result"=>'Success',"RebillCustomerID"=>$RebillCustomerID);
				}
				else  
				{
					$ErrorDetails	=	$this->getTextBetweenTags($data,'ErrorDetails');
					return array("Result"=>'Fail',"ErrorDetails"=>$ErrorDetails);
				}
				/*curl_close($ch);
				return $RebillCustomerID		=	$this->getTextBetweenTags($data,'RebillCustomerID');*/
		}
	}
	
	function creatEvent($RebillCustomerID)
	{
		
		if(!is_numeric($RebillCustomerID))
		{
			return 	'RebillCustomerID is invalid';
		}
		global 	$url;
		$profileXml		=	$this->eventexml($RebillCustomerID);
		$request		=	$this->prepareRequest($profileXml);
		$ch 			= 	curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 36000);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
		$data 			= 	curl_exec($ch);    
		if(curl_errno($ch))
		{
				//return curl_error($ch); 
				$xml =  simplexml_load_string(curl_error($ch));
				return 'error';
		}
		else
		{
				curl_close($ch);
				$Result					=	$this->getTextBetweenTags($data,'Result');
				if($Result=='Success')
				{
					$RebillCustomerID	=	$this->getTextBetweenTags($data,'RebillCustomerID');
					$RebillID			=	$this->getTextBetweenTags($data,'RebillID');
					return array("Result"=>'Success',"RebillCustomerID"=>$RebillCustomerID,"RebillID"=>$RebillID);
				}
				else  
				{
					$ErrorDetails	=	$this->getTextBetweenTags($data,'ErrorDetails');
					$RebillID		=	$this->getTextBetweenTags($data,'RebillID');
					return array("Result"=>'Fail',"ErrorDetails"=>$ErrorDetails);
				}
		}
	}
	/******************************************************************************/
	// DIRECT PAYMENT FUNCTIONS START HERE
	/******************************************************************************/
	function directPayment()
	{
		global  $eWAYCustomerID,
				$eWayTotalAmount,
				$ewayCustomerFirstName,
				$ewayCustomerLastName,
				$ewayCustomerEmail,
				$ewayCustomerAddress,
				$ewayCustomerPostcode,
				$ewayCustomerInvoiceDescription,
				$ewayCustomerInvoiceRef,
				$ewayCardHoldersName,
				$ewayCardNumber,
				$ewayCardExpiryMonth,
				$ewayCardExpiryYear,
				$ewayCVN,
				$ewayTrxnNumber,
				$ewayOption1,
				$ewayOption2,
				$ewayOption3,
				$directPaymentUrl,
				$eWaySOAPActionURL;
				$testUrl = "https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp";
				$liveUrl = "https://www.eway.com.au/gateway_cvn/xmlpayment.asp";
				$eWaySOAPActionURL = "https://www.eway.com.au/gateway/managedpayment";
				$eWayCustomerId = "91901390"; /* test account */
				$eWayTotalAmount = 100; /* 1$ = 100 cent */
				$directXML = "<ewaygateway>".
				"<ewayCustomerID>".$eWAYCustomerID."</ewayCustomerID>".
				"<ewayTotalAmount>".$eWayTotalAmount."</ewayTotalAmount>".
				"<ewayCustomerFirstName>".$ewayCustomerFirstName."</ewayCustomerFirstName>".
				"<ewayCustomerLastName>".$ewayCustomerLastName."</ewayCustomerLastName>".
				"<ewayCustomerEmail>".$ewayCustomerEmail."</ewayCustomerEmail>".
				"<ewayCustomerAddress>".$ewayCustomerAddress."</ewayCustomerAddress>".
				"<ewayCustomerPostcode>".$ewayCustomerPostcode."</ewayCustomerPostcode>".
				"<ewayCustomerInvoiceDescription>".$ewayCustomerInvoiceDescription."</ewayCustomerInvoiceDescription>".
				"<ewayCustomerInvoiceRef>".$ewayCustomerInvoiceRef."</ewayCustomerInvoiceRef>".
				"<ewayCardHoldersName>".$ewayCardHoldersName."</ewayCardHoldersName>".
				"<ewayCardNumber>".$ewayCardNumber."</ewayCardNumber>".
				"<ewayCardExpiryMonth>".$ewayCardExpiryMonth."</ewayCardExpiryMonth>".
				"<ewayCardExpiryYear>".$ewayCardExpiryYear."</ewayCardExpiryYear>".
				"<ewayCVN>".$ewayCVN."</ewayCVN>".
				"<ewayTrxnNumber>".$ewayTrxnNumber."</ewayTrxnNumber>".
				"<ewayOption1>".$ewayOption1."</ewayOption1>".
				"<ewayOption2>".$ewayOption2."</ewayOption2>".
				"<ewayOption3>".$ewayOption3."</ewayOption3>".
			"</ewaygateway>";
			  //echo $directXML;
			  //exit;
				$result = $this->makeCurlCall($testUrl, /* CURL URL */"POST", /* CURL CALL METHOD */
				array( /* CURL HEADERS */
					"Content-Type: text/xml; charset=utf-8",
					"Accept: text/xml",
					"Pragma: no-cache",
					"SOAPAction: ".$eWaySOAPActionURL,
					"Content_length: ".strlen(trim($directXML))
				),
				null, /* CURL GET PARAMETERS */
				$directXML /* CURL POST PARAMETERS AS XML */
			);
			if($result != null && isset($result["response"])) {//$response = new SimpleXMLElement($result["response"]);
			   // $response = simpleXMLToArray($response);
			 $result				=	$result["response"];
			 // exit;
			  $ewayTrxnStatus		=	$this->getTextBetweenTags($result,'ewayTrxnStatus');
			  if($ewayTrxnStatus)
			  {
					$ewayTrxnNumber	=	  $this->getTextBetweenTags($result,'ewayTrxnNumber');
					$ewayAuthCode	=	  $this->getTextBetweenTags($result,'ewayAuthCode');
					return array(
									"ewayTrxnStatus"=>$ewayTrxnStatus,
									"ewayTrxnNumber"=>$ewayTrxnNumber,
									"ewayAuthCode"=>$ewayAuthCode
								);
			  }
			  else
			  {
				   return  'error: Account creation fail';
			  }
			}
			die("");
	}
	
	/* makeCurlCall */
	function makeCurlCall($url, $method = "GET", $headers = null, $gets = null, $posts = null) {
		$ch = curl_init();
		if($gets != null)
		{
			$url.="?".(http_build_query($gets));
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		if($posts != null)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
		}
		if($method == "POST") {
			curl_setopt($ch, CURLOPT_POST, true);
		} else if($method == "PUT") {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		} else if($method == "HEAD") {
			curl_setopt($ch, CURLOPT_NOBODY, true);
		}
		if($headers != null && is_array($headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$response = curl_exec($ch);
		$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        //print_r($code);	
		curl_close($ch);
		return array(
			"code" => $code,
			"response" => $response
		);
	}
	/*********************************************************************************/
	// COMMON FUNCTIONS
	/********************************************************************************/
	function getTextBetweenTags($string, $tagname)
	{
		if($string)
		{
			$pattern = "/<$tagname>(.*?)<\/$tagname>/";
			preg_match($pattern, $string, $matches);
			return $matches[1];
		}
		else
		{
			 return 'Error';
		}
	}
}

//
/*$customerTitle 	   	=  'sdf asdfsda fsa';
$customerFirstName 	=  'asdf sad';
$customerAddress   	=  'asdf asdf sadf';
$customerLastName  	=  'df ';
$customerSuburb    	=  '2242';
$customerState     	=  'customerState';
$customerCompany   	=  'customerCompany';
$customerPostCode  	=  '1212';
$customerCountry   	=  'AUS';
$customerEmail     	=  'emails@gmail.com';
$customerFax 		=  '23256234';
$customerPhone1 	=  '2311423';
$customerPhone2 	=  '56342212123';
$customerRef 		=  'user223';
$customerJobDesc 	=  'This is complete job description';
$customerComments 	=  'this is comment part';
$eWAYCustomerID 	=  '91901390';
$Username 			=  'umar@gumptech.net.sand';
$Password 			=  '01Arid1326';
$customerURL 		= 'http:gumptech.net';*/

$eWAYCustomerID			=	'87654321';
$eWayTotalAmount		=	'100';
$ewayCustomerFirstName	=	'Khandad';
$ewayCustomerLastName	=	'Niazi';
$ewayCustomerEmail		=	'kdniazi@gmail.com';
$ewayCustomerAddress	=	'This is email address.';
$ewayCustomerPostcode	=	'3212';
$ewayCustomerInvoiceDescription=	'Complete detils are here';	
$ewayCustomerInvoiceRef	=	'123424';	
$ewayCardHoldersName	=	'TestAccount';	
$ewayCardNumber			=	'4444333322221111';	
$ewayCardExpiryMonth	=	'12';
$ewayCardExpiryYear		=	'2014';
$ewayCVN				=	'123';	
$ewayTrxnNumber			=	'1234534';	
$ewayOption1			=	'';	
$ewayOption2			=	'';
$ewayOption3			=	'';
$directPaymentUrl		=	'http:gumptech.net';
$eWaySOAPActionURL		=	'';	

$ep			=	new eway();
$response	=	$ep->directPayment();
print_r($response);