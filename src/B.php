<?php

namespace My\Example;

class B {
    function doStuffWithClassA() {
        // B.php depends on A.php here
        echo (new A())->getStatuscode();

        // Introduce an intentional issue to be detected by etsy/phan analysis.
        $foo = 1;
        $foo->length;
    }
}
