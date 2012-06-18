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

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Show some statistics if stat info is off.
	if (!$settings['show_stats_index'])
		echo '
	<div id="index_common_stats">
		', $txt['members'], ': ', $context['common_stats']['total_members'], ' &nbsp;&#8226;&nbsp; ', $txt['posts_made'], ': ', $context['common_stats']['total_posts'], ' &nbsp;&#8226;&nbsp; ', $txt['topics'], ': ', $context['common_stats']['total_topics'], '
		', ($settings['show_latest_member'] ? ' ' . $txt['welcome_member'] . ' <strong>' . $context['common_stats']['latest_member']['link'] . '</strong>' . $txt['newest_member'] : '') , '
	</div>';

	// Show the news fader?  (assuming there are things to show...)
	if ($settings['show_newsfader'] && !empty($context['fader_news_lines']))
	{
		echo '
	<div id="newsfader">
		<div class="cat_bar">
			<h3 class="catbg">
				<img id="newsupshrink" src="', $settings['images_url'], '/collapse.gif" alt="*" title="', $txt['upshrink_description'], '" align="bottom" style="display: none;" />
				', $txt['news'], '
			</h3>
		</div>
		<ul class="reset" id="smfFadeScroller"', empty($options['collapse_news_fader']) ? '' : ' style="display: none;"', '>';

			foreach ($context['news_lines'] as $news)
				echo '
			<li>', $news, '</li>';

	echo '
		</ul>
	</div>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/fader.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[

		// Create a news fader object.
		var oNewsFader = new smf_NewsFader({
			sSelf: \'oNewsFader\',
			sFaderControlId: \'smfFadeScroller\',
			sItemTemplate: ', JavaScriptEscape('<strong>%1$s</strong>'), ',
			iFadeDelay: ', empty($settings['newsfader_time']) ? 5000 : $settings['newsfader_time'], '
		});

		// Create the news fader toggle.
		var smfNewsFadeToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['collapse_news_fader']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'smfFadeScroller\'
			],
			aSwapImages: [
				{
					sId: \'newsupshrink\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ', JavaScriptEscape($txt['upshrink_description']), ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ', JavaScriptEscape($txt['upshrink_description']), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'collapse_news_fader\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'newsupshrink\'
			}
		});
	// ]]></script>';
	}

	echo '
	<div id="boardindex_table">';
	
	// Show the "Board name      Topics  Posts    Last Post" header.
	echo '
		<table class="table_list table_border" cellspacing="1" cellpadding="5">
			<thead>
				<tr class="titlebg">
					<th class="lefttext" colspan="2">', $txt['board_name'], '</th>
					<th class="stats">', $txt['board_topics'], '</th>
					<th class="stats">', $txt['posts'], '</th>
					<th>', $txt['last_post'], '</th>
				</tr>
			</thead>';
	
	/* Each category in categories is made up of:
	id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
	new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down image),
	and boards. (see below.) */
	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		echo '
			<tbody class="header" id="category_', $category['id'], '">
				<tr>
					<td colspan="5">
						<div class="cat_bar">
							<h3 class="catbg">';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
								<a class="collapse" href="', $category['collapse_href'], '">', $category['collapse_image'], '</a>';

		if (!$context['user']['is_guest'] && !empty($category['show_unread']))
			echo '
								<a class="unreadlink" href="', $scripturl, '?action=unread;c=', $category['id'], '">', $txt['view_unread_category'], '</a>';

		echo '
								', $category['link'], '
							</h3>
						</div>
					</td>
				</tr>
			</tbody>';

		// Assuming the category hasn't been collapsed...
		if (!$category['is_collapsed'])
		{

		echo '
			<tbody class="content" id="category_', $category['id'], '_boards">';
			/* Each board in each category's boards has:
			new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
			children (see below.), link_children (easier to use.), children_new (are they new?),
			topics (# of), posts (# of), link, href, and last_post. (see below.) */
			foreach ($category['boards'] as $board)
			{
				echo '
				<tr id="board_', $board['id'], '" class="windowbg2">
					<td class="icon windowbg">
						<a href="', ($board['is_redirect'] || $context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '">';

				// If the board or children is new, show an indicator.
				if ($board['new'] || $board['children_new'])
					echo '
							<img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'on', $board['new'] ? '' : '2', '.png" alt="', $txt['new_posts'], '" title="', $txt['new_posts'], '" />';
				// Is it a redirection board?
				elseif ($board['is_redirect'])
					echo '
							<img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'redirect.png" alt="*" title="*" />';
				// No new posts at all! The agony!!
				else
					echo '
							<img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'off.png" alt="', $txt['old_posts'], '" title="', $txt['old_posts'], '" />';

				echo '
						</a>
					</td>
					<td class="info">
						<a class="subject" href="', $board['href'], '" name="b', $board['id'], '">', $board['name'], '</a>';

				// Has it outstanding posts for approval?
				if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
					echo '
						<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link">(!)</a>';

				echo '

						<p>', $board['description'] , '</p>';

				// Show the "Moderators: ".  Each has name, href, link, and id. (but we're gonna use link_moderators.)
				if (!empty($board['moderators']))
					echo '
						<em class="smalltext">
						', count($board['moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</em>';

				// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
				if (!empty($board['children']))
				{
					// Sort the links into an array with new boards bold so it can be imploded.
					$children = array();
					/* Each child in each board's children has:
						id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
					foreach ($board['children'] as $child)
						$children[] = $child['new'] ? '<strong>' . $child['link'] . '</strong>' : $child['link'];

					echo '
						<em class="smalltext"><br />
						', $txt['parent_boards'], ': ', implode(', ', $children), '</em>';
				}

				// Show some basic information about the number of posts, etc.
					echo '
					</td>';
				if (!$board['is_redirect'])
				{
					echo '
					<td class="stats windowbg">
						<p>', comma_format($board['posts']), ' ', $board['is_redirect'] ? $txt['redirects'] : $txt['posts'], '</p>
					</td>';
					// Changed this section to separate posts/topics
					echo '
					<td class="stats windowbg">
						<p>', $board['is_redirect'] ? '' : comma_format($board['topics']) . ' ' . $txt['board_topics'], '</p>
					</td>';
				}
				else
				{
					echo '
					<td class="stats windowbg" colspan="2">
						<p>', comma_format($board['posts']), ' ', $txt['redirects'] , '</p>
					</td>';
				}
					echo '
					<td class="lastpost">';
					
				/* The board's and children's 'last_post's have:
				time, timestamp (a number that represents the time.), id (of the post), topic (topic id.),
				link, href, subject, start (where they should go for the first unread post.),
				and member. (which has id, name, link, href, username in it.) */
				if (!empty($board['last_post']['id']))
					echo '
						<p><strong>', $txt['last_post'], '</strong>  ', $txt['by'], ' ', $board['last_post']['member']['link'] , '<br />
						', $txt['in'], ' ', $board['last_post']['link'], '<br />
						', $txt['on'], ' ', $board['last_post']['time'], '
						</p>';
				echo '
					</td>
				</tr>';
			}
		echo '
			</tbody>';
		}
	}
		echo '
			<tbody class="titlebg">
				<tr>
					<td colspan="5">';

					if ($context['user']['is_logged'])
					{
							echo '
						<div id="posting_icons" class="floatleft">';

							// Mark read button.
							$mark_read_button = array(
								'markread' => array('text' => 'mark_as_read', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=all;' . $context['session_var'] . '=' . $context['session_id']),
							);

							echo '
							<ul class="reset">
								<li class="floatleft"><img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'new_some.png" alt="" /> ', $txt['new_posts'], '</li>
								<li class="floatleft"><img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'new_none.png" alt="" /> ', $txt['old_posts'], '</li>
								<li class="floatleft"><img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'new_redirect.png" alt="" /> ', $txt['redirect_board'], '</li>
							</ul>
						</div>';

							// Show the mark all as read button?
							if ($settings['show_mark_read'] && !empty($context['categories']))
								echo '<div class="mark_read">', template_button_strip($mark_read_button, 'right'), '</div>';
					}
					else
					{
							echo '
						<div id="posting_icons" class="flow_hidden">
							<ul class="reset">
								<li class="floatleft"><img src="', $settings['images_url'], '/new_none.png" alt="" /> ', $txt['old_posts'], '</li>
								<li class="floatleft"><img src="', $settings['images_url'], '/new_redirect.png" alt="" /> ', $txt['redirect_board'], '</li>
							</ul>
						</div>';
					}
					
				echo '	
					</td>
				</tr>
			</tbody>';	
	echo '
		</table>
	</div><br />';

	template_info_center();
}

