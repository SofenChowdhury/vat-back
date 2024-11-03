<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <link href="https://fonts.googleapis.com/css?family=Work+Sans:200,300,400,500,600,700" rel="stylesheet">

    <!-- CSS Reset : BEGIN -->
    <style>

        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,
body {
    margin: 0 auto !important;
    padding: 0 !important;
    height: 100% !important;
    width: 100% !important;
    background: #f1f1f1;
}

/* What it does: Stops email clients resizing small text. */
* {
    -ms-text-size-adjust: 100%;
    -webkit-text-size-adjust: 100%;
}

/* What it does: Centers email on Android 4.4 */
div[style*="margin: 16px 0"] {
    margin: 0 !important;
}

/* What it does: Stops Outlook from adding extra spacing to tables. */
table,
td {
    mso-table-lspace: 0pt !important;
    mso-table-rspace: 0pt !important;
}

/* What it does: Fixes webkit padding issue. */
table {
    border-spacing: 0 !important;
    border-collapse: collapse !important;
    table-layout: fixed !important;
    margin: 0 auto !important;
}

/* What it does: Uses a better rendering method when resizing images in IE. */
img {
    -ms-interpolation-mode:bicubic;
}

/* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
a {
    text-decoration: none;
}

/* What it does: A work-around for email clients meddling in triggered links. */
*[x-apple-data-detectors],  /* iOS */
.unstyle-auto-detected-links *,
.aBn {
    border-bottom: 0 !important;
    cursor: default !important;
    color: inherit !important;
    text-decoration: none !important;
    font-size: inherit !important;
    font-family: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
}

/* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
.a6S {
    display: none !important;
    opacity: 0.01 !important;
}

/* What it does: Prevents Gmail from changing the text color in conversation threads. */
.im {
    color: inherit !important;
}

/* If the above doesn't work, add a .g-img class to any image in question. */
img.g-img + div {
    display: none !important;
}

/* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
/* Create one of these media queries for each additional viewport size you'd like to fix */

/* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
@media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
    u ~ div .email-container {
        min-width: 320px !important;
    }
}
/* iPhone 6, 6S, 7, 8, and X */
@media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
    u ~ div .email-container {
        min-width: 375px !important;
    }
}
/* iPhone 6+, 7+, and 8+ */
@media only screen and (min-device-width: 414px) {
    u ~ div .email-container {
        min-width: 414px !important;
    }
}
    </style>

    <!-- CSS Reset : END -->

    <!-- Progressive Enhancements : BEGIN -->
    <style>

	    .primary{
	background: #17bebb;
}
.bg_white{
	background: #ffffff;
}
.bg_light{
	background: #f7fafa;
}
.bg_black{
	background: #000000;
}
.bg_dark{
	background: rgba(0,0,0,.8);
}
.email-section{
	padding:2.5em;
}

/*BUTTON*/
.btn{
	padding: 10px 15px;
	display: inline-block;
}
.btn.btn-primary{
	border-radius: 5px;
	background: #6217be;
	color: #ffffff;
}
.btn.btn-white{
	border-radius: 5px;
	background: #ffffff;
	color: #000000;
}
.btn.btn-white-outline{
	border-radius: 5px;
	background: transparent;
	border: 1px solid #fff;
	color: #fff;
}
.btn.btn-black-outline{
	border-radius: 0px;
	background: transparent;
	border: 2px solid #000;
	color: #000;
	font-weight: 700;
}
.btn-custom{
	color: rgba(0,0,0,.3);
	text-decoration: underline;
}

h1,h2,h3,h4,h5,h6{
	font-family: 'Work Sans', sans-serif;
	color: #000000;
	margin-top: 0;
	font-weight: 400;
}

body{
	font-family: 'Work Sans', sans-serif;
	font-weight: 400;
	font-size: 15px;
	line-height: 1.8;
	color: rgba(0,0,0,.4);
}

a{
	color: #17bebb;
}

table{
}
/*LOGO*/

.logo h1{
	margin: 0;
}
.logo h1 a{
	color: #17bebb;
	font-size: 24px;
	font-weight: 700;
	font-family: 'Work Sans', sans-serif;
}

