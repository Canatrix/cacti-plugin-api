<?php

function api_user_admin_tab(){
	echo '
		<td width="1"></td>
		<td '. ((get_request_var_request("tab") == "api_settings_edit") ? "bgcolor='silver'" : "bgcolor='#DFDFDF'").' nowrap="nowrap" width="130" align="center" class="tab">
					<span class="textHeader"><a href="'.htmlspecialchars("user_admin.php?action=user_edit&tab=api_settings_edit&id=" . $_GET["id"]).'">RestAPI accesses</a></span>
				</td>
	';
	
}

function api_user_admin_run_action(){
	global $colors;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	$graph_policy_array = array(
		1 => "Allow",
		2 => "Deny");

	if (!empty($_GET["id"])) {
		$policy = db_fetch_row("SELECT policy_graphs,policy_trees,policy_hosts,policy_graph_templates FROM user_auth WHERE id = " . get_request_var("id"));

		$header_label = "[edit: " . db_fetch_cell("SELECT username FROM user_auth WHERE id = " . get_request_var("id")) . "]";
	} else {
		$policy = array(
			'policy_graphs' => '1', 
			'policy_trees'  => '1', 
			'policy_hosts'  => '1', 
			'policy_graph_templates' => '1'
		);
	}

	?>
	<table width='100%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'>Graph policies will be evaluated in the order shown until a match is found.</span>
			</td>
		</tr>
	</table>
	<?php

	/* box: graph permissions */
	html_start_box("<strong>Api keys</strong>", "100%", $colors["header"], "3", "center", "");

	$rows = db_fetch_assoc("SELECT * 
		FROM api_key
		
		WHERE api_key.user_id = " . get_request_var("id", 0) . "
		ORDER BY api_key.api_key_id");

	?>
	
	

	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1">
				
				<?php
				$i = 0;
				if (count($rows)) {
					?>
					<tr>
					<td>ID</td>
					<td>Api ID:Secret</td>
					<td>Api URL with key</td>
					<td></td>
				</tr>
						<?
				foreach ($rows as $row) {
					$i++;
					print "	<tr>
							<td width=\"40\"><span style='font-weight: bold;'>#{$row["api_key_id"]} )</span>
								</td>
							<td width=\"250\">
<input type=\"text\" value=\"" . $row["user_id"]."x".$row["api_key_id"].":".$row["api_key"] . "\" readonly onclick=\"this.select();\"  style=\"width:100%;background: inherit;border: inherit;color: inherit;\" />"."
									</td>
							<td><input type=\"text\" value=\"".
									(isset($_SERVER['HTTPS'])?"https":"http")."://".$row["user_id"]."x".$row["api_key_id"].":".$row["api_key"]."@".$_SERVER["SERVER_NAME"].(
									(isset($_SERVER['HTTPS'])&&$_SERVER["SERVER_PORT"]!="443")||(!isset($_SERVER['HTTPS'])&&$_SERVER["SERVER_PORT"]!="80")?":".$_SERVER["SERVER_PORT"]:""
									).dirname($_SERVER["PHP_SELF"])."/plugins/api/v1/".
									"\" readonly onclick=\"this.select();\"  style=\"width:100%;background: inherit;border: inherit;color: inherit;\" /></td>
							<td  width=\"40\" align='right'><a href='" . htmlspecialchars("user_admin.php?action=remove_api_key&type=graph&api_key_id=" . $row["api_key_id"] . "&id=" . $_GET["id"]) . "'><img src='images/delete_icon.gif' style='height:10px;width:10px;' border='0' alt='Delete'></a>&nbsp;</td>
						</tr>\n";
				}
				}else{ print "<tr><td><em>No api keys</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
	<?php

	html_end_box(false);

	?>
	<table align='center' width='100%'>
		<tr>
			<td nowrap><? /*Add Graph:&nbsp;
				
				<?php form_dropdown("perm_graphs",db_fetch_assoc("SELECT local_graph_id, title_cache FROM graph_templates_graph WHERE local_graph_id > 0 AND local_graph_id NOT IN (SELECT item_id FROM user_auth_perms WHERE user_auth_perms.type=1 AND user_auth_perms.user_id=".get_request_var("id",0).") ORDER BY title_cache"),"title_cache","local_graph_id","","","");?> */ ?>
			</td>
			<td align="right">
				&nbsp;<input type="submit" onclick="document.getElementsByName('action')[0].value='add_api_key';" value="Add key" name="add_key_x" title="Add New Random Api Key" />
			</td>
		</tr>
	</table>

	<?php

	html_end_box(false);

	?>
	

	<?php
	form_hidden_box("save_component_graph_perms","1","");

}

function api_user_admin_action($action){
	input_validate_input_number(get_request_var("id"));

	if($action == "add_api_key"){
		if(($user = db_fetch_row("SELECT * FROM user_auth WHERE id = " . get_request_var_post("id")))==null){
			return false;
		}
		
		$api_key = md5(uniqid(rand(), true));
		db_execute("INSERT INTO api_key SET user_id = '".get_request_var_post("id")."', api_key = '{$api_key}'");
		
		api_update_access();
		
		header("Location: user_admin.php?action=user_edit&tab=api_settings_edit&id=" . get_request_var_post("id") );
		return true;
	}elseif($action == "remove_api_key"){
		if(($user = db_fetch_row("SELECT * FROM user_auth WHERE id = " . get_request_var("id")))==null){
			return false;
		}
		
		db_execute("DELETE FROM api_key WHERE user_id = ".get_request_var("id")." AND api_key_id = ".get_request_var("api_key_id"));

		api_update_access();
		
		header("Location: user_admin.php?action=user_edit&tab=api_settings_edit&id=" . get_request_var("id") );

		return true;
	}else return false;

}

function api_update_access(){
	$htps = array();
	$rows = db_fetch_assoc("SELECT * FROM api_key WHERE 1");
	if(count($rows)){
		foreach($rows as $row){
			$htps[] = $row["user_id"]."x".$row["api_key_id"].":".crypt($row["api_key"], base64_encode($row["api_key"]));
		}
	}
	$htpf = realpath(dirname(realpath(__FILE__))."/../v1/.htpasswd");
	$htaf = dirname(realpath(__FILE__))."/../v1/.htaccess";

	$f = fopen($htpf,"w+");
	fwrite($f,implode("\n",$htps));
    fclose($f);

	$htas[] = "# Don't edit this file! It is automatically generated";
	$htas[] = "AuthType Basic";
	$htas[] = "AuthName \"Cacti API v1\"";
	$htas[] = "AuthUserFile {$htpf}";
	$htas[] = "Require valid-user";

	$htas[] = "RewriteEngine On";
	$htas[] = "RewriteCond %{REQUEST_FILENAME} !-f";
	$htas[] = "RewriteRule ^(.*)$ index.php/$1";

	$f = fopen($htaf,"w+");
	fwrite($f,implode("\n",$htas)."\n");
    fclose($f);

}


?>