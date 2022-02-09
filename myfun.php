<?php

/**
 * Plugin Name: My function
 * Description: My function
 * Version: 1.0.1
 * Author: m-jalali
 * Author URI: http://www.m-jalali.ir
 */

const myfun_dir_template = WP_PLUGIN_DIR . '//my-function//template//';
const myfun_option_name = 'myfun_list';

function myfun_get_data()
{
    $myfun_list = unserialize(get_option(myfun_option_name, ''));
    if (empty($myfun_list))
        foreach (myfun_get_posation() as $key => $value)
            $myfun_list[$key] = array();
    return $myfun_list;
}
function myfun_update_data($myfun_list)
{
    return update_option(myfun_option_name, serialize((array)$myfun_list));
}

function myfun_get_posation()
{
    return array(
        'plugin' => 'after plugin',
        'before_theme' => 'before theme',
        'after_theme' => 'after theme',
        'wp_loaded' => 'wp loaded',
        'init' => 'init',
    );
}

function myfun_plugin_setup()
{
    $myfun_list =  myfun_get_data();
    foreach ((array)$myfun_list['plugin'] as $key => $value) {
        if (file_exists(myfun_dir_template . "{$value['file_name']}.php")) {
            if ($value['require'])
                require(myfun_dir_template . "{$value['file_name']}.php");
            else
                require_once(myfun_dir_template . "{$value['file_name']}.php");
        }
    }
}
function myfun_before_setup_theme()
{
    $myfun_list =  myfun_get_data();
    foreach ((array)$myfun_list['before_theme'] as $key => $value) {
        if (file_exists(myfun_dir_template . "{$value['file_name']}.php")) {
            if ($value['require'])
                require(myfun_dir_template . "{$value['file_name']}.php");
            else
                require_once(myfun_dir_template . "{$value['file_name']}.php");
        }
    }
}
function myfun_after_setup_theme()
{
    $myfun_list =  myfun_get_data();
    foreach ((array)$myfun_list['after_theme'] as $key => $value) {
        if (file_exists(myfun_dir_template . "{$value['file_name']}.php")) {
            if ($value['require'])
                require(myfun_dir_template . "{$value['file_name']}.php");
            else
                require_once(myfun_dir_template . "{$value['file_name']}.php");
        }
    }
}
function myfun_init()
{
    $myfun_list =  myfun_get_data();
    foreach ((array)$myfun_list['init'] as $key => $value) {
        if (file_exists(myfun_dir_template . "{$value['file_name']}.php")) {
            if ($value['require'])
                require(myfun_dir_template . "{$value['file_name']}.php");
            else
                require_once(myfun_dir_template . "{$value['file_name']}.php");
        }
    }
}
function myfun_wp_loaded()
{
    $myfun_list =  myfun_get_data();
    foreach ((array)$myfun_list['wp_loaded'] as $key => $value) {
        if (file_exists(myfun_dir_template . "{$value['file_name']}.php")) {
            if ($value['require'])
                require(myfun_dir_template . "{$value['file_name']}.php");
            else
                require_once(myfun_dir_template . "{$value['file_name']}.php");
        }
    }
}

// Fires before the theme is loaded.
add_action('setup_theme', 'myfun_before_setup_theme');
// Fires after the theme is loaded.
add_action('after_setup_theme', 'myfun_after_setup_theme');
add_action('init', 'myfun_init');
add_action('wp_loaded', 'myfun_wp_loaded');
// Fires after the plugin is loaded.
myfun_plugin_setup();

function myfun_add_menu()
{
    $tt_page = add_menu_page("My function", "My function", "manage_options", "myfun-panel", "myfun_admin_panel_display", null, 99);
}
add_action("admin_menu", "myfun_add_menu");

