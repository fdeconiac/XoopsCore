<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * Extended User Profile
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @package         profile
 * @since           2.3.0
 * @author          Jan Pedersen
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id$
 */

/**
 * @param XoopsModule $module
 * @return bool
 */
function xoops_module_install_profile($module)
{

    $xoops = Xoops::getInstance();
    $xoops->registry()->set('profile_id', $module->getVar('mid'));

    // Create registration steps
    profile_install_addStep(_PROFILE_MI_STEP_BASIC, '', 1, 0);
    profile_install_addStep(_PROFILE_MI_STEP_COMPLEMENTARY, '', 2, 1);

    // Create categories
    profile_install_addCategory(_PROFILE_MI_CATEGORY_PERSONAL, 1);
    profile_install_addCategory(_PROFILE_MI_CATEGORY_MESSAGING, 2);
    profile_install_addCategory(_PROFILE_MI_CATEGORY_SETTINGS, 3);
    profile_install_addCategory(_PROFILE_MI_CATEGORY_COMMUNITY, 4);

    profile_install_addField('name', XoopsLocale::REAL_NAME, '', 1, 'textbox', 1, 1, 1, array(), 2, 255);
    profile_install_addField('user_from', XoopsLocale::LOCATION, '', 1, 'textbox', 1, 2, 1, array(), 2, 255);
    profile_install_addField('timezone_offset', XoopsLocale::TIME_ZONE, '', 1, 'timezone', 1, 3, 1, array(), 2, 0);
    profile_install_addField('user_occ', XoopsLocale::OCCUPATION, '', 1, 'textbox', 1, 4, 1, array(), 2, 255);
    profile_install_addField('user_intrest', XoopsLocale::INTEREST, '', 1, 'textbox', 1, 5, 1, array(), 2, 255);
    profile_install_addField('bio', XoopsLocale::EXTRA_INFO, '', 1, 'textarea', 2, 6, 1, array(), 2, 0);
    profile_install_addField('user_regdate', XoopsLocale::MEMBER_SINCE, '', 1, 'datetime', 3, 7, 0, array(), 0, 10);

    profile_install_addField('user_icq', XoopsLocale::ICQ, '', 2, 'textbox', 1, 1, 1, array(), 2, 255);
    profile_install_addField('user_aim', XoopsLocale::AIM, '', 2, 'textbox', 1, 2, 1, array(), 2, 255);
    profile_install_addField('user_yim', XoopsLocale::YIM, '', 2, 'textbox', 1, 3, 1, array(), 2, 255);
    profile_install_addField('user_msnm', XoopsLocale::MSNM, '', 2, 'textbox', 1, 4, 1, array(), 2, 255);

    profile_install_addField('user_viewemail', XoopsLocale::ALLOW_OTHER_USERS_TO_VIEW_EMAIL, '', 3, 'yesno', 3, 1, 1, array(), 2, 1, false);
    profile_install_addField('attachsig', XoopsLocale::ALWAYS_ATTACH_MY_SIGNATURE, '', 3, 'yesno', 3, 2, 1, array(), 0, 1, false);
    profile_install_addField('user_mailok', XoopsLocale::Q_RECEIVE_OCCASIONAL_EMAIL_NOTICES_FROM_ADMINISTRATORS, '', 3, 'yesno', 3, 3, 1, array(), 2, 1, false);
    profile_install_addField('theme', _PROFILE_MA_THEME, '', 3, 'theme', 1, 4, 1, array(), 0, 0, false);

    profile_install_addField('url', _PROFILE_MI_URL_TITLE, '', 4, 'textbox', 1, 1, 1, array(), 2, 255);
    profile_install_addField('posts', XoopsLocale::POSTS, '', 4, 'textbox', 3, 2, 0, array(), 0, 255);
    profile_install_addField('rank', XoopsLocale::RANK, '', 4, 'rank', 3, 3, 2, array(), 0, 0);
    profile_install_addField('last_login', XoopsLocale::LAST_LOGIN, '', 4, 'datetime', 3, 4, 0, array(), 0, 10);
    profile_install_addField('user_sig', XoopsLocale::SIGNATURE, '', 4, 'textarea', 1, 5, 1, array(), 0, 0);

    profile_install_initializeProfiles();
    return true;
}

