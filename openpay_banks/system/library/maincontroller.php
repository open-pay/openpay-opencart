<?php

//Opencart v 2.0.1.1
class MainController extends Controller {

    protected $error;

    public function __construct($registry) {

        parent::__construct($registry);

        if (!defined('HTTPS_CATALOG'))
            define('HTTPS_CATALOG', 'http://' . $this->sanitizePath($this->request->server['HTTP_HOST']) . '/');

        if (version_compare(PHP_VERSION, '5.3.0') < 0)
            throw new Exception('At least PHP 5.3.0 needed');

        $registry->set('debugLog', new debugLog($this->config->get('config_error_filename'), $registry));
        $this->error = array('warning' => array(), 'attention' => array(),);
    }

    protected function fillSetting($setting_name, $default = '') {
        return isset($this->request->post[$setting_name]) ? trim($this->request->post[$setting_name]) : ( $this->config->has($setting_name) ? trim($this->config->get($setting_name)) : $default );
    }

    protected function merge(Array &$target, Array $with, $rewrite = false) {
        foreach ($with as $key => $value)
            if ($rewrite || !isset($target[$key]))
                $target[$key] = $value;
    }

    protected function isEmptyArray(Array $array) {
        foreach ($array as $element) {
            if (is_array($element)) {
                if (!$this->isEmptyArray($element))
                    return false;
            } else
                return false;
        }
        return true;
    }

    protected function sanitizePath($path) {
        return str_replace('//', '/', $path);
    }

    public function modelExists($model) {
        return file_exists(DIR_APPLICATION . 'model/' . $model . '.php');
    }

}

?>