<?php

namespace Anna;

/**
 * ------------------------------------------------
 * Error
 * ------------------------------------------------.
 *
 * Classe para tratamento de erros
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 06, novembro 2015
 */
class Error
{
    /**
     * Determina a forma como o registro do erro será feito com base na variável de ambiente setada nas configurações.
     *
     * @param Exception $ex
     */
    public static function log($ex)
    {
        $env = \Anna\Config::getInstance()->get('app.enviroment');

        if ($env == 'development') {
            self::logDev($ex);
        } else {
            self::logProduction($ex);
        }
    }

    /**
     * Registra os erros em arquivo enquanto direciona o usuário para uma página genérica de erro.
     *
     * @param \Exception $ex
     */
    public static function logProduction(\Exception $ex)
    {
        $message = $ex->getMessage();
        $code = $ex->getCode();
        $file = $ex->getFile();
        $line = $ex->getLine();
        $trace = $ex->getTraceAsString();

        $log_folder = SYS_ROOT.'errors'.DS;

        $log = fopen($log_folder.'errors.log', 'a+');

        fwrite($log, 'message: '.$message.PHP_EOL);
        fwrite($log, 'code: '.$code.PHP_EOL);
        fwrite($log, 'file: '.$file.PHP_EOL);
        fwrite($log, 'line: '.$line.PHP_EOL);
        fwrite($log, 'trace: '.$trace.PHP_EOL);
        fwrite($log, '================================================================================'.PHP_EOL);
        fclose($log);

        //RedirectResponse('http://example.com/');
        $redirect = new \Symfony\Component\HttpFoundation\RedirectResponse('/erro');
        $redirect->send();
    }

    /**
     * Envia os dados para a tela para apreciação do desenvolvedor.
     *
     * @param \Exception $ex
     */
    public static function logDev(\Exception $ex)
    {
        uncaughtExceptionHandler($ex);
    }

    /**
     * Registra os erros em arquivo enquanto direciona o usuário para uma página genérica de erro.
     *
     */
    public static function logFile($text)
    {
        $log_folder = SYS_ROOT.'errors'.DS;
        $log = fopen($log_folder.'errors.log', 'a+');

        fwrite($log, $text.PHP_EOL);
        fwrite($log, '================================================================================'.PHP_EOL);
        fclose($log);
    }
}
