<?php
namespace app\Actions;

use app\Utils;

class ActionTurnLeft extends Action{
    public function body() {
        $this->robot->facing = Utils::D_AFTER_TURN[$this->robot->facing][Utils::LEFT];
        return true;
    }
    
    
}