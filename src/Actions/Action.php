<?php
namespace app\Actions;

use app\Utils;
use app\Robot;

class Action {
    
    public $robot;
    public $c;
    public $xDelta = 0;
    public $yDelta = 0;    
    public $step;
    
    /**
     * 
     * @param Robot $robot
     * @throws \Exception
     */
    
    
    public static function runStep(Robot $robot, $step = null) : bool{
        if (is_null($step)) {
            $step = $robot->step;
        }
//  step may be different in case of stuck processing
                
        switch ($step) {    
            case 'TR':                
                $action = new  ActionTurnRight($robot);
                break;
            case 'TL':                
                $action = new ActionTurnLeft($robot);
                break;
            case 'A':                
                $action = new ActionAdvance($robot);
                break;
            case 'B':                
                $action = new ActionBack($robot);
                break;
            case 'C':                
                $action = new ActionClean($robot);
                break;
            default:
                throw new \Exception('Unknown command');
        }
        $action->step = $step;
        $res = $action->go();        
        return $res;
    }
            
    public function __construct($robot) {
        $this->robot = $robot;        
        $this->c = $robot->command;
    }
            
    protected function before() {
        $newBatteryLevel = $this->robot->battery - Utils::BATTERY_COSTS[$this->step];
//  if the battery is empty then stop
        if ($newBatteryLevel < 0) {
            $this->c->log('The battery is empty. Program is stopped.');
            throw new \Exception('Empty battery!');
        }                    
        $this->robot->battery = $newBatteryLevel;
    }
    
    public function after() {
       if ($this->xDelta != 0 || $this->yDelta != 0) {
//  position was changed (moving actions A or B)
            $this->robot->position = ['X' => $this->robot->position['X'] + $this->xDelta, 'Y' => $this->robot->position['Y'] + $this->yDelta];
//  new position was visited
            $this->robot->addToVisited($this->robot->position);
        }
        $this->c->log($this->logString());
    }    
     
    public function go() {
//  battery level will be decreased in any case
        $this->before();
        $res = $this->body();
        if ($res) {
//  position will be changed only if the action was successful
            $this->after();
        } 
        return $res;
    }
           
    public function body() {   
//  always be true for non-moving actions in case when the battery level is enouth        
        return true;
    }
    
    public function logString() {    
//  log string when success        
        $robot = $this->robot;
        $xPos = $robot->position['X'];
        $yPos = $robot->position['Y'];
        if ($robot->stuckStage == 0) {
//  the zero step is not good for log            
            $stepNumToLog = 1 + $robot->stepNum;        
            $res =  "Step $stepNumToLog => $this->step done. Position: ($xPos, $yPos), Battery: $robot->battery, Facing: $robot->facing";
        }
        else {
            $res =  "  BS stage $robot->stuckStage, step $robot->stuckStep => $this->step done. Position: ($xPos, $yPos), Battery: $robot->battery, Facing: $robot->facing";
        }
            
        return $res;
    }
    
    
    public function canGo(int $x, int $y)
//  moving action A or B can be performed only if new cell is of S type
    {
        $val = $this->robot->map[$y][$x] ?? null;
        if (is_null($val) || $val == 'C') {
            return false;
        }
        return true;
    }

    public function move($dir = 1) {        
            $robot = $this->robot;
            switch ($robot->facing) {
            case Utils::D_EAST:
                $this->xDelta = $dir;
                break;

            case Utils::D_WEST:
                $this->xDelta = -$dir;
                break;

            case Utils::D_NORTH:
                $this->yDelta = -$dir;
                break;

            case Utils::D_SOUTH:
                $this->yDelta = $dir;
                break;
        }
        $newX = $robot->position['X'] + $this->xDelta;
        $newY = $robot->position['Y'] + $this->yDelta;        
        if (!$this->canGo($newX, $newY)) {
            
            if ($robot->stuckStage == 0) {                
//  Program step in process. First BS (back-off strategy) level will be started.                
                $stepNumToLog = 1 + $robot->stepNum;
                $res =  "Step $stepNumToLog => $this->step FAILED (an obstacle is hit). Back-off attempt (BS) will be performed. Battery: $robot->battery";                
            }                
            else {
//  BS in process. Next BS (back-off strategy) level will be started                
                $res =  "  BS stage $robot->stuckStage, step $robot->stuckStep => $this->step FAILED (an obstacle is hit). Battery: $robot->battery";
            }
            $this->c->log($res);
            $robot->stuckStage += 1;
            $robot->stuckStep = 0;
            Action::startStuckStage($robot);            
            return false;
        }
     
        return true;
    }
    
    public static function startStuckStage(Robot $robot)
    {
        $res = true;
        $cnt = count(Utils::STUCK_STAGES);
        if ($robot->stuckStage > $cnt) {
//  if there is no more BS stages defined, then the robot gives up            
            $robot->command->log('Robot is completely stuck. Program is stopped.');
            throw new \Exception('Completely stuck!');
        }
            
        $stage = Utils::STUCK_STAGES[$robot->stuckStage];
        foreach ($stage as $key => $value) {
            $robot->stuckStep = $key;
            $res = Action::runStep($robot, $value);
//  the first failure in the BS chain will cause the next BS chain starting (if defined)             
            if (!$res) {
                break;
            }
        }
//  when some of BS stages will be fully completed, then the control will be returned to the robot
        if ($res) {            
            $robot->stuckStage = 0;
            $robot->stuckStep = 0;            
        }

    }       
}