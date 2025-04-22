<?php
error_reporting(1);
header("Access-Control-Allow-Origin: *"); 
header("Content-Security-Policy: Frame-Ancestors https://$_GET[shop] https://admin.shopify.com;");
header("X-Frame-Options: ALLOWALL");
header("X-XSS-Protection: 1; mode=block");

?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap-responsive.min.css">
<style>			  
p {
  font-size: 16px !important;
  line-height: 25px;
  margin-bottom: 20px;
}
body {
  margin: 0;
  padding: 0;
  font-family: -apple-system,BlinkMacSystemFont,San Francisco,Segoe UI,Roboto,Helvetica Neue,sans-serif;
}
.contentdata {
  background: #eee none repeat scroll 0 0;
  padding: 20px;
}
.contentdata img {
  margin-top: 25px;
  width: 100%;
}
ul li {
  
  font-weight: bold;
}

h2 {
    font-size: 21px!important;
    line-height: 28px !important;
}
</style>  
    <div class="main">
	<div class="main-inner">
	    <div class="container">
		   <!--<h6 class="bigstats" style="text-align:right; margin-top: 40px;"><a href="javascript:;" onclick="history.go(-1); return false;" style="font-size: 22px;"> GO Back</a></h6>-->
            <div class="row" id="content_tabs" style="margin-top:30px;">
                <div class="span12 tab-content active">
                    <div class="widget widget-nopad">
                        <div class="widget-header" style="">
                            <h3 style="">Uninstall Instructions</h3>
                        </div>
                        <!-- /widget-header -->
                        <div class="widget-content">
                            <div class="widget big-stats-container">
                                <div class="widget-content">
									<div class="contentdata">
									<h2>You have declined Recurring Billing Charge, due to which you won't be able to use app services. Please follow these steps for uninstallation of app: </h2>
									<p></p><p></p>
									<ul>
									<br><li>Go to Apps in Admin.</li>
									<br><li>Delete "Salonist ".</li>
									<img src="images/delete.png">
									<br> <br>
									<br><li>Everything is done now!</li>
									<br><li>Reinstall the app and approve subsciption charges for app!</li>
									<br></ul>
									</div>
								</div>
                                <!-- /widget-content --> 
							</div>
                        </div>
                    </div>
                    <!-- /widget --> 
                </div>
            </div>
            <!-- /row --> 
        </div>
        <!-- /container --> 
    </div>
    <!-- /main-inner --> 
</div>
