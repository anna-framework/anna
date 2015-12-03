<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use Anna\Routers\Router;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * Class RouteListCommand
 * ---------------------------------------.
 *
 * Exibe a lista de rotas já cadastrada
 */
class RouteListCommand extends Command
{
    protected function configure()
    {
        $this->setName('route:list');
        $this->setDescription('Exibe a lista de rotas registradas');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rotas = Router::getInstance()->getCollection()->all();
        $data = [];

        error_reporting(0);
        foreach ($rotas as $key => $route) {
            $watcher = isset($route->getDefaults()['watcher']) ? $route->getDefaults()['watcher'] : '';
            $methods = $route->getMethods();
            $methods = (count($methods) > 0) ? implode(', ', $methods) : 'ANY';
            $data[] = [$route->getPath(), $methods, $watcher, $route->getDefaults()['path']];
        }

        $table = new Table($output);

        $table->setHeaders(['Routes', 'Methods', 'Watchers', 'Controllers']);
        $table->setRows($data);

        $output->writeln('Anna: estas sao as rotas registradas ate o momento:');
        $table->render($output);
    }
}
