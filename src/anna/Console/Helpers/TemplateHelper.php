<?php

namespace Anna\Console\Helpers;

use Anna\Error;

class TemplateHelper
{
    private static $instance;

    private $templates_folder;

    public function __construct()
    {
        $this->templates_path = ANNA_ROOT.'Assets'.DS;
    }

    /**
     * @return TemplateHelper
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function render($template, $params)
    {
        $template_path = $this->templates_path.$template.'.dolly';

        if (is_file($template_path)) {
            $template = file_get_contents($template_path);
            foreach ($params as $key => $value) {
                $template = str_replace('{{'.$key.'}}', $value, $template);
            }

            $template = '<?php'.PHP_EOL.PHP_EOL.$template;

            return $template;
        } else {
            Error::log(new \Exception('Arquivo de template nao encontrado.'));

            return false;
        }
    }
}
