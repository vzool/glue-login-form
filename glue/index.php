<?
/*
* This Login needs following things to make it secure:
* - HTTPS (Run login page on Encrypted Connection)
* - Protection against SQL-Injection
*/

session_start();

require_once("glue.php");

$urls = array(
	'/glue/' => 'index',
	'/glue/logout' => 'logout',
	'/glue/captcha' => 'captcha',
);

class index {

	function GET() {

		if(isset($_SESSION['is_logged'])){
			
			echo "<h1>Hello Again, {$_SESSION['name']}!</h1>";

			echo "<a href='/glue/logout'>Log Me Out!</a>";

		}else{

			$csrf = sha1(uniqid(time()));

			$_SESSION['csrf'] = $csrf;

			$form = " 
			<h1>Quick Login not Secure (needs HTTPS)</h1>
			<hr/>
			<form METHOD='POST'>
				<input type='hidden' name='csrf' value='$csrf'/>
				<label>User Name:</label><input type='text' name='username'/> <br/>
				<label>Password:</label><input type='password' name='pwd'/> <br/>
				<img src='/glue/captcha'/><br/>
				<label>Captcha:</label><input type='text' name='captcha'/> <br/>
				<input type='submit' name='Login' value='Login me in !'/>
			</form>
			";

			echo $form;
		}
	}

	function POST(){

		if($_POST['username'] === 'test' && $_POST['pwd'] === 'test' && $_SESSION['captcha'] == $_POST['captcha'] && $_SESSION['csrf'] === $_POST['csrf']){

			echo "<h1>Salam {$_POST['username']}, You have logged Successfully...</h1>";

			echo "<a href='/glue'><h4>Goto Your page now</h4></a>";

			$_SESSION['is_logged'] = True;
			$_SESSION['name'] = $_POST['username'];

		}else{

			echo "<h1>Failed to login, <a href='/glue'>try again</a></h1>";
		}
	}
}

class logout{

	function GET(){

		session_destroy();

		header('location: /glue');
	}
}

/* it does generate captcha and save it to session on the fly */
class captcha{

	function generatePassword($length = 5) {
        	
		$code = rand(1000, 9999);
        
        $possibleChars = "ABCDEFGHJKLMNPQRSTUVWXYZ" . $code;
        $password = '';

        for($i = 0; $i < $length; $i++) {
            $rand = rand(0, strlen($possibleChars) - 1);
            $password .= substr($possibleChars, $rand, 1);
        }

        return str_shuffle($password);
    }

	function GET(){

		$code = $this->generatePassword();

		$_SESSION["captcha"] = $code;
		$im = imagecreatetruecolor(260, 24);
		$bg = imagecolorallocate($im, 0, 0, 0); //background color blue
		$fg = imagecolorallocate($im, 255, 255, 255);//text color white
		imagefill($im, 0, 0, $bg);
		imagestring($im, 5, 100, 5,  $code, $fg);
		header("Cache-Control: no-cache, must-revalidate");
		header('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
	}
}

glue::stick($urls);
