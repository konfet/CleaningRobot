<?php

namespace Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use app\Utils;

class Test extends TestCase
{
    private $command;

    public function __construct(string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->command = $this->createApplication()->find('cleaner');

    }

    private function createApplication() 
    {
        return require __DIR__ . '../bootstrap.php';
    }

    public function testInvalidSourceFileStructure()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'source' => __DIR__ . '../../files/testInvalidFileStructure.json',
            'result' => __DIR__ . '../../files/result.json'
        ]);

        $output = $commandTester->getDisplay();
        $pos = stripos($output, "source json file has invalid structure");
        $this->assertEquals(is_int($pos), TRUE);
    }
    
    public function testFileNotExists()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'source' => __DIR__ . '../../files/not-exist.json',
            'result' => __DIR__ . '../../files/result.json'
        ]);

        $output = $commandTester->getDisplay();
        $pos = stripos($output, "Source path is not valid");
        $this->assertEquals(is_int($pos), TRUE);
    }
    
    public function testBatteryEmpty()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'source' => __DIR__ . '../../files/testBatteryEmpty.json',
            'result' => __DIR__ . '../../files/testBatteryEmpty_result.json',
            //'--logfile' => __DIR__ . '../../files/testBatteryEmpty_log.txt'
        ]);

        $output = $commandTester->getDisplay();
        $pos = stripos($output, "The battery is empty");
        $this->assertEquals(is_int($pos), TRUE);
    }
    
    public function testCompletelyStuck()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'source' => __DIR__ . '../../files/testCompletelyStuck.json',
            'result' => __DIR__ . '../../files/testCompletelyStuck_result.json'
            //'--logfile' => __DIR__ . '../../files/testCompletelyStuck_log.txt'
        ]);

        $output = $commandTester->getDisplay();
        $pos = stripos($output, "Robot is completely stuck");
        $this->assertEquals(is_int($pos), TRUE);
    }
    
    public function doMYQ($idx)
    {
        $commandTester = new CommandTester($this->command);
        $sourceMYQ = __DIR__ . "../../files/testMYQ{$idx}.json";
        $resMYQ = __DIR__ . "../../files/testMYQ{$idx}_result.json";
        $resMYQ_achieved = __DIR__ . "../../files/testMYQ{$idx}_resultAchieved.json";
        $logfile = __DIR__ . "../../files/testMYQ{$idx}_log.txt";
        
        $commandTester->execute([
            'source' => $sourceMYQ,
            'result' => $resMYQ_achieved,
            '--logfile' => $logfile]
            
        );        
//  compare test result file given by MyQ and the file created by the progtam         
        $compare = Utils::compareResults($resMYQ, $resMYQ_achieved);
        $this->assertEquals($compare, 1);
    }
    
    public function testMYQ1() {
        $this->doMYQ(1);
    }
    
    public function testMYQ2() {
        $this->doMYQ(2);
    }
}    