/*HERO*/
.hero{
	position: relative;
	z-index: 0;
}

.hero .text{
	color: rgba(0,0,0,.3);
}
.hero .text h2{
	color: #000;
	font-size: 34px;
	margin-bottom: 15px;
	font-weight: 300;
	line-height: 1.2;
}
.hero .text h3{
	font-size: 24px;
	font-weight: 200;
}
.hero .text h2 span{
	font-weight: 600;
	color: #000;
}


/*PRODUCT*/
.product-entry{
	display: block;
	position: relative;
	float: left;
	padding-top: 20px;
}
.product-entry .text{
	/* width: calc(100% - 125px); */
	padding-left: 20px;
}
.product-entry .text h3{
	margin-bottom: 0;
	padding-bottom: 0;
}
.product-entry .text p{
	margin-top: 0;
}
.product-entry img, .product-entry .text{
	float: left;
}

ul.social{
	padding: 0;
}
ul.social li{
	display: inline-block;
	margin-right: 10px;
}

/*FOOTER*/

.footer{
	border-top: 1px solid rgba(0,0,0,.05);
	color: rgba(0,0,0,.5);
}
.footer .heading{
	color: #000;
	font-size: 20px;
}
.footer ul{
	margin: 0;
	padding: 0;
}
.footer ul li{
	list-style: none;
	margin-bottom: 10px;
}
.footer ul li a{
	color: rgba(0,0,0,1);
}


@media screen and (max-width: 500px) {


}


    </style>


