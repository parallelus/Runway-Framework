<?php 
	switch ($this->navigation) {		
		case 'auth':{
			if(isset($_POST['log'], $_POST['pwd'])){
				$this->set_user_credentials(strip_tags($_POST['log']), strip_tags($_POST['pwd']));
				if($this->auth_user_login())
				    $link = home_url().'/wp-admin/admin.php?page=accounts&navigation=success';
    			else 
    				$link = home_url().'/wp-admin/admin.php?page=accounts&navigation=error';
    			$redirect = '<script type="text/javascript">window.location = "'.$link.'";</script>';
				echo $redirect;
			}
		}

		case 'error':{
			echo '<div id="message" class="error"><p>' . __('Your login/password is incorrect', 'framework') . '</p></div>';
			$this->view('admin-home');
			break;
		}

		case 'success':{
			echo '<div id="updated" class="updated"><p>' . __('Your login is successful', 'framework') . '</p></div>';
			$this->view('admin-home');
			break;
		}

		default:{
			$this->view('admin-home');
		} break;
	}
?>