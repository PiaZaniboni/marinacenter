<?php
/* @var $global DUP_PRO_Global_Entity */


defined("ABSPATH") or die("");
require_once(DUPLICATOR_PRO_PLUGIN_PATH . 'classes/package/class.pack.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . 'classes/entities/class.storage.entity.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . 'classes/entities/class.package.template.entity.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . 'classes/entities/class.global.entity.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.brand.entity.php');

global $wpdb;

//POST BACK
$action_updated = null;
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'duplicator_pro_package_active' : $action_response = DUP_PRO_U::__('Package settings have been reset.');
			break;
	}
}

DUP_PRO_U::initStorageDirectory();

$manual_template = DUP_PRO_Package_Template_Entity::get_manual_template();

$dup_tests = array();
$dup_tests = DUP_PRO_Server::getRequirments();
$default_name1 = DUP_PRO_Package::get_default_name();
$default_name2 = DUP_PRO_Package::get_default_name(false);
$default_notes = $manual_template->notes; 

$view_state = DUP_PRO_UI_ViewState::getArray();
$ui_css_storage = (isset($view_state['dup-pack-storage-panel']) && $view_state['dup-pack-storage-panel']) ? 'display:block' : 'display:none';
$ui_css_archive = (isset($view_state['dup-pack-archive-panel']) && $view_state['dup-pack-archive-panel']) ? 'display:block' : 'display:none';
$ui_css_installer = (isset($view_state['dup-pack-installer-panel']) && $view_state['dup-pack-installer-panel']) ? 'display:block' : 'display:none';

$storage_list = DUP_PRO_Storage_Entity::get_all();
$storage_list_count = count($storage_list);
$dup_intaller_files = implode(", ", array_keys(DUP_PRO_Server::getInstallerFiles()));
	
$global = DUP_PRO_Global_Entity::get_instance();
$dbbuild_mode = ($global->package_mysqldump ? 'mysqldump' : 'PHP');
?>

<style>
	/* -----------------------------
    PACKAGE OPTS*/
	form#dup-form-opts {margin-top:10px}
    form#dup-form-opts label {line-height:22px}
    form#dup-form-opts input[type=checkbox] {margin-top:3px}
    form#dup-form-opts textarea, input[type="text"], input[type="password"] {width:100%}
	input#package-name {padding:4px;  height: 2em; font-size: 1.2em;  line-height: 100%; width: 100%;   margin: 0 0 3px;}
	select#template_id {padding: 2px; height:2em; font-size: 1.2em;  line-height: 100%; width: 100%;  }
	label.lbl-larger {font-size:1.2em !important; font-weight: bold;}
    textarea#package-notes {height:75px;}
	div.dup-notes-add {float:right; margin:0;}
	div#dup-notes-area {display:none;}
	select#template_id {width:100%; margin-bottom:4px}
	div.dpro-general-area {line-height:27px; margin:0 0 5px 0}
	div#dpro-template-specific-area table td:first-child {width:100px; font-weight: bold}
	div.dup-box {margin:12px 0 0 0}
	
	div#dpro-da-notice {padding:10px; line-height:20px; font-size: 14px}
	div#dpro-da-notice-info {display:none; padding: 5px}
	
	/*TABS*/
	ul.add-menu-item-tabs li, ul.category-tabs li {padding:3px 30px 5px}
</style>

<!-- TODO:
	- The "Switch Now" button should ajax post to DUP_PRO_CTRL_Package::switchDupArchiveNotice() and then refresh page and hide notice
	- The "Hide this Notice" should ajax post to DUP_PRO_CTRL_Package::switchDupArchiveNotice() and then jQuery hide notice.
	- See DUP_PRO_CTRL_Package::addQuickFilters for example
	- Update wording
