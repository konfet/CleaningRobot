<?php

namespace app;

use app\Cleaner;
use app\Utils;
use Symfony\Component\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Main extends Command
{
   
    public $output;
    private $logPath;
    private $stream;
    private $logger;
    
    protected function configure()
    {
        $this
            ->setName('cleaner')
            ->setDescription('Cleaning robot')
            ->setHelp('Run cleaning robot')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'Path to the source file.'
            )
            ->addArgument(
                'result',
                InputArgument::REQUIRED,
                'Path to the result file.'
            )
            ->addOption(
                'logfile', null,
                InputOption::VALUE_REQUIRED,
                'The complete log should be written to this file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        $sourcePath = $input->getArgument('source');
        $resultPath = $input->getArgument('result');        
        $this->logPath = $input->getOption('logfile');
         
        if ($this->logPath) {
            $this->createLogger();
        }
                            
        $this->output = $output;
        
        if (!file_exists($sourcePath)) {
            $output->writeln('Source path is not valid!');
            return 0;
        }

        if (!file_exists(pathinfo($resultPath)['dirname'])) {
            $output->writeln('Result path is not valid!');
            return 0;
        }              
        
        try {                               
            $robot = new Cleaner($this, $sourcePath);
            $robot->start();                                  
        }
        catch (\Exception $e) {
            $output->writeln($e->getMessage());                            
        }
        finally {                       
            if (!is_null($robot->output)) {                                
                file_put_contents($resultPath, json_encode($robot->output));
                $output->writeln("The result file is saved: $resultPath");                            
            }
            if ($this->logPath) {
                $output->writeln("The log file is saved: $this->logPath");
            }
            return 0;
        }        
    }
    
    private function createLogger() {
        if (file_exists($this->logPath)) {
            unlink($this->logPath);
        }                    
        $this->stream = new StreamHandler($this->logPath, Logger::DEBUG);
        $formatter = new LineFormatter("%message%\n", "Y n j, g:i a");
        $this->stream->setFormatter($formatter);
// Create the main logger of the app
        $this->logger = new Logger('robot_logger');
        $this->logger->pushHandler($this->stream);
    }
    
    public function log($text) {
        if ($this->logPath) {
            $this->logger->info($text);
        }
        else {
            $this->output->writeln($text);
        }                
    }   
}