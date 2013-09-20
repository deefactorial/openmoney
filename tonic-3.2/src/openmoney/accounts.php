<?php

namespace openmoney;

use Tonic;
use Tonic\Resource,
Tonic\Response,
Tonic\ConditionException;

/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /accounts
 * @uri /accounts/info
 */
class accounts extends Resource
{


	private $user;
	private $username;

	/**
	 * The setup() method is called when the resource is executed. We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
		require("rest_connect.php");
		require("../password.php");
		if(isset($_SERVER['PHP_AUTH_USER'])&&isset($_SERVER['PHP_AUTH_PW'])){
				
			//echo "Attempting to authenticate user.\n";
			$users_q = mysqli_query($db,"SELECT * FROM users WHERE user_name='" . mysqli_real_escape_string($db,$_SERVER['PHP_AUTH_USER']) ."'");
			$users = mysqli_fetch_array($users_q);
			//echo "username:".$users['user_name']."\n";
			//echo "password:".$_SERVER['PHP_AUTH_PW']."\n";
			//echo "check1".password_verify($_SERVER['PHP_AUTH_PW'],$users['password2'])."\n";
			//echo "check2".password_verify($_SERVER['PHP_AUTH_PW'],$users['password'])."\n";
			if(password_verify($_SERVER['PHP_AUTH_PW'],$users['password2']) OR (password_verify($_SERVER['PHP_AUTH_PW'], $users['password']))){
				//echo "verified\n";
				$this->user = $users;
				$this->username = $users['user_name'];
			} else {
				throw new Tonic\UnauthorizedException;
			}
		} else {
			throw new Tonic\UnauthorizedException;
		}
	}
	/**
	 * Use this method to handle GET HTTP requests.
	 *
	 * The optional :name parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 *
	 * @method GET
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function sayAccounts()
	{
		
			require("rest_connect.php");
			$accounts_array = array();
			$default_count = 0;
			$accounts_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='".$this->user['id']."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
			while($accounts = mysqli_fetch_array($accounts_q)){
				
				$balance = 0.00000;
				$balance_decimal = number_format($balance,2);
				$user_journal_q = @mysqli_query($db,$test = "SELECT * FROM user_journal WHERE currency='".$accounts['currency']."' AND trading_account='".$accounts['trading_name']."' AND user_id='".$this->user['id']."' ORDER BY id DESC");
				if($user_journal = @mysqli_fetch_array($user_journal_q)){
					$balance = floatval($user_journal['balance']);
					$balance_decimal = number_format($balance,2);
				}
				
				$default = false;
				if(($accounts['currency_id']==1)&&($default_count==0)){
					$default_count++;
					$default = true;
				}
				array_push($accounts_array,array('account' => array("id" => $accounts['user_account_currencies_id'],
												  "default" => $default,
												  "type" => array("id" => $accounts['user_account_currencies_id'],
																"name" => $accounts['trading_name'],
																"currency" => array("id" => $accounts['currency_id'],
																					"symbol" => $accounts['currency'],
																					"name" => $accounts['currency']))),
										  	      'status' => array( "balance" => $balance,
															  		"formattedBalance" => "$balance_decimal ".$accounts['currency'],
															  		"availableBalance" => 0.000000,
															  		"formattedAvailableBalance" => "0.00 ".$accounts['currency'],
															  		"reservedAmount" => 0,
															  		"formattedReservedAmount" => "0.00 ".$accounts['currency'],
															  		"creditLimit" => 0.000000,
															  		"formattedCreditLimit" => "0.00 ".$accounts['currency'])));
			}
			$result = new Response(200, $accounts_array);

		return $result;
	}




	/**
	 * Condition method to turn output into JSON.
	 *
	 * This condition sets a before and an after filter for the request and response. The
	 * before filter decodes the request body if the request content type is JSON, while the
	 * after filter encodes the response body into JSON.
	 */
	protected function json()
	{
		$this->before(function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode($request->data);
			}
		});
		$this->after(function ($response) {
			$response->contentType = "application/json";
			if (isset($_GET['jsonp'])) {
				$response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
			} else {
				$response->body = json_encode($response->body);
			}
		});
	}

}

/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /accounts/:accountID/status
 */
