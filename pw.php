<? //(c)GPL bruno and michael, based on https://raw.github.com/ircmaxell/password_compat/

require_once('password.php');
require_once('connect.php');

$user_email = isset($_REQUEST['email'])?$_REQUEST['email']:'';
$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
$length = 8;
$new_pw = substr(str_shuffle($chars),0,$length);
$new_pw_hash = password_hash($new_pw, PASSWORD_BCRYPT);

if ($user_email) { //NEW PASSWORD
  $subject = "OpenMoney: new password";
  $msg = "you apparently requested a new password for your OpenMoney account.  Here it is: <b>$new_pw</b> . 
          <br>You can still use your old one.  <a href={$CFG->url}>Open Money</a>
          <br>We recommend you click on Settings and change your password to something secret and memorable for you";
  $update = exec_sql("update users set password= ? where email = ? and confirmed>'0'",
                     array($new_pw_hash, $user_email),"creating password",2);
  if($update AND email_letter($user_email, $CFG->admin_email, $subject, $msg)) { 
    echo "<br>new password sent to $user_email<p><a href={$CFG->url}index.php>back</a>"; 
  }
}
$confirm = isset($_REQUEST['confirm'])?$_REQUEST['confirm']:'';
$sandbox = ($CFG->site_type!='Live')?1:0;  //is this a live site or a sandbox?

if ($confirm) {
  $unconfirmed_users = exec_sql("select * from users where confirmed>'0' and password2=''",array(),"new password-less members");
  foreach($unconfirmed_users as $row) {
    $username = $row['user_name'];
    $userid = $row['id'];
    $dupl = "ON DUPLICATE KEY UPDATE id=id"; //to deal with duplicates on unique keys
    // in SANDBOX mode, create user's private space and make him steward of it
    $spaceid = $sandbox?exec_sql("insert into spaces (space_name) values (?)",array($username),
			      "creating unique space for $username",2):'';
    $userspaceid = $sandbox?exec_sql("insert into user_spaces (space_id,user_id,class) values (?,?,'steward')",array($spaceid,$userid),
				  "making $username a steward of his own space",2):'';
    // make user a member of a chosen SPACE and CURRENCY
    $space_name = isset($_REQUEST['space_name'])?$_REQUEST['space_name']:'';
    $space_id = exec_sql("select id from spaces where space_name = ?",array($space_name),'space_id',1); 
    $space_id = $space_id?$space_id:1; //default to the first space
    $currency = isset($_REQUEST['currency'])?$_REQUEST['currency']:'cc';
    $currency_id = exec_sql("select id from currencies where currency = ?",array($currency),'currency_id',1); 
    $currency_id = $currency_id?$currency_id:1; //default to the first currency
    $insert1 = exec_sql("insert into user_spaces (space_id,user_id,class) values (?,?,'user') ON DUPLICATE KEY UPDATE id=id",
 			array($space_id,$userid),"inserting $username into user_space ",2);
    $insert2 = exec_sql("insert into user_account_currencies (user_space_id,trading_name,currency_id) values (?,?,?) $dupl",
 			array($insert1,$username,$currency_id),"inserting $username into user_account_currencies",2);
    $address = $row['email'];
    $new_pw = substr(str_shuffle($chars),0,$length);
    $new_pw_hash = password_hash($new_pw, PASSWORD_BCRYPT);
    $fname = $row['fname'];
    $msg = "Hello $fname <p>Your account on OpenMoney has been confirmed. <p> please go to {$CFG->url} 
     <p>your username is now: <b>$username</b> <br>and your password is <b>$new_pw</b> <p> Please change it right away by clicking on 
       settings in the top menu <p>( {$CFG->url}/settings.php )<p> Welcome to OpenMoney - Michael Linton"; 
    $msg2 = "$fname signed up for an account on OpenMoney {$CFG->url} ";
    $subject = "OpenMoney: new account for $username";
    if(email_letter($address, $CFG->admin_email, $subject, $msg)) { echo "<br>sending confirmation email to $address"; }
    $id = $row['id'];
    $update = exec_sql("update users set password2= ? where id = ?",array($new_pw_hash, $id),"creating password",2);
    email_letter($CFG->admin_email,$address,$subject,$msg2);
  }
  echo "<br><a href=main.php>back</a>";
  exit;
}

// check if all passwords are hashed
$blank_passwords = exec_sql("SELECT * FROM users WHERE password2='' and confirmed>'0'",array(),"blank passwords");
foreach($blank_passwords as $row){
  $old_pw = $row['password'];
  $id = $row['id'];
  $new_pw = password_hash($old_pw, PASSWORD_BCRYPT);
  $update1 = exec_sql("update users set password2=? WHERE id=?",array($new_pw,$id),"updating blank passwords",2);
  echo "<br>$id: new_pw";
}
?>
