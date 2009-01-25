<?php
if (!defined('e107_INIT')) { exit; }
register_shortcode('forum_post_shortcodes', true);
initShortcodeClass('forum_post_shortcodes');

class forum_post_shortcodes
{
	var $e107;
	var $threadInfo;
	var $forum;
	
	function forum_post_shortcodes()
	{
		$this->e107 = e107::getInstance();
	}
	
	function get_latestposts($parm)
	{
		$parm = ($parm ? $parm : 10);
		global $LATESTPOSTS_START, $LATESTPOSTS_END, $LATESTPOSTS_POST;
		$txt = $this->e107->tp->parseTemplate($LATESTPOSTS_START, true);
		$start = max($this->threadInfo['thread_total_replies'] - $parm, 0);
		$num = min($this->threadInfo['thread_total_replies'], $parm);
	
		$tmp = $this->forum->postGet($this->threadInfo['thread_id'], $start, $num);
		
		for($i = count($tmp)-1; $i > 0; $i--)
		{
			setScVar('forum_shortcodes', 'postInfo', $tmp[$i]);
			$txt .= $this->e107->tp->parseTemplate($LATESTPOSTS_POST, true);
		}
		$txt .= $this->e107->tp->parseTemplate($LATESTPOSTS_END, true);
		return $txt;
	}

	function get_threadtopic()
	{
		global $THREADTOPIC_REPLY;
		$tmp = $this->forum->postGet($this->threadInfo['thread_id'], 0, 1);
		setScVar('forum_shortcodes', 'postInfo', $tmp[0]);
		return $this->e107->tp->parseTemplate($THREADTOPIC_REPLY, true);
	}

	function get_forumstart()
	{
		return "<form enctype='multipart/form-data' method='post' action='".e_SELF.'?'.e_QUERY."' id='dataform'>";
	}
	
	function get_formend()
	{
	return '</form>';
	}

	function get_forumjump()
	{
		return forumjump();
	}
	
	function get_userbox()
	{
		global $userbox;
		return (USER == false ? $userbox : '');
	}
	
	function get_subjectbox()
	{
		global $subjectbox, $action;
		return ($action == 'nt' ? $subjectbox : '');
	}
	
	function get_posttype()
	{
		global $action;
		return ($action == 'nt' ? LAN_63 : LAN_73);
	}
	
	function get_postbox()
	{
		global $post, $pref;
		$rows = (e_WYSIWYG) ? 15 : 10;
		$ret = "<textarea class='tbox' id='post' name='post' cols='70' rows='{$rows}' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$post</textarea>\n<br />\n";
		if(!e_WYSIWYG)
		{
			$ret .= display_help('helpb', 'forum');
		}
		return $ret;
	}
	
	function get_buttons()
	{
		global $action, $eaction;
		$ret = "<input class='button' type='submit' name='fpreview' value='".LAN_323."' /> ";
		if ($action != 'nt')
		{
			$ret .= ($eaction ? "<input class='button' type='submit' name='update_reply' value='".LAN_78."' />" : "<input class='button' type='submit' name='reply' value='".LAN_74."' />");
		}
		else
		{
			$ret .= ($eaction ? "<input class='button' type='submit' name='update_thread' value='".LAN_77."' />" : "<input class='button' type='submit' name='newthread' value='".LAN_64."' />");
		}
		return $ret;
	}
	
	function get_fileattach()
	{
		global $pref, $fileattach, $fileattach_alert;

		if ($pref['forum_attach'] && strpos(e_QUERY, 'edit') === FALSE && (check_class($pref['upload_class']) || getperms('0')))
		{
			if (is_writable(e_FILE.'public'))
			{
				return $fileattach;
			}
			else
			{
				$FILEATTACH = '';
				if(ADMIN)
				{
					if(!$fileattach_alert)
					{
						$fileattach_alert = "<tr><td colspan='2' class='nforumcaption2'>".($pref['image_post'] ? LAN_390 : LAN_416)."</td></tr><tr><td colspan='2' class='forumheader3'>".LAN_FORUM_1."</td></tr>\n";
					}
					return $fileattach_alert;
				}
			}
		}
	}
	
	function get_postthreadas()
	{
		global $action, $thread_info;
		if (MODERATOR && $action == "nt")
		{
			$thread_sticky = (isset($_POST['threadtype']) ? $_POST['threadtype'] : $thread_info['head']['thread_sticky']);
			return "<br /><span class='defaulttext'>".LAN_400."<input name='threadtype' type='radio' value='0' ".(!$thread_sticky ? "checked='checked' " : "")." />".LAN_1."&nbsp;<input name='threadtype' type='radio' value='1' ".($thread_sticky == 1 ? "checked='checked' " : "")." />".LAN_2."&nbsp;<input name='threadtype' type='radio' value='2' ".($thread_sticky == 2 ? "checked='checked' " : "")." />".LAN_3."</span>";
		}
		return '';
	}
	
	function get_backlink()
	{
		global $forum, $thread_info,$eaction, $action,$BREADCRUMB;
		$forum->set_crumb(TRUE,($action == "nt" ? ($eaction ? LAN_77 : LAN_60) : ($eaction ? LAN_78 : LAN_406." ".$thread_info['head']['thread_name'])));
		return $BREADCRUMB;
	}
	
	function get_noemotes()
	{
		if($eaction == true) { return ; }
		return "<input type='checkbox' name='no_emote' value='1' />&nbsp;<span class='defaulttext'>".LAN_FORUMPOST_EMOTES.'</span>';
	}
	
	function get_emailnotify()
	{
		global $pref, $thread_info, $action, $eaction;
		if($eaction == true) { return ; }
		if ($pref['email_notify'] && $action == 'nt' && USER)
		{
			if(isset($_POST['fpreview']))
			{
				$chk = ($_POST['email_notify'] ? "checked = 'checked'" : '');
			}
			else
			{
				if(isset($thread_info))
				{
					$chk = ($thread_info['head']['thread_active'] == 99 ? "checked='checked'" : '');
				}
				else
				{
					$chk = ($pref['email_notify_on'] ? "checked='checked'" : '');
				}
			}
			return "<br /><input type='checkbox' name='email_notify' value='1' {$chk} />&nbsp;<span class='defaulttext'>".LAN_380."</span>";
		}
		return '';
	}
	
	function get_poll()
	{
		global $poll_form, $action, $pref;
		if ($action == 'nt' && check_class($pref['forum_poll']) && strpos(e_QUERY, 'edit') === false)
		{
			return $poll_form;
		}
		return '';
	}

}	
?>