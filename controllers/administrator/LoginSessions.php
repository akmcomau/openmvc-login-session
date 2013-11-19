<?php

namespace modules\login_session\controllers\administrator;

use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;

class LoginSessions extends Controller {

	protected $show_admin_layout = TRUE;

	protected $permissions = [
		'index' => ['administrator'],
	];

	public function index() {

	}

}