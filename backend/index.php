<?php
/*
	DESARROLLADO POR JORGE MENDEZ
	programandoweb.net
*/
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
switch (ENVIRONMENT){
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

date_default_timezone_set("America/Bogota");

/*
	CUSTOM CONFIGURACION
*/

if($_SERVER['HTTP_HOST'] == "localhost"){
	$dominio		=		'http://'. $_SERVER['HTTP_HOST'].'/learning';
	/*DATABASE*/
	define('DB_PREFIJO','pgrw_');
	define('DB_USER','root');
	define('DB_PASS','');
	define('DB_DATABASE','inschool');
}
else{
	$dominio		=		'https://' . $_SERVER['HTTP_HOST'].'/';
	/*DATABASE*/
	define('DB_PREFIJO','pgrw_');
	define('DB_USER','pioxii_db');
	define('DB_PASS','F8x!+lBibK?S');
	define('DB_DATABASE','pioxii_db');
}

/*HEADER*/
define('DOMINIO',$dominio);
define('SEO_KEYWORDS',"");
define('SEO_DESCRIPTION',"");
define('SEO_TITLE',"InSchool® Colegio Pio XII");
define('SEO_NAME',"InSchool® Colegio Pio XII");
define('SEO_GENERATOR',"@InSchool");



/*SMTP*/
define('PROTOCOL'				,	"mail");
define('SMTP_HOST'				,	"pioxii.mundosostenible.co");
define('SMTP_PORT'				,	"465");
define('SMTP_TIMEOUT'			,	"7");
define('SMTP_USER'				,	"sofia@pioxii.mundosostenible.co");
define('SMTP_PASS'				,	"#R8MVTgVkQqk");
define('CHARSET'				,	"utf-8");
define('NEWLINE'				,	"\r\n");
define('MAILTYPE'				,	"html");
define('VALIDATION'				,	TRUE);
define('FROM_NAME'				,	"InSchool® Colegio Pio XII");
define('FROM_EMAIL'				,	SMTP_USER);
define('CHAT',FALSE);
define('SOCKET',TRUE);
define('EMAIL_ADMIN'				,	"Notificaciones Sofia Android");

/*FILES*/
define('CSS',DOMINIO."/template/css/");
define('JS',DOMINIO."/template/js/");
define('IMG',DOMINIO."/images/");
define('THIRDPARTY',DOMINIO."/template/thirdParty/");
define('PATH_IMG',dirname(__FILE__)."/images/");

/*OTHERS*/
define('ELEMENTOS_X_PAGINA',10);
define('PATH_BASE_APP',dirname(__FILE__).'/app/');
define('PATH_BASE',dirname(__FILE__).'/');
define('PATH_APP',PATH_BASE_APP.'application/');
define('PATH_CONTROLLERS',PATH_APP.'controllers');
define('PATH_MODEL',PATH_APP.'models');
define('PATH_VIEW',PATH_APP.'views');
define('PATH_LIBRARIES',PATH_APP.'libraries');
define('SESSION_TIME',3600);
define('PROFILE_TIME',360);//30 dias
define('MODULO_X_DEFAULT',"Apanel");
define('TEMPLATE',"template");
define('EXTRACCION',1);

/*URLS*/
define('URL_PROFILE',"https://www.bachelorsportal.com/universities/");
define('AUTENTICACION_REGISTER_REQUIERE_ACTIVACION ',true);

define('PUBLIC_KEY','PGRW::Puv8xE2hPrDGi4HIEvluAenGubfHx5fUuq');
define('PRIVATE_KEY','PGRW::Pr4hCWdBswiBkoCfyHGMsMl0967dTblug7');

define('PUSH_KEY','0b44a940a9fa8983e9874a4ef6739467');
define('PUSH_URL','https://colombia.programandoweb.net:5051');

define('SOCKET_KEY',PUSH_KEY);
define('SOCKET_SERVER','colombia.programandoweb.net');
define('SOCKET_URL','ApiRest/post?modulo=Chat&m=socketJavacript&formato=none');
define('SOCKET_PORT',5010);

/*
 *---------------------------------------------------------------
 * SYSTEM DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" directory.
 * Set the path if it is not in the same directory as this file.
 */
	$system_path = PATH_BASE_APP.'system';

/*
 *---------------------------------------------------------------
 * APPLICATION DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * directory than the default one you can set its name here. The directory
 * can also be renamed or relocated anywhere on your server. If you do,
 * use an absolute (full) server path.
 * For more info please see the user guide:
 *
 * https://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
	$application_folder = PATH_BASE_APP.'application';

/*
 *---------------------------------------------------------------
 * VIEW DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view directory out of the application
 * directory, set the path to it here. The directory can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application directory.
 * If you do move this, use an absolute (full) server path.
 *
 * NO TRAILING SLASH!
 */
	$view_folder = '';


/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 */
	// The directory name, relative to the "controllers" directory.  Leave blank
	// if your controller is not in a sub-directory within the "controllers" one
	// $routing['directory'] = '';

	// The controller class file name.  Example:  mycontroller
	// $routing['controller'] = '';

	// The controller function you wish to be called.
	// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 */
	// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to the system directory
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	// Name of the "system" directory
	define('SYSDIR', basename(BASEPATH));

	// The path to the "application" directory
	if (is_dir($application_folder))
	{
		if (($_temp = realpath($application_folder)) !== FALSE)
		{
			$application_folder = $_temp;
		}
		else
		{
			$application_folder = strtr(
				rtrim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		$application_folder = BASEPATH.strtr(
			trim($application_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

	// The path to the "views" directory
	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.'views';
	}
	elseif (is_dir($view_folder))
	{
		if (($_temp = realpath($view_folder)) !== FALSE)
		{
			$view_folder = $_temp;
		}
		else
		{
			$view_folder = strtr(
				rtrim($view_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.strtr(
			trim($view_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once BASEPATH.'core/CodeIgniter.php';
