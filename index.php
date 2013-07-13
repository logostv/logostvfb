<?php
/**
 * @package     Extly.kApp
 * @subpackage  ServerAuth - FacebookApp to authorize and grant permissions for the AutoTweet standard App or your own App
 *
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://www.extly.com http://support.extly.com http://www.prieco.com
 */

define('_JEXEC', true);

// No direct access
defined('_JEXEC') or die('Restricted index access');

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'facebook-php-sdk/facebook.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'facebookapp.php';

/*

----------------------------------------------------------------------
--- EXPERTS MODE ONLY -------------
----------------------------------------------------------------------

define('MY_APP_ID', 'YOUR APP_ID HERE');
define('MY_APP_SECRET', 'YOUR APP_SECRET HERE');
define('MY_CANVAS_PAGE', 'http://apps.facebook.com/your-app-here');

These constants are a second way to define the parameters for every
channel using this app. In general, there's no need of constants
definition. They are provided only if you want to fix the values.
For example, in our Facebook App, used by several different users.

The usual AutoTweetNG authorization workflow is from AutoTweetNG's
backend. When you create a channel, you select "Own App"=Yes, fill
"App ID", "App Secret", "Canvas URL", and press *"Authorize
application and grant Permissions"* button.

http://www.extly.com/how-to-autotweet-from-your-own-facebook-app.html#/facebook-add-account-with-your-own-app

*/

// To show debugging information
define('DEBUG_ENABLED', false);