function myfun_admin_panel_display()
{
    $action = !empty($_GET) && !empty($_GET['action']) ? $_GET['action'] : "first";
    $id = !empty($_GET) && !empty($_GET['id']) ? $_GET['id'] : -1;

    if (!empty($_POST)) {
        $successful = true;
        $myfun_list = (array) myfun_get_data();

        // myfun_pos, myfun_require, myfun_file_name, myfun_file_text
        // action Add
        if (!empty($_POST['action']) && $_POST['action'] == "add" && !empty($_POST['myfun_file_name'])) {
            $args = array(
                'file_name' => '',
                'require' => false,
            );
            $args['file_name'] = str_replace(' ', '-', $_POST['myfun_file_name']);
            $args['require'] = !empty($_POST['myfun_require']) ? true : false;
            $file_path =  myfun_dir_template . $args['file_name'] . ".php";
            if (!file_exists($file_path)) {
                $myfile = fopen($file_path, "w");
                $text = wp_unslash($_POST['myfun_file_text']);
                $successful = fwrite($myfile, $text) === false ? false : $successful;
                fclose($myfile);
                $myfun_list[$_POST['myfun_pos']][] = $args;
                $successful = $successful && myfun_update_data($myfun_list);
            } else
                $successful = false;
        }
        // action Edit
        else if (!empty($_POST['action']) && $_POST['action'] == "edit" && !empty($_POST['myfun_id'])) {
            $id = $_POST['myfun_id'];
            $ids = explode('-', (string)$id);
            if (key_exists($ids[0], $myfun_list) && key_exists($ids[1], $myfun_list[$ids[0]])) {
                $args = $myfun_list[$ids[0]][$ids[1]];
                $file_name = str_replace(' ', '-', $_POST['myfun_file_name']);
                $file_path =  myfun_dir_template . $args['file_name'] . ".php";;
                $is_list_change = false;
                if ($file_name != $args['file_name'] && file_exists($file_path)) {
                    unlink($file_path);
                    $is_list_change = true;
                }
                $file_path =  myfun_dir_template . $file_name . ".php";;
                $myfile = fopen($file_path, "w");

                $text = wp_unslash($_POST['myfun_file_text']);
                $successful = (fwrite($myfile, $text) === false ? false : true) && $successful;
                fclose($myfile);
                $args['file_name'] = $file_name;
                $is_list_change = $is_list_change || $args['require'] != !empty($_POST['myfun_require']);
                $args['require'] = !empty($_POST['myfun_require']) ? true : false;
                if ($is_list_change) {
                    $myfun_list[$ids[0]][$ids[1]] = $args;
                    $successful = $successful && myfun_update_data($myfun_list);
                }
            } else
                $successful = false;
        }
        // action remove
        else if (!empty($_POST['action']) && $_POST['action'] == "remove" && !empty($_POST['myfun_id'])) {
            $id = $_POST['myfun_id'];
            $ids = explode('-', (string)$id);
            if (key_exists($ids[0], $myfun_list) && key_exists($ids[1], $myfun_list[$ids[0]])) {
                $file_name = $myfun_list[$ids[0]][$ids[1]]['file_name'];
                $file_path =  myfun_dir_template . $file_name . ".php";;
                if (file_exists($file_path)) {
                    $successful = $successful && unlink($file_path);
                }
                unset($myfun_list[$ids[0]][$ids[1]]);
                $successful = $successful && myfun_update_data($myfun_list);
            } else
                $successful = false;
        } else
            $successful = false;

        if ($successful) {
            echo "<div class=\"\">successful</div>";
        } else {
            echo "<div class=\"\">un successful</div>";
        }
    }
?>
    <div class="wrap">
        <?php
        if ($action == 'add')
            myfun_add_page_display();
        else if ($action == 'edit' && $id != -1)
            myfun_add_page_display($id);
        else if ($action == 'remove' && $id != -1)
            myfun_remove_page_display($id);
        else
            myfun_first_page_display();
        ?>
    </div>
<?php
}



function myfun_remove_page_display($id)
{
    $file_name = '';
    $myfun_list = (array) myfun_get_data();
    $ids = explode('-', (string)$id);
    if (key_exists($ids[0], $myfun_list) && key_exists($ids[1], $myfun_list[$ids[0]])) {
        $file_name = $myfun_list[$ids[0]][$ids[1]]['file_name'];
    }
?>
    <form action="admin.php?page=myfun-panel" method="POST">
        <p>Are you sure you want to delete the <?php echo $file_name; ?> page query?</p>
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="submit" value="Remove" class="button button-primary">
        <a class="button button-cancel" href="admin.php?page=myfun-panel&action=first" class="page-title-action">Cancel</a>
    </form>
<?php
}

