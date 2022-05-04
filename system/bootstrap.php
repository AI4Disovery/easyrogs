<?php
  // 3rd Party
  require_once(__DIR__ . '/../vendor/autoload.php');

  // settings
  $dotEnv = new Dotenv\Dotenv(__DIR__ . '/..');
  $dotEnv->load();
  require_once(__DIR__ . "/settings.php");

  // payments
  use Stripe\Stripe;
  use Stripe\StripeClient;
  Stripe::setApiKey(STRIPE_API_KEY);
  $stripe = new StripeClient(STRIPE_API_KEY);

  // logging
  error_reporting(E_ALL & ~E_NOTICE);
  ini_set("log_errors", 1);
  ini_set("error_log", LOGS_DIR . "/errors.log");
  assert_options( ASSERT_ACTIVE, $_ENV['APP_ENV'] !== 'prod' );
  assert_options( ASSERT_WARNING, 1 );

  require_once __DIR__ . '/library/classes/logger.class.php';
  $logger = new EasyRogs\Logger();
  set_error_handler('EasyRogs\error_logger');
  set_exception_handler('EasyRogs\exception_logger');

  // lib
  require_once __DIR__ . '/library/classes/httpresponse.php';
  require_once __DIR__ . '/library/classes/AdminDAO.php';
  require_once __DIR__ . '/library/helper.php';

  // templates
  mb_internal_encoding('utf-8');
  mb_regex_encoding('utf-8');

  $smarty = new Smarty();
  $smarty->template_dir = __DIR__ . '/templates';
  $smarty->compile_dir  = __DIR__ . '/../tmp/templates_c';

  function smartyAssignGlobals($smarty) {
    $smarty->assign([
      'ASSETS_URL'              => ASSETS_URL,
      'APP_GOOGLE_ANALYTICS_ID' => APP_GOOGLE_ANALYTICS_ID
      // ... Add globals here ...
      // WARNING: If $smarty->clearAllAssign() is called these need to be redefined,
      // you can call smartyAssignGlobals() any time.
    ]);
  } smartyAssignGlobals($smarty);

  // snippets
  require_once(__DIR__ . "/snippets.php");

  // models
  require_once(__DIR__ . '/models/index.php');

  // controllers
  require_once(__DIR__ . '/controllers/index.php');

  // mailing
  require_once __DIR__ . '/mailers/index.php';

  // globals (LEGACY)
  date_default_timezone_set("America/Los_Angeles");
  $dateformate				    =	'n/j/Y';
  $systemmaintitle			  =	"AI4Discovery";
  $systemmaindescription	=	"AI4Discovery is an electronic discovery system. It allows attorneys and their support staff to 
create and 
Serve Discovery instantly, easily, and inexpensively. No paper, toner, envelopes, or postage. It also allows attorneys and their clients to collaborate on Discovery Responses. ";
  $screensnotincludes			=	"";
  $AdminDAO 	            = new AdminDAO;

  // jobs
  $queuePersistor = new Qutee\Persistor\Pdo();
  $queuePersistor->setOptions([
    'dsn'        => "mysql:dbname=" . DBNAME . ";host=" . DBHOST,
    'username'   => DBUSER,
    'password'   => DBPASS,
    'table_name' => 'jobs_queue'
  ]);
  $jobsQueue = new Qutee\Queue();
  $jobsQueue->setPersistor($queuePersistor);

  // session
  require_once __DIR__ . '/library/classes/login.class.php';

  @session_start();
  @set_time_limit(0);
  ini_set('session.bug_compat_warn', 0);
  ini_set('session.bug_compat_42', 0);
  ini_set('max_input_vars', 25000);

  $_SESSION['upload_url']		  =	UPLOAD_URL;
  $_SESSION['admin_url']		  =	DOMAIN;
  $_SESSION['admin_path']		  =	SYSTEMPATH ."application/"; //!! TODO This was failing, makes me think it's not used much
  $_SESSION['system_path']	  =	SYSTEMPATH;
  $_SESSION['library_path']	  =	SYSTEMPATH ."library/";
  $_SESSION['framework_path']	=	FRAMEWORK_PATH;
  $_SESSION['framework_url']	=	FRAMEWORK_URL;

  require_once(__DIR__ . '/library/classes/sessionuser.php');
  $currentUser = isset($_SESSION['addressbookid'])
                 ? new SessionUser($_SESSION['addressbookid'])
                 : null;
