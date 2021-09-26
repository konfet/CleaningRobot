<?php

namespace app\Actions;
use app\Utils;

class ActionTurnRight extends Action{
    public function body() {
        $this->robot->facing = Utils::D_AFTER_TURN[$this->robot->facing][Utils::RIGHT];
        return true;
    }
    
    
}