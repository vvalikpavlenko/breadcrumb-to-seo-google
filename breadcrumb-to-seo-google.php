<?php
/**
 * Plugin Name: Breadcrumb to SEO Google
 * Description: Plugin for Breadcrumbs, with SEO attributes
 * Plugin URI:  hhttps://github.com/vvalikpavlenko/breadcrumb-to-seo-google
 * Author:      Valentyn Pavlenko
 * Author URI:  https://valik.pavlenko.org.ua/
 * Version:     1.3.1
 * License: GPLv2 or later
 * Text Domain: vvBreadcrumbToSEO
 * Requires PHP: 7.4
 *
 * @package BreadcrumbToSEOGoogle
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

Copyright 2005-2023 Automattic, Inc.
*/

defined('ABSPATH') || exit;

class_exists('VVBreadcrumb') || exit;

if (!defined('VVBREADCRUMB_VER')) {
  define('VVBREADCRUMB_VER', '1.3.1');
}

// Define Directory PATH
if (!defined('VVBREADCRUMB_DIR_PATH')) {
  define('VVBREADCRUMB_DIR_PATH', plugin_dir_path(__FILE__));
}


class VVBreadcrumb
{
  public $icon_home = "<svg width='24' viewBox='0 0 24 24' fill='currentColor' xmlns='http://www.w3.org/2000/svg'><path d='M11.9998 3.26318L1.26318 12.6253H4.48421V20.9474H9.85276V14.706H14.1473V20.9474H19.5158V12.6253H22.7369L11.9998 3.26318Z'/></svg>";

  public function register()
  {
    add_action('vv_breadcrumb', [$this, 'get_breadcrumb']);
    add_shortcode('vv_breadcrumb', [$this, 'get_breadcrumb']);

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_plugin_setting_link']);

    // Add menu in admin 'admin point'
    add_action('admin_menu', [$this, 'add_custom_menu']);

    add_action('admin_init', [$this, 'setting_init']);
    add_action('wp_head', [$this, 'get_style_front']);
  }

  public function add_custom_menu()
  {
    add_menu_page(
      esc_html__('Breadcrumb', 'xProductSettings'),
      esc_html__('Breadcrumb', 'xProductSettings'),
      'manage_options',
      'setting_breadcrumb_to_seo',
      [$this, 'setting_breadcrumb_page'],
      'dashicons-building',
      12
    );
  }

  public function add_plugin_setting_link($links)
  {
    $url = esc_url(add_query_arg(
      'page',
      'setting_breadcrumb_to_seo',
      get_admin_url() . 'admin.php'
    ));
    $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
    array_push(
      $links,
      $settings_link
    );
    return $links;
  }

  public function setting_breadcrumb_page()
  {
    require_once VVBREADCRUMB_DIR_PATH . 'admin/admin.php';
  }

  public function setting_init()
  {
    $id_options = 'breadcrumb_setting_options';
    $page = 'setting_breadcrumb_to_seo';
    register_setting('breadcrumb_setting', $id_options);

    add_settings_section(
      $id_options,
      esc_html__('Settings', 'xProductSettings'),
      [$this, 'settings_section_html'],
      $page
    );

    add_settings_field(
      'separator_navigation',
      esc_html__('Separator in navigation chains', 'vvBreadcrumbToSEO'),
      [$this, 'get_field_html'],
      $page,
      $id_options,
      [
        'type'         => 'text',
        'name'         => 'separator_navigation',
        'section'      => $id_options,
        'defaultValue' => '»'
      ]
    );

    add_settings_field(
      'styles_class',
      esc_html__('Add class in block', 'vvBreadcrumbToSEO'),
      [$this, 'get_field_html'],
      $page,
      $id_options,
      [
        'type'    => 'text',
        'name'    => 'styles_class',
        'section' => $id_options
      ]
    );

    add_settings_field(
      'type_page_home',
      esc_html__('Type text page home', 'vvBreadcrumbToSEO'),
      [$this, 'get_field_html'],
      $page,
      $id_options,
      [
        'type'    => 'select',
        'name'    => 'type_page_home',
        'section' => $id_options,
        'options' => [
          [
            'value' => 'home',
            'label' => esc_html__('Name of the site', 'vvBreadcrumbToSEO')
          ],
          [
            'value' => 'text',
            'label' => esc_html__('Text', 'vvBreadcrumbToSEO')
          ],
          [
            'value' => 'icon',
            'label' => esc_html__('Icon home', 'vvBreadcrumbToSEO')
          ]
        ]
      ]
    );

    add_settings_field(
      'title_home',
      esc_html__('Title home', 'vvBreadcrumbToSEO'),
      [$this, 'get_field_html'],
      $page,
      $id_options,
      [
        'type'    => 'text',
        'name'    => 'title_home',
        'section' => $id_options
      ]
    );
  }

  public function settings_section_html()
  {
    echo esc_html__('Global settings', 'vvBreadcrumbToSEO');
  }

