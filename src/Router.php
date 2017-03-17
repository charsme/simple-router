<?php

namespace Resilient;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use FastRoute\RouteParser\Std as StdParser;

class Router {
    
    private $routemap;
    
    private $opt = [
                    'cache' => false
                    ];
    
    public function __construct (array $routemap, array $opt = [])
    {
        $this->routemap = $routemap;
        
        $this->opt = array_merge($this->opt, $opt);
    }
    
    
}
