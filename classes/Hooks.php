<?php

namespace modules\login_session\classes;

use core\classes\exceptions\RedirectException;
use core\classes\Hook;
use core\classes\Model;
use core\classes\Authentication;
use core\classes\models\Customer;
use core\classes\models\Administrator;

class Hooks extends Hook {

	public function init_authentication(Authentication $auth) {
		$model = new Model($this->config, $this->database);

		// validate customer login session
		$login_session = $model->getModel('\modules\login_session\classes\models\LoginSession');
		$login_session = $login_session->getCustomerSession(session_id());
		if ($login_session) {
			if (!$login_session->isValid()) {
				$auth->logoutCustomer(FALSE);
				throw new RedirectException($this->url->getUrl('Customer', 'login').'?expired=1');
			}
			else {
				$login_session->updated = date('c');
				$login_session->update();
			}
		}
		else {
			$auth->logoutCustomer(FALSE);
		}

		// validate administrator login session
		$login_session = $model->getModel('\modules\login_session\classes\models\LoginSession');
		$login_session = $login_session->getAdministratorSession(session_id());
		if ($login_session) {
			if (!$login_session->isValid()) {
				$auth->logoutAdministrator(FALSE);
				throw new RedirectException($this->url->getUrl('Administrator', 'login').'?expired=1');
			}
			else {
				$login_session->updated = date('c');
				$login_session->update();
			}
		}
		else {
			$auth->logoutAdministrator(FALSE);
		}
	}

	public function after_loginCustomer(Customer $customer) {
		$model = new Model($this->config, $this->database);
		$login_session = $model->getModel('\modules\login_session\classes\models\LoginSession');

		// kick existing logins
		$login_session->kickCustomer($customer->id);

		// insert login session record
		$login_session->customer_id = $customer->id;
		$login_session->session_id = session_id();
		$login_session->created = date('c');
		$login_session->updated = date('c');
		$login_session->ip = $this->request->serverParam('REMOTE_ADDR');
		$login_session->insert();
	}

	public function after_loginAdministrator(Administrator $admin) {
		$model = new Model($this->config, $this->database);
		$login_session = $model->getModel('\modules\login_session\classes\models\LoginSession');

		// kick existing logins
		$login_session->kickAdministrator($admin->id);

		// insert login session record
		$login_session->administrator_id = $admin->id;
		$login_session->session_id = session_id();
		$login_session->created = date('c');
		$login_session->updated = date('c');
		$login_session->ip = $this->request->serverParam('REMOTE_ADDR');
		$login_session->insert();
	}

	public function after_logoutCustomer($customer_id) {
		$model = new Model($this->config, $this->database);
		$login_session = $model->getModel('\modules\login_session\classes\models\LoginSession');

		// logout login session record
		$login_session = $login_session->getCustomerSession(session_id());
		if ($login_session) {
			$login_session->logged_out = TRUE;
			$login_session->update();
		}
	}

	public function after_logoutAdministrator($admin_id) {
		$model = new Model($this->config, $this->database);
		$login_session = $model->getModel('\modules\login_session\classes\models\LoginSession');

		// logout login session record
		$login_session = $login_session->getAdministratorSession(session_id());
		if ($login_session) {
			$login_session->logged_out = TRUE;
			$login_session->update();
		}
	}
}