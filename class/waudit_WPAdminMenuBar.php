<?php
class waudit_WPAdminMenuBar extends GakplSecurityAudit {
  
  public $score = -1;

  public function __construct() {

      parent::__construct();

      if (!isset($this->H) || empty($this->H)) {
        $this->H = new waudit_Helper;
      }
      
      if (!isset($this->A) || empty($this->A)) {
        $this->A = new waudit_API;
      }    
      if (!isset($this->A) || empty($this->A)) {
        $this->T = new waudit_Test;
      }


      $this->A->internal_request = true;
      $this->score = $this->A->operation_waudit_get_score();
      $this->score = $this->score;

      add_action('wp_before_admin_bar_render', array($this,'links'), 100);
     

  }


  /**
   * Adds admin bar items for easy access to the theme creator and editor
   */
  function links() {
      $this->add("<img class=\"ab-icon\" src=\"{$this->imgdir}waudit_griffin logo_light_24.png\"/> Waudit"); // Parent item
      $this->add("<img class=\"ab-icon\" src=\"{$this->imgdir}hammer_arrow.png\"/> Run Security Audit", "/waudit/audit_run/?redirect_previous=1", "<img class=\"ab-icon\" src=\"{$this->imgdir}waudit_griffin logo_light_24.png\"/> Waudit");
      $this->add("<img class=\"ab-icon\" src=\"{$this->imgdir}hammer_pencil.png\"/> Current score: {$this->score}", "", "<img class=\"ab-icon\" src=\"{$this->imgdir}waudit_griffin logo_light_24.png\"/> Waudit");
  }

  /**
   * Add's menu parent or submenu item.
   * @param string $name the label of the menu item
   * @param string $href the link to the item (settings page or ext site)
   * @param string $parent Parent label (if creating a submenu item)
   *
   * @return void
   * @author Slavi Marinov <http://orbisius.com>
   * */
  function add($name, $href = '', $parent = '', $custom_meta = array()) {
      global $wp_admin_bar;

      if (!is_super_admin()
              || !is_admin_bar_showing()
              || !is_object($wp_admin_bar)
              || !function_exists('is_admin_bar_showing')) {
          return;
      }

      // Generate ID based on the current filename and the name supplied.
      $id = str_replace('.php', '', basename(__FILE__)) . '-' . $name;
      $id = preg_replace('#[^\w-]#si', '-', $id);
      $id = strtolower($id);
      $id = trim($id, '-');

      $parent = trim($parent);

      // Generate the ID of the parent.
      if (!empty($parent)) {
          $parent = str_replace('.php', '', basename(__FILE__)) . '-' . $parent;
          $parent = preg_replace('#[^\w-]#si', '-', $parent);
          $parent = strtolower($parent);
          $parent = trim($parent, '-');
      }

      // links from the current host will open in the current window
      $site_url = site_url();

      $meta_default = array();
      $meta_ext = array( 'target' => '_blank' ); // external links open in new tab/window

      $meta = (strpos($href, $site_url) !== false) ? $meta_default : $meta_ext;
      $meta = array_merge($meta, $custom_meta);

      $wp_admin_bar->add_node(array(
          'parent' => $parent,
          'id' => $id,
          'title' => $name,
          'href' => $href,
          'meta' => $meta,
      ));
  }
   

}
?>