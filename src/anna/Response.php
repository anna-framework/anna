<?php

namespace Anna;
use Anna\Application;
use Anna\Helpers\Jsonhelper;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * -------------------------------------------------------------
 * Response
 * -------------------------------------------------------------
 *
 * Fachada a classe Response da biblioteca httpfoundation do symfony
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 16, Novembro 2015
 * @package Anna
 */
class Response extends \Symfony\Component\HttpFoundation\Response 
{

	public function display()
    {
		$di = Application::getInstance()->getInjector();
		$request = $di->get('Anna\Request');
		$this->prepare($request);
		$this->send();
	}

	public function displayJson($data)
    {
		if (is_array($data) || is_object($data)) {
			$this->content = Jsonhelper::encode($data);
		}

		$this->headers = new ResponseHeaderBag(['Content-type' => 'application/json']);
		$this->display();
	}

}