<?php
/**
 * 查询账单状态，并返回
 * http://docs.whmcs.com/Creating_Pages
 * http://stackoverflow.com/questions/20087207/call-whmcs-sessionuid-from-external-domain
 */
# Required File Includes
$whmcs_version = "6";
if($whmcs_version == "5"){
    //WHMCS5.x 包含以下2个文件
    require_once __DIR__ . '/../../../dbconnect.php';
    require_once __DIR__ . '/../../../includes/functions.php';
} else if($whmcs_version == "6") {
    //WHMCS 6.x 包含以下1个文件
    require_once __DIR__ . '/../../../init.php';
}

$invoiceid = $_REQUEST['invoiceid'];

$ca = new WHMCS_ClientArea();

$userid = $ca->getUserID() ;

if($userid == 0){
    exit;
}
//echo $userid;
$query = "select * from tblinvoices where id='".$invoiceid."' and userid='".$userid."'";
$result = mysql_query($query) or die(mysql_error());
if($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

    $status=$row['status'];
    $paymentmethod=$row['paymentmethod'];

}
if($status == "Paid"){
    if($paymentmethod == "alipay"){
        echo "SUCCESS";
    }else{
        echo "FAIL. It is not alipay";
    }
}else{
    echo "FAIL. Not paid";
}
if ($_SESSION['uid'] and false) {

    $query="SELECT * FROM tblclients WHERE id='" .$_SESSION['uid'] . "'";

    $result = mysql_query($query) or die(mysql_error());
    if($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

        $clientsdetails['firstname']=$row['firstname'];
        $clientsdetails['lastname']=$row['lastname'];
        $clientsdetails['email']=$row['email'];

    }

    echo ($row['firstname'].' '.$row['lastname'].' | '.$row['email']);
    $GLOBALS['_qq_']['response_type'] = $row['email'];
    $GLOBALS['_oauth_'] = $GLOBALS['_qq_']['redirect_uri'];
    $GLOBALS['_qq_']['appid'] = sha1($GLOBALS['_qq_']['appid'].$GLOBALS['_qq_']['appkey'].$GLOBALS['_qq_']['response_type']);
    header("Location:".get_qq_login_url());
}
