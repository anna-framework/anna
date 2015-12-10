<?php

namespace Anna\Helpers;

/**
 * -------------------------------------------------------------
 * Benchmarker
 * -------------------------------------------------------------.
 *
 * Classe simples de benchmark para verificar performance de algoritmos
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 03, Novembro 2015
 */
class Benchmarker
{
    private $registers = [];

    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function start($label)
    {
        $this->registers[$label] = ['start' => microtime()];
    }

    public function end($label)
    {
        $this->registers[$label]['end'] = microtime();
    }

    public function calc()
    {
        $result = '<table cellspacing="0">
						<thead>
							<tr style="background: #5E8FA7; color: white;">
								<td style="padding: 5px; width: 100px;">Label</td>
								<td style="padding: 5px; width: 100px;">Lapse</td>
							</tr>
						</thead>
						<tbody>';

        foreach ($this->registers as $label => $values) {
            $lapse = $values['end'] - $values['start'];
            $result .= "<tr><td>$label</td><td style=\"text-align: right\">{$lapse}s</td></tr>";
        }

        $result .= '</tbody></table>';

        return $result;
    }
}
