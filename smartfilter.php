<?php
/*
Plugin Name: SmartFilter Security
Plugin URI: https://smartfiltersecurity.com/wordpress
Description: Secure your blog against content attacks while allowing rich posts and comments. By filtering malicious content from posts and comments, SmartFilter Security prevents from redirects, site defacement, theft of information and more. Get your SmartFilter Security running right away: (1) Get your free API key and create your rules at <a href="https://smartfiltersecurity.com/wordpress">smartfiltersecurity.com</a> (2) Enter your keys in your SmartFilter configuration page on WordPress.
Version: 1.8
Author: Prevoty
Author URI: https://www.prevoty.com
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once('lib/smartfilter.class.php');

if (!class_exists("SmartFilterPlugin")) { 
  class SmartFilterPlugin { 
    public function __construct() { 
      add_action("admin_menu", array(&$this, "admin_menu"));
      $plugin = plugin_basename(__FILE__);
      add_filter("plugin_action_links_$plugin", array(&$this, 'plugin_settings_link'));
    }

    public static function activate() { } 
    
    public static function deactivate() { } 

    public function admin_menu() { 
      add_options_page('SmartFilter Settings', 'SmartFilter', 'manage_options', 'smartfilter', array(&$this, 'plugin_settings_page'));
    }

    public function plugin_settings_link($links) { 
      $settings_link = '<a href="options-general.php?page=smartfilter">Settings</a>'; 
      array_unshift($links, $settings_link); 
      return $links; 
    }

    public function plugin_settings_page() { 
      if (!current_user_can('manage_options')) { 
        wp_die(__('You do not have sufficient permissions to access this page.'));
      }

      // If POST request, go ahead and update options
      if (isset($_POST['apikey']) && isset($_POST['postsrulekey']) && isset($_POST['commentsrulekey']) && 
          wp_verify_nonce($_POST['nonce'], 'smartfilter_settings')) {
        // Set the keys
        update_option('smartfilter_apikey', htmlentities(trim($_POST['apikey'])));
        update_option('smartfilter_postsrulekey', htmlentities(trim($_POST['postsrulekey'])));
        update_option('smartfilter_commentsrulekey', htmlentities(trim($_POST['commentsrulekey'])));

        // Checkbox: posts
        if (isset($_POST['filterposts'])) { update_option('smartfilter_filterposts', 1); } 
        else { update_option('smartfilter_filterposts', 0); }

        // Checkbox: comments
        if (isset($_POST['filtercomments'])) { update_option('smartfilter_filtercomments', 1); } 
        else { update_option('smartfilter_filtercomments', 0); }

        // Verify the keys
        $this->verify();
        $this->verify_posts_rule();
        $this->verify_comments_rule();

        // Check quota
        $this->check_quota();

        // Redirect (we've already put markup out)
        echo "<script>window.location='options-general.php?page=smartfilter';</script>";
        exit;
      }

      include(sprintf("%s/templates/settings.php", dirname(__FILE__))); 
    }

    public function revert_filters() {
      // Revert Posts
      remove_filter('content_save_pre', array(&$this, 'filter_posts'));
      remove_filter('excerpt_save_pre', array(&$this, 'filter_posts'));
      remove_filter('content_filtered_save_pre', array(&$this, 'filter_posts'));
      add_filter('content_save_pre', 'wp_filter_post_kses');
      add_filter('excerpt_save_pre', 'wp_filter_post_kses');
      add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
      // Revert Comments
      remove_filter('pre_comment_content', array(&$this, 'filter_comments'));
      add_filter('pre_comment_content', 'wp_filter_kses');
    }

    public function smartfilter_alert_key() {
      echo '<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">  
          <style type="text/css">  
.sf_activate{min-width:825px;border:1px solid #187246;padding:5px;margin:15px 0;background:#78BD9C;background-image:-webkit-gradient(linear,0% 0,80% 100%,from(#78BD9C),to(#187246));background-image:-moz-linear-gradient(80% 100% 120deg,#187246,#78BD9C);-moz-border-radius:3px;border-radius:3px;-webkit-border-radius:3px;position:relative;overflow:hidden}.sf_activate .initials{position:absolute;top:15px;right:10px;font-size:80px;color:#13A35D;font-family:Georgia, "Times New Roman", Times, serif;z-index:1}.sf_activate .sf_button{font-weight:bold;border:1px solid #029DD6;border-top:1px solid #06B9FD;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#FFF;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.sf_activate .sf_button:hover{text-decoration:none !important;border:1px solid #029DD6;border-bottom:1px solid #00A8EF;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#F0F8FB;background:#0079B1;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#0079B1),to(#0092BF));background-image:-moz-linear-gradient(0% 100% 90deg,#0092BF,#0079B1);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.sf_activate .sf_button_border{border:1px solid #006699;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6)}.sf_activate .sf_button_container{cursor:pointer;display:inline-block;background:#D9F2EC;padding:5px;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;width:266px}.sf_activate .sf_description{position:absolute;top:22px;left:285px;margin-left:25px;color:#D9F2EC;font-size:15px;z-index:1000}.sf_activate .sf_description strong{color:#FFF;font-weight:normal}
          </style>                       
          <a href="options-general.php?page=smartfilter"> 
            <div class="sf_activate">  
              <div class="initials">SF</div>     
              <div class="sf_button_container" onclick="document.sf_activate.submit();">  
                <div class="sf_button_border">          
                  <div class="sf_button">Enter Your SmartFilter API Key</div>  
                </div>  
              </div>  
              <div class="sf_description"><strong>SmartFilter</strong> is almost ready. You must enter your API key for it to work.</div>  
            </div>  
          </a>  
        </div>';
    }

    public function smartfilter_alert_posts_rule() {
      echo "<div class='error'><p>SmartFilter Posts rule key is invalid. Temporarily using WordPress security filters. <a href='options-general.php?page=smartfilter'>Edit Settings</a>.</p></div>";
    }

    public function smartfilter_alert_comments_rule() {
      echo "<div class='error'><p>SmartFilter Comments rule key is invalid. Temporarily using WordPress security filters. <a href='options-general.php?page=smartfilter'>Edit Settings</a>.</p></div>";
    }

    public function smartfilter_alert_network() {
      echo "<div class='error'><p>Could not connect to the SmartFilter cloud. Temporarily using WordPress security filters. <a href='options-general.php?page=smartfilter'>Edit Settings</a>.</p></div>";
    }

    public function smartfilter_alert_quota() {
      echo "<div class='error'><p>You've reached the API limit for SmartFilter. Temporarily using WordPress security filters. <a href='options-general.php?page=smartfilter'>Edit Settings</a>.</p></div>";
    }

    public function set_filters() {
      // Add the necessary hooks here if verified - remove the built-in security
      // ONLY set the filters on POST requests
      if (($this->is_api_key_verified()) && (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')) {
        if ($this->is_on_for_posts() && $this->is_posts_rule_verified()) {
          remove_filter('content_save_pre', 'wp_filter_post_kses');
          remove_filter('excerpt_save_pre', 'wp_filter_post_kses');
          remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
          add_filter('content_save_pre', array(&$this, 'filter_posts'));
          add_filter('excerpt_save_pre', array(&$this, 'filter_posts'));
          add_filter('content_filtered_save_pre', array(&$this, 'filter_posts'));
        }
        if ($this->is_on_for_comments() && $this->is_comments_rule_verified()) {
          remove_filter('pre_comment_content', 'wp_filter_kses');
          add_filter('pre_comment_content', array(&$this, 'filter_comments'));
        }
      }
      else {
        $this->revert_filters();
      }
    }

    public function banner() {
      // Key verification
      if (!$this->is_api_key_verified()) {
        add_action('admin_notices', array(&$this, 'smartfilter_alert_key'));
      }
      else {
        // Posts rule verification
        if ($this->is_on_for_posts() && !$this->is_posts_rule_verified()) {
          add_action('admin_notices', array(&$this, 'smartfilter_alert_posts_rule'));
        }
        // Comments rule verification
        if ($this->is_on_for_comments() && !$this->is_comments_rule_verified()) {
          add_action('admin_notices', array(&$this, 'smartfilter_alert_comments_rule'));
        }
        // Quota limits
        if ($this->is_at_quota()) {
          add_action('admin_notices', array(&$this, 'smartfilter_alert_quota'));
        }
      }
    }

    public function is_on_for_posts() {
      return (get_option('smartfilter_filterposts') == 1);
    }

    public function is_on_for_comments() {
      return (get_option('smartfilter_filtercomments') == 1);
    }

    public function is_api_key_verified() {
      return (get_option('smartfilter_verified') == 1);
    }

    public function is_posts_rule_verified() {
      return (get_option('smartfilter_postsruleverified') == 1);
    }

    public function is_comments_rule_verified() {
      return (get_option('smartfilter_commentsruleverified') == 1);
    }

    public function is_at_quota() {
      return (get_option('smartfilter_quota_reached') == 1);
    }

    public function verify() {
      $key = get_option('smartfilter_apikey');
      if (strlen($key) > 0) {
        $client = new SmartFilterClient($key);
        try {
          if ($client->verify()) {
            update_option('smartfilter_verified', 1);
            return true;
          }
        } 
        catch (Exception $e) {
          // Pass through to end
        }
      }
      update_option('smartfilter_verified', 0);
      return false;
    }

    public function verify_posts_rule() {
      $rule = get_option('smartfilter_postsrulekey');
      if ($this->is_api_key_verified() && strlen($rule) > 0) {
        $key = get_option('smartfilter_apikey');
        $client = new SmartFilterClient($key);
        try {
          if ($client->verify_rule($rule)) {
            update_option('smartfilter_postsruleverified', 1);
            return true;
          }
        } 
        catch (Exception $e) {
          // Pass through to end
        }
      }
      update_option('smartfilter_postsruleverified', 0);
      return false;
    }

    public function verify_comments_rule() {
      $rule = get_option('smartfilter_commentsrulekey');
      if ($this->is_api_key_verified() && strlen($rule) > 0) {
        $key = get_option('smartfilter_apikey');
        $client = new SmartFilterClient($key);
        try {
          if ($client->verify_rule($rule)) {
            update_option('smartfilter_commentsruleverified', 1);
            return true;
          }
        } 
        catch (Exception $e) {
          // Pass through to end
        }
      }
      update_option('smartfilter_commentsruleverified', 0);
      return false;
    }

    public function check_quota() {
      $info = $this->info();
      if (isset($info['maximum']) && isset($info['remaining'])) {
        // Limited plans
        if (intval($info['maximum']) > 0) {
          if (intval($info['remaining']) <= 0) {
            update_option('smartfilter_quota_reached', 1);
          }
          else {
            update_option('smartfilter_quota_reached', 0);
          }
        }
        // Unlimited plans
        else if (intval($info['maximum']) == 0) {
          update_option('smartfilter_quota_reached', 0);
        }
      }
    }

    public function info() {
      if ($this->is_api_key_verified()) {
        $key = get_option('smartfilter_apikey');
        try {
          $client = new SmartFilterClient($key);
          return $client->info();
        }
        catch (Exception $e) {
          // Pass through to end
        }
      }
      return array();
    }

    public function filter_posts($data) {
      // Do not waste a call if the data is empty
      if (strlen(trim($data)) == 0) {
        return $data;
      }
      $key = get_option('smartfilter_apikey');
      $rule = get_option('smartfilter_postsrulekey');
      $client = new SmartFilterClient($key);
      try {
        $data = stripslashes($data);
        $response = $client->filter($data, $rule);
        if (isset($response['output'])) {
          $this->check_quota();
          return $response['output'];
        }
      } 
      catch (Exception $e) {
        // Pass through to end
      }
      $this->revert_filters();
      return wp_filter_post_kses($data);
    }

    public function filter_comments($data) {
      // Do not waste a call if the data is empty
      if (strlen(trim($data)) == 0) {
        return $data;
      }
      $key = get_option('smartfilter_apikey');
      $rule = get_option('smartfilter_commentsrulekey');
      $client = new SmartFilterClient($key);
      try {
        $data = stripslashes($data);
        $response = $client->filter($data, $rule);
        if (isset($response['output'])) {
          $this->check_quota();
          return $response['output'];
        }
      } 
      catch (Exception $e) {
        // Pass through to end
      } 
      $this->revert_filters();
      return wp_filter_kses($data);
    }
  }
}

register_activation_hook(__FILE__, array('SmartFilterPlugin', 'activate')); 
register_deactivation_hook(__FILE__, array('SmartFilterPlugin', 'deactivate')); 
$smartfilter = new SmartFilterPlugin(); 
$smartfilter->set_filters();
$smartfilter->banner();