  public function add_name($name, $positionIndex)
  {
    $str_link = '<span itemprop="name">%1$s</span><meta itemprop="position" content="%2$s" />';
    return sprintf($str_link, $name, $positionIndex);
  }

  public function add_link($name, $positionIndex, $link)
  {
    $str_link = '<a href="%1$s" itemprop="item" title="%2$s"><span itemprop="name">%2$s</span></a><meta itemprop="position" content="%3$s" />';

    return sprintf($str_link, $link, $name, $positionIndex);
  }

  public function add_list($name, $positionIndex, $link = false, $class = false)
  {
    $list = '<li itemprop="itemListElement" class="vp-breadcrumb__item" itemscope itemtype="https://schema.org/ListItem"';

    $list .= $class ? 'class="vv-breadcrumb__item ' . $class . '">' : 'class="vv-breadcrumb__item">';

    $list .= $link ? $this->add_link($name, $positionIndex, $link) : $this->add_name($name, $positionIndex);

    $list .= '</li>';

    return $list;
  }

  public function get_breadcrumb()
  {
    $positionIndex = 1;
    // Get text domain for translations
    $theme = wp_get_theme();
    $text_domain = $theme->get('TextDomain');
    $options = get_option('breadcrumb_setting_options');
    $type_page_home = $options['type_page_home'];
    // Open list
    $breadcrumb = '<ul itemscope itemtype="https://schema.org/BreadcrumbList" id="breadcrumb" class="vp-breadcrumb">';
    $home_name = get_bloginfo('name');
    switch ($type_page_home) {
      case 'icon':
        $home_name = $this->icon_home;
        break;
      case 'text':
        $home_name = $options['title_home'];
        break;
    }
    // Front page
    if (is_front_page()) {
      $breadcrumb .= $this->add_list($home_name, $positionIndex);
    } else {
      $breadcrumb .= $this->add_list($home_name, $positionIndex, home_url());
    }
    $positionIndex++;

    // Category, tag, author and date archives
    if (is_archive() && !is_tax() && !is_post_type_archive()) {

      // Title of archive
      if (is_category() or is_tag()) {
        $breadcrumb .= $this->add_list(single_cat_title('', false), $positionIndex++);
      } else if (is_author()) {
        $breadcrumb .= $this->add_list(get_the_author(), $positionIndex++);
      } else if (is_date()) {
        if (is_day()) {
          $breadcrumb .= $this->add_list(get_the_time('F j, Y'), $positionIndex++);
        } else if (is_month()) {
          $breadcrumb .= $this->add_list(get_the_time('F, Y'), $positionIndex++);
        } else if (is_year()) {
          $breadcrumb .= $this->add_list(get_the_time('Y'), $positionIndex++);
        }
      }
    } // Posts
    else if (is_singular('post')) {

      // Post categories
      $post_cats = get_the_category();

      if ($post_cats[0]) {
        $breadcrumb .= $this->add_list($post_cats[0]->name, $positionIndex, get_category_link($post_cats[0]->term_id));
        $positionIndex++;
      }

      // Post title
      $breadcrumb .= $this->add_list(get_the_title(), $positionIndex, false, 'is-active');
      $positionIndex++;
    } // Pages
    else if (is_page() && !is_front_page()) {
      $post = get_post(get_the_ID());

      // Page parents
      if ($post->post_parent) {
        $parent_id = $post->post_parent;
        $crumbs = [];

        while ($parent_id) {
          $page = get_page($parent_id);
          $crumbs[] = $this->add_list(get_the_title($page->ID), $positionIndex++, get_permalink($page->ID));
          $parent_id = $page->post_parent;
        }

        $crumbs = array_reverse($crumbs);

        foreach ($crumbs as $crumb) {
          $breadcrumb .= $crumb;
        }
      }

      // Page title
      $breadcrumb .= $this->add_list(get_the_title(), $positionIndex++, false, 'is-active');
    }

    // Attachments
    if (is_attachment()) {
      // Attachment parent
      $post = get_post(get_the_ID());

      if ($post->post_parent) {
        $breadcrumb .= $this->add_list(get_the_title($post->post_parent), $positionIndex++, get_permalink($post->post_parent));
      }

      // Attachment title
      $breadcrumb .= $this->add_list(get_the_title(), $positionIndex++, false, 'is-active');
    }

    // Search
    if (is_search()) {
      $breadcrumb .= $this->add_list(__('Search', $text_domain), $positionIndex++, false, 'is-active');;
    }

    // 404
    if (is_404()) {
      $breadcrumb .= $this->add_list(__('404', $text_domain), $positionIndex++, false, 'is-active');
    }

    // Custom Post Type Archive
    if (is_post_type_archive()) {
      $breadcrumb .= $this->add_list(post_type_archive_title('', false), $positionIndex++, false, 'is-active');
    }

    // Custom Taxonomies
    if (is_tax()) {
      // Get the post types the taxonomy is attached to
      $current_term = get_queried_object();

      $attached_to = [];
      $post_types = get_post_types();

      foreach ($post_types as $post_type) {
        $taxonomies = get_object_taxonomies($post_type);

        if (in_array($current_term->taxonomy, $taxonomies)) {
          $attached_to[] = $post_type;
        }
      }

      // Post type archive link
      $output = false;

      foreach ($attached_to as $post_type) {
        $cpt_obj = get_post_type_object($post_type);

        if (!$output && get_post_type_archive_link($cpt_obj->name)) {
          $breadcrumb .= $this->add_list($cpt_obj->labels->name, $positionIndex++, get_post_type_archive_link($cpt_obj->name));
          $output = true;
        }
      }

      // Term title
      $breadcrumb .= $this->add_list(single_term_title('', false), $positionIndex++, false, 'is-active');
    }

    // Custom Post Types
    if (is_single() && get_post_type() != 'post' && get_post_type() != 'attachment') {
      $cpt_obj = get_post_type_object(get_post_type());

      // Is cpt hierarchical like pages or posts?
      if (is_post_type_hierarchical($cpt_obj->name)) {
        // Like pages

        // Cpt archive
        if (get_post_type_archive_link($cpt_obj->name)) {
          $breadcrumb .= $this->add_list($cpt_obj->labels->name, $positionIndex++, get_post_type_archive_link($cpt_obj->name));
        }

        // Cpt parents
        $post = get_post(get_the_ID());

        if ($post->post_parent) {
          $parent_id = $post->post_parent;
          $crumbs = [];

          while ($parent_id) {
            $page = get_page($parent_id);
            $crumbs[] = $this->add_list(get_the_title($page->ID), $positionIndex++, get_permalink($page->ID));
            $parent_id = $page->post_parent;
          }

          $crumbs = array_reverse($crumbs);

          foreach ($crumbs as $crumb) {
            $breadcrumb .= $this->add_list($crumb, $positionIndex++);
          }
        }
      } else {
        // Like posts

        // Cpt archive
        if (get_post_type_archive_link($cpt_obj->name)) {
          $breadcrumb .= $this->add_list($cpt_obj->labels->name, $positionIndex, get_post_type_archive_link($cpt_obj->name));
        }

        // Get cpt taxonomies
        $cpt_taxes = get_object_taxonomies($cpt_obj->name);

        if ($cpt_taxes && is_taxonomy_hierarchical($cpt_taxes[0])) {
          // Other taxes attached to the cpt could be hierachical, so need to look into that.
          $cpt_terms = get_the_terms(get_the_ID(), $cpt_taxes[0]);

          if (is_array($cpt_terms)) {
            $output = false;

            foreach ($cpt_terms as $cpt_term) {
              if (!$output) {
                $breadcrumb .= $this->add_list($cpt_term->name, $positionIndex++, get_term_link($cpt_term->term_taxonomy_id));
                $output = true;
              }
            }
          }
        }
      }

      // Cpt title
      $breadcrumb .= $this->add_list(get_the_title(), $positionIndex, false, 'is-active');
    }

    // Close list
    $breadcrumb .= '</ul>';

    // Ouput
    echo $breadcrumb;
  }

