<?php
//$partner         = "";        //合作伙伴ID
//$security_code   = "";        //安全检验码
//$seller_email    = "";        //卖家支付宝帐户
function alipay_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"支付宝（NeWorld）"),
     "seller_email" => array("FriendlyName" => "卖家支付宝帐户", "Type" => "text", "Size" => "32", ),
     "partnerID" => array("FriendlyName" => "合作伙伴ID", "Type" => "text", "Size" => "32", ),
     "security_code" => array("FriendlyName" => "安全检验码", "Type" => "text", "Size" => "32", ),
     "transport" => array ("FriendlyName" => "访问模式", "Type" => "dropdown", "Options" =>
                              "http,https", "Description" => "根据服务器是否支持SSL访问而选择", "Default" => "1", ),
	 "multi_site" => array("FriendlyName" => "启动多站点兼容模式", "Type" => "yesno", "Size" => "50", "Description" => "用于多个站点同一支付宝商家接口",),
	 "site_security_code" => array("FriendlyName" => "站点识别码", "Type" => "text", "Size" => "50", "Description" => "兼容模式下站点识别码 , 避免支付宝重单",),
     "testmode" => array("FriendlyName" => "测试模式", "Type" => "yesno", "Description" => "测试模式(暂时不可用)", ),
    );
	return $configarray;
}

function alipay_link($params) {

	$_input_charset  = "utf-8";   //字符编码格式 目前支持 GBK 或 utf-8
	$sign_type       = "MD5";     //加密方式 系统默认(不要修改)
	$transport       = $params['transport'];   //访问模式,你可以根据自己的服务器是否支持ssl访问而选择http以及https访问模式(系统默认,不要修改)
	
	# Gateway Specific Variables
	$gatewayPID = $params['partnerID'];
	$gatewaySELLER_EMAIL = $params['seller_email'];
	$gatewaySECURITY_CODE = $params['security_code'];
	$TEST_MODE=$params['testmode'];

	# Invoice Variables
	if ($params['multi_site']) {
		$invoiceid = $params['site_security_code']."-".$params['invoiceid'];
	} else {
		$invoiceid = $params['invoiceid'];
	}
	$description = $params["description"];
	$amount = $params['amount']; # Format: ##.##

	# System Variables
	$companyname 		= $params['companyname'];
	$systemurl 			= $params['systemurl'];
	$currency 			= $params['currency'];
	$return_url			= $systemurl."/modules/gateways/alipay/return.php";
	$notify_url			= $systemurl."/modules/gateways/alipay/notify.php";
	$qcode = array(
		"service"         => "create_direct_pay_by_user",  					//交易类型
		"partner"         => $gatewayPID,          							//合作商户号
		"return_url"      => $return_url,         							//同步返回
		"notify_url"      => $notify_url,       							//异步返回
		"_input_charset"  => $_input_charset,   							//字符集，默认为GBK
		"subject"         => "$companyname 账单 #$invoiceid",        		//商品名称，必填
		"body"            => $description,        							//商品描述，必填
		"out_trade_no"    => $invoiceid,      								//商品外部交易号，必填（保证唯一性）
		"total_fee"       => $amount,            							//商品单价，必填（价格不能为0）
		"payment_type"    => "1",               							//默认为1,不需要修改
		"qr_pay_mode"	  => "1",											//二维码模式
		"show_url"        => $systemurl,         							//商品相关网站
		"seller_email"    => $gatewaySELLER_EMAIL      						//卖家邮箱，必填
	);
	$webpay = array(
		"service"         => "create_direct_pay_by_user",  					//交易类型
		"partner"         => $gatewayPID,          							//合作商户号
		"return_url"      => $return_url,         							//同步返回
		"notify_url"      => $notify_url,       							//异步返回
		"_input_charset"  => $_input_charset,   							//字符集，默认为GBK
		"subject"         => "$companyname 账单 #$invoiceid",        		//商品名称，必填
		"body"            => $description,        							//商品描述，必填
		"out_trade_no"    => $invoiceid,      								//商品外部交易号，必填（保证唯一性）
		"total_fee"       => $amount,            							//商品单价，必填（价格不能为0）
		"payment_type"    => "1",               							//默认为1,不需要修改
		"show_url"        => $systemurl,         							//商品相关网站
		"seller_email"    => $gatewaySELLER_EMAIL      						//卖家邮箱，必填
	);

	$qcodepay = new alipay_service($qcode,$gatewaySECURITY_CODE,$sign_type);
	$webpay = new alipay_service($webpay,$gatewaySECURITY_CODE,$sign_type);
	$qcodelink=$qcodepay->create_url();
	$webpaylink=$webpay->create_url();
	
	$code = '<div class="alipay" style="max-width: 230px;margin: 0 auto"><div id="alipayimg" style="border: 1px solid #AAA;border-radius: 4px;overflow: hidden;margin-bottom: 5px;"><iframe src="'.$qcodelink.'" width="300" height="292" frameborder="0" scrolling="no" style="transform: scale(.9);margin: -50px 0 -24px -37px;"></iframe></div>';
	$code_ajax = '<a href="'.$webpaylink.'" target="_blank" id="alipayDiv" class="btn btn-success btn-block">前往支付宝进行支付</a></div>';
	$code_ajax = $code_ajax.'
<!--微信支付ajax跳转-->
	<script>
    //设置每隔 5000 毫秒执行一次 load() 方法
    setInterval(function(){load()}, 5000);
    function load(){
        var xmlhttp;
        if (window.XMLHttpRequest){
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{
            // code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                trade_state=xmlhttp.responseText;
                if(trade_state=="SUCCESS"){
                    document.getElementById("alipayimg").style.display="none";
                    document.getElementById("alipayDiv").innerHTML="支付成功";
                    //延迟 2 秒执行 tz() 方法
                    setTimeout(function(){tz()}, 5000);
                    function tz(){
                        window.location.href="'.$systemurl.'/viewinvoice.php?id='.$params['invoiceid'].'";
                    }
                }
            }
        }
        //invoice_status.php 文件返回订单状态，通过订单状态确定支付状态
        xmlhttp.open("get","'.$systemurl.'/modules/gateways/alipay/invoice_status.php?invoiceid='.$params['invoiceid'].'",true);
        //下面这句话必须有
        //把标签/值对添加到要发送的头文件。
        //xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        //xmlhttp.send("out_trade_no=002111");
        xmlhttp.send();
    }
</script>';
	
	$code = $code.$code_ajax;
	
	if (stristr($_SERVER['PHP_SELF'], 'viewinvoice')) {
		return $code;
	} else {
		return '<img style="width: 150px" src="'.$systemurl.'/modules/gateways/alipay/alipay.png" alt="支付宝支付" />';
	}
}


