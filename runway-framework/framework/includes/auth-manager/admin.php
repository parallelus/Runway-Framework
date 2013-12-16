<?php 
	switch ($this->navigation) {		
		case 'auth':{
			if(isset($_POST['log'], $_POST['pwd'])){
				$this->set_user_credentials(strip_tags($_POST['log']), strip_tags($_POST['pwd']));
				$this->auth_user();
			}
		}

		default:{
			$this->view('admin-home');
		} break;
	}
?>