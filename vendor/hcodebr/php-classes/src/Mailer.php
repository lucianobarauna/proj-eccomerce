<?php

// Não precisamos colocar o use aqui pq ela já está no diretórico raiz php-classes
namespace Hcode;

use Rain\Tpl;

class Mailer
{
    // Criada constantes para facilitar a configuração.
    const USERNAME = "lucianobaraunadev@gmail.com";
    const PASSWORD = "ay22bj42ca88";
    const NAME_FROM = "Loja PHP ECommerce";

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {

        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email/", # diretório de templates
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", # diretório de cache do template
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        // Criando o template.
        $tpl = new Tpl;

        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName, true);

        //Create a new PHPMailer instance
        $this->$mail = new \PHPMailer;

        //Tell PHPMailer to use SMTP
        $this->$mail->isSMTP();

        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->$mail->SMTPDebug = 2;

        //Set the hostname of the mail server
        $this->$mail->Host = 'smtp.gmail.com';

        // use
        // $this->$mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->$mail->Port = 587;

        //Set the encryption system to use - ssl (deprecated) or tls
        $this->$mail->SMTPSecure = 'tls';

        //Whether to use SMTP authentication
        $this->$mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        // $this->$mail->Username = "username@gmail.com";
        $this->$mail->Username = Mailer::USERNAME;

        //Password to use for SMTP authentication
        // $this->$mail->Password = "yourpassword";
        $this->$mail->Password = Mailer::PASSWORD;

        //Set who the message is to be sent from
        $this->$mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

        //Set an alternative reply-to address
        $this->$mail->addReplyTo('', 'Responde para esse email');

        //Set who the message is to be sent to
        $this->$mail->addAddress($toAddress, $toName);

        //Set the subject line
        $this->$mail->Subject = $subject;

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $this->$mail->msgHTML($html);
        // $html - Estamos renderizando esse html pelo RainTpl.

        //Replace the plain text body with one created manually
        $this->$mail->AltBody = 'This is a plain-text message body';

        // //Attach an image file
        // // $this->$mail->addAttachment('images/phpmailer_mini.png');
        // //send the message, check for errors
        // if (!$this->$mail->send()) {
        //     echo "Mailer Error: " . $this->$mail->ErrorInfo;
        // } else {
        //     echo "Message sent!";
        //     //Section 2: IMAP
        //     //Uncomment these to save your message in the 'Sent Mail' folder.
        //     #if (save_mail($this->$mail)) {
        //     #    echo "Message saved!";
        //     #}
        // }
    }
    public function send()
    {
        return $this->mail->send();
    }
}


?>