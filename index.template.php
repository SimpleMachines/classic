<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	http://www.simplemachines.org/
*/

// Initialize the template... mainly little settings.
function template_init()
{
	global $context, $settings, $options, $txt;

	/* Use images from default theme when using templates from the default theme?
		if this is 'always', images from the default theme will be used.
		if this is 'defaults', images from the default theme will only be used with default templates.
		if this is 'never' or isn't set at all, images from the default theme will not be used. */
	$settings['use_default_images'] = 'never';

	/* What document type definition is being used? (for font size and other issues.)
		'xhtml' for an XHTML 1.0 document type definition.
		'html' for an HTML 4.01 document type definition. */
	$settings['doctype'] = 'xhtml';

	/* The version this template/theme is for.
		This should probably be the version of SMF it was created for. */
	$settings['theme_version'] = '2.0';

	/* Set a setting that tells the theme that it can render the tabs. */
	$settings['use_tabs'] = true;

	/* Use plain buttons - as opposed to text buttons? */
	$settings['use_buttons'] = true;

	/* Show sticky and lock status separate from topic icons? */
	$settings['separate_sticky_lock'] = true;

	/* Does this theme use the strict doctype? */
	$settings['strict_doctype'] = false;

	/* Does this theme use post previews on the message index? */
	$settings['message_index_preview'] = false;

	/* Set the following variable to true if this theme requires the optional theme strings file to be loaded. */
	$settings['require_theme_strings'] = false;
}

// The main sub template above the content.
function template_html_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Show right to left and the character set for ease of translating.
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>';

	// The ?fin20 part of this link is just here to make sure browsers don't cache it wrongly.
	echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?fin20" />';

	// Some browsers need an extra stylesheet due to bugs/compatibility issues.
	foreach (array('ie7', 'ie6', 'webkit') as $cssfix)
		if ($context['browser']['is_' . $cssfix])
			echo '
	<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/css/', $cssfix, '.css" />';

	echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/style.css" />';	
	// RTL languages require an additional stylesheet.
	if ($context['right_to_left'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/rtl.css" />';

	// Here comes the JavaScript bits!
	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?fin20"></script>
	<script type="text/javascript" src="', $settings['theme_url'], '/scripts/theme.js?fin20"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_iso_case_folding = ', $context['server']['iso_case_folding'] ? 'true' : 'false', ';
		var smf_charset = "', $context['character_set'], '";', $context['show_pm_popup'] ? '
		var fPmPopup = function ()
		{
			if (confirm("' . $txt['show_personal_messages'] . '"))
				window.open(smf_prepareScriptUrl(smf_scripturl) + "action=pm");
		}
		addLoadEvent(fPmPopup);' : '', '
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
	// ]]></script>';

	echo '
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<meta name="description" content="', $context['page_title_html_safe'], '" />', !empty($context['meta_keywords']) ? '
	<meta name="keywords" content="' . $context['meta_keywords'] . '" />' : '', '
	<title>', $context['page_title_html_safe'], '</title>';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex" />';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '" />';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help" />
	<link rel="search" href="', $scripturl, '?action=search" />
	<link rel="contents" href="', $scripturl, '" />';

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?type=rss;action=.xml" />';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['current_topic']))
		echo '
	<link rel="prev" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=prev" />
	<link rel="next" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=next" />';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0" />';

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	echo '
</head>
<body>';
}

function template_body_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// The wrapper div, forum width setting is set here
	echo !empty($settings['forum_width']) ? '
	<div id="wrapper" style="width: ' . $settings['forum_width'] . '">' : '', '
		<div id="head">
			<div id="logo_title">
				<h1 class="forumtitle">
					<a href="', $scripturl, '"><img src="', empty($context['header_logo_url_html_safe']) ? $settings['images_url'] . '/smflogo.gif' : $context['header_logo_url_html_safe'] ,'" alt="' . $context['forum_name'] . '" />', '</a>
				</h1>
				<div class="user">';
				
		// If the user is logged in, display stuff like their name, new messages, etc.
		if ($context['user']['is_logged'])
		{
			echo '
					', $txt['hello_member'], ' <strong>', $context['user']['name'], '</strong>', $context['allow_pm'] ? ', ' . $txt['msg_alert_you_have'] . ' <a href="' . $scripturl . '?action=pm">' . $context['user']['messages'] . ' ' . ($context['user']['messages'] != 1 ? $txt['msg_alert_messages'] : $txt['message_lowercase']) . '</a>' . $txt['newmessages4'] . ' ' . $context['user']['unread_messages'] . ' ' . ($context['user']['unread_messages'] == 1 ? $txt['newmessages0'] : $txt['newmessages1']) : '', '.';

			// Are there any members waiting for approval?
			if (!empty($context['unapproved_members']))
				echo '<br />
					', $context['unapproved_members'] == 1 ? $txt['approve_thereis'] : $txt['approve_thereare'], ' <a href="', $scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve">', $context['unapproved_members'] == 1 ? $txt['approve_member'] : $context['unapproved_members'] . ' ' . $txt['approve_members'], '</a> ', $txt['approve_members_waiting'];

			// Is the forum in maintenance mode?
			if ($context['in_maintenance'] && $context['user']['is_admin'])
				echo '<br />
					<strong>', $txt['maintain_mode_on'], '</strong>';

			if (!empty($context['open_mod_reports']) && $context['show_open_reports'])
				echo '<br />
					<a href="', $scripturl, '?action=moderate;area=reports">', sprintf($txt['mod_reports_waiting'], $context['open_mod_reports']), '</a>';
			echo '
				<br />', $context['current_time'];
		}
		// Otherwise they're a guest - this time ask them to either register or login - lazy bums...
		elseif (!empty($context['show_login_bar']))
		{
			echo '
					<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>
					<form id="guest_form" action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
						<input type="text" name="user" size="10" class="input_text" />
						<input type="password" name="passwrd" size="10" class="input_password" />
						<select name="cookielength">
							<option value="60">', $txt['one_hour'], '</option>
							<option value="1440">', $txt['one_day'], '</option>
							<option value="10080">', $txt['one_week'], '</option>
							<option value="43200">', $txt['one_month'], '</option>
							<option value="-1" selected="selected">', $txt['forever'], '</option>
						</select>
						<input type="submit" value="', $txt['login'], '" class="button_submit" /><br />
						<div class="info">', $txt['quick_login_dec'], '</div>';

			if (!empty($modSettings['enableOpenID']))
				echo '
						<br /><input type="text" name="openid_identifier" id="openid_url" size="25" class="input_text openid_login" />';

			echo '
						<input type="hidden" name="hash_passwrd" value="" />
					</form>';
		}

		echo '
				</div>					
			</div>
			<div id="main_menu">', template_menu() ,'</div>
			<div class="news">';
			
	// Show a random news item? (or you could pick one from news_lines...)
	if (!empty($settings['enable_news']))
		echo '
				<span>', $txt['news'], ': </span>
				<p>', $context['random_news_line'], '</p>';
				
		echo '	
			</div>
		</div>';

	// The main content should go here.
	echo '
	<div id="content">';

	// Custom banners and shoutboxes should be placed here, before the linktree.
	// Show the navigation tree.
	theme_linktree();
	
}

