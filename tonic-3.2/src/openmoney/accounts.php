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
			$accounts_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='".$this->user['id']."'") or die($test . mysqli_error($db));
			while($accounts = mysqli_fetch_array($accounts_q)){
				
				$balance = 0.00000;
				$user_journal_q = @mysqli_query($db,$test = "SELECT * FROM user_journal WHERE currency='".$accounts['currency']."' AND user_id='".$this->user['id']."' ORDER BY tid DESC");
				$user_journal = @mysqli_fetch_array($user_journal_q);
				$balance = floatval($user_journal['balance']);
				$balance_decimal = number_format($balance,2);
				
				
				$default = false;
				if($accounts['currency_id']==1)
					$default = true;
				array_push($accounts_array,array('account' => array("id" => $accounts['user_account_currencies_id'],
												  "default" => $default,
												  "type" => array("id" => $accounts['user_space_id'],
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
class account extends Resource
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
			$accounts_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.id='".mysqli_real_escape_string($db, intval($accountID))."' AND uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='".$this->user['id']."'") or die($test . mysqli_error($db));
			while($accounts = mysqli_fetch_array($accounts_q)){
	
				$balance = 0.00000;
				$user_journal_q = @mysqli_query($db,$test = "SELECT * FROM user_journal WHERE currency='".$accounts['currency']."' AND user_id='".$this->user['id']."' ORDER BY tid DESC");
				$user_journal = @mysqli_fetch_array($user_journal_q);
				$balance = floatval($user_journal['balance']);
				$balance_decimal = number_format($balance,2);
	
	
				$default = false;
				if($accounts['currency_id']==1)
					$default = true;
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
