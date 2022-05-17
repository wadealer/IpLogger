<?php

access_ensure_global_level(config_get('manage_plugin_threshold'));

layout_page_header(plugin_lang_get('config_title'));

layout_page_begin('manage_overview_page.php');

print_manage_menu('manage_plugin_page.php');

$g_project = helper_get_current_project();
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="form-container">
		<form action="<?php echo plugin_page('config') ?>" method="post">
			<fieldset>
				<div class="widget-box widget-color-blue2">
					<div class="widget-header widget-header-small">
						<h4 class="widget-title lighter">
							<i class="ace-icon fa fa-exchange"></i>
							<?php echo plugin_lang_get('config_title') ?>
						</h4>
					</div>

					<?php echo form_security_field('plugin_IpLogger_config') ?>
					<div class="widget-body">
						<div class="widget-main no-padding">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-striped">

									<!-- Export Access Level  -->
									<tr>
										<td class="category" width="15%">
											<?php echo plugin_lang_get('config_logging_user_threshold_title') ?>
										</td>
										<td>
											<select id="logging_user_threshold" name="logging_user_threshold" class="input-sm"><?php
												print_enum_string_option_list(
													'access_levels',
													plugin_config_get('logging_user_threshold', null, false, null, $g_project)
												);
												?></select>
										</td>
									</tr>

									<tr>
										<td class="category" width="15%">
											<?php echo plugin_lang_get('config_proxy_ips') ?>
										</td>
										<td>
											<input type="text" id="proxy_ips" name="proxy_ips" class="form-control"
											       value="<?php echo plugin_config_get('proxy_ips', null, false, null, $g_project); ?>" />
										</td>
									</tr>

								</table>
							</div>
						</div>
						<div class="widget-toolbox padding-8 clearfix">
							<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('action_update') ?>" />
						</div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<?php
layout_page_end();
