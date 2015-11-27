<?php
namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;

use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

use \Psy\Shell;
use \Psy\Configuration;

/**
 * ---------------------------------------
 * WorkTableCommand
 * ---------------------------------------
 *
 * Inicializa sessão interavida php para debug na linha de comando.
 *
 * @package Anna\Console\Commands
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
        $psy_shell = new Shell( $psy_config);
        
        $psy_shell->run();        
    }
    
    /**
     * Retorna a configuração do Psy
     * @return array
     */
    private function getPsyConfig()
    {
        return [
            'pager' => 'more',
            'historySize' => 0,
            'eraseDuplicates' => false,
            'usePcntl' => false,
            'useReadline' => false,
            'requireSemicolons' => true,
            'errorLoggingLevel' => E_ALL & ~E_NOTICE,
            'commands' => array(
                new \Psy\Command\ParseCommand,
            ),
            'casters' => array(
                'MyFooClass' => 'MyFooClassCaster::castMyFooObject',
            ),
            'tabCompletion' => false,
            'tabCompletionMatchers' => array(
                new \Psy\TabCompletion\Matcher\MongoClientMatcher,
                new \Psy\TabCompletion\Matcher\MongoDatabaseMatcher,
            ),
            'warnOnMultipleConfigs' => true,
        ];
    }
    
}
