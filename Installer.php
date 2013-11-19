<?php

namespace modules\login_session;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\Language;
use core\classes\Model;
use core\classes\Menu;

class Installer {
	protected $config;
	protected $database;

	public function __construct(Config $config, Database $database) {
		$this->config = $config;
		$this->database = $database;
	}

	public function install() {
		$model = new Model($this->config, $this->database);

		$table = $model->getModel('\\modules\\login_session\\classes\\models\\LoginSession');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();
	}

	public function uninstall() {
		$model = new Model($this->config, $this->database);

		$table = $model->getModel('\\modules\\login_session\\classes\\models\\LoginSession');
		$table->dropTable();
	}

	public function enable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/checkout.php', DS.'modules'.DS.'checkout');

		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$main_menu->insert_menu(['users', 'customers'], 'login_sessions', [
			'controller' => 'administrator/LoginSessions',
			'method' => 'index',
		]);
		$main_menu->update();
	}

	public function disable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/checkout.php', DS.'modules'.DS.'checkout');
		// Remove some menu items to the admin menu
		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$menu = $main_menu->getMenuData();
		unset($menu['users']['children']['login_sessions']);
		$main_menu->setMenuData($menu);
		$main_menu->update();
	}
}