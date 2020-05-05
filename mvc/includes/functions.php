<?php
function sendEmail($address, $subject, $message, $fromName = 'UGO800 Support', $fromEmail = 'support@west-travel.info', $replyTo = 'support@west-travel.info', $is_html = TRUE) {
      require_once 'includes/PHPMailer-master/class.phpmailer.php';
      $mail = new PHPMailer();
      $mail->IsSMTP();
      $mail->CharSet="UTF-8";
      //$mail->SMTPSecure = 'tls';
      $mail->Host = 'mail.west-travel.info';
      $mail->Port = 25;
      $mail->Username = 'support@west-travel.info';
      $mail->Password = 'liucg1234';
      $mail->SMTPAuth = true;
      if(isset($_GET['debug']) && $_GET['debug']==3){$mail->SMTPDebug  = 1; }
      //	$mail->SMTPDebug  = 1;

      $mail->From = $fromEmail;
      $mail->FromName = $fromName;
      $mail->AddAddress($address);
      $mail->AddReplyTo($replyTo, $fromName);

      $mail->IsHTML($is_html);
      $mail->Subject = $subject;
      $mail->Body    = $message;

      if(!$mail->Send()){
      $output['success'] = FALSE;
      $output['error'] ="Mailer Error: " . $mail->ErrorInfo;
      $headers  = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
      $headers .= "From: " . $fromEmail . "\r\n";
      $sent = mail($address,$subject,$message, $headers);
      }else{
      $output['success'] = TRUE;
      }
      return $output;
}