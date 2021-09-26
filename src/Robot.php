<?php
namespace app;

interface Robot
{
    public function setPosition($position);
    
    public function addToProcessed($position);
    
    public function addToVisited($position);
    
    public function start();
    
    public function validateInput($input);
    
    public function initOutput();
    
}