function template_body_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	</div>';

	// Show the "Powered by" and "Valid" logos, as well as the copyright. Remember, the copyright must be somewhere!
	echo '
	<div id="foot">
		<ul class="reset">
			<li class="copy_right">', theme_copyright(), '</li>
			<li><a id="button_xhtml" href="http://validator.w3.org/check?uri=referer" target="_blank" class="new_win" title="', $txt['valid_xhtml'], '"><span>', $txt['xhtml'], '</span></a></li>
			', !empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']) ? '<li><a id="button_rss" href="' . $scripturl . '?action=.xml;type=rss" class="new_win"><span>' . $txt['rss'] . '</span></a></li>' : '', '
			<li class="last"><a id="button_wap2" href="', $scripturl , '?wap2" class="new_win"><span>', $txt['wap2'], '</span></a></li>
		</ul>';

	// Show the load time?
	if ($context['show_load_time'])
		echo '
		<p>', $txt['page_created'], $context['load_time'], $txt['seconds_with'], $context['load_queries'], $txt['queries'], '</p>';

	echo '</div>', !empty($settings['forum_width']) ? '
</div>' : '';
}

function template_html_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
</body></html>';
}
// Show a linktree.  This is that thing that shows "My Community | General Category | General Discussion"..
function theme_linktree($force_show = false)
{
	global $context, $settings, $options;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;
		
	// Folder style or inline?  Inline has a smaller font.
	echo '<div class="nav"', $settings['linktree_inline'] ? ' style="font-size: smaller;"' : '', '>';

	// Each tree item has a URL and name.  Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		// Show the | | |-[] Folders.
		if (!$settings['linktree_inline'])
		{
			if ($link_num > 0)
				echo str_repeat('<img src="' . $settings['images_url'] . '/icons/linktree_main.gif" alt="| " border="0" />', $link_num - 1), '<img src="' . $settings['images_url'] . '/icons/linktree_side.gif" alt="|-" border="0" />';
			echo '<img src="' . $settings['images_url'] . '/icons/folder_open.gif" alt="+" border="0" />&nbsp; ';
		}

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'];

		// Show the link, including a URL if it should have one.
		echo '<strong>', $settings['linktree_link'] && isset($tree['url']) ? '<a href="' . $tree['url'] . '" class="nav">' . $tree['name'] . '</a>' : $tree['name'], '</strong>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo $tree['extra_after'];

		// Don't show a separator for the last one.
		if ($link_num != count($context['linktree']) - 1)
			echo $settings['linktree_inline'] ? ' &nbsp;|&nbsp; ' : '<br />';
	}
	echo '</div>';

	$shown_linktree = true;
}

// Show the menu up top.  Something like [home] [help] [profile] [logout]...
function template_menu()
{
	global $context, $settings, $options, $scripturl, $txt;

	// We aren't showing all the buttons in this theme.
	$hide_buttons = array('pm', 'mlist');

	foreach ($context['menu_buttons'] as $act => $button)
		// Hide the buttons we don't want to see
		if (in_array($act, $hide_buttons))
			continue;
		else
			echo '
				<a href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>', $settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/' . $act . '.gif" alt="' . $button['title'] . '" border="0" />' : $button['title'], '</a>', !empty($button['is_last']) ? '' : $context['menu_separator'];
}

// Generate a strip of buttons.
function template_button_strip($button_strip, $direction = 'top', $strip_options = array())
{
	global $settings, $context, $txt, $scripturl;

	if (!is_array($strip_options))
		$strip_options = array();

	// List the buttons in reverse order for RTL languages.
	if ($context['right_to_left'])
		$button_strip = array_reverse($button_strip, true);

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		if (!isset($value['test']) || !empty($context[$value['test']]))
			$buttons[] = '
				<li><a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="button_strip_' . $key . (isset($value['active']) ? ' active' : '') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '><span>' . $txt[$value['text']] . '</span></a></li>';
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	// Make the last one, as easy as possible.
	$buttons[count($buttons) - 1] = str_replace('<span>', '<span class="last">', $buttons[count($buttons) - 1]);

	echo '
		<div class="buttonlist', !empty($direction) ? ' float' . $direction : '', '"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"': ''), '>
			<ul>',
				implode('', $buttons), '
			</ul>
		</div>';
}

?>