<?php
namespace WPMinecraftJP\Controller;

use WPMinecraftJP\App;
use WPMinecraftJP\Configure;

class AdminController extends Controller {
    private $group;
    public function __construct() {
        parent::__construct();
        $this->group = add_utility_page(__('MinecraftJP Settings', 'minecraftjp'), 'MinecraftJP', 'manage_options', 'minecraftjp', array(&$this, 'settings'), 'dashicons-cloud');
        add_filter('plugin_action_links', array(&$this, 'filterPluginActionLinks'), 10, 2);
        add_action('admin_notices', array(&$this, 'actionAdminNotices'));
        add_action('show_user_profile', array(&$this, 'actionShowUserProfile'));
    }

    public function settings() {
        if (!empty($_POST['updateSettings'])) {
            if (!wp_verify_nonce(isset($_POST['token']) ? $_POST['token'] : '', 'minecraftjp_settings')) {
                $this->setFlash(__('Bad request.', App::NAME), 'default', array('class' => 'error'));
                wp_safe_redirect(admin_url('?page=minecraftjp'));
                exit;
            }

            $fields = array('client_id', 'client_secret', 'username_suffix');
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    Configure::write($field, $_POST[$field]);
                }
            }
            Configure::write('avatar_enable', isset($_POST['avatar_enable']) && $_POST['avatar_enable'] == '1' ? 1 : 0);
            Configure::write('force_users_can_register', isset($_POST['force_users_can_register']) && $_POST['force_users_can_register'] == '1' ? 1 : 0);

            header('Location: ' . admin_url('?page=minecraftjp&success=' . urlencode(__('Settings saved.'))));
            exit;
        }

        $this->set('group', $this->group);
        $this->render('admin_settings');
    }

    public function actionAdminNotices() {
        $name = App::NAME . '_flash';
        $messages = isset($_COOKIE[$name]) ? json_decode(stripcslashes($_COOKIE[$name]), true) : array();
        foreach ($messages as $message) {
            $class = isset($message['params']['class']) ? $message['params']['class'] : 'updated';
            print <<<_HTML_
<div class="{$class}">
<p><strong>{$message['message']}</strong></p>
</div>
_HTML_;
        }
        setcookie($name, '', time() - 3600, '/');
    }

    public function actionShowUserProfile() {
        $this->set('isLinked', get_user_meta(get_current_user_id(), 'minecraftjp_sub', true));
        $this->render('user_profile');
    }

    public function filterPluginActionLinks($links, $file) {
        if ($file == plugin_basename(App::getPluginFile())) {
            array_unshift($links, '<a href="' . admin_url('admin.php?page=minecraftjp') . '">' . __('Settings') . '</a>');
        }
        return $links;
    }
}