$facebookapp = new FacebookApp;
$ok = $facebookapp->init();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>AutoTweetNG Connector</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/font-awesome.css" rel="stylesheet">

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="ico/favicon.ico">

        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">

    </head>

    <body>

        <div class="jumbotron masthead">
            <div class="container-fluid">
                <p><br/></p>
                <h1><img src="ico/isologo-autotweet-20120831-75.png"/> AutoTweet NG Connector</h1>
                <p><br/></p>
            </div>
        </div>

        <div class="container-fluid">

            <div class="row-fluid">
                <div class="span12">

					<?php

					$ref = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL);
					if (!preg_match('/^http(s?):\/\/apps.facebook.com/', $ref))
					{
						echo '<div class="alert alert-block alert-error">
							<button data-dismiss="alert" class="close" type="button">×</button>';
						echo '<p><i class="icon-fire"></i> Please, don\'t access the app directly from your browser.</p>
							<ul><li>You must call <b>AutoTweet NG Connector</b>
							from <b>AutoTweet</b> component backend in Joomla, clicking on
							the <b>Authorization Button</b>.</li>
							</ul>';
						echo '</div>';
					}

					if (!$ok)
					{
						echo '<div class="alert alert-block alert-error">
							<button data-dismiss="alert" class="close" type="button">×</button>';
						echo '<p><i class="icon-fire"></i> Wrong parameters:</p>
							<ul><li>You must call <b>AutoTweet NG Connector</b>
							from <b>AutoTweet</b> component backend in Joomla, clicking on
							the <b>Authorization Button</b>, or</li>
							<li>You must define MY_APP_ID, and MY_APP_SECRET in the Facebook App index.php.</li>
							</ul>';
						echo '</div>';
					}
					else
					{

						if (!$facebookapp->login())
						{
							?>
							<p><i class="icon-info-sign"></i> This application connects your account to your Joomla! AutoTweet NG installation.</p>
							<p>To post status messages from Joomla! to your
								<b>Facebook Status</b> (personal profile, Facebook page,
								group or event) you must <b>add this application and grant
									extended permissions</b> for AutoTweet.</p>
							<p><br/></p>
							<?php
							// Redirect to login and authorization
							echo '<a class="btn btn-info" onclick="top.location.href =\'' . $facebookapp->login_url . '\';" href="#">Authorize!</a>';
						}
						else
						{
							// Login Ok
							try
							{

								$facebookapp->facebook->setExtendedAccessToken();
								$extended_token = $_SESSION['fb_' . $facebookapp->APP_ID . '_access_token'];

								if (!$extended_token)
								{
									echo '<div class="alert alert-block alert-error">
										<button data-dismiss="alert" class="close" type="button">×</button>';
									echo '<p><i class="icon-fire"></i>
										Error getExtendedAccessToken</p>';
									echo '</div>';
								}

								$signed_request = $facebookapp->facebook->getSignedRequest();
								$exp = $signed_request['expires'];

								$user = $facebookapp->facebook->api('/me');
								$pages = $facebookapp->facebook->api('/me/accounts');
								$groups = $facebookapp->facebook->api('/me/groups');
								$events = $facebookapp->facebook->api('/me/events');

								echo '<div class="alert alert-info">';
								echo '<h2>Congratulations!</h2>';
								echo '<p><i class="icon-check"></i> You have authorized the application and granted the permissions.</p>';
								echo '<p>Now, you can create the <b>Facebook channel</b> in
									AutoTweet component backend, and select your Profile, App,
									Page, Group or Event. <i class="icon-hand-right"></i></p>';
								echo '<p><em>Please, copy and paste
									<span class="label label-info">User-ID</span> and
									<span class="label label-info">Access Token</span>
									in the new AutoTweet\'s <b>Facebook Account</b>.</em></p>';
								echo '</div>';
								?>
								<div class="facebook-tokens">
									<ul class="nav nav-tabs" id="myTab">
										<li class="active"><a data-toggle="tab" href="#home"><i class="icon-user"></i> Profile</a></li>
										<li><a data-toggle="tab" href="#profile"><i class="icon-home"></i> Pages and apps</a></li>
										<li><a data-toggle="tab" href="#groups"><i class="icon-group"></i> Groups</a></li>
										<li><a data-toggle="tab" href="#events"><i class="icon-calendar"></i> Events</a></li>
									</ul>
									<div class="tab-content" id="myTabContent">
										<div id="home" class="tab-pane fade in active">
											<dl class="dl-horizontal">
												<dt>User Name</dt>
												<dd><?php echo $user['name']; ?></dd>
												<dt>Access Token</dt>
												<dd><small><?php echo $extended_token; ?></small></dd>
											</dl>
											<p><em>Please, copy and paste
													<span class="label label-info">User-ID</span>
													and <span class="label label-info">Access Token</span>
													in the new AutoTweet's <b>Facebook Account</b>.</em></p>
											<p><br/><br/></p>
										</div>
										<div id="profile" class="tab-pane fade">
											<table class="table">
												<thead>
													<tr>
														<th>Name</th>
													</tr>
												</thead>
												<tbody>
													<?php
													foreach ($pages['data'] as $page)
													{
														echo '<tr><td>' . $page['name'] . '</td></tr>';
													}
													?>
												</tbody>
											</table>
										</div>
										<div id="groups" class="tab-pane fade">
											<table class="table">
												<thead>
													<tr>
														<th>Name</th>
													</tr>
												</thead>
												<tbody>
			<?php
			foreach ($groups['data'] as $group)
			{
				echo '<tr><td>' . $group['name'] . '</td></tr>';
			}
			?>
												</tbody>
											</table>
										</div>
										<div id="events" class="tab-pane fade">
											<table class="table">
												<thead>
													<tr>
														<th>Name</th>
													</tr>
												</thead>
												<tbody>
			<?php
			foreach ($events['data'] as $event)
			{
				echo '<tr><td>' . $event['name'] . '</td></tr>';
			}
			?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
								<?php
							}
							catch (facebookphpsdk\FacebookApiException $e)
							{
								echo '<div class="alert alert-block alert-error">
									<button data-dismiss="alert" class="close" type="button">×</button>
									<i class="icon-fire"></i> Error: ';
								echo $e;
								echo '</div>';
							}
						}
					}
					?>

                </div>
            </div>

            <p><br/><br/><br/><br/><br/><br/><br/><br/><br/></p>
            <hr class="soften">

        </div>

        <!-- Footer
        ================================================== -->
        <footer class="footer">
            <div class="container-fluid">
                <p class="pull-right"><a href="#">Back to top</a></p>
                <p>Additional information about AutoTweet: <a href="http://www.extly.com/" target="_blank">Extly.com - Joomla Extensions</a></p>
                <p>For more information:
					<a href="http://www.extly.com/autotweet-ng-user-documentation.html" target="_blank">AutoTweet Documentation</a></p>
                <p>Support: <a href="http://support.extly.com" target="_blank">http://support.extly.com</a></p>
                <ul class="footer-links">
                    <li><a href="http://www.extly.com/blog.html" target="_blank">Read the Extly.com blog</a></li>
                    <li><a href="http://support.extly.com" target="_blank">Submit issues</a></li>
                    <li><a href="http://support.extly.com/projects/autotweet_ng_pro/issues" target="_blank">Roadmap and changelog</a></li>
                </ul>
            </div>
        </footer>

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.min.js"></script>
    </body>
</html>