  public function get_style_front()
  {
    require_once VVBREADCRUMB_DIR_PATH . 'front/style.php';
  }

  public function get_field_html($args)
  {
    $options = get_option($args['section']);
    if ($args['type'] == 'checkbox') {
      ?>
      <input type="checkbox" <?php if ($args['required']) echo 'required' ?> id="<?php echo $args['name']; ?>"
             name="<?php echo "{$args['section']}[{$args['name']}]"; ?>"
             placeholder="<?php echo $args['placeholder']; ?>"
             value="<?php echo $args['value']; ?>" <?php echo checked($args['value'], $options[$args['name']], false); ?> />
      <?php
    } elseif ($args['type'] == 'select') {
      ?>
      <select <?php if ($args['required']) echo 'required' ?>
        name="<?php echo "{$args['section']}[{$args['name']}]"; ?>"
        id="<?php echo $args['name']; ?>" placeholder="<?php echo $args['placeholder']; ?>"
        value="<?php echo $options[$args['name']]; ?>">
        <?php
        foreach ($args['options'] as $item) {
          $selected = $item['value'] === $options[$args['name']] ? 'selected="selected"' : '';
          echo "<option value='{$item['value']}' " . $selected . ">{$item['label']}</option>";
        }
        ?>
      </select>
      <?php
    } else {
      $value = $options[$args['name']];
      if ($args['required']) {
        $value = $value ? $value : $args['defaultValue'];
      }
      ?>
      <input <?php if ($args['required']) echo 'required' ?>
        type="<?php echo $args['type']; ?>"
        id="<?php echo $args['тфьу']; ?>"
        name="<?php echo "{$args['section']}[{$args['name']}]"; ?>"
        placeholder="<?php echo $args['placeholder']; ?>"
        value="<?php echo $value; ?>"
        <?php if ($args['min']) echo 'min="' . $args['min'] . '"'; ?> />
      <?php
    }
  }
}

$vVBreadcrumb = new VVBreadcrumb();
$vVBreadcrumb->register();
?>
