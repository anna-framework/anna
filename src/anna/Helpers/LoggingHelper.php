<?php

namespace Anna\Helpers;

/**
 * -------------------------------------------------------------
 * SafeHelper
 * -------------------------------------------------------------.
 *
 * Helper para autilizar nas questões de segurança
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 11, Novembro 2015
 */
class LoggingHelper
{
    private $log_file, $fp;

    public function lfile($path)
    {
        $this->log_file = $path;
    }

    public function lwrite($message)
    {
        if (!is_resource($this->fp)) {
            $this->lopen();
        }

        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        $time = @date('[d/M/Y:H:i:s]');

        fwrite($this->fp, "$time ($script_name) $message".PHP_EOL);
    }

    public function lclose()
    {
        fclose($this->fp);
    }

    private function lopen()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $log_file_default = 'c:/php/logfile.txt';
        } else {
            $log_file_default = '/tmp/logfile.txt';
        }

        $lfile = $this->log_file ? $this->log_file : $log_file_default;
        $this->fp = fopen($lfile, 'a') || exit("Can't open $lfile!");
    }

    /*
    * Escreve arquivo de log -> DATE-FILE-LOG
    */

    public function write($message, $file)
    {
        $this->lfile(SYS_ROOT."logs\\$file.txt");

        $this->lwrite($message);

        $this->lclose();
    }
}
