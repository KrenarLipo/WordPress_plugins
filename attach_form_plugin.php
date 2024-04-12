<?php
/*
    Plugin Name: Ditila Plugin
    Plugin URI: #
    Description: Contact form with attachment support and JS validation.
    Author: Krenar Lipo
    Version: 1.0
*/

function contact_form_markup() {

$form_action    = get_permalink();
$author_default = $_COOKIE['comment_author_'.COOKIEHASH];
$email_default  = $_COOKIE['comment_author_email_'.COOKIEHASH];

if ( ($_SESSION['contact_form_success']) ) {
$contact_form_success = '<p style="color: green"><strong>Faleminderit për kontaktin! Ju lutem shikoni emailin tuaj ose Spamin!</strong></p>';
unset($_SESSION['contact_form_success']);
}

$markup = <<<EOT

<div id="commentform"><h3>Dërgesa e Pyetësorit</h3>

	{$contact_form_success}
     
   <form onsubmit="return validateForm(this);" action="{$form_action}" method="post" enctype="multipart/form-data" style="text-align: left">
   
   <p style="margin:10px 5px"><input type="text" name="author" id="author" value="{$author_default}" size="22" style="border: 1px solid #787878;" /> <label for="author"><small>Emri Juaj*</small></label></p>
   <p style="margin:10px 5px"><input type="text" name="email" id="email" value="{$email_default}" size="22" style="border: 1px solid #787878;" /> <label for="email"><small>Emaili ku do dërgoni*</small></label></p>
   <p style="margin:10px 5px"><input type="text" name="subject" id="subject" value="" size="22" style="border: 1px solid #787878;" /> <label for="subject"><small>Subjekti*</small></label></p>
   <p style="margin:10px 5px"><textarea name="message" id="message" cols="100%" rows="5" style="border: 1px solid #787878;">shkruani mesazhin tuaj...</textarea></p>
   <p style="margin:10px 5px"><label for="attachment"><small>Attachment</small></label> <input type="file" name="attachment" id="attachment" /></p>
   <p style="margin:10px 5px"><input name="send" type="submit" id="send" value="Merr Excelin" /></p>
   
   <input type="hidden" name="contact_form_submitted" value="1">
   
   </form>
   
</div>

EOT;

return $markup;

}

add_shortcode('contact_form', 'contact_form_markup');

function contact_form_process() {

session_start();

 if ( !isset($_POST['contact_form_submitted']) ) return;

 $author  = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : null;
 $email   = ( isset($_POST['email']) )   ? trim(strip_tags($_POST['email'])) : null;
 $subject = ( isset($_POST['subject']) ) ? trim(strip_tags($_POST['subject'])) : null;
 $message = ( isset($_POST['message']) ) ? trim(strip_tags($_POST['message'])) : null;

 if ( $author == '' ) wp_die('Error: please fill the required field (name).'); 
 if ( !is_email($email) ) wp_die('Error: please enter a valid email address.');
 if ( $subject == '' ) wp_die('Error: please fill the required field (subject).');
 
 //we will add e-mail sending support here soon
 
require_once ABSPATH . WPINC . '/class-phpmailer.php';
$mail_to_send = new PHPMailer();

$mail_to_send->FromName = $author;
$mail_to_send->From     = $email;
$mail_to_send->Subject  = $subject;
$mail_to_send->Body     = $message;

$mail_to_send->AddReplyTo('info@akzm.net');
$mail_to_send->AddAddress($email); //contact form destination e-mail

if ( !$_FILES['attachment']['error'] == 4 ) { //something was send
	
	if ( $_FILES['attachment']['error'] == 0 && is_uploaded_file($_FILES['attachment']['tmp_name']) )
	
		$mail_to_send->AddAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
	
	else 
		
		wp_die('Error: there was a problem with the file upload. Try again later.');
		
}

if ( !$mail_to_send->Send() ) wp_die('Error: unable to send e-mail - status code: ' . $mail_to_send->ErrorInfo);

$_SESSION['contact_form_success'] = 1;

 
 header('Location: ' . $_SERVER['HTTP_REFERER']);
 exit();

} 

add_action('init', 'contact_form_process');

function contact_form_js() { ?>

<script type="text/javascript">
function validateForm(form) {

	var errors = '';
	var regexpEmail = /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/;
		
	if (!form.author.value) errors += "Error: please fill the required field (name).\n";
	if (!regexpEmail.test(form.email.value)) errors += "Error: please enter a valid email address.\n";
	if (!form.subject.value) errors += "Error: please fill the required field (subject).\n";

	if (errors != '') {
		alert(errors);
		return false;
	}
	
return true;
	
}
</script>

<?php }

add_action('wp_head', 'contact_form_js');

?>