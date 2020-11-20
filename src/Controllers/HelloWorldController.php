<?php

namespace App\Controllers;

class HelloWorldController {
    private $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public function __invoke($request) 
    {
        $name = $request->getAttribute('name');

        echo "Hello, {$name}, {$this->foo}";
    }
}