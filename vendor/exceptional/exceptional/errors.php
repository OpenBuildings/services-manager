<?php

class PhpException extends ErrorException {

     function __construct($errstr, $errno, $errfile, $errline) {
         parent::__construct($errstr, 0, $errno, $errfile, $errline);
     }

}

class PhpError extends PhpException {
    /*
     * Must change the error message for undefined variables
     * Otherwise, Exceptional groups all errors together (regardless of variable name)
     */
    function __construct($errstr, $errno, $errfile, $errline) {
        if (@substr($errstr, 0, 25) == "Call to undefined method ") {
            $errstr = substr($errstr, 25)." is undefined";
        }
        parent::__construct($errstr, $errno, $errfile, $errline);
    }

}

class PhpWarning extends PhpException {
}

class PhpStrict extends PhpException {
}

class PhpParse extends PhpException {
}

class PhpNotice extends PhpException {
    /*
     * Must change the error message for undefined variables
     * Otherwise, Exceptional groups all errors together (regardless of variable name)
     */
    function __construct($errstr, $errno, $errfile, $errline) {
        if (@substr($errstr, 0, 20) == "Undefined variable: ") {
            $errstr = "\$".substr($errstr, 20)." is undefined";
        }
        parent::__construct($errstr, $errno, $errfile, $errline);
    }

}
