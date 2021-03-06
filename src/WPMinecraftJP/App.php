<?php
namespace WPMinecraftJP;

class App {
    const NAME = 'minecraftjp';
    const REWRITE_VERSION = 1;

    public static function init() {
        Configure::load();

        // localization
        load_plugin_textdomain(self::NAME, false, dirname(plugin_basename(self::getPluginFile())) . DIRECTORY_SEPARATOR . 'locale');

        Controller\UserController::init();
        if (is_admin()) {
            add_action('init', array(__CLASS__, 'initAdmin'));
        } else {
            add_action('init', array(__CLASS__, 'initPublic'));
            add_action('template_redirect', array(__CLASS__, 'dispatch'), -100);
        }
        add_action('init', array(__CLASS__, 'initRewriteRule'));
    }

    public static function activate() {
    }

    public static function getPluginFile() {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'minecraftjp.php');
    }

    public function initPublic() {
        global $wp;

        $wp->add_query_var('minecraftjp_action');
    }

    public function initAdmin() {
        add_action('admin_menu', array('WPMinecraftJP\Controller\AdminController', 'init'));
    }

    public function initRewriteRule() {
        add_rewrite_rule('^minecraftjp/(.*)?', 'index.php?minecraftjp_action=$matches[1]', 'top');
        if (Configure::read('rewrite_version') < self::REWRITE_VERSION) {
            flush_rewrite_rules();
            Configure::write('rewrite_version', self::REWRITE_VERSION);
        }
    }

    public function dispatch() {
        global $wp;

        if (empty($wp->query_vars['minecraftjp_action'])) {
            return;
        }

        $action = $wp->query_vars['minecraftjp_action'];

        $controller = Controller\PublicController::init();


        if (!$controller->dispatch($action)) {
            status_header(404);
            header('Content-type: application/json');
            echo json_encode(array('error' => 'Unknown endpoint'));
        }
        exit;
    }
}