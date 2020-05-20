<?php
require_once __DIR__ . '/../system/bootstrap.php';
require_once($_SESSION['framework_path']."adminsecurity.php");

$addressbookid			=	$_SESSION['addressbookid'];
$_SESSION['section']	=	@$_GET['sectionid'];
$section				=	$_SESSION['section'];
$tabres					=	$AdminDAO->getrows("system_screen s,system_groupscreen gs,system_groups g, system_addressbook a","s.*"," 1 AND s.pkscreenid = gs.fkscreenid AND gs.fkgroupid = g.pkgroupid AND a.fkgroupid = g.pkgroupid AND pkaddressbookid = '$addressbookid' ");
?>
<style>
.datepicker{ z-index: 1151 !important; }
.navbar-nav > li > a:hover,
.navbar-nav > li > a:focus,
.navbar-nav .open > a,
.navbar-nav .open > a:hover,
.navbar-nav .open > a:focus {
  background: #FFFFFF !important;
}
navbar.navbar-static-top a, .nav.navbar-nav li a {
     color: #34495e !important;
}
.hpanel {
	margin-top:60px;
}
.fixed-navbar #wrapper {
    top: 0px;
}
</style>

<div id="header" class="" style="background-color:#f7f9fa">
    <div class="color-line" />
    <div id="logo" class="light-version"> 
        <h4 style="color:#34495e; font-size:17px; font-weight:600; ">
            <a class="mylogo f32" href="index.php">
                <?= $systemmaintitle ?> 
                <?php if ($_ENV['APP_ENV'] !== 'prod'): ?>
                  <div style="font-family: Arial; color: white; font-size: 12px; display: inline-block; background-color: red; padding: 3px; text-transform: uppercase"><?= exec('git rev-parse --abbrev-ref HEAD') ?></div>
                <?php endif; ?>
            </a>
        </h4> 
    </div>
        
    <!-- Right sidebar -->
    <nav role="navigation">
        <div class="navbar-right">
            <ul class="nav navbar-nav no-borders">
                <li class="dropdown"  id="nav-welcome">
                    <h4 style="color:#34495e !important; font-size:12px !important; font-weight:500 !important; padding-top:10px; margin-right:20px;">  
                        Welcome <?= $_SESSION['name'] ?>
                    </h4>
                </li>
                <li id="nav-ctxhelp">
                    <h4 style="color:#34495e !important; font-size:12px !important; font-weight:500 !important; padding-top:10px; margin-right:20px;">  
                        <a onclick="javascript:showCtxHelp();" href="javascript:;"><b>Help</b></a>
                    </h4>
                </li>
                <li class="dropdown"  id="nav-faq">
                    <h4 style="color:#34495e !important; font-size:12px !important; font-weight:500 !important; padding-top:10px; margin-right:20px;">  
                        <a onclick="javascript:showFAQ();" href="javascript:;"><b>FAQ</b></a>
                    </h4>
                </li>
                <li class="dropdown"  id="nav-cases">
                    <h4 style="color:#34495e !important; font-size:12px !important; font-weight:500 !important; padding-top:10px; margin-right:20px;">  
                        <a href="javascript:void(0);" onclick="selecttab('44_tab','get-cases.php','44');"><b>Cases</b></a> 
                    </h4>
                </li>
                <li class="dropdown"  id="nav-myprofile">
                    <h4 style="color:#34495e !important; font-size:12px !important; font-weight:500 !important; padding-top:10px; margin-right:20px;">  
                        <a href="javascript:;" onclick="javascript: selecttab('8_tab','<?= ROOTURL ?>system/application/get-profile.php','8');">
                        <b>My Profile</b>
                        </a>
                    </h4>
                </li>
                <li class="dropdown"  id="nav-logout" style="padding-right: 16px;">
                    <h4 style="color:#34495e !important; font-size:12px !important; font-weight:500 !important; padding-top:10px; margin-right:20px;">  
                        <a href="<?php echo FRAMEWORK_URL; ?>signout.php">
                        <b>Log Out</b>
                        </a>
                    </h4>
                </li>
            </ul>
        </div>
    </nav>
    </div>
</div>