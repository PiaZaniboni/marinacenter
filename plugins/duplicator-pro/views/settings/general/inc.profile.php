<?php
defined("ABSPATH") or die("");

$global  = DUP_PRO_Global_Entity::get_instance();

$nonce_action	 = 'duppro-settings-general-edit';
$action_updated  = null;
$action_response = DUP_PRO_U::__("Profile Settings Updated");
$dup_version = DUPLICATOR_PRO_VERSION;

//SAVE RESULTS
if (isset($_REQUEST['action'])) {

    check_admin_referer($nonce_action);
	if ($_REQUEST['action'] == 'save') {
		$global->profile_idea	= isset($_POST['_profile_idea']) ? 1 : 0;
		$global->profile_beta	= isset($_POST['_profile_beta']) ? 1 : 0;
    }

	$action_updated = $global->save();
	$global->adjust_settings_for_system();	
}

?>

<style>
	td.profiles {padding-left: 20px}
	td.profiles p.description {margin:5px 0 20px 25px; max-width: 800px !important}
	sup.new-badge {background-color:maroon; border: maroon 1px solid; border-radius:8px; color:#fff; padding:1px 3px 2px 3px; margin:0; font-size:11px; line-height:11px; display:inline-block; font-style: normal}
	label.profile-type {font-size:16px !important; font-weight: bold}
</style>

<form id="dup-settings-form" action="<?php echo self_admin_url('admin.php?page=' . DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG); ?>" method="post" data-parsley-validate>
<?php wp_nonce_field($nonce_action); ?>
<input type="hidden" id="dup-settings-action" name="action" value="save">
<input type="hidden" name="page" value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
<input type="hidden" name="tab" value="general">
<input type="hidden" name="subtab" value="profile">

<?php if ($action_updated) : ?>
	<div class="notice notice-success is-dismissible dpro-wpnotice-box"><p><?php echo $action_response; ?></p></div>
<?php endif; ?>

<!-- ===============================
OPTIONS -->

<table class="form-table">
	<tr>
		<td class="profiles">
			<!-- ================
			LIVE -->
			<label class="profile-type"><?php DUP_PRO_U::_e("New Features "); echo  "({$dup_version})" ; ?></label>
			<p class="description">
				<?php

				$brandingLink = self_admin_url()."admin.php?page=".DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG.'&tab=package&sub=brand';

				DUP_PRO_U::_e("<sup class='new-badge'>new</sup> <b>Installer Branding (Freelancer+)</b> - Create your own WordPress distribution with a custom branded installer. <br/>");


				DUP_PRO_U::_e("<small>Go to <a href='{$brandingLink}'>Settings > Packages > Branding</a> to configure.</small>");
				echo '<br/><br/>';
				DUP_PRO_U::_e("All changes for v{$dup_version} can be found in <a href='https://snapcreek.com/duplicator/docs/changelog/' target='_blank'>changelog</a>.");
				?>
			</p><br/><br/>
			
			<?php
				DUP_PRO_U::_e("The Beta and Design Concepts sections let you preview and test upcoming features the Duplicator team is working on. Enable those feature sets by checking the appropriate checkbox.<br/>  "
					. "We recommend against using Beta and Design Concept features on production sites. <br/> "
					. "Please leave your <a href='https://snapcreek.com/support/?idea=1' target='_blank'><b>feedback</b></a>!");
			?>
			<br/><br/>


			


			<!-- ================
			BETA -->
			<input type="checkbox" name="_profile_beta" id="_profile_beta" <?php echo DUP_PRO_UI::echoChecked($global->profile_beta); ?> />
			<label for="_profile_beta" class="profile-type"><?php DUP_PRO_U::_e("Beta Features"); ?></label>
			<i class="fa fa-question-circle"
				data-tooltip-title="<?php DUP_PRO_U::_e("Debug views"); ?>"
				data-tooltip="<?php DUP_PRO_U::_e('Checking this checkbox will enable various beta features.  These features should NOT be used in production environments.  Please '
					. 'let us know your thoughts and report any issue encountered.  This will help to more quickly get the feature out of Beta.'); ?>"></i>

			<p class="description">
				<?php
					DUP_PRO_U::_e("- None ");
				?>
			</p>

			<!-- ================
			IDEA -->
			<input type="checkbox" name="_profile_idea" id="_profile_idea" <?php echo DUP_PRO_UI::echoChecked($global->profile_idea); ?> />
			<label for="_profile_idea" class="profile-type"><?php DUP_PRO_U::_e("Design Concepts"); ?></label>
			<i class="fa fa-question-circle"
				data-tooltip-title="<?php DUP_PRO_U::_e("Concept Views"); ?>"
				data-tooltip="<?php DUP_PRO_U::_e('Checking this checkbox will enable various idea design concepts.  These features MAY NOT function fully and should '
					. 'NEVER be used in production enviroments.  In some cases the features will simply just be UI mockups.  Please let us know what you think of the concepts as '
					. 'they may eventually become features. '); ?>"></i>
			
			<p class="description">
				<?php
					$importURL = self_admin_url()."admin.php?page=".DUP_PRO_Constants::$TOOLS_SUBMENU_SLUG.'&tab=import';

					//DUP_PRO_U::_e("None");
					DUP_PRO_U::_e("<b>Drag &amp; Drop Install:</b> Overwrite a site by dragging a package into the plugin. No need to FTP a package!<br/>");
					DUP_PRO_U::_e("<small>Go to <a href='{$importURL}'>Tools > Import</a> to overwrite the current site. </small>");
				?>
			</p><br/>




		</td>
	</tr>
</table>

<p class="submit" style="margin:5px 0px 0xp 5px;">
	<input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::_e('Save Feature Profiles') ?>" style="display: inline-block;" />
</p>
</form>
