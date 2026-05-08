<?php



namespace LARAVEL\Helpers;
use Illuminate\Http\Request;
use LARAVEL\Core\Singleton;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email
{
    use Singleton;
    private $d;
    private $data = array();
    private $company = array();
    private $optcompany = '';

    function __construct()
    {
    }

    public function set($key, $value)
    {
        if (!empty($key) && !empty($value)) {
            $this->data[$key] = $value;
        }
    }

    public function get($key)
    {
        return (!empty($this->data[$key])) ? $this->data[$key] : '';
    }


    public function addAttrs($array1 = array(), $array2 = array())
    {
        if (!empty($array1) && !empty($array2)) {
            foreach ($array2 as $k2 => $v2) {
                array_push($array1, $v2);
            }
        }

        return $array1;
    }

    public function markdown($path = '', $params = array())
    {
        $content = '';

        if (!empty($path)) {
            ob_start();
            view($path, ['params' => $params]);
            $content = ob_get_contents();
            ob_clean();
        }
        return $content;
    }

    public function send($owner = '', $arrayEmail = array(), $subject = "", $message = "", $file = '', $optCompany = array(), $company = array())
    {
        $mail = new PHPMailer(true);
        $this->set('last_message_id', '');
        $this->set('last_error', '');
        $this->set('last_smtp_reply', '');
        $this->set('last_smtp_trace', '');
        $smtpDebugLines = [];
        $isDev = (string) config('app.environment') === 'dev';

        $config_host = '';
        $config_port = 0;
        $config_secure = '';
        $config_email = '';
        $config_password = '';
        $config_username = '';
        $from_email = '';

        if ($optCompany['mailertype'] == 1) {
            $config_host = $optCompany['ip_host'];
            $config_port = $optCompany['port_host'];
            $config_secure = $optCompany['secure_host'];
            $config_email = $optCompany['email_host'];
            $config_password = $optCompany['password_host'];
            $config_username = $config_email;
            $from_email = $config_email;

            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = $config_secure;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        } else if ($optCompany['mailertype'] == 2) {
            $config_host = $optCompany['host_gmail'];
            $config_port = $optCompany['port_gmail'];
            $config_secure = $optCompany['secure_gmail'];
            $config_email = $optCompany['email_gmail'];
            $config_password = $optCompany['password_gmail'];
            $config_username = $config_email;
            $from_email = $config_email;
            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = $config_secure;
        }

        $mail->SMTPDebug = $isDev ? 2 : 0;
        if ($isDev) {
            $mail->Debugoutput = function ($str, $level) use (&$smtpDebugLines) {
                $smtpDebugLines[] = '[' . $level . '] ' . trim((string) $str);
            };
        }

        $mail->Host = $config_host;
        if ($config_port) {
            $mail->Port = $config_port;
        }

        // Improve SMTP compatibility for providers that validate EHLO/hostname strictly.
        $heloDomain = trim((string) ($optCompany['ehlo_domain'] ?? ''));
        if ($heloDomain === '') {
            if (($optCompany['mailertype'] ?? 0) == 2) {
                $heloDomain = 'gmail.com';
            } elseif ($config_host !== '') {
                $heloDomain = preg_replace('/^smtp\./i', '', (string) $config_host);
            }
        }
        if ($heloDomain !== '') {
            $mail->Helo = $heloDomain;
            $mail->Hostname = $heloDomain;
        }

        $mail->Username = $config_username;
        $mail->Password = $config_password;

        $fromAddress = $from_email ?: $config_email;
        if (!filter_var((string) $fromAddress, FILTER_VALIDATE_EMAIL) && !empty($optCompany['email']) && filter_var((string) $optCompany['email'], FILTER_VALIDATE_EMAIL)) {
            $fromAddress = (string) $optCompany['email'];
        }
        $mail->SetFrom($fromAddress, $company['namevi']);


        if ($owner == 'admin') {
            $mail->AddAddress($optCompany['email'], $company['namevi']);
        } else if ($owner == 'customer') {
            if ($arrayEmail && count($arrayEmail) > 0) {
                foreach ($arrayEmail as $vEmail) {
                    $mail->AddAddress($vEmail['email'], $vEmail['name']);
                }
            }
        }
        $mail->AddReplyTo($optCompany['email'], $company['namevi']);
        $mail->CharSet = "utf-8";
        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
        $mail->Subject = $subject;

        $mail->MsgHTML($message);

        if ($file != '' && isset($_FILES[$file]) && !$_FILES[$file]['error']) {
            $mail->AddAttachment($_FILES[$file]["tmp_name"], $_FILES[$file]["name"]);
        }

        try {
            if ($mail->Send()) {
                $this->set('last_message_id', (string) $mail->getLastMessageID());
                $smtp = $mail->getSMTPInstance();
                if ($smtp) {
                    $this->set('last_smtp_reply', trim((string) $smtp->getLastReply()));
                }
                if (!empty($smtpDebugLines)) {
                    $this->set('last_smtp_trace', implode("\n", $smtpDebugLines));
                }
                return true;
            }

            $this->set('last_error', (string) $mail->ErrorInfo);
            $smtp = $mail->getSMTPInstance();
            if ($smtp) {
                $this->set('last_smtp_reply', trim((string) $smtp->getLastReply()));
            }
            if (!empty($smtpDebugLines)) {
                $this->set('last_smtp_trace', implode("\n", $smtpDebugLines));
            }
            return false;
        } catch (\Throwable $e) {
            $this->set('last_error', (string) $e->getMessage());
            $smtp = $mail->getSMTPInstance();
            if ($smtp) {
                $this->set('last_smtp_reply', trim((string) $smtp->getLastReply()));
            }
            if (!empty($smtpDebugLines)) {
                $this->set('last_smtp_trace', implode("\n", $smtpDebugLines));
            }
            return false;
        }
    }
}
