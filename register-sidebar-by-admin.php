<?php
/*
   Plugin Name: Register Sidebar By Admin 
   Plugin URI: https://profiles.wordpress.org/p4wparamjeet
   description: Dynamic sidebar manage by wordpress admin panel
   Version: 1.0
   Author: Paramjeet Kumawat
   Author URI: https://www.upwork.com/freelancers/~01fc348f42626d2077
   License: GPL2
   */

/**
 * Set a path to the plugin's root directory.
 */
if ( ! defined( 'REGISTER_SIDEBAR_BY_ADMIN_DIR' ) )
    define( 'REGISTER_SIDEBAR_BY_ADMIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Main plugin class.
 */
class Register_Sidebars_By_Admin {

    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct(){
        add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links') );
        add_action('admin_menu',array(&$this, 'add_register_sidebars_page_settings' ));
        add_action('admin_enqueue_scripts',array(&$this, 'add_register_sidebars_by_admin_scripts' ));
        add_action( 'widgets_init', array( __CLASS__, 'register_sidebars' ),20);
    }
    public function add_register_sidebars_by_admin_scripts(){
        if(isset($_GET['page']) && $_GET['page'] != 'dynamic-register-sidebars-by-admin-page') return;

        wp_enqueue_script('register-sidebar-by-admin-js', plugins_url('assets/sidebar-by-admin-js.js', __FILE__), array('jquery'), '1.0.0', true);
        wp_enqueue_style( 'register-sidebar-by-admin-css', plugins_url('assets/sidebar-by-admin-css.css', __FILE__), array(), '1.0.0', 'all');
    }
    public static function register_sidebars(){
        $_register_sidebar_areas = array();
        $_register_sidebar_areas = unserialize(get_option('_dynamic_register_sidebars'));

        $sidebar_area_defaults = array(
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="title">',
            'after_title'   => '</h3>'
        );
        if ( ! empty( $_register_sidebar_areas ) && is_array( $_register_sidebar_areas ) ) {
            foreach ( $_register_sidebar_areas as $key => $area ) {
                register_sidebar(array(
                    'id'            => $key,
                    'name'          => $area['name'],
                    'description'   => $area['description'],
                    'before_widget' => ( isset( $area['before_widget'] ) ) ? $area['before_widget'] : $sidebar_area_defaults['before_widget'],
                    'after_widget'  => ( isset( $area['after_widget'] ) )  ? $area['after_widget']  : $sidebar_area_defaults['after_widget'],
                    'before_title'  => ( isset( $area['before_title'] ) )  ? $area['before_title']  : $sidebar_area_defaults['before_title'],
                    'after_title'   => ( isset( $area['after_title'] ) )   ? $area['after_title']   : $sidebar_area_defaults['after_title']
                ));
            }
        }
    }
    public function add_register_sidebars_page_settings(){
        add_submenu_page(
            'themes.php',
            'Register Sidebars By Admin',
            'Register Sidebars By Admin',
            'manage_options',
            'dynamic-register-sidebars-by-admin-page',
            array(&$this,'register_sidebars_by_admin_view_callback'));
    }
    public function applyActions($action,$data){
        if(empty($action)) return array('status'=>false,'msg'=>"Error occurred! Form has no action.");;
        switch ($action){
            case 'insert' : {
                $is_nonce_valid = ( isset( $data['dynapic_register_sidebar_insert_nonce'] ) && wp_verify_nonce( $data['dynapic_register_sidebar_insert_nonce'], 'dynapic_register_sidebar_' . $data['_nonce_id'] ) ) ? true : false;
                if(!$is_nonce_valid) return array('status'=>false,'msg'=>"Error occurred! Form not secure.");
                $_new_sidebars = array();
                $key = 'register-sidebar-by-admin-' . sanitize_key( $data['_dyn_sidebar_name'] );
                $_new_sidebars[$key] = array('id'=>$key,'name'=>$data['_dyn_sidebar_name'],'description'=>$data['_dyn_sidebar_description']);
                if($_old_sidebars = get_option('_dynamic_register_sidebars')){
                    $_old_sidebars_arr = unserialize($_old_sidebars);
                    $_new_sidebars = array_merge($_old_sidebars_arr,$_new_sidebars);
                }
                $_new_sidebars_arr = serialize($_new_sidebars);
                if($_new_sidebars_arr){
                    update_option('_dynamic_register_sidebars',$_new_sidebars_arr);
                    return array('status'=>1,'msg'=>"Sidebar has been inserted successfully.");
                }
                return array('status'=>false,'msg'=>"Error occurred! Sidebar not updated.");
                break;
            }
            case 'update':{
                $is_nonce_valid = ( isset( $data['dynapic_register_sidebar_update_nonce'] ) && wp_verify_nonce( $data['dynapic_register_sidebar_update_nonce'], 'dynapic_register_sidebar_' . $data['_nonce_id'] ) ) ? true : false;
                if(!$is_nonce_valid) return array('status'=>false,'msg'=>"Error occurred! Form not secure.");
                $_sidebar_id = $data['_sidebar_id'];
                $_sidebars_list = unserialize(get_option('_dynamic_register_sidebars'));
                if(count($_sidebars_list) > 0 && $_sidebar_id && array_key_exists($_sidebar_id,$_sidebars_list)){
                    $_sidebars_list[$_sidebar_id]['name'] = $data['_dyn_sidebar_name'];
                    $_sidebars_list[$_sidebar_id]['description'] = $data['_dyn_sidebar_description'];
                    update_option('_dynamic_register_sidebars',serialize($_sidebars_list));
                    return array('status'=>true,'msg'=>"Sidebar has been updated successfully.");
                }
                return array('status'=>false,'msg'=>"Error occurred! Sidebar not updated.");
                break;
            }
            case 'delete':{
                $is_nonce_valid = ( isset( $data['dynapic_register_sidebar_delete_nonce'] ) && wp_verify_nonce( $data['dynapic_register_sidebar_delete_nonce'], 'dynapic_register_sidebar_' . $data['_nonce_id'] ) ) ? true : false;
                if(!$is_nonce_valid) return array('status'=>false,'msg'=>"Error occurred! Form not secure.");
                $_sidebar_id = $data['_sidebar_id'];
                $_sidebars_list = unserialize(get_option('_dynamic_register_sidebars'));
                if(count($_sidebars_list) > 0 && $_sidebar_id && array_key_exists($_sidebar_id,$_sidebars_list)){
                    unset($_sidebars_list[$_sidebar_id]);
                    unset($GLOBALS['wp_registered_sidebars'][$_sidebar_id]);
                    update_option('_dynamic_register_sidebars',serialize($_sidebars_list));
                    return array('status'=>true,'msg'=>"Sidebar has been deleted successfully.");
                }
                return array('status'=>false,'msg'=>"Error occurred! Sidebar not deleted.");
                break;
            }
        }
        return array('status'=>false,'msg'=>"Error occurred!");
    }
    public function register_sidebars_by_admin_view_callback(){
        echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
        echo '<h2>Register Sidebars By Admin</h2>';
        $response = null;
        if(isset($_REQUEST['frm-action']) && !empty($_REQUEST['frm-action'])){ /*Apply Form Action*/
            $response = $this->applyActions($_REQUEST['frm-action'],$_REQUEST);
        }

        $_wp_register_sidebars = $GLOBALS['wp_registered_sidebars'];
        $_register_siderbar = array();
        if(get_option('_dynamic_register_sidebars')) $_register_siderbar = unserialize(get_option('_dynamic_register_sidebars'));
        $_register_siderbar_arr = array_merge($_wp_register_sidebars,$_register_siderbar);
        //echo "<pre>"; print_r($_wp_register_sidebars); print_r($_register_siderbar_arr); exit;
        ?>
        <?php if($response) { ?>
            <?php $_cls = ($response['status'] !== false)?'notice-success':'notice-error error'; ?>
            <div id="message" class="updated notice <?php echo $_cls;?> is-dismissible">
                <p><?php echo $response['msg']?></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
        <?php } ?>
        <ul class="dynamic_sidebar_list">
        <?php 
        $count_sidebar = 1;
        foreach ($_register_siderbar_arr as $sidebar ) { ?>
            <li>
                <div class="item" data-sidebar-id="<?php echo $sidebar['id'];?>">
                    <span><?php echo ucwords( $sidebar['name'] );?></span>
                    <?php if ( $count_sidebar != 1 ): echo '<span>Sidebar Key - '.'register-sidebar-by-admin-' . sanitize_key( $sidebar['name'] ).'</span>'; endif; $count_sidebar = $count_sidebar + 1?>
                    <?php if($_register_siderbar && array_key_exists($sidebar['id'],$_register_siderbar)){ ?>
                        <a class="actions edit-sidebar" title="Edit Sidebar" href="javascript:"><span class="dashicons dashicons-edit"></span></a>
                        <a class="actions trash-sidebar" title="Delete Sidebar" href="javascript:"><span class="dashicons dashicons-trash"></span></a>
                    <?php }?>
                </div>
                <?php if($_register_siderbar && array_key_exists($sidebar['id'],$_register_siderbar)){ ?>
                <div class="edit_sidebar_ctr sidebar-edit-form-<?php echo $sidebar['id'];?>">
                    <form action="" method="post">
                        <table class="form-table">
                            <tbody>
                            <tr class="form-field form-required">
                                <th scope="row"><label for="_dyn_sidebar_name">Sidebar Name <span class="description">(required)</span></label></th>
                                <td><input name="_dyn_sidebar_name" type="text" id="_dyn_sidebar_name" value="<?php echo ucwords( $sidebar['name'] );?>" aria-required="true" autocapitalize="none" autocorrect="off"></td>
                            </tr>
                            <tr class="form-field form-required">
                                <th scope="row"><label for="_dyn_sidebar_description">Description</th>
                                <td><textarea name="_dyn_sidebar_description" id="_dyn_sidebar_description"><?php echo $sidebar['description'];?></textarea></td>
                            </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <?php $_nonce_id = trim($sidebar['id']); ?>
                            <input type="hidden" name="_sidebar_id" value="<?php echo trim($sidebar['id'])?>" />
                            <input type="hidden" name="frm-action" value="update" />
                            <input type="hidden" name="_nonce_id" value="<?php echo $_nonce_id?>" />
                            <?php wp_nonce_field( 'dynapic_register_sidebar_' .$_nonce_id, 'dynapic_register_sidebar_update_nonce', false ); ?>
                            <input type="submit" name="addSidebar" id="addSidebar" class="button button-primary" value="Update Sidebar">
                        </p>
                    </form>
                </div>
                <form action="" method="post" id="deleteSidebar-<?php echo $sidebar['id'];?>" style="display: none;">
                    <?php $_nonce_id = trim($sidebar['id']); ?>
                    <input type="hidden" name="_sidebar_id" value="<?php echo trim($sidebar['id'])?>" />
                    <input type="hidden" name="frm-action" value="delete" />
                    <input type="hidden" name="_nonce_id" value="<?php echo $_nonce_id?>" />
                    <?php wp_nonce_field( 'dynapic_register_sidebar_' .$_nonce_id, 'dynapic_register_sidebar_delete_nonce', false ); ?>
                </form>
                <?php }?>
            </li>
        <?php } ?>
        </ul>
        <div class="alignleft actions">
            <p>
                <button type="button" class="button create-new-sidebar" id="create-new-sidebar">Register New Sidebar</button>
            </p>
        </div>
        <div class="register_new_sidebar_ctr">
            <form action="" method="post">
                <table class="form-table">
                    <tbody>
                    <tr class="form-field form-required">
                        <th scope="row"><label for="_dyn_sidebar_name">Sidebar Name <span class="description">(required)</span></label></th>
                        <td><input name="_dyn_sidebar_name" type="text" id="_dyn_sidebar_name" value="" aria-required="true" autocapitalize="none" autocorrect="off"></td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row"><label for="_dyn_sidebar_description">Description</th>
                        <td><textarea name="_dyn_sidebar_description" id="_dyn_sidebar_description"></textarea></td>
                    </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <?php $_nonce_id = uniqid(); ?>
                    <input type="hidden" name="frm-action" value="insert" />
                    <input type="hidden" name="_nonce_id" value="<?php echo $_nonce_id?>" />
                    <?php wp_nonce_field( 'dynapic_register_sidebar_' .$_nonce_id, 'dynapic_register_sidebar_insert_nonce', false ); ?>
                    <input type="submit" name="addSidebar" id="addSidebar" class="button button-primary" value="Add New Sidebar">
                </p>
            </form>
        </div>
        <?php
        echo '</div>';
    }
    public function plugin_action_links($links){
        unset( $links['edit'] );
        $links['manage'] = '<a href="' . admin_url('themes.php?page=dynamic-register-sidebars-by-admin-page') . '">'.__('Settings', 'ps-dynamic-register-sidebars').'</a>';
        return $links;
    }
}
add_action( 'plugins_loaded', array( 'Register_Sidebars_By_Admin', 'getInstance' ) );
