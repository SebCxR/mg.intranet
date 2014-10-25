{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
<!DOCTYPE html>
<html>
	<head>
		<title>{$PAGETITLE}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- for Login page we are added -->
		<link href="libraries/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="libraries/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="libraries/bootstrap/css/jquery.bxslider.css" rel="stylesheet" />
		<script src="libraries/jquery/jquery.min.js"></script>
		<script src="libraries/jquery/boxslider/jquery.bxslider.js"></script>
		<script src="libraries/jquery/boxslider/jquery.bxslider.min.js"></script>
		<script src="libraries/jquery/boxslider/respond.min.js"></script>
		<script>
			jQuery(document).ready(function(){
				scrollx = jQuery(window).outerWidth();
				window.scrollTo(scrollx,0);
				slider = jQuery('.bxslider').bxSlider({
				auto: true,
				pause: 4000,
				randomStart : true,
				autoHover: true
			});
			jQuery('.bx-prev, .bx-next, .bx-pager-item').live('click',function(){ slider.startAuto(); });
			}); 
		</script>
	</head>
	<body>
		<div class="container-fluid login-container">
			<div class="row-fluid">
				<div class="span3">
					<div class="logo"><img src="test/logo/macon.granulats.png">
					<br />
					<a target="_blank" href="http://{$COMPANY_DETAILSCOMPANY_DETAILS.website}">{$COMPANY_DETAILS.name}</a>
					</div>
				</div>
				<div class="span9">
					<div class="helpLinks">
						<a href="https://www.macongranulats.com">Macon.Granulats.com</a> | 
						<a href="https://wiki.vtiger.com/vtiger6/">Vtiger Wiki</a> | 
						<a href="https://www.vtiger.com/crm/videos/">Vtiger videos </a> | 
						<a href="https://discussions.vtiger.com/">Vtiger Forums</a> 
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="content-wrapper">
						<div class="container-fluid">
							<div class="row-fluid">
								<div class="span6">
									<div class="carousal-container">
										<div><h2>&nbsp;</h2></div>
										<ul class="bxslider">
											<li>
												<div id="slide01" class="slide">
													<img class="pull-left" src="http://www.carrieres-poitou-charentes.com/criblage-poitou-charentes/images/granulants/photo3_t.jpg"/>
													<img class="pull-right" src="http://www.carrieres-poitou-charentes.com/criblage-poitou-charentes/images/granulants/photo2_t.jpg"/>
												</div>
											</li>
											<li>
												<div id="slide02" class="slide">
													<img class="pull-left" src="http://www.carrieres-poitou-charentes.com/criblage-poitou-charentes/images/granulants/photo4_t.jpg"/>
													<img class="pull-right" src="http://www.carrieres-poitou-charentes.com/criblage-poitou-charentes/images/granulants/photo1_t.jpg"/>
												</div>
											</li>
											<li>
												<div id="slide03" class="slide">
													<img class="pull-left" src="http://www.carrieres-poitou-charentes.com/criblage-poitou-charentes/images/granulants/photo6_t.jpg"/>
													<img class="pull-right" src="test/logo/macon.granulats.png"/>
												</div>
											</li>
										</ul>
									</div>
								</div>
								<div class="span6">
									<div class="login-area">
										<div class="login-box" id="loginDiv">
											<div class="">
												<h3 class="login-header">Connexion &agrave; l'intranet</h3>
											</div>
											<form class="form-horizontal login-form" style="margin:0;" action="index.php?module=Users&action=Login" method="POST">
												{if isset($smarty.request.error)}
													<div class="alert alert-error">
														<p>Nom ou mot de passe incorrect.</p>
													</div>
												{/if}
												{if isset($smarty.request.fpError)}
													<div class="alert alert-error">
														<p>Nom ou adresse email incorrect.</p>
													</div>
												{/if}
												{if isset($smarty.request.status)}
													<div class="alert alert-success">
														<p>Un message vous a &eacute;t&eacute; envoy&eacute;, veuillez consulter votre bo&icirc;te mails.</p>
													</div>
												{/if}
												{if isset($smarty.request.statusError)}
													<div class="alert alert-error">
														<p>La configuration SMTP du serveur de mails est insuffisante.</p>
													</div>
												{/if}
												<div class="control-group">
													<label class="control-label" for="username"><b>Utilisateur</b></label>
													<div class="controls">
														<input type="text" id="username" name="username" placeholder="Utilisateur">
													</div>
												</div>

												<div class="control-group">
													<label class="control-label" for="password"><b>Mot de passe</b></label>
													<div class="controls">
														<input type="password" id="password" name="password" placeholder="Mot de passe">
													</div>
												</div>
												<div class="control-group signin-button">
													<div class="controls" id="forgotPassword">
														<button type="submit" class="btn btn-primary sbutton">Valider</button>
														&nbsp;&nbsp;&nbsp;
														<br/><br/><small><a>j'ai encore oubli&eacute; mon mot de passe...</a></small>
													</div>
												</div>
												{* Retain this tracker to help us get usage details *}
												{*<img src='//stats.vtiger.com/stats.php?uid={$APPUNIQUEKEY}&v={$CURRENT_VERSION}&type=U' alt='' title='' border=0 width='1px' height='1px'>*}
											</form>
											<div class="login-subscript">
												<small> Bas&eacute; sur vtiger CRM {$CURRENT_VERSION}</small>
											</div>
										</div>
										
										<div class="login-box hide" id="forgotPasswordDiv">
											<form class="form-horizontal login-form" style="margin:0;" action="forgotPassword.php" method="POST">
												<div class="">
													<h3 class="login-header">Mot de passe perdu</h3>
												</div>
												<div class="control-group">
													<label class="control-label" for="username"><b>Utilisateur</b></label>
													<div class="controls">
														<input type="text" id="username" name="username" placeholder="Utilisateur">
													</div>
												</div>
												<div class="control-group">
													<label class="control-label" for="email"><b>Email</b></label>
													<div class="controls">
														<input type="text" id="email" name="email"  placeholder="Email">
													</div>
												</div>
												<div class="control-group signin-button">
													<div class="controls" id="backButton">
														<input type="submit" class="btn btn-primary sbutton" value="Valider" name="retrievePassword">
														&nbsp;&nbsp;&nbsp;<a>Retour</a>
													</div>
												</div>
											</form>
										</div>
										
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="navbar navbar-fixed-bottom">
			<div class="navbar-inner">
				<div class="container-fluid">
					<div class="row-fluid">
						<div class="span6 pull-left" >
							<div class="footer-content">
								<small>&#169 2004-{date('Y')}&nbsp;
									<a href="https://www.vtiger.com"> vtiger.com</a> | 
									<a href="javascript:mypopup();">License</a> </small>
							</div>
						</div>
						<div class="span6 pull-right" >
							<div class="pull-right footer-icons">
							</div>
						</div>
					</div>   
				</div>    
			</div>   
		</div>
	</body>
	<script>
		jQuery(document).ready(function(){
			jQuery("#forgotPassword a").click(function() {
				jQuery("#loginDiv").hide();
				jQuery("#forgotPasswordDiv").show();
			});
			
			jQuery("#backButton a").click(function() {
				jQuery("#loginDiv").show();
				jQuery("#forgotPasswordDiv").hide();
			});
			
			jQuery("input[name='retrievePassword']").click(function (){
				var username = jQuery('#user_name').val();
				var email = jQuery('#emailId').val();
				
				var email1 = email.replace(/^\s+/,'').replace(/\s+$/,'');
				var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ;
				var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;
				
				if(username == ''){
					alert('Merci de saisir un nom d\'utilisateur valide');
					return false;
				} else if(!emailFilter.test(email1) || email == ''){
					alert('Merci de saisir une adresse email valide');
					return false;
				} else if(email.match(illegalChars)){
					alert( "L'adresse email contient des caract&egrave;res interdits.");
					return false;
				} else {
					return true;
				}
				
			});
		});
	</script>
</html>	
{/strip}
