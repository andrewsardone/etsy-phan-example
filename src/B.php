<?php

namespace My\Example;

class B {
    function doStuffWithClassA() {
        echo (new A())->getStatuscode();
    }
}
