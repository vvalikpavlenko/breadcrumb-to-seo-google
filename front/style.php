<?php
defined('ABSPATH') || exit;
$options = get_option('breadcrumb_setting_options');
$separator_navigation = $options['separator_navigation'];
?>

<style>
  .vp-breadcrumb li:not(:last-child):after {
    content: "<?php echo $separator_navigation;?>";
  }

  .vp-breadcrumb svg {
    position: relative;
    top: 5px;
  }
</style>
