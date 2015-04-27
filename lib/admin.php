<?php

namespace CustomLoginSuite\Admin;


if ( !defined( 'CLIS' ) ) exit;

if ( !class_exists( 'ClisAdmin' ) ) :

class ClisAdmin {

  public static function instance() {
    
    static $instance = null;
    
    if ( null === $instance ) {
      $instance = new ClisAdmin;
      $instance->setup_globals();
      $instance->init();
      $instance->setup_actions();
    }
    
    return $instance;
  }

  public function __construct() { /* Do nothing here */ }

  public function setup_globals() {
    // Global Object
    global $clis;
    $this->core = is_object($clis) && !empty($clis) ? $clis : \CustomLoginSuite\Core\Clis::instance();
    
  }

  private function init() {
    
    // Capabilities
    $this->minimum_capability = apply_filters( 'clis_admin_minimum_capability', 'edit_posts' ); // -> Contributor
    $this->webmaster_capability = apply_filters( 'clis_admin_webmaster_capability', 'edit_pages' ); // -> Editor
    $this->maximum_capability = apply_filters( 'clis_admin_maximum_capability', 'activate_plugins' ); // -> Administrator, and Super Admin
    
    // Paths
    $this->admin_template_dir = apply_filters( 'clis_admin_template_dir', $this->core->plugin_dir . 'templates/admin/' );
    
  }

  private function setup_actions() {
    
    // General Actions
    add_action( 'admin_menu', array($this, 'admin_menus') );
    
    // Add New Actions
    do_action( 'clis_get_admin_template', array($this, 'get_admin_template') );
    
    // Filters
    add_filter( 'plugin_action_links', array($this, 'modify_plugin_action_links'), 10, 2 );
    
  }

  public function admin_menus() {
    $operating_capability = $this->minimum_capability;
    
    $menus = [];
    
/*
    $menus[] = add_menu_page( 
      __('TOP MENU', $this->core->domain_name), 
      __('INDEX MENU', $this->core->domain_name), 
      $operating_capability, 
      'clis_admin_index', 
      array($this, 'admin_page_render'), 
      'dashicons-xxx', 
      3
    );
*/
    
    $menus[] = add_submenu_page( 
      'options-general.php', 
      __('Custom Login Suite Options', $this->core->domain_name), 
      __('Custom Login Suite Options', $this->core->domain_name), 
      $operating_capability, 
      'clis-general-options', 
      array($this, 'admin_page_render') 
    );
    
    // クエリ文字列をパースして配列変数$this->queryに格納する
    wp_parse_str( $_SERVER['QUERY_STRING'], $this->query );
    
    foreach ($menus as $menu) {
      add_action( 'admin_enqueue_scripts', array($this, 'admin_assets') );
      add_action( "admin_head-$menu", array($this, 'admin_header') );
      add_action( "admin_footer-$menu", array($this, 'admin_footer') );
      add_action( 'admin_notices', array($this, 'admin_notices') );
    }
  }
  
  public function admin_page_render() {
    // admin_menus() で定義した管理ページをレンダリングする
    if (isset($this->query['page']) && !empty($this->query['page'])) {
      
      $template_file_path = sprintf('%s%s.php', $this->admin_template_dir, $this->query['page']);
      
      if (file_exists($template_file_path)) 
        require_once( apply_filters( 'include_template-' . $this->query['page'], $template_file_path ) );
      
    }
    
  }
  
  public function admin_assets() {
    // 管理パネルで読み込まれるCSS/JavaScripを登録するフック（※ 全管理パネル共通）
    
  }

  public function admin_header() {
    // 管理パネルのHTMLヘッダ内（<head>タグ内）へのフック（※ 本プラグインで追加される管理ページ共通）
  }

  public function admin_footer() {
    // 管理パネルのHTMLフッター（</body>直前）へのフック（※ 本プラグインで追加される管理ページ共通）
    
  }

  public function admin_notices() {
    // 管理パネルの通知欄へのフック（※ 全管理パネル共通）
    if (false !== get_transient( "{CLIS}-error" )) {
      $messages = get_transient( "{CLIS}-error" );
      $classes = 'error';
    } elseif (false !== get_transient( "{CLIS}-notice" )) {
      $messages = get_transient( "{CLIS}-notice" );
      $classes = 'updated';
    }
    
    if (isset($messages) && !empty($messages)) :
?>
    <div id="message" class="<?php echo $classes; ?>">
      <ul>
      <?php foreach( $messages as $message ): ?>
        <li><?php echo esc_html($message); ?></li>
      <?php endforeach; ?>
      </ul>
    </div>
<?php
    endif;
  }
  
  private function register_admin_notices( $code="{CLIS}-error", $message, $expire_seconds=10, $is_init=false ) {
    if (!$this->core->errors || $is_init) 
      $this->core->errors = new \WP_Error();
    
    if (is_object($this->core->errors)) {
      $this->core->errors->add( $code, $message );
      set_transient( $code, $this->core->errors->get_error_messages(), $expire_seconds );
    }
    
//    return $this->core->errors;
  }
  
  public function modify_plugin_action_links( $links, $file ) {
    if (plugin_basename($this->core->plugin_main_file) !== $file) 
      return $links;
    
    if (false === $this->core->plugin_enabled) 
      return $links;
    
    $prepend_new_links = $append_new_links = array();
    
    $prepend_new_links['settings'] = sprintf(
      '<a href="%s">%s</a>', 
      add_query_arg([ 'page' => $this->core->domain_name ], admin_url('options-general.php')), 
      esc_html__( 'Settings', $this->core->domain_name )
    );
    
    unset($links['edit']);
    
    $append_new_links['edit'] = sprintf(
      '<a href="%s">%s</a>', 
      add_query_arg([ 'file' => plugin_basename($this->core->plugin_main_file) ], admin_url('plugin-editor.php')), 
      esc_html__( 'Edit', $this->core->domain_name )
    );
    
    return array_merge($prepend_new_links, $links, $append_new_links);
  }

  /**
   * 管理パネル用HTMLコンポーネント生成メソッド
   */
  public function component_user_list( $table_name=null, $show_columns=array() ) {
    // 指定テーブルのレコード一覧を出力 （※ 「CustomDataBaseTables」プラグインのショートコードのラッパー）
    if (!isset($table_name) || empty($table_name)) 
      return;
    
    list($status, $raw_table_name, $table_schema) = $this->core->extend->get_table_schema( $table_name );
    if (!$status || $raw_table_name !== $table_name) 
      return;
    
    $exclude_columns = array_keys($table_schema);
    foreach ($exclude_columns as $i => $column_name) {
      if (in_array($column_name, $show_columns)) {
        unset($exclude_columns[$i]);
      }
    }
    $shortcode_strings = sprintf(
      '[cdbt-view table="%s" bootstrap_style="%s" display_title="%s" desplay_search="%s" display_list_num="%s" enable_sort="%s" exclude_cols="%s" add_class="%s"]', 
      $table_name, 
      true, 
      false, 
      true, 
      true, 
      true, 
      implode(',', $exclude_columns), 
      ''
    );
    
    return do_shortcode( $shortcode_strings );
  }
  
  
}

endif; // end of class_exists()