-->
<?php if (isset($global->notices) && ($global->notices->dupArchiveSwitch) && ($global->archive_build_mode !== DUP_PRO_Archive_Build_Mode::DupArchive)) :?>
	<div class="notice notice-info is-dismissible">
		<div id="dpro-da-notice">
			<i class="fa fa-info-circle fa-lg"></i>
			<?php DUP_PRO_U::_e('Switch to the new \'DupArchive\' archive format for improved performance!') ?>
			<a href="javascript:void(0)" onClick="jQuery('#dpro-da-notice-info').toggle();">[<?php DUP_PRO_U::_e('more details...'); ?>]</a><br/>
			
			<div id="dpro-da-notice-info">
				<?php DUP_PRO_U::_e("DupArchive is faster and more stable than zip on budget hosts and can package large sites on all hosts.  "
					. "A DupArchive file ends in '.daf' rather than '.zip' and works the same as a zip when installing. It requires installer.php for extraction (host File Managers support zip but not daf).  "
					. "Should you decide to switch back to the old zip format after switching to DupArchive, you can easily do so by going to the Settings &gt; Packages options.  Please note that this option "
					. "is currently only affects manual builds and not scheduled builds.") ?>
			</div>
			<div style="padding: 5px">
				<a href='javascript:void(0)' class='button button-small' onClick='DupPro.Pack.HandleDupArchiveNotice(true);'><?php DUP_PRO_U::_e("Switch Now!") ?></a>
				<a href='javascript:void(0)' class='button button-small' onClick='DupPro.Pack.HandleDupArchiveNotice(false);'><?php DUP_PRO_U::_e("Hide This Notice") ?></a>
			</div>
		</div>
	</div>
<?php endif; ?>


<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar">
	<tr>
		<td>
			<div id="dup-wiz">
				<div id="dup-wiz-steps">
					<div class="active-step"><a>1-<?php DUP_PRO_U::_e('Setup'); ?></a></div>
					<div><a>2-<?php DUP_PRO_U::_e('Scan'); ?> </a></div>
					<div><a>3-<?php DUP_PRO_U::_e('Build'); ?> </a></div>
				</div>
				<div id="dup-wiz-title" style="white-space: nowrap">
					<?php DUP_PRO_U::_e('Step 1: Package Setup'); ?>
				</div> 
			</div>	
		</td>
		<td>
			<div class="btnnav">
				<a href="<?php echo $packages_tab_url; ?>" class="add-new-h2"><i class="fa fa-archive"></i> <?php DUP_PRO_U::_e('Packages'); ?></a>
				<span> <?php _e("Create New"); ?></span>
			</div>
		</td>
	</tr>
</table>
<hr class="dpro-edit-toolbar-divider"/>

<?php if (! empty($action_response)) : ?>
    <div id="message" class="notice notice-success"><p><?php echo $action_response; ?></p></div>
<?php endif; ?>	

<?php require_once('s1.setup1.reqs.php'); ?>

<form id="dup-form-opts" method="post" action="?page=duplicator-pro&tab=packages&inner_page=new2" data-parsley-validate data-parsley-ui-enabled="true" >
	<input type="hidden" id="dup-form-opts-action" name="action" value="">
	<div class="dpro-general-area">
		<label class="lbl-larger">&nbsp;Apply Template:</label>
		<i class="fa fa-question-circle" 
			data-tooltip-title="<?php DUP_PRO_U::_e("Apply Template:"); ?>" 
			data-tooltip="<?php DUP_PRO_U::_e('An optional template configuration that can be applied to this package setup. An [Unassigned] template will retain the settings from the last scan/build.'); ?>"></i>
		<div style="float:right">
			<a href="admin.php?page=duplicator-pro-templates" title="<?php DUP_PRO_U::_e("Templates") ?>" target="_blank">[<?php DUP_PRO_U::_e("Templates"); ?>]</a>
		</div>
		<br/>
		<select data-parsley-ui-enabled="false" onChange="DupPro.Pack.EnableTemplate();" name="template_id" id="template_id" >
			<option value="<?php echo $manual_template->id; ?>"><?php echo '[' . DUP_PRO_U::__('Unassigned') . ']' ?></option>
			<?php
				$templates = DUP_PRO_Package_Template_Entity::get_all();
				if (count($templates) == 0) {
					$no_templates = __('No Templates');
					echo "<option value='-1'>$no_templates</option>";
				} else {
					foreach ($templates as $template) {
						echo "<option value='{$template->id}'>{$template->name}</option>";
					}
				}
				?>
		</select>

		<label for="package-name" class="lbl-larger">&nbsp;<?php DUP_PRO_U::_e('Name') ?>:</label>
		<a href="javascript:void(0)" onClick="DupPro.Pack.ResetName()" title="<?php DUP_PRO_U::_e('Toggle a default name') ?>"><i class="fa fa-undo"></i></a>
		<div class="dup-notes-add">
			<a href="javascript:void(0)" onClick="jQuery('#dup-notes-area').toggle()">[<?php DUP_PRO_U::_e('Add Notes') ?>]</a>
		</div>
		<input id="package-name"  name="package-name" type="text" maxlength="40"  required="true" data-regexp="^[0-9A-Za-z|_]+$" />
		<div id="dup-notes-area">
			<label class="lbl-larger">&nbsp;<?php DUP_PRO_U::_e('Notes') ?>:</label><br/>
			<textarea id="package-notes" name="package-notes" maxlength="300" /></textarea>
		</div>
	</div>

	<?php 
		require_once('s1.setup2.store.php');
		require_once('s1.setup3.archive.php');
		require_once('s1.setup4.install.php');
	?>
	
	<div class="dup-button-footer">
		<input type="button" value="<?php DUP_PRO_U::_e("Reset") ?>" class="button button-large" <?php echo ($dup_tests['Success']) ? '' : 'disabled="disabled"'; ?> onClick="DupPro.Pack.ResetSettings()" />
		<input id="button-next" type="submit" onClick="DupPro.Pack.BeforeSubmit()" value="<?php DUP_PRO_U::_e("Next") ?> &#9654;" class="button button-primary button-large" <?php echo ($dup_tests['Success']) ? '' : 'disabled="disabled"'; ?> />
	</div>