class accountStatus extends Resource
{

	private $user;
	private $username;
	
	/**
	 * The setup() method is called when the resource is executed. We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
		require("rest_connect.php");
		require("../password.php");
		if(isset($_SERVER['PHP_AUTH_USER'])&&isset($_SERVER['PHP_AUTH_PW'])){
	
			//echo "Attempting to authenticate user.\n";
			$users_q = mysqli_query($db,"SELECT * FROM users WHERE user_name='" . mysqli_real_escape_string($db,$_SERVER['PHP_AUTH_USER']) ."'");
			$users = mysqli_fetch_array($users_q);
			//echo "username:".$users['user_name']."\n";
			//echo "password:".$_SERVER['PHP_AUTH_PW']."\n";
			//echo "check1".password_verify($_SERVER['PHP_AUTH_PW'],$users['password2'])."\n";
			//echo "check2".password_verify($_SERVER['PHP_AUTH_PW'],$users['password'])."\n";
			if(password_verify($_SERVER['PHP_AUTH_PW'],$users['password2']) OR (password_verify($_SERVER['PHP_AUTH_PW'], $users['password']))){
				//echo "verified\n";
				$this->user = $users;
				$this->username = $users['user_name'];
			} else {
				throw new Tonic\UnauthorizedException;
			}
		} else {
			throw new Tonic\UnauthorizedException;
		}
	}

	/**
	 * Use this method to handle GET HTTP requests.
	 *
	 * The optional :accountID parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 *
	 * @method GET
	 * @param  str $accountID
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function sayAccountsStatus($accountID = 0)
	{
		if($accountID != 0){
			require("rest_connect.php");
			$accounts_array = array();
			$default_count = 0;
			$accounts_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.id='".mysqli_real_escape_string($db, intval($accountID))."' AND uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='".$this->user['id']."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
			if($accounts = mysqli_fetch_array($accounts_q)){
	
				$balance = 0.00000;
				$user_journal_q = @mysqli_query($db,$test = "SELECT * FROM user_journal WHERE currency='".$accounts['currency']."' AND trading_account='".$accounts['trading_name']."' AND user_id='".$this->user['id']."' ORDER BY id DESC");
				$user_journal = @mysqli_fetch_array($user_journal_q);
				$balance = floatval($user_journal['balance']);
				$balance_decimal = number_format($balance,2);
	
	
				$default = false;
				if(($accounts['currency_id']==1)&&($default_count==0)){
					$default_count++;
					$default = true;
				}
					
				$accounts_array = array( "balance" => $balance,
						"formattedBalance" => "$balance_decimal ".$accounts['currency'],
						"availableBalance" => 0.000000,
						"formattedAvailableBalance" => "0.00 ".$accounts['currency'],
						"reservedAmount" => 0,
						"formattedReservedAmount" => "0.00 ".$accounts['currency'],
						"creditLimit" => 0.000000,
						"formattedCreditLimit" => "0.00 ".$accounts['currency']);
	
	
			}
			$result = new Response(200, $accounts_array);
		} else {
			throw new Tonic\NotFoundException;
		}
		return $result;
	}
	
	
	/**
	 * Condition method to turn output into JSON.
	 *
	 * This condition sets a before and an after filter for the request and response. The
	 * before filter decodes the request body if the request content type is JSON, while the
	 * after filter encodes the response body into JSON.
	 */
	protected function json()
	{
		$this->before(function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode($request->data);
			}
		});
		$this->after(function ($response) {
			$response->contentType = "application/json";
			if (isset($_GET['jsonp'])) {
				$response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
			} else {
				$response->body = json_encode($response->body);
			}
		});
	}
}



/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /accounts/:accountID/history
 */
class accountHistory extends Resource
{

	private $user;
	private $username;

