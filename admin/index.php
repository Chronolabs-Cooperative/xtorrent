<?php

//require_once __DIR__ . '/../../../include/cp_header.php';
require_once __DIR__ . '/admin_header.php';

include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
include_once XOOPS_ROOT_PATH . '/class/xoopsform/grouppermform.php';
include_once __DIR__ . '/../class/xtorrent_lists.php';

$mytree = new XoopsTree($xoopsDB -> prefix('xtorrent_cat'), 'cid', 'pid');

function Download()
{
    global $xoopsDB, $_GET, $_POST, $myts, $mytree, $xoopsModuleConfig, $xoopsModule;

    $lid           = 0;
    $cid           = 0;
    $title         = '';
    $url           = 'https://';
    $homepage      = 'https://';
    $homepagetitle = '';
    $version       = '';
    $size          = 0;
    $platform      = '';
    $screenshot    = '';
    $price         = 0;
    $description   = '';
    $mirror        = 'https://';
    $license       = '';
    $paypalemail   = '';
    $features      = '';
    $requirements  = '';
    $forumid       = 0;
    $limitations   = '';
    $dhistory      = '';
    $status        = 0;
    $is_updated    = 0;
    $offline       = 0;
    $published     = 0;
    $expired       = 0;
    $updated       = 0;
    $versiontypes  = '';
    $publisher     = '';
    $ipaddress     = '';
    $notifypub     = '';

    if (isset($_POST['lid']))
    {
        $lid = (int)$_POST['lid'];
    } elseif (isset($_GET['lid']))
    {
        $lid = (int)$_GET['lid'];
    } 
    else
    {
        $lid = 0;
    } 
    $directory     = $xoopsModuleConfig['screenshots'];

    $result        = $xoopsDB -> query('SELECT COUNT(*) FROM '
                                       . $xoopsDB -> prefix('xtorrent_cat')
                                       . '');
    list($numrows) = $xoopsDB -> fetchRow($result);

    $down_array    = '';

    if ($numrows)
    {
        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject -> displayNavigation(basename(__FILE__));

    		echo "<fieldset><legend style='font-weight:bold;color:#900;'>" . _AM_XTORRENT_FILE_ALLOWEDAMIME . "</legend>
    		      <div style='padding: 8px;'>";

        $query          = 'SELECT mime_ext FROM '
                          . $xoopsDB -> prefix('xtorrent_mimetypes')
                          . ' WHERE mime_admin = 1 ORDER by mime_ext';
        $result         = $xoopsDB -> query($query);
        $allowmimetypes = '';
        while ($mime_arr = $xoopsDB -> fetchArray($result))
        {
            echo $mime_arr['mime_ext'] . " | ";
        } 		
    		echo '</div><br>';

        if ($lid)
        {
            $sql           = 'SELECT * FROM ' . $xoopsDB -> prefix('xtorrent_downloads') . ' WHERE lid=' . $lid . '';
            $down_array    = $xoopsDB -> fetchArray($xoopsDB -> query($sql));

            $lid           = $down_array['lid'];
            $cid           = $down_array['cid'];
            $title         = $myts -> htmlSpecialChars($down_array['title']);
            $url           = $myts -> htmlSpecialChars($down_array['url']);
            $homepage      = $myts -> htmlSpecialChars($down_array['homepage']);
            $homepagetitle = $myts -> htmlSpecialChars($down_array['homepagetitle']);
            $version       = $down_array['version'];
            $size          = (int)$down_array['size'];
            $platform      = $myts -> htmlSpecialChars($down_array['platform']);
            $publisher     = $myts -> htmlSpecialChars($down_array['publisher']);
            $screenshot    = $myts -> htmlSpecialChars($down_array['screenshot']);
            $price         = $myts -> htmlSpecialChars($down_array['price']);
            $description   = $myts -> htmlSpecialChars($down_array['description']);
            $mirror        = $myts -> htmlSpecialChars($down_array['mirror']);
            $license       = $myts -> htmlSpecialChars($down_array['license']);
            $features      = $myts -> htmlSpecialChars($down_array['features']);
            $requirements  = $myts -> htmlSpecialChars($down_array['requirements']);
            $limitations   = $myts -> htmlSpecialChars($down_array['limitations']);
            $dhistory      = $myts -> htmlSpecialChars($down_array['dhistory']);
            $published     = $down_array['published'];
            $expired       = $down_array['expired'];
            $updated       = $down_array['updated'];
            $offline       = $down_array['offline'];
            $forumid       = $down_array['forumid'];
            $ipaddress     = $down_array['ipaddress'];
            $notifypub     = $down_array['notifypub'];
            $sform         = new XoopsThemeForm(_AM_XTORRENT_FILE_MODIFYFILE, 'storyform', xoops_getenv('PHP_SELF'));
        } 
        else
        {
            $sform = new XoopsThemeForm(_AM_XTORRENT_FILE_CREATENEWFILE, 'storyform', xoops_getenv('PHP_SELF'));
        } 

        $sform                 -> setExtra('enctype="multipart/form-data"');
        if ($lid) $sform       -> addElement(new XoopsFormLabel(_AM_XTORRENT_FILE_ID, $lid));
        if ($ipaddress) $sform -> addElement(new XoopsFormLabel(_AM_XTORRENT_FILE_IP, $ipaddress));
        $memberHandler         = xoops_getHandler('member');
        $group_list            = $memberHandler -> getGroupList();

        $gpermHandler  = xoops_getHandler('groupperm');
        $groups        = $gpermHandler -> getGroupIds('xtorrentownFilePerm', $lid, $xoopsModule -> getVar('mid'));

        $groups = ($groups) ? $groups : true;
        $sform  -> addElement(new XoopsFormSelectGroup(_AM_XTORRENT_FCATEGORY_GROUPPROMPT, 'groups', true, $groups, 5, true));

        $titles_tray     = new XoopsFormElementTray(_AM_XTORRENT_FILE_TITLE, '<br>');
        $titles          = new XoopsFormText('', 'title', 50, 255, $title);
        $titles_tray     -> addElement($titles);
        $titles_checkbox = new XoopsFormCheckBox('', 'title_checkbox', 0);
        $titles_checkbox -> addOption(1, _AM_XTORRENT_FILE_USE_UPLOAD_TITLE);
        $titles_tray     -> addElement($titles_checkbox);
        $sform           -> addElement($titles_tray);

        $mytree = new XoopsTree($xoopsDB -> prefix('xtorrent_cat'), 'cid', 'pid');
        $sform  -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_DLURL, 'url', 50, 255, $url), true);
        $sform  -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_MIRRORURL, 'mirror', 50, 255, $mirror), false);
        $sform  -> addElement(new XoopsFormFile(_AM_XTORRENT_FILE_DUPLOAD, 'userfile', 0));
        $mytree = new XoopsTree($xoopsDB -> prefix('xtorrent_cat'), 'cid', 'pid');
        ob_start();
        //$sform -> addElement(new XoopsFormHidden('cid', $cid));
        $mytree -> makeMySelBox('title', 'title', $cid, 0);
        $sform  -> addElement(new XoopsFormLabel(_AM_XTORRENT_FILE_CATEGORY, ob_get_contents()));
        ob_end_clean();

        $sform -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_HOMEPAGETITLE, 'homepagetitle', 50, 255, $homepagetitle), false);
        $sform -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_HOMEPAGE, 'homepage', 50, 255, $homepage), false);
        $sform -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_VERSION, 'version', 10, 20, $version), false);
        $sform -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_PUBLISHER, 'publisher', 50, 255, $publisher), false);
        $sform -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_SIZE, 'size', 10, 20, $size), false);

        $platform_array  = $xoopsModuleConfig['platform'];
        $platform_select = new XoopsFormSelect('', 'platform', $platform, '', '', 0);
        $platform_select -> addOptionArray($platform_array);
        $platform_tray   = new XoopsFormElementTray(_AM_XTORRENT_FILE_PLATFORM, '&nbsp;');
        $platform_tray   -> addElement($platform_select);
        $sform           -> addElement($platform_tray);

        $license_array  = $xoopsModuleConfig['license'];
        $license_select = new XoopsFormSelect('', 'license', $license, '', '', 0);
        $license_select -> addOptionArray($license_array);
        $license_tray   = new XoopsFormElementTray(_AM_XTORRENT_FILE_LICENCE, '&nbsp;');
        $license_tray   -> addElement($license_select);
        $sform          -> addElement($license_tray);

        $limitations_array  = $xoopsModuleConfig['limitations'];
        $limitations_select = new XoopsFormSelect('', 'limitations', $limitations, '', '', 0);
        $limitations_select -> addOptionArray($limitations_array);
        $limitations_tray   = new XoopsFormElementTray(_AM_XTORRENT_FILE_LIMITATIONS, '&nbsp;');
        $limitations_tray   -> addElement($limitations_select);
        $sform              -> addElement($limitations_tray);

        $sform -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_PRICE, 'price', 10, 20, $price), false);

        $sform -> addElement(new XoopsFormDhtmlTextArea(_AM_XTORRENT_FILE_DESCRIPTION, 'description', $description, 15, 60), true);
        $sform -> addElement(new XoopsFormTextArea(_AM_XTORRENT_FILE_KEYFEATURES, 'features', $features, 7, 60), false);
        $sform -> addElement(new XoopsFormTextArea(_AM_XTORRENT_FILE_REQUIREMENTS, 'requirements', $requirements, 7, 60), false);
        $sform -> addElement(new XoopsFormTextArea(_AM_XTORRENT_FILE_HISTORY, 'dhistory', $dhistory, 7, 60), false);
        if ($lid && !empty($dhistory))
        {
            $sform -> addElement(new XoopsFormTextArea(_AM_XTORRENT_FILE_HISTORYD, 'dhistoryaddedd', "", 7, 60), false);
        } 
        $graph_array       = XtsLists::getListTypeAsArray(XOOPS_ROOT_PATH . '/' . $xoopsModuleConfig['screenshots'], $type = 'images');
        $indeximage_select = new XoopsFormSelect('', 'screenshot', $screenshot);
        $indeximage_select -> addOptionArray($graph_array);
        $indeximage_select -> setExtra("onchange='showImgSelected(\"image\", \"screenshot\", \"" . $xoopsModuleConfig['screenshots'] . '", "", "' . XOOPS_URL . "\")'");
        $indeximage_tray   = new XoopsFormElementTray(_AM_XTORRENT_FILE_SHOTIMAGE, '&nbsp;');
        $indeximage_tray   -> addElement($indeximage_select);
        if (!empty($imgurl))
        {
            $indeximage_tray -> addElement(new XoopsFormLabel('', "<br><br><img src='" . XOOPS_URL . '/' . $xoopsModuleConfig['screenshots'] . '/' . $screenshot . "' name='image' id='image' alt=''>"));
        } 
        else
        {
            $indeximage_tray -> addElement(new XoopsFormLabel('', "<br><br><img src='" . XOOPS_URL . "/uploads/blank.gif' name='image' id='image' alt=''>"));
        } 
        $sform -> addElement($indeximage_tray);

        $sform -> insertBreak(sprintf(_AM_XTORRENT_FILE_MUSTBEVALID, '<b>' . $directory . '</b>'), 'even');

        ob_start();
        xtorrent_getforum($forumid);
        $sform -> addElement(new XoopsFormLabel(_AM_XTORRENT_FILE_DISCUSSINFORUM, ob_get_contents()));

        ob_end_clean();

        $publishtext = (!$lid && !$published) ? _AM_XTORRENT_FILE_SETPUBLISHDATE : _AM_XTORRENT_FILE_SETNEWPUBLISHDATE;
        if ($published > time())
        {
            $publishtext = _AM_XTORRENT_FILE_SETPUBDATESETS;
        } 
        $ispublished          = ($published > time()) ? 1 : 0;
        $publishdates         = ($published > time()) ? _AM_XTORRENT_FILE_PUBLISHDATESET . formatTimestamp($published, 'Y-m-d H:s') : _AM_XTORRENT_FILE_SETDATETIMEPUBLISH;
        $publishdate_checkbox = new XoopsFormCheckBox('', 'publishdateactivate', $ispublished);
        $publishdate_checkbox -> addOption(1, $publishdates . '<br><br>');

        if ($lid)
        {
            $sform -> addElement(new XoopsFormHidden('was_published', $published));
            $sform -> addElement(new XoopsFormHidden('was_expired', $expired));
        } 

        $publishdate_tray = new XoopsFormElementTray(_AM_XTORRENT_FILE_PUBLISHDATE, '');
        $publishdate_tray -> addElement($publishdate_checkbox);
        $publishdate_tray -> addElement(new XoopsFormDateTime($publishtext, 'published', 15, $published));
        $publishdate_tray -> addElement(new XoopsFormRadioYN(_AM_XTORRENT_FILE_CLEARPUBLISHDATE, 'clearpublish', 0, ' ' . _YES . '', ' ' . _NO . ''));
        $sform            -> addElement($publishdate_tray);

        $isexpired           = ($expired > time()) ? 1: 0;
        $expiredates         = ($expired > time()) ? _AM_XTORRENT_FILE_EXPIREDATESET . formatTimestamp($expired, 'Y-m-d H:s') : _AM_XTORRENT_FILE_SETDATETIMEEXPIRE;
        $warning             = ($published > $expired && $expired > time()) ? _AM_XTORRENT_FILE_EXPIREWARNING : '';
        $expiredate_checkbox = new XoopsFormCheckBox('', 'expiredateactivate', $isexpired);
        $expiredate_checkbox -> addOption(1, $expiredates . '<br><br>');

        $expiredate_tray = new XoopsFormElementTray(_AM_XTORRENT_FILE_EXPIREDATE . $warning, '');
        $expiredate_tray -> addElement($expiredate_checkbox);
        $expiredate_tray -> addElement(new XoopsFormDateTime(_AM_XTORRENT_FILE_SETEXPIREDATE . '<br>', 'expired', 15, $expired));
        $expiredate_tray -> addElement(new XoopsFormRadioYN(_AM_XTORRENT_FILE_CLEAREXPIREDATE, 'clearexpire', 0, ' ' . _YES . '', ' ' . _NO . ''));
        $sform           -> addElement($expiredate_tray);

        $filestatus_radio = new XoopsFormRadioYN(_AM_XTORRENT_FILE_FILESSTATUS, 'offline', $offline, ' ' . _YES . '', ' ' . _NO . '');
        $sform            -> addElement($filestatus_radio);

        $up_dated           = (0 == $updated) ? 0 : 1;
        $file_updated_radio = new XoopsFormRadioYN(_AM_XTORRENT_FILE_SETASUPDATED, 'up_dated', $up_dated, ' ' . _YES . '', ' ' . _NO . '');
        $sform              -> addElement($file_updated_radio);

        $sform            -> insertBreak(_AM_XTORRENT_FILE_CREATENEWSSTORY, 'bg3');
        $submitNews_radio = new XoopsFormRadioYN(_AM_XTORRENT_FILE_SUBMITNEWS, 'submitNews', 0, ' ' . _YES . '', ' ' . _NO . '');
        $sform            -> addElement($submitNews_radio);

        include_once XOOPS_ROOT_PATH . '/class/xoopstopic.php';
        $xt = new XoopsTopic($xoopsDB -> prefix('topics'));

        ob_start();
        $xt    -> makeTopicSelBox(0, 0, 'newstopicid');
        $sform -> addElement(new XoopsFormLabel(_AM_XTORRENT_FILE_NEWSCATEGORY, ob_get_contents()));

        ob_end_clean();
        $sform -> addElement(new XoopsFormText(_AM_XTORRENT_FILE_NEWSTITLE, 'newsTitle', 50, 255, ''), false);

        if ($lid && 0 == $published)
        {
            $approved         = (0 == $published) ? 0 : 1;
            $approve_checkbox = new XoopsFormCheckBox(_AM_XTORRENT_FILE_EDITAPPROVE, 'approved', 1);
            $approve_checkbox -> addOption(1, ' ');
            $sform            -> addElement($approve_checkbox);
        } 

        if (!$lid)
        {
            $button_tray = new XoopsFormElementTray('', '');
            $button_tray -> addElement(new XoopsFormHidden('status', 1));
            $button_tray -> addElement(new XoopsFormHidden('notifypub', $notifypub));
            $button_tray -> addElement(new XoopsFormHidden('op', 'addDownload'));
            $button_tray -> addElement(new XoopsFormButton('', '', _AM_XTORRENT_BSAVE, 'submit'));
            $sform       -> addElement($button_tray);
        } 
        else
        {
            $button_tray = new XoopsFormElementTray('', '');
            $button_tray -> addElement(new XoopsFormHidden('lid', $lid));
            $button_tray -> addElement(new XoopsFormHidden('status', 2));
            $hidden      = new XoopsFormHidden('op', 'addDownload');
            $button_tray -> addElement($hidden);

            $butt_dup    = new XoopsFormButton('', '', _AM_XTORRENT_BMODIFY, 'submit');
            $butt_dup    -> setExtra('onclick="this.form.elements.op.value=\'addDownload\'"');
            $button_tray -> addElement($butt_dup);

            $butt_dupct  = new XoopsFormButton('', '', _AM_XTORRENT_BDELETE, 'submit');
            $butt_dupct  -> setExtra('onclick="this.form.elements.op.value=\'delDownload\'"');
            $button_tray -> addElement($butt_dupct);

            $butt_dupct2 = new XoopsFormButton('', '', _AM_XTORRENT_BCANCEL, 'submit');
            $butt_dupct2 -> setExtra('onclick="this.form.elements.op.value=\'downloadsConfigMenu\'"');
            $button_tray -> addElement($butt_dupct2);
            $sform       -> addElement($button_tray);
        } 
        $sform -> display();
        unset($hidden);
    } 
    else
    {
        redirect_header('category.php?', 1, _AM_XTORRENT_CCATEGORY_NOEXISTS);
        exit();
    } 

    if ($lid)
    {
        global $imagearray; 
        // Vote data
        $result01         = $xoopsDB -> query('SELECT COUNT(*) FROM '
                                              . $xoopsDB -> prefix('xtorrent_votedata')
                                              . ' ');
        list($totalvotes) = $xoopsDB -> fetchRow($result01);

        $result02  = $xoopsDB -> query('SELECT ratingid, ratinguser, rating, ratinghostname, ratingtimestamp FROM '
                                       . $xoopsDB -> prefix('xtorrent_votedata')
                                       . ' WHERE lid = '
                                       . $lid
                                       . ' AND ratinguser != 0 ORDER BY ratingtimestamp DESC');
        $votesreg  = $xoopsDB -> getRowsNum($result02);
        $result03  = $xoopsDB -> query('SELECT ratingid, ratinguser, rating, ratinghostname, ratingtimestamp FROM '
                                       . $xoopsDB -> prefix('xtorrent_votedata')
                                       . ' WHERE lid = '
                                       . $lid
                                       . ' AND ratinguser = 0 ORDER BY ratingtimestamp DESC');
        $votesanon = $xoopsDB -> getRowsNum($result03);

        echo "<fieldset><legend style='font-weight:bold;color:#900;'>" . _AM_XTORRENT_VOTE_RATINGINFOMATION . "</legend>
		          <div style='padding:8px;'><b>" . _AM_XTORRENT_VOTE_TOTALVOTES . "</b>" . $totalvotes . "<br><br>";

        printf(_AM_XTORRENT_VOTE_REGUSERVOTES, $votesreg);

        echo '<br>';

        printf(_AM_XTORRENT_VOTE_ANONUSERVOTES, $votesanon);

        echo "</div>
              <table class='outer' style='width:100%;'>
              <tr>
   	          <th style='text-align:center;'>" . _AM_XTORRENT_VOTE_USER . "</th>
   	          <th style='text-align:center;'>" . _AM_XTORRENT_VOTE_IP . "</th>
              <th style='text-align:center;'>" . _AM_XTORRENT_VOTE_RATING . "</th>
              <th style='text-align:center;'>" . _AM_XTORRENT_VOTE_USERAVG . "</th>
              <th style='text-align:center;'>" . _AM_XTORRENT_VOTE_TOTALRATE . "</th>
              <th style='text-align:center;'>" . _AM_XTORRENT_VOTE_DATE . "</th>
              <th style='text-align:center;'>" . _AM_XTORRENT_MINDEX_ACTION . '</th>
              </tr>';

        if (0 == $votesreg)
        {
            echo "<tr><td colspan='7' class='even' style='text-align:center;'><b>" . _AM_XTORRENT_VOTE_NOREGVOTES . '</b></td></tr>';
        } 
        while (list($ratingid, $ratinguser, $rating, $ratinghostname, $ratingtimestamp) = $xoopsDB -> fetchRow($result02))
        {
            $result04       = $xoopsDB -> query('SELECT rating FROM '
                                                . $xoopsDB -> prefix('xtorrent_votedata')
                                                . ' WHERE ratinguser = '
                                                . $ratinguser);
            $uservotes      = $xoopsDB -> getRowsNum($result04);
            $formatted_date = formatTimestamp($ratingtimestamp, $xoopsModuleConfig['dateformat']);
            $useravgrating  = 0;
            while (list($rating2) = $xoopsDB -> fetchRow($result04))
            {
                $useravgrating = $useravgrating + $rating2;
            } 
            $useravgrating = $useravgrating / $uservotes;
            $useravgrating = number_format($useravgrating, 1);
            $ratinguname   = XoopsUser::getUnameFromId($ratinguser);

            echo "<tr>
                  <td class='head' style='text-align:center;'>" . $ratinguname . "</td>
              		<td class='even' style='text-align:center;'>" . $ratinghostname . "</td>
              		<td class='even' style='text-align:center;'>" . $rating . "</td>
              		<td class='even' style='text-align:center;'>" . $useravgrating . "</td>
              		<td class='even' style='text-align:center;'>" . $uservotes . "</td>
              		<td class='even' style='text-align:center;'>" . $formatted_date . "</td>
              		<td class='even' style='text-align:center;'>
              		<a href='index.php?op=delVote&amp;lid=" . $lid . '&amp;rid=' . $ratingid . "'>" . $imagearray['deleteimg'] . '</a></td>
                  </tr>';
        } 
        echo "</table><br>
          		<table class='outer' style='width:100%;'>
          		<tr>
          		<th style='text-align:center;'>" . _AM_XTORRENT_VOTE_USER . "</th>
          		<th style='text-align:center;'>" . _AM_XTORRENT_VOTE_IP . "</th>
          		<th style='text-align:center;'>" . _AM_XTORRENT_VOTE_RATING . "</th>
          		<th style='text-align:center;'>" . _AM_XTORRENT_VOTE_USERAVG . "</th>
          		<th style='text-align:center;'>" . _AM_XTORRENT_VOTE_TOTALRATE . "</th>
          		<th style='text-align:center;'>" . _AM_XTORRENT_VOTE_DATE . "</th>
          		<th style='text-align:center;'>" . _AM_XTORRENT_MINDEX_ACTION . '</th>
          		</tr>';

        if (0 == $votesanon)
        {            
            echo "<tr><td colspan='7' class='even' style='text-align:center;'><b>" . _AM_XTORRENT_VOTE_NOUNREGVOTES . '</b></td></tr>';
        } 
        while (list($ratingid, $ratinguser, $rating, $ratinghostname, $ratingtimestamp) = $xoopsDB -> fetchRow($result03))
        {
            $result05       = $xoopsDB -> query('SELECT rating FROM '
                                                . $xoopsDB -> prefix('xtorrent_votedata')
                                                . ' WHERE ratinguser = '
                                                . $ratinguser);
            $uservotes      = $xoopsDB -> getRowsNum($result05);
            $formatted_date = formatTimestamp($ratingtimestamp, $xoopsModuleConfig['dateformat']);
            $useravgrating  = 0;
            while (list($rating2) = $xoopsDB -> fetchRow($result04))
            {
                $useravgrating += $rating2;
            } 
            $useravgrating /= $uservotes;
            $useravgrating = number_format($useravgrating, 1);
            $ratinguname   = XoopsUser::getUnameFromId($ratinguser);

            echo "<tr>
                  <td class='head' style='text-align:center;'>" . $ratinguname . "</td>
              		<td class='even' style='text-align:center;'>" . $ratinghostname . "</td>
              		<td class='even' style='text-align:center;'>" . $rating . "</td>
              		<td class='even' style='text-align:center;'>" . $useravgrating . "</td>
              		<td class='even' style='text-align:center;'>" . $uservotes . "</td>
              		<td class='even' style='text-align:center;'>" . $formatted_date . "</td>
              		<td class='even' style='text-align:center;'>
              		<a href='index.php?op=delVote&amp;lid=" . $lid . '&amp;rid=' . $ratingid . "'>" . $imagearray['deleteimg'] . '</a></td>
                  </tr>';
        } 
        echo '</table>';
    }
    echo '</fieldset>'; 
    require_once __DIR__ . '/admin_footer.php';
} 

