<?php global $smartfilter; ?>

<div class="wrap" style="width:800px;">
  <h2>SmartFilter Settings</h2> 
  <form method="post" action="options-general.php?page=smartfilter">
    <table class="form-table">
      <tr valign="top">
        <td colspan="2">
          <h3 style="margin-top:0;">1. Get your API Key</h3>
          <p>
            <a href="https://smartfiltersecurity.com" target="_blank">SmartFilter</a> enables your audience to share links, embeds, markups and rich content in their posts and/or comments without the risk of being hacked by cross-site scripting (XSS) attacks. 
            In order for SmartFilter to work, you first need to get your keys from <a href="https://smartfiltersecurity.com/wordpress" target="_blank">SmartFilterSecurity.com</a>.
          </p>
          <p>
            Please enter your API key below (<a href="https://smartfiltersecurity.com/wordpress" target="_blank">Get your key</a>):
          </p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="apikey"><strong>API Key:</strong></label></th>
        <td>
          <input style="width:300px" type="text" name="apikey" id="apikey" value="<?php echo get_option('smartfilter_apikey'); ?>" />
          <span id="apikey_status">
            <?php if ($smartfilter->is_api_key_verified()) { ?>
              <img src="<?php echo plugins_url('green.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />
            <? } else { ?>
              <img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />
            <? } ?>
          </span>
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <h3>2. Choose how you want SmartFilter to work</h3>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="protection"><strong>Protection:</strong></label></th>
        <td>
          <input type="checkbox" name="filterposts" id="filterposts" <?php if ($smartfilter->is_on_for_posts()) { echo "checked"; } ?> /> Protect my posts from XSS<br/>
          <input type="checkbox" name="filtercomments" id="filtercomments" <?php if ($smartfilter->is_on_for_comments()) { echo "checked"; } ?> /> Protect my comments from XSS
        </td>
      </tr>
      <tr id="rule_definition" class="rule" valign="top">
        <td colspan="2">
          <h3>3. Set your custom rules for posts and comments</h3>
          <p>
            Select what kind of content you want to safely pass through your SmartFilter (HTML tags, attributes, protocols, media, etc.). 
            Easily fine-tune your own SmartFilter at <a href="https://smartfiltersecurity.com/rules" target="_blank">SmartFilterSecurity.com/rules</a> and enter your own rule keys below. 
          </p>
        </td>
      </tr>
      <tr id="posts_rule_form" class="rule" valign="top">
        <th scope="row"><label for="postsrulekey"><strong>Posts Rule Key:</strong></label></th>
        <td>
          <input style="width:300px" type="text" name="postsrulekey" id="postsrulekey" value="<?php echo get_option('smartfilter_postsrulekey'); ?>" />
          <span id="postsrulekey_status">
            <?php if ($smartfilter->is_posts_rule_verified()) { ?>
              <img src="<?php echo plugins_url('green.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />
            <? } else { ?>
              <img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />
            <? } ?>
          </span>
          <br/>
          <small>The rule key you want to use to filter posts. <a href="https://smartfiltersecurity.com/rules" target="_blank">Manage your rules</a>.</small>
        </td>
      </tr>
      <tr id="comments_rule_form" class="rule" valign="top">
        <th scope="row"><label for="commentsrulekey"><strong>Comments Rule Key:</strong></label></th>
        <td>
          <input style="width:300px" type="text" name="commentsrulekey" id="commentsrulekey" value="<?php echo get_option('smartfilter_commentsrulekey'); ?>" />
          <span id="commentsrulekey_status">
            <?php if ($smartfilter->is_comments_rule_verified()) { ?>
              <img src="<?php echo plugins_url('green.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />
            <? } else { ?>
              <img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />
            <? } ?>
          </span>
          <br/>
          <small>The rule key you want to use to filter comments. <a href="https://smartfiltersecurity.com/rules" target="_blank">Manage your rules</a>.</small>
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <h3 id="notification_heading">
            <? if (!$smartfilter->is_on_for_posts() && !$smartfilter->is_on_for_comments()) { ?>
              3. Notifications
            <? } else { ?>
              4. Notifications
            <? } ?>
          </h3>
          <p>
            SmartFilter is set up to send you email notifications when it detects and filters suspicious content. 
            <a href="https://smartfiltersecurity.com/alerts" target="_blank">Enable alerts</a> to receive email notifications. 
          </p>
        </td>
      </tr>
    </table>
    <?php 
      wp_nonce_field('smartfilter_settings','nonce');
      @submit_button(); 
    ?>
  </form>