	/**
	 * The setup() method is called when the resource is executed. We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
		require("rest_connect.php");
		require("../password.php");
		if(isset($_SERVER['PHP_AUTH_USER'])&&isset($_SERVER['PHP_AUTH_PW'])){

			//echo "Attempting to authenticate user.\n";
			$users_q = mysqli_query($db,"SELECT * FROM users WHERE user_name='" . mysqli_real_escape_string($db,$_SERVER['PHP_AUTH_USER']) ."'");
			$users = mysqli_fetch_array($users_q);
			//echo "username:".$users['user_name']."\n";
			//echo "password:".$_SERVER['PHP_AUTH_PW']."\n";
			//echo "check1".password_verify($_SERVER['PHP_AUTH_PW'],$users['password2'])."\n";
			//echo "check2".password_verify($_SERVER['PHP_AUTH_PW'],$users['password'])."\n";
			if(password_verify($_SERVER['PHP_AUTH_PW'],$users['password2']) OR (password_verify($_SERVER['PHP_AUTH_PW'], $users['password']))){
				//echo "verified\n";
				$this->user = $users;
				$this->username = $users['user_name'];
			} else {
				throw new Tonic\UnauthorizedException;
			}
		} else {
			throw new Tonic\UnauthorizedException;
		}
	}

	/**
	 * Use this method to handle GET HTTP requests.
	 *
	 * The optional :accountID parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 *
	 * @method GET
	 * @param  str $accountID
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function sayAccountsHistory($accountID = 0)
	{
		if($accountID != 0){
			require("rest_connect.php");
			
			
			/*Path variable id: The desired account id
			Query parameters:
			currentPage: Number indicating the current result page, starting with 0 (the default)
			pageSize: Amount of results returned. 10 by default, 100 max.
			showStatus: A boolean flag indicating whether the account status will also be returned on the result
			paymentFilterId: Numeric identifier for a payment filter.
			memberId: Numeric identifier for a related member
			memberPrincipal: Principal (username, e-mail, custom field, depending on what is configured on the rest channel) for a related member
			beginDate: Date filter for transfers, returning transfers with date greater or equals the given date. The date is formatted as ISO 8601 (yyyy-mm-ddThh:mm:ss)
			endDate: Date filter for transfers, returning transfers with date less or equals the given date. The date is formatted as ISO 8601 (yyyy-mm-ddThh:mm:ss)
			customValue.<fieldInternalName>: Filters returned transfers by a custom field value. Only works for custom fields marked to be used on search
			*/
			$pageSize = 10;
			$currentPage = 0;
			$showStatus = false;
			$paymentFilterID = 0;
			$memberID = 0;
			$memberPrincipal = '';
			$beginDate = 0;
			$endDate = strtotime("now");
			
			//$requestData = $this->request->data;
			if(isset($_GET)){
				if( isset($_GET['pageSize']) && ($_GET['pageSize']>0) && ($_GET['pageSize']<101) ){
					$pageSize = $_GET['pageSize'];
				}
				if(isset($_GET['currentPage'])){
					$currentPage = $_GET['currentPage'];
				}
				if(isset($_GET['showStatus'])){
					$showStatus = $_GET['showStatus'];
				}
				if(isset($_GET['paymentFilterID'])){
					$paymentFilterID = $_GET['paymentFilterID'];
				}
				if(isset($_GET['memberID'])){
					$memberID = $_GET['memberID'];
				}
				if(isset($_GET['memberPrincipal'])){
					$memberPrincipal = $_GET['memberPrincipal'];
				}
				if(isset($_GET['beginDate'])){
					$beginDate = strtotime(str_replace("T", " ",$_GET['beginDate'])." GMT");
				}
				if(isset($_GET['endDate'])){
					$endDate = strtotime(str_replace("T", " ",$_GET['endDate'])." GMT");
				}
				
			}
			
			$currentStartEntry = $currentPage * $pageSize;
			
