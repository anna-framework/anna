<?php

namespace Anna\Helpers;

/**
 * -------------------------------------------------------------
 * LogHelper
 * -------------------------------------------------------------.
 *
 * Helper para autilizar nas questões de segurança
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 11, Novembro 2015
 */
class LogHelper
{
    private $path = SYS_ROOT.'logs'.DS;

    public function __construct()
    {
        $this->path = dirname(__FILE__).$this->path;
    }

    /**
     * Escreve arquivo de log.
     * @param string $message
     */
    public function writel($message, $fileSalt)
    {
        $date = new \DateTime();
        $log = $this->path.$date->format('Y-m-d').'-'.md5($date->format('Y-m-d').$fileSalt).'.txt';
        if (is_dir($this->path)) {
            if (!file_exists($log)) {
                $fh = fopen($log, 'a+') || die('Fatal Error !');
                $logcontent = 'Time : '.$date->format('H:i:s')."\r\n".$message."\r\n";
                fwrite($fh, $logcontent);
                fclose($fh);
            } else {
                $this->edit($log, $date, $message);
            }
        } else {
            if (mkdir($this->path, 0777) === true) {
                $this->writel($message);
            }
        }
    }

    /**
     * @param string $log
     * @param \DateTime $date
     * @param string $message
     */
    private function edit($log, $date, $message)
    {
        $logcontent = 'Time : '.$date->format('H:i:s')."\r\n".$message."\r\n\r\n";
        $logcontent = $logcontent.file_get_contents($log);
        file_put_contents($log, $logcontent);
    }
}