function template_info_center()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Here's where the "Info Center" starts...
	echo '
<table border="0" width="100%" cellspacing="1" cellpadding="4" class="table_border">
	<tr class="titlebg">
		<td align="center" colspan="2">', sprintf($txt['info_center_title'], $context['forum_name_html_safe']), '</td>
	</tr>';

	// This is the "Recent Posts" bar.
	if (!empty($settings['number_recent_posts']))
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['recent_posts'], '</td>
	</tr>
	<tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			<a href="', $scripturl, '?action=recent">
				<img src="', $settings['images_url'], '/post/xx.gif" alt="', $txt['recent_posts'], '" border="0" /></a>
		</td>
		<td class="windowbg2">';

		// Only show one post.
		if ($settings['number_recent_posts'] == 1)
		{
			// latest_post has link, href, time, subject, short_subject (shortened with...), and topic. (its id.)
			echo '
			<strong><a href="', $scripturl, '?action=recent">', $txt['recent_posts'], '</a></strong><br />
			<span class="smalltext">
				', $txt['recent_view'], ' &quot;', $context['latest_post']['link'], '&quot; ', $txt['recent_updated'], ' (', $context['latest_post']['time'], ')<br />
			</span>';
		}
		// Show lots of posts.
		elseif (!empty($context['latest_posts']))
		{
			echo '
			<table width="100%" border="0">';
			/* Each post in latest_posts has:
				board (with an id, name, and link.), topic (the topic's id.), poster (with id, name, and link.),
				subject, short_subject (shortened with...), time, link, and href. */
			foreach ($context['latest_posts'] as $post)
				echo '
				<tr>
					<td align="right" valign="top" nowrap="nowrap">[', $post['board']['link'], ']</td>
					<td valign="top">', $post['link'], ' ', $txt['by'], ' ', $post['poster']['link'], '</td>
					<td align="right" valign="top" nowrap="nowrap">', $post['time'], '</td>
				</tr>';
			echo '
			</table>';
		}
		echo '
		</td>
	</tr>';
	}

	// Show information about events, birthdays, and holidays on the calendar.
	if ($context['show_calendar'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '</td>
	</tr><tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			<a href="', $scripturl, '?action=calendar">
				<img src="', $settings['images_url'], '/icons/calendar.gif" border="0" width="20" alt="', $txt['calendar'], '" /></a>
		</td>
		<td class="windowbg2" width="100%">
			<span class="smalltext">';

		// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P.
		if (!empty($context['calendar_holidays']))
			echo '
				<span class="holiday">', $txt['calendar_prompt'], ' ', implode(', ', $context['calendar_holidays']), '</span><br />';

		// People's birthdays.  Like mine.  And yours, I guess.  Kidding.
		if (!empty($context['calendar_birthdays']))
		{
			echo '
				<span class="birthday">', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</span> ';
			/* Each member in calendar_birthdays has:
				id, name (person), age (if they have one set?), is_last. (last in list?), and is_today (birthday is today?) */
			foreach ($context['calendar_birthdays'] as $member)
				echo '
				<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<strong>' : '', $member['name'], $member['is_today'] ? '</strong>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] ? '<br />' : ', ';
		}
		// Events like community get-togethers.
		if (!empty($context['calendar_events']))
		{
			echo '
				<span class="event">', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</span> ';
			/* Each event in calendar_events should have:
				title, href, is_last, can_edit (are they allowed?), modify_href, and is_today. */
			foreach ($context['calendar_events'] as $event)
				echo '
				', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<strong>' . $event['title'] . '</strong>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br />' : ', ';

			// Show a little help text to help them along ;).
			if ($context['calendar_can_edit'])
				echo '
				(<a href="', $scripturl, '?action=helpadmin;help=calendar_how_edit" onclick="return reqWin(this.href);">', $txt['calendar_how_edit'], '</a>)';
		}
		echo '
			</span>
		</td>
	</tr>';
	}

	// Show a member bar.  Not heavily ornate, but functional at least.
	if ($settings['show_member_bar'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['members'], '</td>
	</tr>
	<tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			', $context['show_member_list'] ? '<a href="' . $scripturl . '?action=mlist">' : '', '<img src="', $settings['images_url'], '/icons/members.gif" border="0" width="20" alt="', $txt['members_list'], '" />', $context['show_member_list'] ? '</a>' : '', '
		</td>
		<td class="windowbg2" width="100%">
			<strong>', $context['show_member_list'] ? '<a href="' . $scripturl . '?action=mlist">' . $txt['members_list'] . '</a>' : $txt['members_list'], '</strong><br />
			<span class="smalltext">', $txt['memberlist_searchable'], '</span>
		</td>
	</tr>';
	}

	// Show some statistical information...
	if ($settings['show_stats_index'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['forum_stats'], '</td>
	</tr>
	<tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			<a href="', $scripturl, '?action=stats">
				<img src="', $settings['images_url'], '/icons/info.gif" alt="', $txt['forum_stats'], '" border="0" /></a>
		</td>
		<td class="windowbg2" width="100%">
			<table border="0" width="90%"><tr>
				<td class="smalltext">
					', $txt['total_topics'], ': <strong>', $context['common_stats']['total_topics'], '</strong> &nbsp;&nbsp;&nbsp;&nbsp; ', $txt['total_posts'], ': <strong>', $context['common_stats']['total_posts'], '</strong><br />
					', !empty($context['latest_post']) ? $txt['latest_post'] . ':
					&quot;' . $context['latest_post']['link'] . '&quot;  (' . $context['latest_post']['time'] . ')<br />' : '', '
					<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a>', $context['show_stats'] ? '<br />
					<a href="' . $scripturl . '?action=stats">' . $txt['more_stats'] . '</a>' : '', '
				</td>
				<td class="smalltext">
					', $txt['total_members'], ': <strong>', $context['show_member_list'] ? '<a href="' . $scripturl . '?action=mlist">' . $context['common_stats']['total_members'] . '</a>' : $context['common_stats']['total_members'], '</strong><br />
					', !empty($settings['show_latest_member']) ? $txt['latest_member'] . ': <strong> ' . $context['common_stats']['latest_member']['link'] . '</strong><br />' : '';
		// If they are logged in, show their unread message count, etc..
		if ($context['user']['is_logged'] && $context['allow_pm'])
			echo '
					', $txt['your_pms'], ': <strong><a href="', $scripturl, '?action=pm">', $context['user']['messages'], '</a></strong> ', $txt['newmessages3'], ': <strong><a href="', $scripturl, '?action=pm">', $context['user']['unread_messages'], '</a></strong>';
		echo '
				</td>
			</tr></table>
		</td>
	</tr>';
	}

	// "Users online" - in order of activity.
	echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['online_users'], '</td>
	</tr><tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<img src="', $settings['images_url'], '/icons/online.gif" alt="', $txt['online_users'], '" border="0" />', $context['show_who'] ? '</a>' : '', '
		</td>
		<td class="windowbg2" width="100%">';

	if ($context['show_who'])
		echo '
			<a href="', $scripturl, '?action=who">';

	echo comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ' . comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();
	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);
	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);
	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . $txt['hidden'];

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	if ($context['show_who'])
		echo '</a>';

	echo '
			<span class="smalltext">';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
		echo '
				', sprintf($txt['users_active'], $modSettings['lastActive']), ':<br />', implode(', ', $context['list_users_online']);

	echo '
			</span>
		</td>
	</tr>';

	// If they are logged in, but statistic information is off... show a personal message bar.
	if ($context['user']['is_logged'] && !$settings['show_stats_index'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['personal_message'], '</td>
	</tr><tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			', $context['allow_pm'] ? '<a href="' . $scripturl . '?action=pm">' : '', '<img src="', $settings['images_url'], '/message_sm.gif" alt="', $txt['personal_message'], '" border="0" />', $context['allow_pm'] ? '</a>' : '', '
		</td>
		<td class="windowbg2" valign="top">
			<strong><a href="', $scripturl, '?action=pm">', $txt['personal_message'], '</a></strong><br />
			<span class="smalltext">
				', $txt['you_have'], ' ', comma_format($context['user']['messages']), ' ', $context['user']['messages'] == 1 ? $txt['message_lowercase'] : $txt['msg_alert_messages'], '.... ', $txt['click'], $context['allow_pm'] ? ' <a href="' . $scripturl . '?action=pm">' . $txt['here'] . '</a>' : '', ' ', $txt['to_view'], '
			</span>
		</td>
	</tr>';
	}

	// Show the login bar. (it's only true if they are logged out anyway.)
	if ($context['show_login_bar'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">
			', $txt['login'], ' <a href="', $scripturl, '?action=reminder" class="smalltext">(' . $txt['forgot_your_password'] . ')</a>
		</td>
	</tr>
	<tr>
		<td class="windowbg" width="20" align="center">
			<a href="', $scripturl, '?action=login">
				<img src="', $settings['images_url'], '/icons/login.gif" alt="', $txt['login'], '" border="0" /></a>
		</td>
		<td class="windowbg2" valign="middle">
			<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
				<table border="0" cellpadding="2" cellspacing="0" align="center" width="100%"><tr>
					<td valign="middle" align="left">
						<label for="user"><strong>', $txt['username'], ':</strong><br /><input type="text" name="user" id="user" size="15" class="input_text" /></label>
					</td>
					<td valign="middle" align="left">
						<label for="passwrd"><strong>', $txt['password'], ':</strong><br /><input type="password" name="passwrd" id="passwrd" size="15" class="input_password" /></label>
					</td>
					<td valign="middle" align="left">
						<label for="cookielength"><strong>', $txt['mins_logged_in'], ':</strong><br /><input type="text" name="cookielength" id="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '" class="input_text" /></label>
					</td>
					<td valign="middle" align="left">
						<label for="cookieneverexp"><strong>', $txt['always_logged_in'], ':</strong><br /><input type="checkbox" name="cookieneverexp" id="cookieneverexp" checked="checked" class="input_check" /></label>
					</td>
					<td valign="middle" align="left">
						<input type="submit" value="', $txt['login'], '" class="button_submit" />
					</td>
				</tr></table>
			</form>
		</td>
	</tr>';
	}

	echo '
</table>';

	// Info center collapse object.
	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		var oInfoCenterToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['collapse_header_ic']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'upshrinkHeaderIC\'
			],
			aSwapImages: [
				{
					sId: \'upshrink_ic\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ', JavaScriptEscape($txt['upshrink_description']), ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ', JavaScriptEscape($txt['upshrink_description']), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'collapse_header_ic\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'upshrinkIC\'
			}
		});
	// ]]></script>';
}
?>