</form>

<!-- CACHE PROTECTION: If the back-button is used from the scanner page then we need to
refresh page in-case any filters where set while on the scanner page -->
<form id="cache_detection">
	<input type="hidden" id="cache_state" name="cache_state" value="" />
</form>

<script>
jQuery(function($) 
{
    var packageTemplates = [];

	<?php
		$counter = 0;
		$templates = DUP_PRO_Package_Template_Entity::get_all(true);
		foreach ($templates as $template)
		{
			$template->installer_opts_secure_pass = base64_decode($template->installer_opts_secure_pass);
			$json = json_encode($template);
			echo "    packageTemplates[$counter] = $json;\n\r\n\r";
			$counter++;
		}
	?>

    DupPro.Pack.BeforeSubmit = function(){
        $('#mu-exclude option').each(function(){
            $(this).attr('selected',true);
        });
    }

	// Template-specific Functions
	DupPro.Pack.GetTemplateById = function (templateId) 
	{
		for (i = 0; i < packageTemplates.length; i++) {
			var currentTemplate = packageTemplates[i];
			if (currentTemplate.id == templateId) {
				return currentTemplate;
			}
		}
		return null;
	};

	DupPro.Pack.PopulateCurrentTemplate = function () 
	{
		var selectedId = $('#template_id').val();
		var selectedTemplate = DupPro.Pack.GetTemplateById(selectedId);
		if (selectedTemplate != null) 
		{
			var name = selectedTemplate.name;
			
			if(selectedTemplate.is_manual) {
				name = "<?php echo DUP_PRO_Package::get_default_name(); ?>";
			}
			
			$("#package-name").val(name);
			$("#package-notes").val(selectedTemplate.notes);

			$("#export-onlydb").prop("checked", selectedTemplate.archive_export_onlydb);
			$("#filter-on").prop("checked", selectedTemplate.archive_filter_on);
			
			$("#filter-dirs").val(selectedTemplate.archive_filter_dirs.split(";").join(";\n"));
			$("#filter-exts").val(selectedTemplate.archive_filter_exts);			
			$("#filter-files").val(selectedTemplate.archive_filter_files.split(";").join(";\n"));
			$("#dbfilter-on").prop("checked", selectedTemplate.database_filter_on);
			DupPro.Pack.ExportOnlyDB();

			if(selectedTemplate.filter_sites !== undefined && selectedTemplate.filter_sites.length > 0){
                for(var i=0; i < selectedTemplate.filter_sites.length;i++){
                    var site_id = selectedTemplate.filter_sites[i];
                    var exclude_option = $('#mu-include').find("option[value="+site_id+"]").first();
                    console.log(exclude_option.html());
                    $("#mu-exclude").append(exclude_option.clone());
                    exclude_option.remove();
                }
            }

			//-- cPanel
			$("#cpnl-enable").prop("checked", selectedTemplate.installer_opts_cpnl_enable);
			$("#cpnl-host").val(selectedTemplate.installer_opts_cpnl_host);
			$("#cpnl-user").val(selectedTemplate.installer_opts_cpnl_user);
			
			$("#secure-on").prop("checked", selectedTemplate.installer_opts_secure_on);
			$("#skipscan").prop("checked", selectedTemplate.installer_opts_skip_scan);
			$("#secure-pass, #secure-pass2").val(selectedTemplate.installer_opts_secure_pass);
									
			$("#cpnl-dbaction").val(selectedTemplate.installer_opts_cpnl_db_action);
			$("#cpnl-dbhost").val(selectedTemplate.installer_opts_cpnl_db_host);
			$("#cpnl-dbname").val(selectedTemplate.installer_opts_cpnl_db_name);
			$("#cpnl-dbuser").val(selectedTemplate.installer_opts_cpnl_db_user);

			var	filterTableKey;
			var databaseFilterTables = selectedTemplate.database_filter_tables.split(",");
			$("#dup-dbtables input").prop("checked", false).css('text-decoration', 'none');

			for (filterTableKey in databaseFilterTables) {
				var filterTable = databaseFilterTables[filterTableKey];
				var selector = "#dbtables-" + filterTable;
				$(selector).prop("checked", true);
				$(selector).parent().css('text-decoration', 'line-through');
			}

			$("#dbhost").val(selectedTemplate.installer_opts_db_host);
			$("#dbname").val(selectedTemplate.installer_opts_db_name);
			$("#dbuser").val(selectedTemplate.installer_opts_db_user);

			$("#cache-wp").prop("checked", selectedTemplate.installer_opts_cache_wp);
			$("#cache-path").prop("checked", selectedTemplate.installer_opts_cache_path);
		} else {
			console.log("Template ID doesn't exist?? " + selectedId);
		}
		
		//Default to Installer cPanel tab if used
		$('#cpnl-enable').is(":checked") ? $('#dpro-cpnl-tab-lbl').trigger("click") : null;
	};


	DupPro.Pack.ResetSettings = function () 
	{
		if (! confirm('<?php DUP_PRO_U::_e("This will clear all of the current package settings.  Would you like to continue?"); ?>'))
			return;
		$('#dup-form-opts')[0].reset();
	};

});