function delVote()
{
    global $xoopsDB, $_GET;
    $xoopsDB -> queryF('DELETE FROM ' . $xoopsDB -> prefix('mydownloads_votedata') . ' WHERE ratingid = ' . $_GET['rid']);
    xtorrent_updaterating(intval($_GET['lid']));
    redirect_header('index.php', 1, _AM_XTORRENT_VOTE_VOTEDELETED);
} 

function addDownload()
{
    global $xoopsDB, $xoopsUser, $xoopsModule, $myts, $_FILES, $xoopsModuleConfig;

    $groups = isset($_POST['groups']) ? $_POST['groups'] : [];
    $lid    = !empty($_POST['lid']) ? $_POST['lid'] : 0;
    $cid    = !empty($_POST['cid']) ? $_POST['cid'] : 0;
    $status = !empty($_POST['status']) ? $_POST['status'] : 2;
    /**
     * Define URL
     */
    if (empty($_FILES['userfile']['name']) && $_POST["url"] && '' != $_POST['url'] && 'https://' != $_POST['url'])
    {
        $url   = ('https://' != $_POST['url']) ? $myts -> addslashes($_POST['url']) : '';
        $size  = empty($size) || !is_numeric($size) ? $myts -> addslashes($_POST['size']) : 0;
        $title = $myts -> addslashes(trim($_POST['title']));
    } 
    else
    {
        global $_FILES;
        $down  = xtorrent_uploading($_FILES, $xoopsModuleConfig['uploaddir'], '', 'index.php', 0, 0);
        $url   = $myts -> addslashes ($down['url']);
        $size  = $down['size'];
        $title = $_FILES['userfile']['name'];
        $ext   = rtrim(strrchr($title, '.'), '.');
        $title = str_replace($ext, '', $title);
        $title = (isset($_POST['title_checkbox']) && 1 == $_POST['title_checkbox']) ? $title : $myts -> addslashes(trim($_POST['title']));
    } 
    /**
     * Get data from form
     */
    $screenshot    = ('blank.png' != $_POST['screenshot']) ? $myts -> addslashes($_POST['screenshot']) : '';

    $homepage      = '';
    $homepagetitle = '';

    if (!empty($_POST['homepage']) || 'https://' != $_POST['homepage'])
    {
        $homepage      = $myts -> addslashes(trim($_POST['homepage']));
        $homepagetitle = $myts -> addslashes(trim($_POST['homepagetitle']));
    } 

    //$topic_tags      = $myts->addslashes(trim($_POST['topic_tags']));
    $version         = !empty($_POST["version"]) ? $myts -> addslashes(trim($_POST["version"])) : 0;
    $platform        = $myts -> addslashes(trim($_POST['platform']));
    $description     = $myts -> addslashes(trim($_POST['description']));
    $submitter       = $xoopsUser -> uid();
    $publisher       = $myts -> addslashes(trim($_POST['publisher']));
    $price           = $myts -> addslashes(trim($_POST['price']));
    $mirror          = formatURL(trim($_POST['mirror']));
    $license         = $myts -> addslashes(trim($_POST['license']));
    //$paypalemail     = $myts->addslashes(trim($_POST['paypalemail']));
    $paypalemail     = '';
    $features        = $myts -> addslashes(trim($_POST['features']));
    $requirements    = $myts -> addslashes(trim($_POST['requirements']));
    $forumid         = (isset($_POST["forumid"]) && $_POST['forumid'] > 0) ? (int)$_POST["forumid"] : 0;
    $limitations     = isset($_POST['limitations']) ? $myts -> addslashes($_POST['limitations']) : '';
    $dhistory        = isset($_POST['dhistory']) ? $myts -> addslashes($_POST['dhistory']) : '';
    $dhistoryhistory = isset($_POST['dhistoryaddedd']) ? $myts -> addslashes($_POST['dhistoryaddedd']) : '';
    if ($lid > 0 && !empty($dhistoryhistory))
    {
        $dhistory .= '\n\n';
        $time      = time();
        $dhistory .= _AM_XTORRENT_FILE_HISTORYVERS . $version . _AM_XTORRENT_FILE_HISTORDATE . formatTimestamp($time, $xoopsModuleConfig['dateformat']) . '\n\n';
        $dhistory .= $dhistoryhistory;
    } 
    $updated = (isset($_POST['was_published']) && $_POST['was_published'] == 0) ? 0 : time();
	
	if (0 == $_POST['up_dated']) {
	    $updated = 0;
		  $status  = 1; 
	}
	
	  $offline   = (1 == $_POST['offline']) ? 1 : 0;
    $approved  = (isset($_POST['approved']) && 1 == $_POST['approved']) ? 1 : 0;
    $notifypub = (isset($_POST['notifypub']) && 1 == $_POST['notifypub']);

    if (!$lid)
    {
        $date        = time();
        $publishdate = time();
    } 
    else
    {
        $publishdate = $_POST['was_published'];
        $expiredate  = $_POST['was_expired'];
    } 

    if (1 == $approved && empty($publishdate))
    {
        $publishdate = time();
    } 

    if (isset($_POST['publishdateactivate']))
    {
        $publishdate = strtotime($_POST['published']['date']) + $_POST['published']['time'];
    } 
    if ($_POST['clearpublish'])
    {
        $result      = $xoopsDB -> query('SELECT date FROM ' . $xoopsDB -> prefix('xtorrent_downloads') . ' WHERE lid=' . $lid);
        list($date)  = $xoopsDB -> fetchRow($result);
        $publishdate = $date;
    } 

    if (isset($_POST['expiredateactivate']))
    {
        $expiredate = strtotime($_POST['expired']['date']) + $_POST['expired']['time'];
    } 
    if ($_POST['clearexpire'])
    {
        $expiredate = '0';
    } 
    /**
     * Update or insert download data into database
     */
    if (!$lid)
    {
        $date        = time();
        $publishdate = time();
        $ipaddress   = $_SERVER['REMOTE_ADDR'];

        $query  = 'INSERT INTO '
                 . $xoopsDB -> prefix('xtorrent_downloads')
                 . '(lid, cid, title, url, homepage, version, size, platform, screenshot, submitter, publisher, status,	date, hits, rating, votes, comments, price, mirror, license, paypalemail, features, requirements,	homepagetitle, forumid, limitations, dhistory, published, expired, updated, offline, description, ipaddress, notifypub)';
        $query .= " VALUES 	('', "
                 . $cid
                 . ", '"
                 . $title
                 . "', '"
                 . $url
                 . "', '"
                 . $homepage
                 . "', '"
                 . $version
                 . "', "
                 . $size
                 . ", '"
                 . $platform
                 . "', '"
                 . $screenshot
                 . "', '"
                 . $submitter
                 . "', '"
                 . $publisher
                 . "', '"
                 . $status
                 . "', '"
                 . $date
                 . "', 0, 0, 0, 0, '"
                 . $price
                 . "', '"
                 . $mirror
                 . "', '"
                 . $license
                 . "', '"
                 . $paypalemail
                 . "', '"
                 . $features
                 . "', '"
                 . $requirements
                 . "', '"
                 . $homepagetitle
                 . "', '"
                 . $forumid
                 . "', '"
                 . $limitations
                 . "', '"
                 . $dhistory
                 . "', '"
                 . $publishdate
                 . "', 0, '"
                 . $updated
                 . "', '"
                 . $offline
                 . "', '"
                 . $description
                 . "', '"
                 . $ipaddress
                 . "', '0')";
        $result = $xoopsDB -> queryF($query);
        $error  = _AM_XTORRENT_SAVED_DATA_NOT . ':<br><br>';
        $error .= $query;
        if (!$result)
        {
            trigger_error($error, E_USER_ERROR);
        } 
        $newid = $xoopsDB -> getInsertId();
        xtorrent_save_Permissions($groups, $newid, 'xtorrentownFilePerm');

		//$tag_handler = xoops_getModuleHandler('tag', 'tag');
		//$tag_handler->updateByItem($_POST['topic_tags'], $newid, 'xtorrent', $cid);

		//echo "Please wait a moment while we poll the torrent...";
		error_reporting(E_ALL);
		include __DIR__ . '/include/pollall.php';
		
		$rt = poll_torrent($newid);
				
		if ($xoopsModuleConfig['poll_tracker']==1){
			$rt = poll_tracker($rt, $newid, $xoopsModuleConfig['poll_tracker_timeout']);
		}
    } 
    else
    {
      $xoopsDB -> query('UPDATE '
                        . $xoopsDB -> prefix('xtorrent_downloads')
                        . " SET cid = "
                        . $cid
                        . ", title = '"
                        . $title
                        . "', url = '"
                        . $url
                        . "', mirror = '"
                        . $mirror
                        . "', paypalemail = '"
                        . $paypalemail
                        . "', license = '"
                        . $license
                        . "', features = '"
                        . $features
                        . "', homepage = '"
                        . $homepage
                        . "', version = '"
                        . $version
                        . "', size = "
                        . $size
                        . ", platform = '"
                        . $platform
                        . "', screenshot = '"
                        . $screenshot
                        . "', publisher = '"
                        . $publisher
                        . "', status = '"
                        . $status
                        . "', price = '"
                        . $price
                        . "', requirements = '"
                        . $requirements
                        . "',	homepagetitle = '"
                        . $homepagetitle
                        . "', forumid = '"
                        . $forumid
                        . "', limitations = '"
                        . $limitations
                        . "', dhistory = '"
                        . $dhistory
                        . "', published = '"
                        . $publishdate
                        . "',	expired = '"
                        . $expiredate
                        . "', updated = '"
                        . $updated
                        . "', offline = '"
                        . $offline
                        . "', description = '"
                        . $description
                        . "' WHERE lid = "
                        . $lid);
        //$tagHandler = xoops_getModuleHandler('tag', 'tag');
        //$tagHandler->updateByItem($_POST['topic_tags'], $lid, 'xtorrent', $cid);
        xtorrent_save_Permissions($groups, $lid, 'xtorrentownFilePerm');
    } 
    /**
     * Send notifications
     */
    if (!$lid)
    {
        $tags                  = [];
        $tags['FILE_NAME']     = $title;
        $tags['FILE_URL']      = XOOPS_URL . '/modules/xtorrent/singlefile.php?cid=' . $cid . '&amp;lid=' . $newid;
        $sql                   = 'SELECT title FROM ' . $xoopsDB -> prefix('xtorrent_cat') . ' WHERE cid=' . $cid;
        $result                = $xoopsDB -> query($sql);
        $row                   = $xoopsDB -> fetchArray($xoopsDB -> query($sql));
        $tags['CATEGORY_NAME'] = $row['title'];
        $tags['CATEGORY_URL']  = XOOPS_URL . '/modules/xtorrent/viewcat.php?cid=' . $cid;
        $notificationHandler  = xoops_getHandler('notification');
        $notificationHandler  -> triggerEvent('global', 0, 'new_file', $tags);
        $notificationHandler  -> triggerEvent('category', $cid, 'new_file', $tags);
    } 
    if ($lid && $approved && $notifypub)
    {
        $tags                  = [];
        $tags['FILE_NAME']     = $title;
        $tags['FILE_URL']      = XOOPS_URL . '/modules/xtorrent/singlefile.php?cid=' . $cid . '&amp;lid=' . $lid;
        $sql                   = 'SELECT title FROM ' . $xoopsDB -> prefix('mydownloads_cat') . ' WHERE cid=' . $cid;
        $result                = $xoopsDB -> query($sql);
        $row                   = $xoopsDB -> fetchArray($result);
        $tags['CATEGORY_NAME'] = $row['title'];
        $tags['CATEGORY_URL']  = XOOPS_URL . '/modules/xtorrent/viewcat.php?cid=' . $cid;
        $notificationHandler  = xoops_gethandler('notification');
        $notificationHandler  -> triggerEvent('global', 0, 'new_file', $tags);
        $notificationHandler  -> triggerEvent('category', $cid, 'new_file', $tags);
        $notificationHandler  -> triggerEvent('file', $lid, 'approve', $tags);
    } 
    $message = (!$lid) ? _AM_XTORRENT_FILE_NEWFILEUPLOAD : _AM_XTORRENT_FILE_FILEMODIFIEDUPDATE ;
    $message = ($lid && !$_POST['was_published'] && $approved) ? _AM_XTORRENT_FILE_FILEAPPROVED : $message;
    if ($_POST['submitNews'] == 1)
    {
        $title = (!empty($_POST['newsTitle'])) ? $_POST['newsTitle'] : $title;
        include_once __DIR__ . '/newstory.php';
    } 
    redirect_header('index.php', 1, $message);
} 
// Page start here
if (isset($_POST))
{
    foreach ($_POST as $k => $v)
    {
        $$k = $v;
    } 
} 