function profile_install_initializeProfiles()
{
    $xoops = Xoops::getInstance();
    $xoops->db();
    global $xoopsDB;
    $module_id = $xoops->registry()->get('profile_id');

    $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("profile_profile") . " (profile_id) " . " SELECT uid " . " FROM " . $xoopsDB->prefix("users"));

    $sql = "INSERT INTO " . $xoopsDB->prefix("group_permission") . " (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) " . " VALUES " . " (" . XOOPS_GROUP_ADMIN . ", " . XOOPS_GROUP_ADMIN . ", {$module_id}, 'profile_access'), " . " (" . XOOPS_GROUP_ADMIN . ", " . XOOPS_GROUP_USERS . ", {$module_id}, 'profile_access'), " . " (" . XOOPS_GROUP_USERS . ", " . XOOPS_GROUP_USERS . ", {$module_id}, 'profile_access'), " . " (" . XOOPS_GROUP_ANONYMOUS . ", " . XOOPS_GROUP_USERS . ", {$module_id}, 'profile_access') " . " ";
    $xoopsDB->queryF($sql);

}

// canedit: 0 - no; 1 - admin; 2 - admin & owner
function profile_install_addField($name, $title, $description, $category, $type, $valuetype, $weight, $canedit, $options, $step_id, $length, $visible = true)
{
    $xoops = Xoops::getInstance();
    $module_id = $xoops->registry()->get('profile_id');

    $profilefield_handler = $xoops->getModuleHandler('field', 'profile');
    $obj = $profilefield_handler->create();
    $obj->setVar('field_name', $name, true);
    $obj->setVar('field_moduleid', $module_id, true);
    $obj->setVar('field_show', 1);
    $obj->setVar('field_edit', $canedit ? 1 : 0);
    $obj->setVar('field_config', 0);
    $obj->setVar('field_title', strip_tags($title), true);
    $obj->setVar('field_description', strip_tags($description), true);
    $obj->setVar('field_type', $type, true);
    $obj->setVar('field_valuetype', $valuetype, true);
    $obj->setVar('field_options', $options, true);
    if ($canedit) {
        $obj->setVar('field_maxlength', $length, true);
    }

    $obj->setVar('field_weight', $weight, true);
    $obj->setVar('cat_id', $category, true);
    $obj->setVar('step_id', $step_id, true);
    $profilefield_handler->insert($obj);

    profile_install_setPermissions($obj->getVar('field_id'), $module_id, $canedit, $visible);

    return true;
    /*
    //$xoopsDB->query("INSERT INTO ".$xoopsDB->prefix("profile_field")." VALUES (0, {$category}, '{$type}', {$valuetype}, '{$name}', " . $xoopsDB->quote($title) . ", " . $xoopsDB->quote($description) . ", 0, {$length}, {$weight}, '', 1, {$canedit}, 1, 0, '" . serialize($options) . "', {$step_id})");
    $gperm_itemid = $obj->getVar('field_id');
    unset($obj);
    $gperm_modid = $module_id;
    $sql = "INSERT INTO " . $xoopsDB->prefix("group_permission") .
        " (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) " .
        " VALUES " .
        ($canedit ?
            " (" . XOOPS_GROUP_ADMIN . ", {$gperm_itemid}, {$gperm_modid}, 'profile_edit'), "
        : "" ) .
        ($canedit == 1 ?
            " (" . XOOPS_GROUP_USERS . ", {$gperm_itemid}, {$gperm_modid}, 'profile_edit'), "
        : "" ) .
        " (" . XOOPS_GROUP_ADMIN . ", {$gperm_itemid}, {$gperm_modid}, 'profile_search'), " .
        " (" . XOOPS_GROUP_USERS . ", {$gperm_itemid}, {$gperm_modid}, 'profile_search') " .
        " ";
    $xoopsDB->query($sql);

    if ( $visible ) {
        $sql = "INSERT INTO " . $xoopsDB->prefix("profile_visibility") .
            " (field_id, user_group, profile_group) " .
            " VALUES " .
            " ({$gperm_itemid}, " . XOOPS_GROUP_ADMIN . ", " . XOOPS_GROUP_ADMIN . "), " .
            " ({$gperm_itemid}, " . XOOPS_GROUP_ADMIN . ", " . XOOPS_GROUP_USERS . "), " .
            " ({$gperm_itemid}, " . XOOPS_GROUP_USERS . ", " . XOOPS_GROUP_ADMIN . "), " .
            " ({$gperm_itemid}, " . XOOPS_GROUP_USERS . ", " . XOOPS_GROUP_USERS . "), " .
            " ({$gperm_itemid}, " . XOOPS_GROUP_ANONYMOUS . ", " . XOOPS_GROUP_ADMIN . "), " .
            " ({$gperm_itemid}, " . XOOPS_GROUP_ANONYMOUS . ", " . XOOPS_GROUP_USERS . ")" .
            " ";
        $xoopsDB->query($sql);
    }
    */
}