class alipay_service {

	var $gateway = "https://mapi.alipay.com/gateway.do?";         //支付接口
	var $parameter;       //全部需要传递的参数
	var $security_code;   //安全校验码
	var $mysign;          //签名

	//构造支付宝外部服务接口控制
	function alipay_service($parameter,$security_code,$sign_type = "MD5",$transport= "https") {
		$this->parameter      = $this->para_filter($parameter);
		$this->security_code  = $security_code;
		$this->sign_type      = $sign_type;
		$this->mysign         = '';
		$this->transport      = $transport;
		if($parameter['_input_charset'] == "")
		$this->parameter['_input_charset']='GBK';
		if($this->transport == "https") {
			$this->gateway = "https://mapi.alipay.com/gateway.do?";
		} else $this->gateway = "http://www.alipay.com/cooperate/gateway.do?";
		$sort_array  = array();
		$arg         = "";
		$sort_array  = $this->arg_sort($this->parameter);
		while (list ($key, $val) = each ($sort_array)) {
			$arg.=$key."=".$this->charset_encode($val,$this->parameter['_input_charset'])."&";
		}
		$prestr = substr($arg,0,count($arg)-2);  //去掉最后一个问号
		$this->mysign = $this->sign($prestr.$this->security_code);
	}

	function create_url() {
		$url         = $this->gateway;
		$sort_array  = array();
		$arg         = "";
		$sort_array  = $this->arg_sort($this->parameter);
		while (list ($key, $val) = each ($sort_array)) {
			$arg.=$key."=".urlencode($this->charset_encode($val,$this->parameter['_input_charset']))."&";
		}
		$url.= $arg."sign=" .$this->mysign ."&sign_type=".$this->sign_type;
		return $url;
	}

	function arg_sort($array) {
		ksort($array);
		reset($array);
		return $array;
	}

	function sign($prestr) {
		$mysign = "";
		if($this->sign_type == 'MD5') {
			$mysign = md5($prestr);
		}elseif($this->sign_type =='DSA') {
			//DSA 签名方法待后续开发
			die("DSA 签名方法待后续开发，请先使用MD5签名方式");
		}else {
			die("支付宝暂不支持".$this->sign_type."类型的签名方式");
		}
		return $mysign;
	}
	function para_filter($parameter) { //除去数组中的空值和签名模式
		$para = array();
		while (list ($key, $val) = each ($parameter)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para[$key] = $parameter[$key];
		}
		return $para;
	}
	//实现多种字符编码方式
	function charset_encode($input,$_output_charset ,$_input_charset ="utf-8" ) {
		$output = "";
		if(!isset($_output_charset) )$_output_charset  = $this->parameter['_input_charset'];
		if($_input_charset == $_output_charset || $input ==null) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}
}

?>