<?php

namespace Anna;

/**
 * -------------------------------------------------------------
 * Paginator
 * -------------------------------------------------------------
 *
 * Classe responsável por facilitar o processo de paginação, pode ser utilizada para gerar o html final
 * ou apenas retornar os dados necessários para contrução manual da paginação em tela
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 18, Novembro 2015
 * @package Anna
 */
class Paginator 
{

	private $total_items;
	private $total_pages;

	/**
	 * Paginator do doctrine
	 * @var Doctrine\ORM\Tools\Pagination\Paginator
	 */
	private $result;
	private $per_page;
	private $path;
	private $active_page;
	private $url_params = [];

	public function __construct($result, $per_page, $active_page)
    {
		$this->total_items = $result->count();
		$this->total_pages = ceil($this->total_items / $per_page);
		$this->per_page = $per_page;
		$this->active_page = $active_page;
		$this->result = $result;
	}

	public function render($path)
    {
		$this->path = $path;
		$ordened = [];

		if ($this->total_pages > 5) {

			if ($this->total_pages - $this->active_page == 1) {
				$first = $this->active_page - 3;
			} elseif($this->total_pages - $this->active_page == 0) {
				$first = $this->active_page - 4;
			} else {
				$first = $this->active_page - 2;
			}

			if ($first < 1) {
                $first = 1;
            }

			$last = $first + 5;

			for($first; $first < $last; $first++){
				if ($first <= $this->total_pages) {
					if ($first == $this->active_page) {
						$ordened['active'] = $first;
					} else {
						$ordened[] = $first;
					}
				} else {
					break;
				}
			}

		} else {
			for($i = 1; $i < $this->total_pages; $i++){
				if ($i == $this->active_page) {
					$ordened['active'] = $i;
				} else {
					$ordened[] = $i;
				}
			}
		}

		return $this->make($ordened);
	}

	public function make($pages)
    {
		$url = $this->makeUrl();

		$buttons = [];
		if ($this->active_page == 1) {
			$disabled = 'disabled';
			$url_prev = 'javascript:void(0)';
		} else {
			$disabled = '';
			$query = $this->active_page - 1;
			$url_prev = $url . '&page=' . $query;
		}

		$buttons[] = '<li class="' . $disabled . '"><a href="' . $url_prev . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';

		foreach($pages as $key => $number){

			if ($key === 'active') {
				$btn_url = 'javascript:void(0)';
				$class = 'active';
			} else {
				$class = '';
				$btn_url = $url . '&page=' . $number;
			}
			$buttons[] = '<li class="' . $class . '"><a href="' . $btn_url . '">' . $number . '</a></li>';
			unset($class);
		}

		if ($this->active_page == $this->total_pages) {
			$disabled = 'disabled';
			$url_next = 'javascript:void(0)';
		} else {
			$disabled = '';
			$query = $this->active_page + 1;
			$url_next = $url . '&page=' . $query;
		}

		$buttons[] = '<li class="' . $disabled . '"><a href="' . $url_next . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';

		$html = '<ul class="pagination">' . implode('', $buttons) . '</ul>';
		return $html;
	}

	/**
	 * Retorna array com os objetos encontrados
	 * @return array
	 */
	public function getResult()
    {
		return $this->result->getIterator();
	}

	public function addUrlParams($array)
    {
		foreach($array as $k => $v){
			$this->url_params[$k] = $v;
        }
	}

	public function addUrlParam($params, $value)
    {
		$this->url_params[$params] = $value;
	}

	private function makeUrl()
    {
		$base_path = path('') . $this->path . '?';

		foreach($this->url_params as $key => $val){
			$base_path .= '&' . $key . '=' . $val;
		}

		return $base_path;
	}

}