//INIT
jQuery(document).ready(function ($) 
{
	var DPRO_NAME_LAST;
	var DPRO_NAME_DEFAULT1	= '<?php echo $default_name1 ?>';
	var DPRO_NAME_DEFAULT2	= '<?php echo $default_name2 ?>';

	DupPro.Pack.checkPageCache = function()
	{
		var $state = $('#cache_state');
		if( $state.val() == "" ) {
			$state.val("fresh-load");
		} else {
			$state.val("cached");
			<?php
				$redirect = admin_url('admin.php?page=duplicator-pro&tab=packages&inner_page=new1');
				echo "window.location.href = '{$redirect}'";
			?>
		}
	}

	DupPro.Pack.EnableTemplate = function () 
	{
		$("#dup-form-opts-action").val('template-create');
		$('#dpro-template-specific-area').show(0);  
		DupPro.Pack.PopulateCurrentTemplate();
		DupPro.Pack.ToggleInstallerPassword();
		DupPro.Pack.ToggleFileFilters();
		DupPro.Pack.ToggleDBFilters();
	}

	DupPro.Pack.ResetName = function ()
	{
		var current = $('#package-name').val();
		switch (current) {
			case DPRO_NAME_LAST     : $('#package-name').val(DPRO_NAME_DEFAULT2); break;
			case DPRO_NAME_DEFAULT1 : $('#package-name').val(DPRO_NAME_LAST); break;
			case DPRO_NAME_DEFAULT2 : $('#package-name').val(DPRO_NAME_DEFAULT1); break;
			default:	$('#package-name').val(DPRO_NAME_LAST);
		}
	};
		
	DupPro.Pack.HandleDupArchiveNotice = function(switchToDupArchive)
	{
		var ajaxData = {
			action: 'DUP_PRO_CTRL_Package_switchDupArchiveNotice',
			nonce: '<?php echo wp_create_nonce('DUP_PRO_CTRL_Package_switchDupArchiveNotice'); ?>',
			enable_duparchive : switchToDupArchive
		};

		$.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: ajaxData,
			beforeSend: function () {
			},
			success: function (data) {
				location.reload();
			},
			error: function (xHr, textStatus) {

				console.log('switchDupArchiveNotice:AJAX error. textStatus=');
				console.log(textStatus);
				location.reload();
			}
		});
	};

	DupPro.Pack.checkPageCache();
	DupPro.Pack.EnableTemplate();
	DupPro.Pack.ExportOnlyDB();
	DPRO_NAME_LAST  = $('#package-name').val();

});
</script>
