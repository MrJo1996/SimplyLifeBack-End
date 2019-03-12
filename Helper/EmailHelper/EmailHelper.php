<?php

// Import PHPMailer classes into the global namespace
//  These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{

    /**
     * Emailelper constructor.
     */
    public function __construct()
    {
    }

    //Funzione per inviare un'email con la nuova password
    function sendResetPasswordEmail($email, $password){

        $messaggio = "Usa questa password temporanea";

        $linkLogin = 'https://www.unimolshare.it/login.php';
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'unimolshare@gmail.com';                 // SMTP username
            $mail->Password = 'projectUnimol300518';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('unimolshare@gmail.com', 'UnimolShare - Automatic Password Recovery');
            $mail->addAddress('andreacb94@gmail.com', 'TEST Andrea');     // Add a recipient
            $mail->Subject = 'UnimolShare - Recupero credenziali';

            //Content
            $mail->isHTML(true); // Setto il formato dell'email in HTML
            $mail->Body    = '<!doctype html><html lang = "it"><header><meta charset="UTF-8"></header>';
            $mail->Body   .= '<body><h1>UnimolShare</h1><div>';
            $mail->Body   .= $messaggio.':<br/><br/><b>'.$password.'</div><br/><div>Vai su '.$linkLogin.' per entrare.</div></body></html>';
            $mail->AltBody = $messaggio.': '.$password.' --- Vai su '.$linkLogin.' per entrare.';
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }



    //Funzione per inviare un'email con la nuova password
    function sendConfermaAccount($email, $link){

        $messaggio = 'Hai appena richiesto di iscriverti ad UnimolShare!<br>Conferma la tua iscrizione col seguente link: ';
        $linkLogin = 'https://www.unimolshare.it/login.php';
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'unimolshare@gmail.com';                 // SMTP username
            $mail->Password = 'projectUnimol300518';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('unimolshare@gmail.com', 'UnimolShare - Automatic Password Recovery');
            $mail->addAddress('andreacb94@gmail.com', 'TEST Andrea');     // Add a recipient
            $mail->Subject = 'UnimolShare - Conferma registrazione';

            //Content
            $mail->isHTML(true); // Setto il formato dell'email in HTML
            $mail->Body    = '<!doctype html><html lang = "it"><header><meta charset="UTF-8"></header>';
            $mail->Body   .= '<body><h1>UnimolShare</h1><div>';
            $mail->Body   .= $messaggio.':<br/><br/><b>'.$link.'</div><br/><div>Vai su '.$linkLogin.' per entrare.</div></body></html>';
            $mail->AltBody = $messaggio.': '.$link.' --- Vai su '.$linkLogin.' per entrare.';
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }



    function sendSegnalazione($nome,$cognome,$motivo,$contatto,$email){

        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'unimolshare@gmail.com';                 // SMTP username
            $mail->Password = 'projectUnimol300518';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('unimolshare@gmail.com', 'UnimolShare - Segnalazione');
            $mail->addAddress('unimolshare@gmail.com', 'Segnalazione');     // Add a recipient
            $mail->Subject = 'UnimolShare - Segnalazione';

            //Content
            $mail->isHTML(true); // Setto il formato dell'email in HTML
            $mail->Body    = '<!doctype html><html lang = "it"><header><meta charset="UTF-8"></header>';
            $mail->Body   .= '<body><h1>Segnalazione</h1><div>';
            $mail->Body   .= $nome.', '.$cognome.'</div><br/><div>il motivo è: '.$motivo.' segnalo lo studente con contatti '.$contatto.' '.$email.'.</div></body></html>';
            $mail->AltBody = $nome.', '.$cognome.'il motivo è: '.$motivo.' segnalo lo studente con contatti: '.$contatto.' '.$email;
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}