</div>
<div style="width: 600px; float:left; border: 1px solid #ccc; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; background: #F7F7F9; padding: 0 15px; margin: 20px 0 0 0; line-height:20px;">
  <h3>Quick Statistics</h3>
  <p>Keep track of every time SmartFilter is called into use and make sure you’re always one step ahead.</p>
  <?php
    $info = $smartfilter->info();
    if (count($info) > 0) {
      $maximum = $info['maximum'];
      $used = $info['used'];
      $remaining = $info['remaining'];
      echo "<p><strong>Allowed SmartCall Requests Per Month</strong>: <u>$maximum</u><br/><strong>This Month's Requests</strong>: <u>$used</u><br/><strong>Remaining Requests</strong>: <u>$remaining</u></p>";
      if ($remaining == 0) {
        echo "<p>You do not have any remaining requests. <a href='https://smartfiltersecurity.com/dashboard'>Upgrade your plan</a>.</p>";
      }
    }
    else {
      echo "<p>Could not fetch statistics for the provided key. Please check the key and try again.</p>";
    }
  ?>
  <p>Need more SmartCall requests? <a href="https://smartfiltersecurity.com/home" target="_blank">Upgrade</a> your account today.</p>
</div>
<br clear="both" />

<style>
<? if (!$smartfilter->is_on_for_posts() && !$smartfilter->is_on_for_comments()) { ?>.rule { display: none; }<? } ?>
<? if (!$smartfilter->is_on_for_posts()) { ?>#posts_rule_form { display: none; }<? } ?>
<? if (!$smartfilter->is_on_for_comments()) { ?>#comments_rule_form { display: none; }<? } ?>
</style>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script>
function check_api_key() {
    var api_key = $('#apikey').val();
    if (api_key.length == 36) {
      $.get("<?php echo plugins_url('api_key_check.php', __FILE__); ?>", { apikey:api_key }, function(data) {
        if (data == 1) {
          $('#apikey_status').html('<img src="<?php echo plugins_url('green.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
        } else {
          $('#apikey_status').html('<img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
        }
      });
    }
    else if (api_key.length == 0) {
      $('#apikey_status').html('<img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
    }
}

function check_posts_rule_key() {
    var api_key = $('#apikey').val();
    var rule_key = $('#postsrulekey').val();
    if (api_key.length == 36 && (rule_key.length == 36 || rule_key.length == 4)) {
      $.get("<?php echo plugins_url('rule_key_check.php', __FILE__); ?>", { apikey:api_key, rulekey:rule_key }, function(data) {
        if (data == 1) {
          $('#postsrulekey_status').html('<img src="<?php echo plugins_url('green.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
        } else {
          $('#postsrulekey_status').html('<img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
        }
      });
    }
    else if (rule_key.length == 0) {
      $('#postsrulekey_status').html('<img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
    }
}

function check_comments_rule_key() {
    var api_key = $('#apikey').val();
    var rule_key = $('#commentsrulekey').val();
    if (api_key.length == 36 && (rule_key.length == 36 || rule_key.length == 4)) {
      $.get("<?php echo plugins_url('rule_key_check.php', __FILE__); ?>", { apikey:api_key, rulekey:rule_key }, function(data) {
        if (data == 1) {
          $('#commentsrulekey_status').html('<img src="<?php echo plugins_url('green.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
        } else {
          $('#commentsrulekey_status').html('<img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
        }
      });
    }
    else if (rule_key.length == 0) {
      $('#commentsrulekey_status').html('<img src="<?php echo plugins_url('red.png', __FILE__); ?>" width="20" style="position:relative; top:6px; left:5px;" />');
    }
}

$('#apikey').blur(function() {
   check_api_key();
});

$('#apikey').keyup(function() {
   check_api_key();
});

$('#postsrulekey').blur(function() {
   check_posts_rule_key();
});

$('#postsrulekey').keyup(function() {
   check_posts_rule_key();
});

$('#commentsrulekey').blur(function() {
   check_comments_rule_key();
});

$('#commentsrulekey').keyup(function() {
   check_comments_rule_key();
});

function close_rule_keys() {
  if (!$('#filterposts').prop('checked') && !$('#filtercomments').prop('checked')) {
    $('.rule').hide();
    $('#notification_heading').html("3. Notifications");
  }
}
$('#filterposts').click(function() {
  $('#rule_definition').show();
  if ($('#filterposts').prop('checked')) {
    $('#posts_rule_form').show();
    check_posts_rule_key();
    $('#notification_heading').html("4. Notifications");
  }
  else {
    $('#posts_rule_form').hide();
  }
  close_rule_keys();
});
$('#filtercomments').click(function() {
  $('#rule_definition').show();
  if ($('#filtercomments').prop('checked')) {
    $('#comments_rule_form').show();
    check_comments_rule_key();
    $('#notification_heading').html("4. Notifications");
  }
  else {
    $('#comments_rule_form').hide();
  }
  close_rule_keys();
});
</script>
