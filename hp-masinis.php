<?php
error_reporting(0);
/* connect */
$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname = 'edisikrl';

$link = mysql_connect($hostname, $username, $password);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db($dbname, $link);
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}
/* connect */

$consumerKey    = 'your-comsumer-key';
$consumerSecret = 'your-consumer-secret-key';
$oAuthToken     = 'your-oauth-token';
$oAuthSecret    = 'your-oauth-secret';

require_once('twitteroauth.php');

$tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);

$results = file_get_contents('http://search.twitter.com/search.json?q=%23edisiKRL');
$results = json_decode($results);

$results = array_reverse($results->results);
foreach($results as $item){    
    // if current tweet id not exists in database, then retweet and insert current tweet to database.
    $rs = mysql_query("SELECT * FROM `hapemasinis` WHERE tweet_id = '" . $item->id_str . "'");
    $num_rows = mysql_num_rows($rs);    
    if($num_rows == 0){
        if(strtolower($item->from_user) != 'edisikrl'){
            //retweet
            $statusText = 'RT @' . $item->from_user . ': ' . $item->text;
            $tweet->post('statuses/update', array('status' => $statusText));
            //insert
            mysql_query("INSERT INTO `hapemasinis` VALUES('".$item->id_str."','".$item->from_user."','".$item->text."','".$item->source."','".$item->created_at."')");
        }
    }
    
}

?>