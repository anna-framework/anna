<?php
namespace Anna\Routers;

use Anna\Response;
use Anna\Routers\Traits\RouterTrait;
use Anna\Routers\Interfaces\RouterInterface;

use \Symfony\Component\Routing\RequestContext;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Routing\Matcher\UrlMatcher;
use \Symfony\Component\Routing\Exception\ResourceNotFoundException;
use \Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * -------------------------------------------------------------
 * Router
 * -------------------------------------------------------------
 *
 * Roteador, utilizado para criar rotas para o sistema
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 13, Novembro 2015
 * @package Anna\Routers
 */
class Router implements RouterInterface
{

	use RouterTrait;

	/**
	 * @var Router
	 */
	private static $instance;

	/**
	 * Implementa padrão Singleton na classe
	 *
	 * @return Router
	 */
	public static function getInstance()
    {
		if (!self::$instance) {
			self::$instance = new Router();
        }

		return self::$instance;
	}

	/**
	 * Efetua a análise da URL para detectar se houve alguma rota encontrada, caso contrário irá retornar
	 * uma rota para tela de erro
	 *
	 * @return array
	 * @todo Por o bloco de verificação dentro de um try...catch pois o match lança excessão caso não encontre rotas se cair no catch retorna rota de erro
	 */
	public function match()
    {
		$context = new RequestContext();
		$context->fromRequest(Request::createFromGlobals());

		$matcher = new UrlMatcher($this->collection, $context);

		try {
			$parameters = $matcher->match(Request::createFromGlobals()->getPathInfo());
		} catch (ResourceNotFoundException $e) {
			return new Response('caminho_nao_encontrado', 404);
		} catch (MethodNotAllowedException $e) {
			return new Response('metodo_nao_permitido', 405);
		}

		return $parameters;
	}

	/**
	 * Retorna uma nova instância de SubRouter para uso em prefixos ou watchers
	 *
	 * @return SubRouter
	 */
	public function getSubRouter()
    {
		return new SubRouter();
	}

	/**
	 * Retorna a coleção de rotas cadastradas até agora
	 *
	 * @return  RouteCollection
	 */
	public function getCollection()
    {
		return $this->collection;
	}
    
}
