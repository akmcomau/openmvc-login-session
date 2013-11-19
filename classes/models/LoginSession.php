<?php

namespace modules\login_session\classes\models;

use core\classes\Model;

class LoginSession extends Model {

	protected $table       = 'login_session';
	protected $primary_key = 'login_session_id';

	protected $columns     = [
		'login_session_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'login_session_session_id' => [
			'data_type'      => 'text',
			'data_length'    => '64',
			'null_allowed'   => FALSE,
		],
		'login_session_logged_out' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'FALSE',
		],
		'login_session_kicked' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'FALSE',
		],
		'login_session_created' => [
			'data_type'      => 'datetime',
			'null_allowed'   => FALSE,
		],
		'login_session_updated' => [
			'data_type'      => 'datetime',
			'null_allowed'   => FALSE,
		],
		'administrator_id' => [
			'data_type'      => 'int',
			'null_allowed'   => TRUE,
		],
		'customer_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => TRUE,
		],
	];

	protected $indexes = [
		'login_session_session_id',
		'login_session_logged_out',
		'login_session_kicked',
		'login_session_created',
		'login_session_updated',
		'administrator_id',
		'customer_id',
	];

	protected $foreign_keys = [
		'administrator_id' => ['administrator', 'administrator_id'],
		'customer_id'      => ['customer', 'customer_id'],
	];

	public function getCustomerSession($session_id) {
		$module_config = $this->config->moduleConfig('Login Sessions');
		$ttl = $module_config->time_to_live;
		$date = $this->database->quote(date('c', strtotime("now - $ttl seconds")));
		$sql = "
			SELECT *
			FROM login_session
			WHERE
				login_session_session_id = ".$this->database->quote($session_id)."
				AND NOT login_session_kicked
				AND NOT login_session_logged_out
				AND login_session_updated > $date
				AND customer_id IS NOT NULL
			ORDER BY
				login_session_updated DESC
			LIMIT 1
		";
		$data = $this->database->querySingle($sql);
		if ($data) {
			return $this->getModel(__CLASS__, $data);
		}
		else {
			return NULL;
		}
	}

	public function getAdministratorSession($session_id) {
		$module_config = $this->config->moduleConfig('Login Sessions');
		$ttl = $module_config->time_to_live;
		$date = $this->database->quote(date('c', strtotime("now - $ttl seconds")));
		$sql = "
			SELECT *
			FROM login_session
			WHERE
				login_session_session_id = ".$this->database->quote($session_id)."
				AND NOT login_session_kicked
				AND NOT login_session_logged_out
				AND login_session_updated > $date
				AND administrator_id IS NOT NULL
			ORDER BY
				login_session_updated DESC
			LIMIT 1
		";
		$data = $this->database->querySingle($sql);
		if ($data) {
			return $this->getModel(__CLASS__, $data);
		}
		else {
			return NULL;
		}
	}

	public function getForCustomer($customer_id) {
		$module_config = $this->config->moduleConfig('Login Sessions');
		$ttl = $module_config->time_to_live;
		$date = $this->database->quote(date('c', strtotime("now - $ttl seconds")));
		$sql = "
			SELECT *
			FROM login_session
			WHERE
				customer_id = ".$this->database->quote($customer_id)."
				AND NOT login_session_kicked
				AND NOT login_session_logged_out
				AND login_session_updated > $date
			ORDER BY
				login_session_updated DESC
			LIMIT 1
		";
		$data = $this->database->querySingle($sql);
		if ($data) {
			return $this->getModel(__CLASS__, $data);
		}
		else {
			return NULL;
		}
	}

	public function kickCustomer($customer_id) {
		$module_config = $this->config->moduleConfig('Login Sessions');
		$sql = "
			UPDATE login_session
				SET login_session_kicked = TRUE
			WHERE
				customer_id = ".$this->database->quote($customer_id)."
				AND NOT login_session_kicked
				AND NOT login_session_logged_out
		";
		return $this->database->executeQuery($sql);
	}

	public function kickAdministrator($admin_id) {
		$module_config = $this->config->moduleConfig('Login Sessions');
		$sql = "
			UPDATE login_session
				SET login_session_kicked = TRUE
			WHERE
				administrator_id = ".$this->database->quote($admin_id)."
				AND NOT login_session_kicked
				AND NOT login_session_logged_out
		";
		return $this->database->executeQuery($sql);
	}

	public function isValid() {
		$module_config = $this->config->moduleConfig('Login Sessions');
		$ttl = $module_config->time_to_live;
		if ($this->login_session_logged_out || $this->login_session_kicked) {
			return FALSE;
		}

		if (strtotime($this->updated) < (time()-$ttl)) {
			return FALSE;
		}

		return TRUE;
	}
}