			$accounts_array = array();
			$accounts_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.id='".mysqli_real_escape_string($db, intval($accountID))."' AND uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='".$this->user['id']."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
			if($accounts = mysqli_fetch_array($accounts_q)){

				$totalCount = 0;
				$balance = 0.00000;
				
				$elements_array = array();
				
				$user_journal_q = @mysqli_query($db,$test = "SELECT * FROM user_journal WHERE currency='".$accounts['currency']."' AND trading_account='".$accounts['trading_name']."' AND user_id='".$this->user['id']."' ORDER BY id DESC LIMIT $currentStartEntry, $pageSize ");
				while($user_journal = @mysqli_fetch_array($user_journal_q)){
					$totalCount++;
					
					$currency_q = @mysqli_query($db, $test = "SELECT * FROM currencies WHERE currency='".$user_journal['currency']."'") or die($test . mysqli_error($db));
					$currency = @mysqli_fetch_array($currency_q);
					
					$trading_name_q = @mysqli_query($db, $test = "SELECT * FROM user_account_currencies WHERE trading_name='".$user_journal['trading_account']."'");
					$trading_name = @mysqli_fetch_array($trading_name_q);
					$account_name_q = @mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, users u WHERE u.id=us.user_id AND us.id=uac.user_space_id AND uac.currency_id='".$currency['id']."' AND uac.trading_name='".$user_journal['with_account']."'");
					$account_name = @mysqli_fetch_array($account_name_q);
					
					array_push($elements_array, array("id"=>$user_journal['id'],
													   "date"=>date("Y-m-d\TH:i:s",$user_journal['tid']).".000+0000",
													   "formattedDate"=>date("Y-m-d",$user_journal['tid']),
													   "processDate"=>date("Y-m-d\TH:i:s",$user_journal['tid']).".000+0000",
													   "formattedProcessDate"=>date("Y-m-d",$user_journal['tid']),
													   "amount"=>floatval($user_journal['amount']),
													   "formattedAmount"=>$user_journal['amount']." ".$accounts['currency'],
													   "transferType"=>array("id"=>$currency['id'],
																			"name"=>$currency['currency']." exchange",
																			"from"=>array("id"=>$trading_name['id'],
																						  "name"=>$user_journal['trading_account'],
																						  "currency"=>array("id"=>$currency['id'],
																											"symbol"=>$currency['currency'],
																											"name"=>$currency['currency'])),
																			"to"=>array("id"=>$account_name['user_account_currencies_id'],
																						  "name"=>$user_journal['with_account'],
																						  "currency"=>array("id"=>$currency['id'],
																											"symbol"=>$currency['currency'],
																											"name"=>$currency['currency']))),
														"description"=>$user_journal['description'],
														"member"=>array("id"=>$account_name['user_account_currencies_id'],
																		"name"=>$account_name['trading_name'],
																		"username"=>$account_name['user_name'],
																		"email"=>$account_name['email'])));
				}

				$default = false;
				if($accounts['currency_id']==1)
					$default = true;
				$accounts_array = array( "currentPage" => $currentPage,
						"pageSize" => $pageSize,
						"totalCount" => $totalCount,
						"elements" => $elements_array);


			}
			$result = new Response(200, $accounts_array);
		} else {
			throw new Tonic\NotFoundException;
		}
		return $result;
	}


	/**
	 * Condition method to turn output into JSON.
	 *
	 * This condition sets a before and an after filter for the request and response. The
	 * before filter decodes the request body if the request content type is JSON, while the
	 * after filter encodes the response body into JSON.
	 */
	protected function json()
	{
		$this->before(function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode($request->data);
			}
		});
		$this->after(function ($response) {
			$response->contentType = "application/json";
			if (isset($_GET['jsonp'])) {
				$response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
			} else {
				$response->body = json_encode($response->body);
			}
		});
	}
}



/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /accounts/transferData/:transactionID
 */
class accountTransferData extends Resource
{

	private $user;
	private $username;

