<?php
	switch ($this->navigation) {
		case 'auth':{
			if(isset($_POST['log'], $_POST['pwd'])){
				if($this->auth_user_login(strip_tags($_POST['log']), strip_tags($_POST['pwd'])))
				    $link = admin_url('admin.php?page=accounts&navigation=success');
    			else
    				$link = admin_url('admin.php?page=accounts&navigation=error');
    			echo '<script type="text/javascript">window.location = "'. esc_url_raw($link) .'";</script>';
			}
			break;
		}

		case 'auth-sign-out': {
			$this->auth_user_signout();
			$link = admin_url('admin.php?page=accounts');
    		echo '<script type="text/javascript">window.location = "'. esc_url_raw($link) .'";</script>';

			break;
		}

		case 'error':{
			echo '<div id="message" class="error"><p>' . __('Your login/password is incorrect', 'runway') . '</p></div>';
			$this->view('admin-home');
			break;
		}

		case 'success':{
			echo '<div id="updated" class="updated"><p>' . __('Your login is successful', 'runway') . '</p></div>';
			$this->view('admin-home');
			break;
		}

		default:{
			$this->view('admin-home');
		} break;
	}
?>