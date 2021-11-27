<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '/usr/local/bin/vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

$to_myemail = 'alexemailserver5656@gmail.com';

function my_set_error($json, $msg_desc, $field = null, $field_msg = null){
  $json['status'] = 'error';
  $json['status_desc'] = $msg_desc;
  if(!empty($field)){
    $json['error_msg'][$field] = $field_msg;
  }
  return $json;
}

function my_validation($json, $from, $phone, $message){
  $msg_desc = "Invalid Input!";
  if (empty($from)) {
    $json = my_set_error($json, $msg_desc, 'f_email', 'This is required!');
  } elseif (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
    $json = my_set_error($json, $msg_desc, 'f_email', 'Invalid email format!');
  }
  if (empty($phone)) {
    $json = my_set_error($json, $msg_desc, 'f_phone', 'This is required!');
  }
  if (empty($message)) {
    $json = my_set_error($json, $msg_desc, 'f_message', 'This is required!');
  }
  return $json;
}

$json = array(
  'status' => "success",
  'status_desc' => 'Thanks! Your message has been sent!',
  'error_msg' => array(
    'f_email' => '',
    'f_phone' => '',
    'f_message' => '',
  )
);

$from     = !empty($_POST['f_email']) ?  $_POST['f_email'] : '';
$phone    = !empty($_POST['f_phone']) ?  $_POST['f_phone'] : '';
$message  = !empty($_POST['f_message']) ? $_POST['f_message'] : '';
$subject  = !empty($_POST['f_subject']) ? $_POST['f_subject'] : 'General';

$json = my_validation($json, $from, $phone, $message);
$message = 'Email: '.$from . ', Phone: '.$phone.', Message: ' . $message;
try {
    //Server settings
    
    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = $to_myemail;                     //SMTP username
    $mail->Password   = 'PASSWORD HERE';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                 //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom($from);
    $mail->addAddress($to_myemail);               //Name is optional
    $mail->addReplyTo($from);

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $message;

    if ($json['status']  === 'success') { 
        $mail->send();
        echo json_encode($json);
    }

} catch (Exception $e) {
    $m_err = error_get_last()['message'];  
    $json = my_set_error($json, 'Unable to send Email! '.$m_err);
    echo json_encode($json);
}