<?php

class ExceptionalEnvironment {

    private static $environment;

    static function to_array() {
        if (!self::$environment) {
            $env = $_SERVER;

            // remove the following $_SERVER variables
            $vars = array("PHPSELF", "SCRIPT_NAME", "SCRIPT_FILENAME", "PATH_TRANSLATED", "DOCUMENT_ROOT", "PHP_SELF", "argv", "argc", "REQUEST_TIME", "PHP_AUTH_PW");
            foreach ($vars as $var) {
                if (isset($env[$var])) {
                    unset($env[$var]);
                }
            }

            // remove variables that begin with HTTP_
            foreach ($env as $k => $v) {
                if (substr($k, 0, 5) == "HTTP_") {
                  unset($env[$k]);
                }
            }

            self::$environment =  array(
                "client" => array(
                  "name" => Exceptional::$client_name,
                  "version" => Exceptional::$version,
                  "protocol_version" => Exceptional::$protocol_version
                ),
                "application_environment" => array(
                  "environment" => "production",
                  "env" => $env,
                  "host" => php_uname("n"),
                  "run_as_user" => self::get_username(),
                  "application_root_directory" => self::get_root_dir(),
                  "language" => "php",
                  "language_version" => phpversion(),
                  "framework" => null,
                  "libraries_loaded" => array()
                )
            );
        }

        return self::$environment;
    }

    static function get_username() {
        $vars = array("LOGNAME", "USER", "USERNAME", "APACHE_RUN_USER");
        foreach ($vars as $var) {
            if (getenv($var)) {
                return getenv($var);
            }
        }
        return "UNKNOWN";
    }

    static function get_root_dir() {
        if (isset($_SERVER["PWD"])) {
            return $_SERVER["PWD"];
        }
        return @$_SERVER["DOCUMENT_ROOT"];
    }

}
