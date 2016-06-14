<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * Class LimpagitCommand
 * ---------------------------------------
 *
 * Comando criado para efetuar limpeza dos arquivos gits da pasta vendor 
 * afim de subi-la para o servidor
 *
 * @package App\Console
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 16/05/2016
 */
class LimpagitCommand extends Command
{

    private $blackList = array('.gitignore', '.travis.yml',  '.travis.yml', '.gitmodules', '.gitattributes', '.coveralls.yml');
    
    /**
     * configure your command here
     */
    protected function configure() 
    {
        $this->setName('LimpaGit');
        $this->setDescription('Remove todos arquivos e pastas git da pasta vendor');
    }

    /**
     * do the thing here
     */
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $baseDir = SYS_ROOT . 'vendor';
        $this->cleanFolders($output, $baseDir);
    }

    private function cleanFolders($output, $baseDir) {
        $scan = $this->scanFolders($baseDir);

        if ($scan === false) {
            $output->writeln("caminho nao encontrado: " . $baseDir);
            return;
        }

        foreach ($scan as $dir) {
            $fullPath = $baseDir . DS . $dir;

            if (is_file($fullPath)) {
                if (in_array($dir, $this->blackList)) {
                    if (!$this->remove($output, $fullPath)) {
                        $output->writeln("Erro ao remover arquivo $fullPath");
                    }
                }
            } else {
                if ($dir == '.git') {
                    if (!$this->remove($output, $fullPath)) {
                        $output->writeln("Erro ao remover $fullPath");
                    }
                } else {
                    $this->cleanFolders($output, $fullPath);
                }
            }
        }
    }
    
    /**
     * Remove pastas . e .. da lista de pastas
     * @param string $dirs
     */
    private function scanFolders($path) {
        $dirs = scandir($path);
        $post = array();
        if ($dirs) {
            foreach ($dirs as $i => $folder) {
                if (!in_array($folder, array('.', '..'))){
                    $post[] = $folder;
                }
            }          
        }

        return $post;
    }

    /**
     * Apaga arquivos e diretórios, no caso desse último recursivamente.
     */
    private function remove($output, $path){

        if (is_file($path)) {
            $output->writeln("removendo arquivo: " . $path);
            return unlink($path);
        } else {
            $scandir = $this->scanFolders($path);

            if (count($scandir) == 0) {
                $output->writeln("removendo diretorio: " . $path);
                return rmdir($path);
            } else {
                foreach ($scandir as $inPath) {
                    if (!$this->remove($output, $path . DS . $inPath)) {
                        $output->writeln("Falha ao remover: " . $path . DS . $inPath);
                    }
                }
            }
        }
    }

}
