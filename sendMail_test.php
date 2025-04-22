<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(!empty($_POST['htmldata'])){
      $orderId=$_POST['orderId'];
      $emailId=$_POST['emailId'];
      require_once 'mpdf60/vendor/autoload.php';
     //define('_MPDF_TTFONTPATH', __DIR__ . '/mpdf60/ttfonts/');
      $mpdf = new mPDF('utf-8', array(200, 254));
      ob_start();
      $data = $_POST['htmldata'];
      $namepdf = md5($orderId);
      $fname ="invoicePdf/".$namepdf.".pdf"; // name the file
      ob_end_clean();
      $mpdf->WriteHTML($data);
      $mpdf->Output( $fname,'F');

     // the message
$msg = "First line of text\nSecond line of text";

// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($msg,70);

// send email
mail($emailId,"My subject",$msg);
    echo $emailId.'======='.$fname.'=============='.$orderId;
	echo 1;
} else {
   echo 0;
}
?>