	/**
	 * The setup() method is called when the resource is executed. We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
		require("rest_connect.php");
		require("../password.php");
		if(isset($_SERVER['PHP_AUTH_USER'])&&isset($_SERVER['PHP_AUTH_PW'])){

			//echo "Attempting to authenticate user.\n";
			$users_q = mysqli_query($db,"SELECT * FROM users WHERE user_name='" . mysqli_real_escape_string($db,$_SERVER['PHP_AUTH_USER']) ."'");
			$users = mysqli_fetch_array($users_q);
			//echo "username:".$users['user_name']."\n";
			//echo "password:".$_SERVER['PHP_AUTH_PW']."\n";
			//echo "check1".password_verify($_SERVER['PHP_AUTH_PW'],$users['password2'])."\n";
			//echo "check2".password_verify($_SERVER['PHP_AUTH_PW'],$users['password'])."\n";
			if(password_verify($_SERVER['PHP_AUTH_PW'],$users['password2']) OR (password_verify($_SERVER['PHP_AUTH_PW'], $users['password']))){
				//echo "verified\n";
				$this->user = $users;
				$this->username = $users['user_name'];
			} else {
				throw new Tonic\UnauthorizedException;
			}
		} else {
			throw new Tonic\UnauthorizedException;
		}
	}

	/**
	 * Use this method to handle GET HTTP requests.
	 *
	 * The optional :accountID parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 *
	 * @method GET
	 * @param  str $accountID
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function sayAccountsTransferData($transactionID = 0)
	{
		if($transactionID != 0){
			require("rest_connect.php");
			
				$elements_array = array();
				
				$user_journal_q = @mysqli_query($db,$test = "SELECT * FROM user_journal WHERE id='".$transactionID."' AND user_id='".$this->user['id']."' ORDER BY tid DESC ");
				while($user_journal = @mysqli_fetch_array($user_journal_q)){
					
					$currency_q = @mysqli_query($db, $test = "SELECT * FROM currencies WHERE currency='".$user_journal['currency']."'") or die($test . mysqli_error($db));
					$currency = @mysqli_fetch_array($currency_q);
					
					$trading_name_q = @mysqli_query($db, $test = "SELECT * FROM user_account_currencies WHERE currency_id='".$currency['id']."' AND trading_name='".$user_journal['trading_account']."'");
					$trading_name = @mysqli_fetch_array($trading_name_q);
					$account_name_q = @mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, users u WHERE u.id=us.user_id AND us.id=uac.user_space_id AND uac.currency_id='".$currency['id']."' AND uac.trading_name='".$user_journal['with_account']."'");
					$account_name = @mysqli_fetch_array($account_name_q);

					
					
					$elements_array = array("id"=>intval($user_journal['id']),
													   "date"=>date("Y-m-d\TH:i:s",$user_journal['tid']).".000+0000",
													   "formattedDate"=>date("Y-m-d",$user_journal['tid']),
													   "processDate"=>date("Y-m-d\TH:i:s",$user_journal['tid']).".000+0000",
													   "formattedProcessDate"=>date("Y-m-d",$user_journal['tid']),
													   "amount"=>floatval($user_journal['amount']-0.000001),
													   "formattedAmount"=> number_format($user_journal['amount'],2)." ".$user_journal['currency'],
													   "transferType"=>array("id"=>$currency['id'],
																			"name"=>$currency['currency']." exchange",
																			"from"=>array("id"=>intval($trading_name['id']),
																						  "name"=>$user_journal['trading_account'],
																						  "currency"=>array("id"=>$currency['id'],
																											"symbol"=>$currency['currency'],
																											"name"=>$currency['currency'])),
																			"to"=>array("id"=>intval($account_name['user_account_currencies_id']),
																						  "name"=>$user_journal['with_account'],
																						  "currency"=>array("id"=>$currency['id'],
																											"symbol"=>$currency['currency'],
																											"name"=>$currency['currency']))),
													   "description"=>$user_journal['description'],
													   "member"=>array("id"=>$account_name['user_account_currencies_id'],
																		"name"=>$account_name['trading_name'],
																		"username"=>$account_name['user_name'],
																		"email"=>$account_name['email']));
				//note to implement contacts check																							
				$accounts_array = array("accountHistoryTransfer" => $elements_array ,
					                	"canAddRelatedMemberAsContact" => false );
				
				
				


			}
			$result = new Response(200, $accounts_array);
		} else {
			throw new Tonic\NotFoundException;
		}
		return $result;
	}


	/**
	 * Condition method to turn output into JSON.
	 *
	 * This condition sets a before and an after filter for the request and response. The
	 * before filter decodes the request body if the request content type is JSON, while the
	 * after filter encodes the response body into JSON.
	 */
	protected function json()
	{
		$this->before(function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode($request->data);
			}
		});
		$this->after(function ($response) {
			$response->contentType = "application/json";
			if (isset($_GET['jsonp'])) {
				$response->body = $_GET['jsonp'].'('.json_encode($response->body, JSON_NUMERIC_CHECK ).');';
			} else {
				$response->body = json_encode($response->body, JSON_NUMERIC_CHECK );
			}
		});
	}
}


