<div class="wrap">
  <h1 class="wp-heading-inline">
    <?php _e('Options', 'vvBreadcrumbToSEO'); ?>
  </h1>
  <?php settings_errors(); ?>
  <div>
    <form method="post" action="options.php">
      <?php
      settings_fields('breadcrumb_setting');
      do_settings_sections('setting_breadcrumb_to_seo');
      submit_button();
      ?>
    </form>
  </div>
</div>
