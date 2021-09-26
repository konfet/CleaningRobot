<?php
namespace app\Actions;

use app\Utils;

class ActionAdvance extends Action{
    public function body() {
        return $this->move();
    }
}