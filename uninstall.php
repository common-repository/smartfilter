<?php
if (!defined('WP_UNINSTALL_PLUGIN'))
  exit();

delete_option('smartfilter_apikey');
delete_option('smartfilter_postsrulekey');
delete_option('smartfilter_commentsrulekey');
delete_option('smartfilter_filterposts');
delete_option('smartfilter_filtercomments');
delete_option('smartfilter_verified');
delete_option('smartfilter_postsruleverified');
delete_option('smartfilter_commentsruleverified');
delete_option('smartfilter_quota_reached');
