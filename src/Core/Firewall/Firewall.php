<?php
namespace LARAVEL\Core\Firewall;
use LARAVEL\Core\Support\Facades\File;

class Firewall{
    private $LARAVELfw_conf;
    private string $ip_dir;
    private $ip;
    public function __construct($LARAVELfw_conf) {
        if ($LARAVELfw_conf === null) return;
        $this->LARAVELfw_conf = $LARAVELfw_conf;
        $this->ip_dir = upload_path('ip');
        $this->ip = request()->ip();
    }
    private function get_ip() {
        $do_check = 0;
        $addrs = [];
        if ($do_check) {
            foreach (array_reverse(explode(',', request()->server('HTTP_X_FORWARDED_FOR'))) as $x_f) {
                $x_f = trim($x_f);
                if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f)) {
                    $addrs[] = $x_f;
                }
            }
            $addrs[] = request()->server('HTTP_CLIENT_IP');
            $addrs[] = request()->server('HTTP_PROXY_USER');
        }
        $addrs[] = request()->server('REMOTE_ADDR');
        foreach ($addrs as $v) {
            if ($v) {
                preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/", $v, $match);
                $ip = $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4];

                if ($ip && $ip != '...') {
                    return $ip;
                }
            }
        }
        $this->show_noti("Việc truy cập của bạn bị cấm vì IP của bạn không hợp lệ.");
        exit();
    }
    private function show_noti($msg, $time = 60) {
        echo "
        <html>
        <head><title>LARAVEL Firewall System</title>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"> 
        <meta http-equiv=\"Refresh\" Content=\"$time; url=\">
        <style type='text/css'>
            html { overflow-x: auto; }
            body { background: #FFF; color: #222; font-family: Arial, Verdana, Tahoma, Times New Roman, Courier; font-size: 11px; line-height: 135%; margin: 0; padding: 0; text-align: center; }
            a:link, a:visited, a:active { background: transparent; color: #0066CC; text-decoration: none; }
            a:hover { background: transparent; color: #000000; text-decoration: underline; }
            #wrapper { margin: 5px auto 20px auto; text-align: left; width: 80%; }
            .borderwrap { background: #FFF; border: 1px solid #EEE; padding: 3px; margin: 0; }
            .borderwrap p { background: #F9F9F9; border: 1px solid #CCC; margin: 5px; padding: 10px; text-align: left; }
            .warnbox { border: 1px solid #F00; background: #FFE0E0; padding: 6px; margin-right: 1%; margin-left: 1%; text-align: left; }
        </style>
        </head>
        <body>
        <div id='wrapper'><br /><br />
            <div class='borderwrap'>
                <p style='font-size:15px; color:#FF3300; font-weight:bold' id='tieude'>CẢNH BÁO</p><br />
                <div class='warnbox' id='canhbao'>
                    <b>Phát hiện dấu hiệu truy cập bất thường:<br>$msg";
        if ($time != -1) {
            echo " Bạn vui lòng đợi <span id='time'>$time s</span><b> nữa.</b>";
        }
        echo "</div><br /></div><br />";
        if ($time != -1) {
            echo "<script> 
                var milisec = 0;
                var seconds = $time;
                document.getElementById('time').innerHTML = '$time';
                function display() {
                    if (milisec <= 0) { 
                        milisec = 9;
                        seconds -= 1;
                    } else {
                        milisec -= 1;
                    }
                    if (seconds <= -1) {
                        milisec = 0; 
                        seconds += 1;
                    }
                    document.getElementById('time').innerHTML = seconds + '.' + milisec + 's';
                    if (seconds == 0 && milisec == 0) {
                        document.getElementById('canhbao').innerHTML = \"<b>Vui lòng bấm phím F5 để nạp lại trang hoặc bấm <a href=''>vào đây nếu chờ quá lâu.</a></b>\";
                        document.getElementById('tieude').innerHTML = \"<b>Bạn có thể truy cập lại</b>\";
                        window.location.href = '';
                    }
                    setTimeout(\"display()\", 100);
                }
                display();
                </script>";
        }
        echo "</div></body></html>";
        exit();
    }
    private function handle_access() {
        $ip = $this->ip;
        $now = time();
        $ip_path =  $this->ip_dir .'/'. $ip;
        $ip_deny = "$ip_path.deny";
        $ip_lock = "$ip_path.lock";
        $ip_lockcount = "$ip_path.lockcount";

        if (file_exists($ip_deny)) {
            $this->block_ip_permanently($ip);
        } elseif (file_exists($ip_lock)) {
            $this->handle_temporary_lock($ip, $now);
        } else {
            $this->handle_normal_access($ip, $now);
        }
    }
    private function block_ip_permanently($ip) {
        @chmod($this->LARAVELfw_conf['htaccess'], 0666);
        @$ft = fopen($this->LARAVELfw_conf['htaccess'], "a");
        @fwrite($ft, "\ndeny from $ip");
        @fclose($ft);
        $this->show_noti("IP của bạn <font color='red'>$ip</font> đã bị chặn truy cập để đảm bảo an toàn. Vui lòng liên lạc với chúng tôi qua email <a href='mailto:{$this->LARAVELfw_conf['email_admin']}'>{$this->LARAVELfw_conf['email_admin']}</a> để bỏ chặn IP của bạn", -1);
    }
    private function handle_temporary_lock($ip, $now) {
        @chmod( $this->ip_dir .'/'. $ip.'.lock', 0666);
        @$time = file_get_contents( $this->ip_dir .'/'. $ip.'.lock');
        $lock_count = file_exists($this->ip_dir .'/'. $ip.'.lockcount') ? file_get_contents($this->ip_dir .'/'. $ip.'.lockcount') : 0;
        $wait = (($this->LARAVELfw_conf['time_wait'] * ($lock_count + 1)) + $time) - $now;
        if ($wait > 0) {
            $this->show_noti("IP của bạn <font color='red'>$ip</font> đã bị chặn truy cập để đảm bảo an toàn.", $wait);
        } else {
            @unlink( $this->ip_dir .'/'. $ip.'.lock');
            @chmod( $this->ip_dir .'/'. $ip, 0666);
            @$ft = fopen( $this->ip_dir .'/'. $ip, "w");
            @fwrite($ft, "1|" . $now);
            @fclose($ft);
        }
    }
    private function handle_normal_access($ip, $now): void
    {
        $ip_path = $this->ip_dir .'/'. $ip;
        if (file_exists($ip_path)) {
            @chmod($ip_path, 0666);
            $c = file_get_contents($ip_path);
            list($con, $firttime) = explode("|", $c);
            $con = intval($con);
            $firttime = intval($firttime);
            $arr_ip_allow = @explode(",", $this->LARAVELfw_conf['ip_allow']);
            if (($con + 1) >= $this->LARAVELfw_conf['max_connect'] && ($now - $firttime) <= $this->LARAVELfw_conf['time_limit'] && !in_array($ip, $arr_ip_allow)) {
                $this->handle_ddos_attack($ip, $now, $con);
            } elseif (($con + 1) < $this->LARAVELfw_conf['max_connect'] && ($now - $firttime) >= $this->LARAVELfw_conf['time_limit']) {
                $this->reset_connection($ip, $now);
            } else {
                $this->update_connection_count($ip, $con, $firttime);
            }
        } else {
            $this->initialize_ip($ip, $now);
        }
    }
    private function handle_ddos_attack($ip, $now, $con) {
        $ip_lock =$this->ip_dir .'/'. $ip.'.lock';
        @chmod($ip_lock, 0666);
        $ft = fopen($ip_lock, "w");
        fwrite($ft, $now);
        fclose($ft);
        $ip_lockcount =$this->ip_dir .'/'. $ip.'.lockcount';
        $lock_count = file_exists($ip_lockcount) ? file_get_contents($ip_lockcount) : 0;
        if (($lock_count + 1) >= $this->LARAVELfw_conf['max_lockcount']) {
            $this->permanently_block_ip($ip);
        } else {
            $wait = ($this->LARAVELfw_conf['time_wait'] * ($lock_count + 1));
            @chmod($ip_lockcount, 0666);
            $ft = fopen($ip_lockcount, "w");
            fwrite($ft, $lock_count + 1);
            fclose($ft);
            $this->show_noti("IP của bạn <font color='red'>$ip</font> đã bị chặn truy cập để đảm bảo an toàn.", $wait);
        }
    }
    private function permanently_block_ip($ip): void
    {
        $ip_deny = $this->ip_dir .'/'.$ip.'.deny';
        @chmod($ip_deny, 0666);
        $ft = fopen($ip_deny, "w");
        fclose($ft);

        @chmod(ROOT_PATH.'/'.$this->LARAVELfw_conf['htaccess'], 0666);
        $ft = fopen($this->LARAVELfw_conf['htaccess'], "a");
        fwrite($ft, "\ndeny from $ip");
        fclose($ft);

        $this->show_noti("IP của bạn <font color='red'>$ip</font> đã bị chặn truy cập để đảm bảo an toàn. Vui lòng liên lạc với chúng tôi qua email <a href='mailto:{$this->LARAVELfw_conf['email_admin']}'>{$this->LARAVELfw_conf['email_admin']}</a> để bỏ chặn IP của bạn", -1);
    }
    private function reset_connection($ip, $now): void
    {
        $ip_path = $this->ip_dir .'/'. $ip;
        @chmod($ip_path, 0666);
        $ft = fopen($ip_path, "w");
        fwrite($ft, "1|" . $now);
        fclose($ft);
    }
    private function update_connection_count($ip, $con, $firttime) {
        $ip_path = $this->ip_dir .'/'. $ip;
        @chmod($ip_path, 0666);
        $ft = fopen($ip_path, "w");
        fwrite($ft, ($con + 1) . "|" . $firttime);
        fclose($ft);
    }
    private function initialize_ip($ip, $now) {
        $ip_path = $this->ip_dir .'/'. $ip;
        @chmod($ip_path, 0666);
        $ft = fopen($ip_path, "w");
        fwrite($ft, "1|" . $now);
        fclose($ft);
    }
    public function clear_ips() {
        if (!file_exists($this->LARAVELfw_conf['htaccess'])) {
            return;
        }
        $htaccess_content = file($this->LARAVELfw_conf['htaccess']);
        $filtered_content = array_filter($htaccess_content, function($line) {
            return stripos($line, 'deny from') === false;
        });
        @$ft = fopen($this->LARAVELfw_conf['htaccess'], "w");
        @fwrite($ft, implode("", $filtered_content));
        @fclose($ft);
        if (File::exists($this->ip_dir)) {
            $files = scandir($this->ip_dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $file_path = $this->ip_dir."/".$file;
                if (basename($file)!=".htaccess" && !is_dir($file_path)) {
                    @unlink($file_path);
                }
            }
        }
    }
    public function run() {
        if ($this->LARAVELfw_conf['firewall']==true) {
            $this->handle_access();
        }
    }
}