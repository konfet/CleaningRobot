<?php
namespace app\Actions;

class ActionClean extends Action{
    public function body() {
        $this->robot->addToProcessed($this->robot->position);
        return true;
    }       
}