</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f1f1f1;">
	<center style="width: 100%; background-color: #f1f1f1;">
    <div style="display: none; font-size: 1px;max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
      &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    <div style="max-width: 800px; margin: 0 auto; margin-top: 2em; margin-bottom: 2em" class="email-container">
    	<!-- BEGIN BODY -->
      <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
      	<tr>
          <td valign="top" class="bg_white" style="padding: 1em 2.5em 0 2.5em;">
          	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          		<tr>
          			<td class="logo" style="text-align: left;">
			            <h1><a href="https://fairelectronics.com.bd">
                            <img src="https://fairelectronics.com.bd/pub/media/logo/Fair-Electronics_1_.png" alt="FairElectronics">    
                        </a></h1>
			          </td>
          		</tr>
          	</table>
          </td>
	      </tr><!-- end tr -->
				<tr>
          <td valign="middle" class="hero bg_white" style="padding: 2em 0 2em 0;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            	<tr>
            		<td style="padding: 0 2.5em; text-align: left;">
            			<div class="text">
            				<h2>Thank you for your order</h2>
            				<h3>Your order number is <strong>{{$order->order_number}}</strong> </h3>
            			</div>
            		</td>
            	</tr>
            </table>
          </td>
	      </tr><!-- end tr -->
	      <tr>
	      	<table class="bg_white" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
	      		    <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
					    <th width="70%" style="text-align:left; padding: 0 2.5em; color: #000; padding-bottom: 20px">Item</th>
					    <th width="15%" style="text-align:left; padding: 0 2.5em; color: #000; padding-bottom: 20px">QTY</th>
					    <th width="15%" style="text-align:right; padding: 0 2.5em; color: #000; padding-bottom: 20px">Price</th>
					</tr>
                        @php
                            $i = 0;
                            $total = 0;
                        @endphp
                        @foreach($order->orderItems as $item)
                            @php
                                $i++;
                                $total += $item->item_price*$item->qty;
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                                <td valign="middle" width="70%" style="text-align:left; padding: 0 2.5em;">
                                    <div class="product-entry">
                                        <img src="{{"http://ec2-13-229-131-244.ap-southeast-1.compute.amazonaws.com/storage/thumbnails/".$item->itemInfo->photo}} " alt="{{ $item->name }}" style="height: 80px; margin-bottom: 20px; display: block;"/>
                                        <div class="text">
                                            <h3>{{ $item->name }}</h3>
                                            <small style="color: rgb(17, 17, 17)">Price: {{number_format($item->item_price, 0)}}</small>
                                            <p></p>
                                        </div>
                                    </div>
                                </th>
                                <td valign="middle" width="15%" style="text-align:left; padding: 0 2.5em; color: rgb(33, 33, 33);">
                                    {{ $item->qty }}
                                </th>
                                <td valign="middle" width="15%" style="text-align:right; color: rgb(33, 33, 33); padding: 0 2.5em;">
                                    {{ number_format($item->item_price*$item->qty) }}
                                </th>
                            </tr>
                        @endforeach
                    <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                        <th valign="middle" colspan="2" style="text-align:right; color: #000; padding: 1em 1em;">
                            VAT & AIT
                        </th>
                        <th valign="middle" style="text-align:right; color: #000; padding: 1em 1em;">
                            {{0}}
                        </th>
                    </tr>
                    <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                        <th valign="middle" colspan="2" style="text-align:right; color: #000; padding: 1em 1em;">
                            Shipping Charge
                        </th>
                        <th valign="middle" style="text-align:right; color: #000; padding: 1em 1em;">
                            {{$order->vat}}
                        </th>
                    </tr>
                    <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                        <th valign="middle" colspan="2" style="text-align:right; color: #000; padding: 1em 1em;">
                            Total
                        </th>
                        <th valign="middle" style="text-align:right; color: #000; padding: 1em 1em;">
                            {{number_format($total+$order->vat)}}
                        </th>
                    </tr>
					  <tr>
					  	<td valign="middle" style="text-align:left; padding: 2em 2em;">
					  		<p><a href="{{"http://ec2-13-229-131-244.ap-southeast-1.compute.amazonaws.com/admin/orders/".$order->id}}" class="btn btn-primary">See Order Details</a></a></p>
					  	</td>
					  </tr>
	      	</table>
	      </tr><!-- end tr -->
      <!-- 1 Column Text + Button : END -->
      </table>
      <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
      	<tr>
          <td valign="middle" class="bg_light footer email-section">
            <table>
            	<tr>
                <td valign="top" width="50%" style="padding-top: 20px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                      <td style="text-align: left; padding-right: 10px;">
                      	<h3 class="heading">Billing</h3>
                        <table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                            <tr>
                                <th width="35%">Name</th>
                                <td width="65%">: {{ $order->customer_name}}</td>
                            </tr> 
                            <tr>
                                <th>Company</th>
                                <td>: {{ $order->company->name}}</td>
                            </tr>  
                            <tr>
                                <th>Branch</th>
                                <td>: {{ $order->shop->title}}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>: {{ $order->customer_phone}}</td>
                            </tr> 
                            <tr>
                                <th>Email</th>
                                <td>: {{ $order->customer_email}}</td>
                            </tr>  
                            <tr>
                                <th>City</th>
                                <td>: {{ $order->customer_city}}</td>
                            </tr>
                            <tr>
                                <th>Thana</th>
                                <td>: {{ $order->customer_thana}}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>: {{ $order->customer_address}}</td>
                            </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
                <td valign="top" width="50%" style="padding-top: 20px;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                      <td style="text-align: left; padding-left: 10px;">
                      	<h3 class="heading">Shipping</h3>
                      	<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                            <tr>
                                <th width="35%">Name</th>
                                <td width="65%">: {{ $order->shipping_name? $order->shipping_name: $order->customer_name}}</td>
                            </tr> 
                            
                            <tr>
                                <th>Phone</th>
                                <td>: {{ $order->shipping_phone? $order->shipping_phone: $order->customer_phone}}</td>
                            </tr> 
                            <tr>
                                <th>Email</th>
                                <td>: {{ $order->shipping_email? $order->shipping_email : $order->customer_email}}</td>
                            </tr>  
                            <tr>
                                <th>City</th>
                                <td>: {{ $order->shipping_city? $order->shipping_city : $order->customer_city}}</td>
                            </tr>
                            <tr>
                                <th>Thana</th>
                                <td>: {{ $order->shipping_area? $order->shipping_area : $order->customer_thana}}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>: {{ $order->shipping_address? $order->shipping_address : $order->customer_address}}</td>
                            </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr><!-- end: tr -->
        <tr>
          <td class="bg_white" style="text-align: center;">
          	<p> Powered by  <a href="#" style="color: rgba(0,0,0,.8);">FairElectronics</a></p>
          </td>
        </tr>
      </table>

    </div>
  </center>
</body>
</html>
