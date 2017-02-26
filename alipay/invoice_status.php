<?php
require_once __DIR__ . '/../../../init.php';

use \Illuminate\Database\Capsule\Manager as Capsule;

$invoiceid = $_REQUEST['invoiceid'];

$ca = new WHMCS_ClientArea();

$userid = $ca->getUserID() ;

if($userid == 0){
    exit;
}
//echo $userid;

$query = Capsule::table('tblinvoices')->where('id', $invoiceid)->where('userid', $userid);

if( $query ) {

    $status 		= $query->status;
    $paymentmethod 	= $query->paymentmethod;

}
if($status == "Paid"){
    if($paymentmethod == "alipay"){
        echo "SUCCESS";
    }else{
        echo "FAIL. It is not alipay";
    }
} else {
    echo "FAIL. Not paid";
}