function myfun_first_page_display()
{
    $myfun_list = myfun_get_data();
?>
    <style>
        .myfun_ul {
            display: block;
        }

        .myfun_ul li {}

        .myfun_ul li {
            display: inline-block;
            float: left;
        }

        .myfun_ul li:first-child::after {
            content: '';
        }

        .myfun_ul li::after {
            content: ',';
            margin-right: 5px;
            color: #ff0000;
        }
    </style>
    <h1 class="wp-heading-inline">My function</h1>
    <div class="row"><a href="admin.php?page=myfun-panel&action=add" class="page-title-action">add</a></div>
    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
            <tr>
                <th scope="col" class="manage-column">function name</th>
                <th scope="col" class="manage-column">posation</th>
                <th scope="col" class="manage-column">require</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($myfun_list)) {
                foreach ((array)$myfun_list as $pos => $value) {
                    foreach ((array)$value as $key => $args) { ?>
                        <tr class="">
                            <td class="">
                                <strong><?php echo (string)$args['file_name']; ?></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="admin.php?page=myfun-panel&action=edit&id=<?php echo $pos . "-" . $key; ?>" aria-label="ویرایش">ویرایش</a> | </span>
                                    <span class="trash"><a href="admin.php?page=myfun-panel&action=remove&id=<?php echo $pos . "-" . $key; ?>" class="submitdelete" aria-label="حذف">حذف</a> | </span>
                                </div>
                            </td>
                            <td class="">
                                <?php echo (string)$pos; ?>
                            </td>
                            <td class="">
                                <?php echo (string)$args['require']; ?>
                            </td>
                        </tr>
            <?php }
                }
            } else echo '<tr class=""><td>null</td></tr>'; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column">function name</th>
                <th scope="col" class="manage-column">posation</th>
                <th scope="col" class="manage-column">require</th>
            </tr>
        </tfoot>

    </table>
<?php
}


function myfun_add_page_display($id = false)
{
    $file_path = ".php";
    $data = array(
        'pos' => '',
        'file_name' => '',
        'require' => false,
        'file_text' => ''
    );
    if ($id !== false) {
        $myfun_list = (array) myfun_get_data();
        $ids = explode('-', (string)$id);
        if (key_exists($ids[0], $myfun_list) && key_exists($ids[1], $myfun_list[$ids[0]])) {
            $data['pos'] = $ids[0];
            $data['file_name'] = $myfun_list[$ids[0]][$ids[1]]['file_name'];
            $data['require'] = $myfun_list[$ids[0]][$ids[1]]['require'];
            $file_path = myfun_dir_template . $data['file_name'] . ".php";
            if (file_exists($file_path)) {
                $myfile = fopen($file_path, "r") or die("Unable to open file!");
                if (filesize($file_path) > 0)
                    $data['file_text'] = fread($myfile, filesize($file_path));
                fclose($myfile);
            }
        }
    }
    $settings = array(
        'codeEditor' => wp_enqueue_code_editor(array('file' => $file_path)),
    );
    wp_enqueue_script('wp-theme-plugin-editor');
    wp_add_inline_script('wp-theme-plugin-editor', sprintf('jQuery( function( $ ) { wp.themePluginEditor.init( $( "#myfun_form_" ), %s ); } )', wp_json_encode($settings)));
?>
    <style>
        .myfun_row {
            padding: 20px;
        }

        .myfun_row label {
            display: inline-block;
            width: 20%;
        }

        .myfun_row .myfun_sec {
            display: inline-block;
            width: 70%;
        }

        .myfun_row .myfun_sec input[type=text],
        .myfun_row .myfun_sec input[type=number],
        .myfun_row .myfun_sec select,
        .myfun_row .myfun_sec textarea {
            width: 30%;
        }
    </style>
    <h1 class="wp-heading-inline">Add Function</h1>
    <div class="row"><a href="admin.php?page=myfun-panel&action=first" class="page-title-action">back</a></div>
    <form id="myfun_form" action="admin.php?page=myfun-panel" method="post">
        <div class="myfun_row">
            <label for="myfun_pos">Select posation</label>
            <div class="myfun_sec">
                <select name="myfun_pos" id="">
                    <?php $poss = myfun_get_posation();
                    foreach ($poss as $pos_key => $pos_value) { ?>
                        <option value="<?php echo $pos_key; ?>" <?php echo ($data['pos'] == $pos_key) ? 'selected' : ''; ?>><?php echo $pos_value; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="myfun_row">
            <label for="myfun_file_name">template name</label>
            <div class="myfun_sec">
                <input type="text" name="myfun_file_name" value="<?php echo $data['file_name']; ?>">
            </div>
        </div>
        <div class="myfun_row">
            <textarea name="myfun_file_text" id="newcontent" cols="30" rows="20"><?php echo $data['file_text']; ?></textarea>
        </div>
        <div class="myfun_row">
            <input type="hidden" name="myfun_id" value="<?php echo $id ? $id : ''; ?>">
            <input type="hidden" name="action" value="<?php echo $id ? 'edit' : 'add'; ?>">
            <input class="button button-primary" type="submit" name="submit" value="<?php echo $id ? 'Save' : 'Add'; ?>">
            <a class="button button-cancel" href="admin.php?page=myfun-panel&action=first" class="page-title-action">back</a>
        </div>
    </form>
<?php
}
