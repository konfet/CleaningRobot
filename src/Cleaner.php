<?php
namespace app;

use app\Utils;
use app\Actions\Action;

class Cleaner implements Robot{
    
//  all parameters of CLI    
    public $command;
    public $sourcePath;
    public $output;

//  program of commands and map of the room do not change
    public $map;    
    public $program;    

//  other params of robot are states and subject to change
    public $step;    
    public $stepNum;    
    public $stuckStage = 0;    
    public $stuckStep = 0;    
    
    public $battery;
    public $facing;
    public $position;
    public $processed;
    public $visited = [];
    
    public function setPosition($position) {
        $this->position = $position;
    }

    public function addToProcessed($position) {        
        $this->output['cleaned'][] = $position;                
    }

    public function addToVisited($position) {
        $this->output['visited'][] = $position;                
    }
        
    public function __construct($command, $sourcePath)
    {
        $this->command = $command;        
        $this->sourcePath = $sourcePath;
    }
            
    public function start(){
        
        $this->beforeStart();
        $this->command->log(sprintf('Started from: (%d,%d), facing: %s', $this->position['X'], $this->position['Y'], $this->facing));
        try {
            foreach ($this->program as $key=>$step) {
                $this->stepNum = $key;
                $this->step = $step;
            //$this->command->log("$key => $step");
                Action::runStep($this);
            }    
        }   
       
        finally {
            $this->output['battery'] = $this->battery;
            $this->output['final'] = $this->position + ["facing" => $this->facing];
//  delete non-unique values from arrays                        
            $this->output['visited'] = array_map("unserialize", array_unique(array_map("serialize", $this->output['visited'])));                    
            $this->output['cleaned'] = array_map("unserialize", array_unique(array_map("serialize", $this->output['cleaned'])));                    
        }                
    }

    public function beforeStart(){        
        $input = json_decode(file_get_contents($this->sourcePath), true);
//      validate if a file is json         
        if (!$input) {
            throw new \Exception('Source file is not a json.');
        }
//      validate source file json structure
        if (!$this->validateInput($input)) {
            throw new \Exception('Source json file has invalid structure.');
        };
                
        $this->map = $input['map'];
        
//      control variables        
        $this->battery = $input['battery'];
        $this->program = $input['commands'];
        
        //$this->position['X','Y'] = [$input['start']['X'], $input['start']['Y']];
        $this->position['X'] = $input['start']['X'];
        $this->position['Y'] = $input['start']['Y'];
        
        $this->facing = $input['start']['facing'];
        $this->visited[] = $this->position;
        $this->processed = [];        
        
        $this->initOutput();                        
    }
    
    
    public function initOutput() {
        $this->output = [
            'visited' => [],
            'cleaned' => [],
            'final' => ['X' => 0, 'Y' => 0, 'facing' => 'Z'],
            'battery' => 0
        ];
        $this->output['visited'][] = $this->position;
    }    
    
    public function validateInput($input) {
       
        if (!array_key_exists('map', $input) ||
            !array_key_exists('start', $input) ||
            !array_key_exists('commands', $input) ||
            !array_key_exists('battery', $input)
        ) return false;

//      content elements types
        if (!is_array($input['map']) ||
            !is_array($input['start']) ||
            !is_array($input['commands']) ||
            !is_int($input['battery'])
        ) return false;
        
//      cell types
        foreach ($input['map'] as $row) {
            foreach ($row as $value) {
                if (!in_array($value, Utils::CELL_TYPES)) return false;
            }
        }
        // action types
        foreach ($input['commands'] as $command) {
            if (!in_array($command, Utils::ACTION_TYPES)) return false;
        }
        
//      start should be (X, Y, Facing)
        if (!array_key_exists('X', $input['start']) ||
            !is_int($input['start']['X']) ||
            !array_key_exists('Y', $input['start']) |
            !is_int($input['start']['Y']) ||
            !array_key_exists('facing', $input['start']) ||
            !is_string($input['start']['facing'])
        ) return false;

        return true;
    }

}
