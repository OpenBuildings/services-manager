<?php

class ExceptionalData {

    protected $exception;
    protected $backtrace = array();

    function __construct(Exception $exception) {
        $this->exception = $exception;

        $trace = $this->exception->getTrace();
        foreach ($trace as $t) {
            if (!isset($t["file"])) continue;
            $this->backtrace[] = "$t[file]:$t[line]:in `$t[function]\'";
        }

        // environment data
        $data = ExceptionalEnvironment::to_array();

        // exception data
        $message = $this->exception->getMessage();
        $now = date("c");

        // spoof 404 error
        $error_class = get_class($this->exception);
        if ($error_class == "Http404Error") {
            $error_class = "ActionController::UnknownAction";
        }

        $data["exception"] = array(
            "exception_class" => $error_class,
            "message" => $message,
            "backtrace" => $this->backtrace,
            "occurred_at" => $now
        );

        // context
        $context = Exceptional::$context;
        if (!empty($context)) {
            $data["context"] = $context;
        }

        if (isset($_SERVER["HTTP_HOST"])) {

            // request data
            $session = isset($_SESSION) ? $_SESSION : array();

            // sanitize headers
            $headers = getallheaders();
            if (isset($headers["Cookie"])) {
              $sessionKey = preg_quote(ini_get("session.name"), "/");
              $headers["Cookie"] = preg_replace("/$sessionKey=\S+/", "$sessionKey=[FILTERED]", $headers["Cookie"]);
            }

            $server = $_SERVER;
            $keys = array("HTTPS", "HTTP_HOST", "REQUEST_URI", "REQUEST_METHOD", "REMOTE_ADDR");
            $this->fill_keys($server, $keys);

            $protocol = $server["HTTPS"] && $server["HTTPS"] != "off" ? "https://" : "http://";
            $url = $server["HTTP_HOST"] ? "$protocol$server[HTTP_HOST]$server[REQUEST_URI]" : "";

            $data["request"] = array(
                "url" => $url,
                "request_method" => strtolower($server["REQUEST_METHOD"]),
                "remote_ip" => $server["REMOTE_ADDR"],
                "headers" => $headers,
                "session" => $session
            );

            $params = array_merge($_GET, $_POST);

            foreach (Exceptional::$blacklist as $filter) {
                $params = $this->filter_params($params, $filter);
            }

            if (!empty($params)) {
                $data["request"]["parameters"] = $params;
            }
        }
        else {
          $data["request"] = array();
        }

        $data["request"]["controller"] = Exceptional::$controller;
        $data["request"]["action"] = Exceptional::$action;

        $this->data = $data;
    }

    function uniqueness_hash() {
        return md5(implode("", $this->backtrace));
    }

    function to_json() {
        return json_encode($this->data);
    }

    function fill_keys(&$arr, $keys) {
        foreach ($keys as $key) {
            if (!isset($arr[$key])) {
                $arr[$key] = false;
            }
        }
    }

    function filter_params($params, $term) {
        foreach ($params as $key => $value) {
            if (preg_match("/$term/i", $key)) {
                $params[$key] = '[FILTERED]';
            }
            elseif (is_array($value)) {
                $params[$key] = $this->filter_params($value, $term);
            }
        }
        return $params;
    }

}

// http://php.net/manual/en/function.getallheaders.php
if (!function_exists("getallheaders")) {
    function getallheaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
           if (substr($name, 0, 5) == "HTTP_") {
               $headers[str_replace(" ", "-", ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
        }
        return $headers;
    }
}
