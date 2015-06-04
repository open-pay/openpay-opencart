<?php

class debugLog extends Log {

    private $registry;
    private $config;
    private $session;

    public function __construct($error_file_name, $registry) {
        parent::__construct($error_file_name);
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->session = $registry->get('session');
        unset($this->session->data['warning']);
    }

    public function write($msg, $obj = null) {
        if (!defined('MODULE_NAME') || !$this->config->get(MODULE_NAME . '_debug'))
            return;
        $moduleName = defined('MODULE_CODE') ? MODULE_CODE : 'Debug Info';
        $str = $moduleName . ': ' . $msg . ' ';
        if ($obj) {
            if (!is_object($obj) && !is_array($obj)) {
                $str .= $obj;
            } elseif (is_array($obj)) {
                if (version_compare(PHP_VERSION, '5.4.0') >= 0)
                    $str .= json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                else
                    $str .= json_encode($obj);
            }
            elseif (is_object($obj)) {
                $str .= serialize($obj);
            }
        }
        parent::write($str);
    }

    public function throwError($error) {
        $moduleName = defined('MODULE_CODE') ? MODULE_CODE : 'Error Info';
        $str = 'Error => ' . $moduleName . ": " . $error;
        parent::write($str);
        $this->session->data['warning'] = empty($this->session->data['warning']) ? $str : $this->session->data['warning'] . '<br>' . $str;
    }

}

;
?>