function profile_install_setPermissions($field_id, $module_id, $canedit, $visible)
{
    $xoops = Xoops::getInstance();
    $xoops->db();
    global $xoopsDB;
    $gperm_itemid = $field_id;
    $gperm_modid = $module_id;
    $sql = "INSERT INTO " . $xoopsDB->prefix("group_permission") . " (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) " . " VALUES " . ($canedit
            ? " (" . XOOPS_GROUP_ADMIN . ", {$gperm_itemid}, {$gperm_modid}, 'profile_edit'), " : "") . ($canedit == 1
            ? " (" . XOOPS_GROUP_USERS . ", {$gperm_itemid}, {$gperm_modid}, 'profile_edit'), "
            : "") . " (" . XOOPS_GROUP_ADMIN . ", {$gperm_itemid}, {$gperm_modid}, 'profile_search'), " . " (" . XOOPS_GROUP_USERS . ", {$gperm_itemid}, {$gperm_modid}, 'profile_search') " . " ";
    $xoopsDB->queryF($sql);

    if ($visible) {
        $sql = "INSERT INTO " . $xoopsDB->prefix("profile_visibility") . " (field_id, user_group, profile_group) " . " VALUES " . " ({$gperm_itemid}, " . XOOPS_GROUP_ADMIN . ", " . XOOPS_GROUP_ADMIN . "), " . " ({$gperm_itemid}, " . XOOPS_GROUP_ADMIN . ", " . XOOPS_GROUP_USERS . "), " . " ({$gperm_itemid}, " . XOOPS_GROUP_USERS . ", " . XOOPS_GROUP_ADMIN . "), " . " ({$gperm_itemid}, " . XOOPS_GROUP_USERS . ", " . XOOPS_GROUP_USERS . "), " . " ({$gperm_itemid}, " . XOOPS_GROUP_ANONYMOUS . ", " . XOOPS_GROUP_ADMIN . "), " . " ({$gperm_itemid}, " . XOOPS_GROUP_ANONYMOUS . ", " . XOOPS_GROUP_USERS . ")" . " ";
        $xoopsDB->queryF($sql);
    }
}

function profile_install_addCategory($name, $weight)
{
    $xoops = Xoops::getInstance();
    $xoops->db();
    global $xoopsDB;
    $xoopsDB->query("INSERT INTO " . $xoopsDB->prefix("profile_category") . " VALUES (0, " . $xoopsDB->quote($name) . ", '', {$weight})");
}

function profile_install_addStep($name, $desc, $order, $save)
{
    $xoops = Xoops::getInstance();
    $xoops->db();
    global $xoopsDB;
    $xoopsDB->query("INSERT INTO " . $xoopsDB->prefix("profile_regstep") . " VALUES (0, " . $xoopsDB->quote($name) . ", " . $xoopsDB->quote($desc) . ", {$order}, {$save})");
}