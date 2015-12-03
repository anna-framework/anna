<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use Psy\Configuration;
use Psy\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * WorkTableCommand
 * ---------------------------------------.
 *
 * Inicializa sessão interavida php para debug na linha de comando.
 */
class DeskCommand extends Command
{
    protected function configure()
    {
        $this->setName('desk');
        $this->setDescription('Inicializa sessao interavida php para debug na linha de comando.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getPsyConfig();
        $psy_config = new Configuration($config);
        $psy_shell = new Shell($psy_config);

        $psy_shell->run();
    }

    /**
     * Retorna a configuração do Psy.
     *
     * @return array
     */
    private function getPsyConfig()
    {
        return [
            'pager'             => 'more',
            'historySize'       => 0,
            'eraseDuplicates'   => false,
            'usePcntl'          => false,
            'useReadline'       => false,
            'requireSemicolons' => true,
            'errorLoggingLevel' => E_ALL & ~E_NOTICE,
            'commands'          => [
                new \Psy\Command\ParseCommand(),
            ],
            'casters' => [
                'MyFooClass' => 'MyFooClassCaster::castMyFooObject',
            ],
            'tabCompletion'         => false,
            'tabCompletionMatchers' => [
                new \Psy\TabCompletion\Matcher\MongoClientMatcher(),
                new \Psy\TabCompletion\Matcher\MongoDatabaseMatcher(),
            ],
            'warnOnMultipleConfigs' => true,
        ];
    }
}