if (isset($_GET))
{
    foreach ($_GET as $k => $v)
    {
        $$k = $v;
    } 
} 

if (!isset($_POST['op']))
{
    $op = isset($_GET['op']) ? $_GET['op'] : 'main';
} 
else
{
    $op = $_POST['op'];
} 

switch ($op)
{
    //Download page
    case 'addDownload':
        addDownload();
        break;

    //Download page
    case 'Download':
        Download();
        break;

    //Del page
    case 'delDownload':
        global $xoopsDB, $_POST, $xoopsModule, $xoopsModuleConfig;
        $confirm = (isset($confirm)) ? 1 : 0;
        if ($confirm)
        {
            $file = XOOPS_ROOT_PATH . "/" . $xoopsModuleConfig['uploaddir'] . '/' . basename($_POST['url']);
            if (is_file($file))
            {
                @unlink($file);
            } 
            $xoopsDB -> query('DELETE FROM ' . $xoopsDB -> prefix('xtorrent_downloads') . ' WHERE lid = ' . $_POST['lid']);
      			$xoopsDB -> query('DELETE FROM ' . $xoopsDB -> prefix('xtorrent_tracker') . ' WHERE lid = ' . $_POST['lid']);
      			$xoopsDB -> query('DELETE FROM ' . $xoopsDB -> prefix('xtorrent_torrent') . ' WHERE lid = ' . $_POST['lid']);
      			$xoopsDB -> query('DELETE FROM ' . $xoopsDB -> prefix('xtorrent_peers') . ' WHERE torrent = ' . $_POST['lid']);
            $xoopsDB -> query('DELETE FROM ' . $xoopsDB -> prefix('mydownloads_votedata') . ' WHERE lid = ' . $_POST['lid']); 
            // delete comments
            xoops_comment_delete($xoopsModule -> getVar('mid'), $_POST['lid']);
            redirect_header('index.php', 1, sprintf(_AM_XTORRENT_FILE_FILEWASDELETED, $title));
            exit();
        } 
        else
        {
            $lid    = isset($_POST['lid']) ? $_POST['lid'] : $lid;
            $result = $xoopsDB -> query('SELECT lid, title, url FROM ' . $xoopsDB -> prefix('xtorrent_downloads') . ' WHERE lid = ' . $lid);
            list($lid, $title, $url) = $xoopsDB -> fetchrow($result);
            xoops_cp_header();
            $adminObject = \Xmf\Module\Admin::getInstance();
            $adminObject -> displayNavigation(basename(__FILE__));
            xoops_confirm([
                           'op'      => 'delDownload',
                           'lid'     => $lid,
                           'confirm' => 1,
                           'title'   => $title,
                           'url'     => $url
                           ], 'index.php', _AM_XTORRENT_FILE_REALLYDELETEDTHIS . '<br><br>' . $title, _DELETE);
            require_once __DIR__ . '/admin_footer.php';
        } 
        break;

    //Del page Votedata
    case 'delVote':
        delVote();
        break;

    //Del page Reviews
    case 'del_review':
        global $xoopsDB, $_POST, $xoopsModule;
        $confirm = (isset($confirm)) ? 1 : 0;
        if ($confirm)
        {
            $xoopsDB -> query('DELETE FROM '
                               . $xoopsDB -> prefix('xtorrent_reviews')
                               . ' WHERE review_id = '
                               . $_POST['review_id']);
            redirect_header('index.php', 1, sprintf(_AM_XTORRENT_FILE_FILEWASDELETED, $title));
            exit();
        } 
        else
        {
            $review_id = isset($_POST['review_id']) ? $_POST['review_id'] : $review_id;
            $sql       = 'SELECT review_id, title FROM '
                          . $xoopsDB -> prefix('xtorrent_reviews')
                          . ' WHERE review_id = '
                          . $review_id;
            $result    = $xoopsDB -> query($sql);
            list($review_id, $title) = $xoopsDB -> fetchrow($result);
            xoops_cp_header();
            $adminObject = \Xmf\Module\Admin::getInstance();
            $adminObject -> displayNavigation(basename(__FILE__)); 
            xoops_confirm([
                           'op'        => 'del_review',
                           'review_id' => $review_id,
                           'confirm'   => 1,
                           'title'     => $title
                           ], 'index.php', _AM_XTORRENT_FILE_REALLYDELETEDTHIS . '<br><br>' . $title, _AM_XTORRENT_BDELETE);
            require_once __DIR__ . '/admin_footer.php';
        } 
        break;

    //Approve page Reviews
    case 'approve_review':
        global $xoopsDB;
        $review_id = isset($_GET['review_id']) ? (int)$_GET['review_id'] : 0;
        $sql       = 'UPDATE ' . $xoopsDB -> prefix('xtorrent_reviews') . " SET submit = 1 WHERE review_id = '" . $review_id . "'";
        $result    = $xoopsDB -> queryF($sql);
        $error     = "<a href='javascript:history.go(-1)'>" . _AM_XTORRENT_BRETURN . '</a><br><br>';
        $error    .= _AM_XTORRENT_REVIEW_DATA_NOT . ':<br><br>';
        $error    .= $sql;
        if (!$result)
        {
            trigger_error($error, E_USER_ERROR);
        } 
        redirect_header('index.php?op=reviews', 1, _AM_XTORRENT_REV_REVIEW_UPDATED);
        break;

    //Edit page Reviews
    case 'edit_review':
        $confirm = isset($confirm) ? 1 : 0;
        if ($confirm)
        {
            $review_id = (int)$_POST['review_id'];
            $title     = $myts -> addSlashes(trim($_POST['title']));
            $review    = $myts -> addSlashes(trim($_POST['review']));
            $rated     = (int)$_POST['rated'];
            $submit    = (int)$_POST['approve'];
            $xoopsDB   -> queryF('UPDATE '
                                  . $xoopsDB -> prefix('xtorrent_reviews')
                                  . " SET title = '"
                                  . $title
                                  . "', review = '"
                                  . $review
                                  . "', rated = '"
                                  . $rated
                                  . "', submit = '"
                                  . $submit
                                  . "' WHERE review_id = '"
                                  . $review_id
                                  . "'");
            redirect_header('index.php', 1, _AM_XTORRENT_REV_REVIEW_UPDATED);
            exit();
        } 
        else
        {
            $sql = 'SELECT * FROM ' . $xoopsDB -> prefix('xtorrent_reviews') . ' WHERE review_id = ' . $_GET['review_id'] . '';
            $arr = $xoopsDB -> fetchArray($xoopsDB -> query($sql));

            xoops_cp_header();
            $adminObject = \Xmf\Module\Admin::getInstance();
            $adminObject -> displayNavigation(basename(__FILE__));

            $sform         = new XoopsThemeForm(_AM_XTORRENT_REV_SNEWMNAMEDESC, 'reviewform', xoops_getenv('PHP_SELF'));
            $sform         -> addElement(new XoopsFormText(_AM_XTORRENT_REV_FTITLE, 'title', 30, 40, $arr['title']), true);
            $rating_select = new XoopsFormSelect(_AM_XTORRENT_REV_FRATING, 'rated', $arr['rated']);
            $rating_select -> addOptionArray([
                                              '1'  => 1,
                                              '2'  => 2,
                                              '3'  => 3,
                                              '4'  => 4,
                                              '5'  => 5,
                                              '6'  => 6,
                                              '7'  => 7,
                                              '8'  => 8,
                                              '9'  => 9,
                                              '10' => 10
                                              ]);
            $sform         -> addElement($rating_select);
            $sform         -> addElement(new XoopsFormDhtmlTextArea(_AM_XTORRENT_REV_FDESCRIPTION, 'review', $arr['review'], 15, 60), true);

			      $approved         = (0 == $arr['submit']) ? 0 : 1;
            $approve_checkbox = new XoopsFormCheckBox(_AM_XTORRENT_REV_FAPPROVE, 'approve', 1);
            $approve_checkbox -> addOption(1, ' ');
            $sform            -> addElement($approve_checkbox);

			      $sform       -> addElement(new XoopsFormHidden('lid', $arr['lid']));
            $sform       -> addElement(new XoopsFormHidden('review_id', $arr['review_id']));
            $sform       -> addElement(new XoopsFormHidden('confirm', 1));
            $button_tray = new XoopsFormElementTray('', '');
            $hidden      = new XoopsFormHidden('op', 'save');
            $button_tray -> addElement($hidden);

            if (!$arr['lid'])
            {
                $butt_create = new XoopsFormButton('', '', _AM_XTORRENT_BSAVE, 'submit');
                $butt_create -> setExtra('onclick="this.form.elements.op.value=\'edit_review\'"');
                $button_tray -> addElement($butt_create);

                $butt_clear = new XoopsFormButton('', '', _AM_XTORRENT_BRESET, 'reset');
                $button_tray -> addElement($butt_clear);

                $butt_cancel = new XoopsFormButton('', '', _AM_XTORRENT_BCANCEL, 'button');
                $butt_cancel -> setExtra('onclick="history.go(-1)"');
                $button_tray -> addElement($butt_cancel);
            } 
            else
            {
                $butt_create = new XoopsFormButton('', '', _AM_XTORRENT_BSAVE, 'submit');
                $butt_create -> setExtra('onclick="this.form.elements.op.value=\'edit_review\'"');
                $button_tray -> addElement($butt_create);

                $butt_delete = new XoopsFormButton('', '', _AM_XTORRENT_BDELETE, 'submit');
                $butt_delete -> setExtra('onclick="this.form.elements.op.value=\'del_review\'"');
                $button_tray -> addElement($butt_delete);

                $butt_cancel = new XoopsFormButton('', '', _AM_XTORRENT_BCANCEL, 'button');
                $butt_cancel -> setExtra('onclick="history.go(-1)"');
                $button_tray -> addElement($butt_cancel);
            } 
            $sform -> addElement($button_tray);
            $sform -> display();
            require_once __DIR__ . '/admin_footer.php';
        } 
        break;

    //Index page Reviews
    case 'reviews':
        include_once XOOPS_ROOT_PATH . '/class/pagenav.php';
        global $xoopsDB, $myts, $xoopsModuleConfig, $imagearray;
        $start  = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $sql    = 'SELECT * FROM ' . $xoopsDB -> prefix('xtorrent_reviews') . ' WHERE submit = 0 ORDER BY review_id';
        $result = $xoopsDB -> query($sql, $xoopsModuleConfig['admin_perpage'], $start);
        $num    = $xoopsDB -> getRowsNum($result);
        $error  = "<a href='javascript:history.go(-1)'>" . _AM_XTORRENT_BRETURN . '</a><br><br>';
        $error .= _AM_XTORRENT_REVIEW_DATA_NOT . ':<br><br>';
        $error .= $sql;
        if (!$result)
        {
            trigger_error($error, E_USER_ERROR);
        } 

        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject -> displayNavigation(basename(__FILE__));

        echo "<fieldset><legend style='font-weight:bold; color:#900;'>" . _AM_XTORRENT_AREVIEWS_INFO . "</legend>
        			<div style='padding-top:8px;'>" . _AM_XTORRENT_AREVIEWS_WAITING . " " . $num . "<div>
        			<div style='padding-top:8px;'>
              <ul>
        			<li>" . $imagearray['approve'] . ' ' . _AM_XTORRENT_AREVIEWS_APPROVE . '
        			<li>' . $imagearray['editimg'] . ' ' . _AM_XTORRENT_AREVIEWS_EDIT . '
        			<li>' . $imagearray['deleteimg'] . ' ' . _AM_XTORRENT_AREVIEWS_DELETE . "
        			</ul>
              </div><br>
        		  <table class='outer' style='width:100%;'>
        		  <tr>
          		<th style='text-align:center; width:3%;'>" . _AM_XTORRENT_REV_ID . "</th>
          		<th style='width:30%;'>" . _AM_XTORRENT_REV_TITLE . "</th>
          		<th style='text-align:center; width:15%;'>" . _AM_XTORRENT_REV_POSTER . "</th>
          		<th style='text-align:center; width:15%;'>" . _AM_XTORRENT_REV_SUBMITDATE . "</th>
          		<th style='text-align:center; width:7%;'>" . _AM_XTORRENT_REV_ACTION . '</th>
          		</tr>';

        if ($num)
        {
            while ($review_array = $xoopsDB -> fetchArray($result))
            {
                $review_id   = (int)$review_array['review_id'];
				        $sql2        = 'SELECT title FROM ' . $xoopsDB -> prefix('xtorrent_downloads') . ' WHERE lid = ' . $review_array['lid'] . '';
                list($title) = $xoopsDB -> fetchrow($result2 = $xoopsDB -> query($sql2));
				        $title       = $myts -> htmlSpecialChars($title);
                $lid         = intval($review_array['lid']);
                $submitter   = XoopsUserUtility::getUnameFromId($review_array['uid']);
                $datetime    = formatTimestamp($review_array['date'], $xoopsModuleConfig['dateformat']);
                $status      = (intval($review_array['submit'])) ? $approved : "<a href='index.php?op=approve_review&review_id=" . $review_id . "'>" . $imagearray['approve'] . '</a>';
                $modify      = "<a href='index.php?op=edit_review&review_id=" . $review_id . "'>" . $imagearray['editimg'] . '</a>';
                $delete      = "<a href='index.php?op=del_review&review_id=" . $review_id . "'>" . $imagearray['deleteimg'] . '</a>';

        echo "<tr>
          		<td class='head' style='text-align:center;'>" . $review_id . "</td>
          		<td class='even' style='white-space:nowrap;'><a href='index.php?op=Download&amp;lid=" . $lid . "'>" . $title . "</a></td>
          		<td class='even' style='text-align:center; white-space:nowrap;'>" . $submitter . "</td>
          		<td class='even' style='text-align:center;'>" . $datetime . "</td>
          		<td class='even' style='text-align:center; white-space:nowrap;'>" . $status . ' ' . $modify . ' ' . $delete . '</td>
          		</tr>';
            } 
        } 
        else
        {
            echo "<tr><td class='head' colspan='6' style='text-align:center;'>" . _AM_XTORRENT_REV_NOWAITINGREVIEWS . '</td></tr>';
        } 
        echo '</table>';
        $pagenav = new XoopsPageNav($num, $xoopsModuleConfig['admin_perpage'] , $start, 'start');
        echo "<div style='text-align:right;'>" . $pagenav -> renderNav() . '</div></fieldset>';
        require_once __DIR__ . '/admin_footer.php';
        break;

    // Default index page
    case 'main':
    default:
        global $xoopsUser, $xoopsDB, $xoopsConfig;
        include_once XOOPS_ROOT_PATH . '/class/pagenav.php';

        $start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
        $start1 = isset($_GET['start1']) ? (int)$_GET['start1'] : 0;
        $start2 = isset($_GET['start2']) ? (int)$_GET['start2'] : 0;
        $start3 = isset($_GET['start3']) ? (int)$_GET['start3'] : 0;
        $start4 = isset($_GET['start4']) ? (int)$_GET['start4'] : 0;
        
        $totalcats = xtorrent_totalcategory();
        
        $result  = $xoopsDB -> query('SELECT COUNT(*) FROM ' . $xoopsDB -> prefix('xtorrent_broken') . '');
        list($totalbrokendownloads) = $xoopsDB -> fetchRow($result);
        $result2 = $xoopsDB -> query('SELECT COUNT(*) FROM ' . $xoopsDB -> prefix('xtorrent_mod') . '');
        list($totalmodrequests) = $xoopsDB -> fetchRow($result2);
        $result3 = $xoopsDB -> query('SELECT COUNT(*) FROM ' . $xoopsDB -> prefix('xtorrent_downloads') . ' WHERE published = 0');
        list($totalnewdownloads) = $xoopsDB -> fetchRow($result3);
        $result4 = $xoopsDB -> query('SELECT COUNT(*) FROM ' . $xoopsDB -> prefix('xtorrent_downloads') . ' WHERE published > 0');
        list($totaldownloads) = $xoopsDB -> fetchRow($result4);
        $result5 = $xoopsDB -> query('SELECT COUNT(*) FROM ' . $xoopsDB -> prefix('xtorrent_reviews') . ' WHERE submit = 0');
        list($newreviews) = $xoopsDB -> fetchRow($result5);

        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();

        $adminObject -> addInfoBox(_AM_XTORRENT_MINDEX_DOWNSUMMARY);
        $adminObject -> addInfoBoxLine("<a href='category.php'>" . _AM_XTORRENT_SCATEGORY . '</a> ' . $totalcats);
        $adminObject -> addInfoBoxLine("<a href='#'>" . _AM_XTORRENT_SFILES . '</a> ' . $totaldownloads);
        $adminObject -> addInfoBoxLine("<a href='newdownloads.php'>" . _AM_XTORRENT_SNEWFILESVAL . '</a> ' . $totalnewdownloads);
        $adminObject -> addInfoBoxLine("<a href='modifications.php'>" . _AM_XTORRENT_SMODREQUEST . '</a> ' . $totalmodrequests);
        $adminObject -> addInfoBoxLine("<a href='brokendown.php'>" . _AM_XTORRENT_SBROKENSUBMIT . '</a> ' . $totalbrokendownloads);
        $adminObject -> addInfoBoxLine("<a href='index.php?op=reviews'>" . _AM_XTORRENT_SREVIEWS . '</a> ' . $newreviews);
        $adminObject -> displayNavigation(basename(__FILE__));
        $adminObject -> displayButton('left');
        $adminObject -> displayIndex();

        if ($totalcats > 0)
        {
            include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

            $mytree     = new XoopsTree($xoopsDB -> prefix('xtorrent_cat'), 'cid', 'pid');
            $sform      = new XoopsThemeForm(_AM_XTORRENT_CCATEGORY_MODIFY, 'category', 'category.php');
            ob_start();
            $sform      -> addElement(new XoopsFormHidden('cid', ''));
            $mytree     -> makeMySelBox('title', 'title');
            $sform      -> addElement(new XoopsFormLabel(_AM_XTORRENT_CCATEGORY_MODIFY_TITLE, ob_get_contents()));
            ob_end_clean();
            $dup_tray   = new XoopsFormElementTray('', '');
            $dup_tray   -> addElement(new XoopsFormHidden('op', 'modCat'));
            $butt_dup   = new XoopsFormButton('', '', _AM_XTORRENT_BMODIFY, 'submit');
            $butt_dup   -> setExtra('onclick="this.form.elements.op.value=\'modCat\'"');
            $dup_tray   -> addElement($butt_dup);
            $butt_dupct = new XoopsFormButton('', '', _AM_XTORRENT_BDELETE, 'submit');
            $butt_dupct -> setExtra('onclick="this.form.elements.op.value=\'del\'"');
            $dup_tray   -> addElement($butt_dupct);
            $sform      -> addElement($dup_tray);
            $sform      -> display();
        } 

        if ($totaldownloads > 0)
        {
            $sql                   = 'SELECT * FROM '
                                      . $xoopsDB -> prefix('xtorrent_downloads')
                                      . ' WHERE published > 0 AND published <= '
                                      . time()
                                      . ' AND (expired = 0 OR expired > '
                                      . time()
                                      . ') AND offline = 0 ORDER BY lid DESC';
            $published_array       = $xoopsDB -> query($sql, $xoopsModuleConfig['admin_perpage'], $start);
            $published_array_count = $xoopsDB -> getRowsNum($xoopsDB -> query($sql));

            xtorrent_downlistheader(_AM_XTORRENT_MINDEX_PUBLISHEDDOWN);

            if ($published_array_count > 0)
            {
                while ($published = $xoopsDB -> fetchArray($published_array))
                {
                    xtorrent_downlistbody($published);
                } 
            } 
            else
            {
                xtorrent_downlistfooter();
            } 
            xtorrent_downlistpagenav($published_array_count, $start, 'art');
            /**
             * Auto Publish
             */
            $sql                = 'SELECT * FROM '
                                   . $xoopsDB -> prefix('xtorrent_downloads')
                                   . ' WHERE published > '
                                   . time()
                                   . ' ORDER BY lid DESC';
            $auto_publish_array = $xoopsDB -> query($sql, $xoopsModuleConfig['admin_perpage'], $start2);
            $auto_publish_count = $xoopsDB -> getRowsNum($xoopsDB -> query($sql));

            xtorrent_downlistheader(_AM_XTORRENT_MINDEX_AUTOPUBLISHEDDOWN);

            if ($auto_publish_count > 0)
            {
                while ($auto_publish = $xoopsDB -> fetchArray($auto_publish_array))
                {
                    xtorrent_downlistbody($auto_publish);
                } 
            } 
            else
            {
                xtorrent_downlistfooter();
            } 
            xtorrent_downlistpagenav($auto_publish_count, $start2, 'art2');
            /**
             * Auto expire FAQ
             */
            $sql               = 'SELECT * FROM '
                                  . $xoopsDB -> prefix('xtorrent_downloads')
                                  . ' WHERE expired > '
                                  . time()
                                  . ' ORDER BY lid DESC';
            $auto_expire_array = $xoopsDB -> query($sql, $xoopsModuleConfig['admin_perpage'], $start3);
            $auto_expire_count = $xoopsDB -> getRowsNum($xoopsDB -> query($sql));

            xtorrent_downlistheader(_AM_XTORRENT_MINDEX_AUTOEXPIRE);

            if ($auto_expire_count > 0)
            {
                while ($auto_expire = $xoopsDB -> fetchArray($auto_expire_array))
                {
                    xtorrent_downlistbody($auto_expire);
                } 
            } 
            else
            {
                xtorrent_downlistfooter();
            } 
            xtorrent_downlistpagenav($auto_expire_count, $start3, 'art3');
            /**
             * Offline FAQ
             */
            $sql           = 'SELECT * FROM '
                              . $xoopsDB -> prefix('xtorrent_downloads')
                              . ' WHERE offline = 1 ORDER BY lid DESC';
            $offline_array = $xoopsDB -> query($sql, $xoopsModuleConfig['admin_perpage'], $start4);
            $offline_count = $xoopsDB -> getRowsNum($xoopsDB -> query($sql));

            xtorrent_downlistheader(_AM_XTORRENT_MINDEX_OFFLINEDOWN);

            if ($offline_count > 0)
            {
                while ($is_offline = $xoopsDB -> fetchArray($offline_array))
                {
                    xtorrent_downlistbody($is_offline);
                } 
            } 
            else
            {
                xtorrent_downlistfooter();
            } 
            xtorrent_downlistpagenav($offline_count, $start4, 'art4');
        }

        xtorrent_serverstats();

        require_once __DIR__ . '/admin_footer.php';
        break;
} 
