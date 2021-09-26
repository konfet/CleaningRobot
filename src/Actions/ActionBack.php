<?php
namespace app\Actions;

use app\Utils;

class ActionBack extends Action{
    public function body() {
        return $this->move(-1);
    }
}