<?php

/**
 * Plugin Name: Breadcrumb to SEO Google
 * Description: Plugin for Breadcrumbs, with SEO attributes
 * Plugin URI:  hhttps://github.com/vvalikpavlenko/breadcrumb-to-seo-google
 * Author:      Valentyn Pavlenko
 * Author URI:  https://valik.pavlenko.com.ua/
 * Version:     1.3.3
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
  define('VVBREADCRUMB_VER', '1.3.2');
}

// Define Directory PATH
if (!defined('VVBREADCRUMB_DIR_PATH')) {
  define('VVBREADCRUMB_DIR_PATH', plugin_dir_path(__FILE__));
}


class VVBreadcrumb
{
  public $icon_home = "<svg width='24' viewBox='0 0 24 24' fill='currentColor' xmlns='http://www.w3.org/2000/svg'><path d='M11.9998 3.26318L1.26318 12.6253H4.48421V20.9474H9.85276V14.706H14.1473V20.9474H19.5158V12.6253H22.7369L11.9998 3.26318Z'/></svg>";
  public $icon_home_outline = "<svg viewBox='0 0 16 16'
     fill='none'
     stroke='currentColor'
     xmlns='http://www.w3.org/2000/svg'>
  <path d='M1.14258 8L7.99972 1.14285L14.8569 8'
        stroke-width='1.14286'
        stroke-linecap='round'
        stroke-linejoin='round' />
  <path d='M2.66602 6.47618V12.5714C2.66602 12.7735 2.74629 12.9673 2.88917 13.1102C3.03206 13.2531 3.22585 13.3333 3.42792 13.3333H5.71363C5.9157 13.3333 6.1095 13.2531 6.25238 13.1102C6.39527 12.9673 6.47554 12.7735 6.47554 12.5714V9.5238C6.47554 9.32173 6.55581 9.12794 6.6987 8.98505C6.84158 8.84217 7.03537 8.7619 7.23744 8.7619H8.76125C8.96332 8.7619 9.15712 8.84217 9.3 8.98505C9.44289 9.12794 9.52316 9.32173 9.52316 9.5238V12.5714C9.52316 12.7735 9.60343 12.9673 9.74632 13.1102C9.8892 13.2531 10.083 13.3333 10.2851 13.3333H12.5708C12.7728 13.3333 12.9666 13.2531 13.1095 13.1102C13.2524 12.9673 13.3327 12.7735 13.3327 12.5714V6.47618'
        stroke-width='1.14286'
        stroke-linecap='round'
        stroke-linejoin='round' />
</svg>
";

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
      esc_html__('Breadcrumb', 'vvBreadcrumbToSEO'),
      esc_html__('Breadcrumb', 'vvBreadcrumbToSEO'),
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
      esc_html__('Settings', 'vvBreadcrumbToSEO'),
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
          ],
          [
            'value' => 'icon-outline',
            'label' => esc_html__('Icon home outline', 'vvBreadcrumbToSEO')
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

  public function add_link($name, $positionIndex, $link, $title, $type_page_home)
  {
    $options = get_option('breadcrumb_setting_options');
    $className = $options['styles_class'];

    $str_link = '<a href="%1$s" itemprop="item" title="%2$s" class="vp-breadcrumb__link ' . ($className ? $className . '__link' : '') . '">';
    if ($type_page_home == 'icon') {
      $str_link .= '<span style="display: none" itemprop="name">%2$s</span>';
      $str_link .= '<span>%3$s</span>';
    } else {
      $str_link .= '<span itemprop="name">%3$s</span>';
    }
    $str_link .= '</a><meta itemprop="position" content="%4$s" />';


    return sprintf($str_link, $link, $title ? $title : $name, $name, $positionIndex);
  }

  public function add_list($name, $positionIndex, $link = false, $class = false, $attribute_name = false, $type_page_home = false)
  {
    $options = get_option('breadcrumb_setting_options');
    $className = $options['styles_class'];

    $list = '<li itemprop="itemListElement" class="vp-breadcrumb__item ' . ($className ? $className . '__item' : '') . '" itemscope itemtype="https://schema.org/ListItem"';

    $list .= $class ? 'class="vv-breadcrumb__item ' . $class . '">' : 'class="vv-breadcrumb__item">';

    $list .= $link ? $this->add_link($name, $positionIndex, $link, $attribute_name, $type_page_home) : $this->add_name($name, $positionIndex);

    $list .= '</li>';

    return $list;
  }

  public function get_breadcrumb()
  {
    $positionIndex = 1;
    $options = get_option('breadcrumb_setting_options');
    $type_page_home = isset($options['type_page_home']) ? $options['type_page_home'] : 'home';
    $className = isset($options['styles_class']) ? $options['styles_class'] : '';

    // Відкриття списку breadcrumbs
    $breadcrumb = '<ul itemscope itemtype="https://schema.org/BreadcrumbList" id="breadcrumb" class="vp-breadcrumb ' . esc_attr($className) . '">';

    // Домашня сторінка: вибір назви (текст, іконка тощо)
    $home_name = get_bloginfo('name');
    switch ($type_page_home) {
      case 'icon':
        $home_name = $this->icon_home;
        break;
      case 'icon-outline':
        $home_name = $this->icon_home_outline;
        break;
      case 'text':
        $home_name = isset($options['title_home']) ? $options['title_home'] : $home_name;
        break;
    }

    // Вивід першого елемента – домашньої сторінки
    if (is_front_page()) {
      $breadcrumb .= $this->add_list($home_name, $positionIndex, false, false, get_bloginfo('name'), $type_page_home);
    } else {
      $breadcrumb .= $this->add_list($home_name, $positionIndex, home_url(), false, get_bloginfo('name'), $type_page_home);
    }
    $positionIndex++;

    // Якщо відкрита сторінка архіву категорії товарів WooCommerce
    if (is_tax('product_cat')) {
      $current_term = get_queried_object();
      if ($current_term && ! is_wp_error($current_term)) {
        $breadcrumb .= $this->add_list(
          $current_term->name,
          $positionIndex++,
          get_term_link($current_term->term_id, 'product_cat'),
          'is-active'
        );
      }
    }
    // Якщо відкрито сторінку товару WooCommerce
    else if (is_singular('product')) {
      $product_id = get_the_ID();
      $terms = get_the_terms($product_id, 'product_cat');

      if (! empty($terms) && ! is_wp_error($terms)) {
        // Вибираємо першу категорію (головну)
        $main_term = reset($terms);
        $breadcrumb .= $this->add_list(
          $main_term->name,
          $positionIndex++,
          get_term_link($main_term->term_id, 'product_cat')
        );
      }

      // Додаємо назву товару
      $breadcrumb .= $this->add_list(get_the_title(), $positionIndex++, false, 'is-active');
    }
    // Інші умови для архівів, записів, сторінок тощо
    else if (is_archive() && ! is_tax() && ! is_post_type_archive()) {
      if (is_category() || is_tag()) {
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
    }
    // Для записів (блогових статей)
    else if (is_singular('post')) {
      $post_cats = get_the_category();
      if (! empty($post_cats)) {
        $breadcrumb .= $this->add_list($post_cats[0]->name, $positionIndex, get_category_link($post_cats[0]->term_id));
        $positionIndex++;
      }
      $breadcrumb .= $this->add_list(get_the_title(), $positionIndex, false, 'is-active');
      $positionIndex++;
    }
    // Для сторінок
    else if (is_page() && ! is_front_page()) {
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
          $breadcrumb .= $crumb;
        }
      }
      $breadcrumb .= $this->add_list(get_the_title(), $positionIndex++, false, 'is-active');
    }

    // Закриття списку
    $breadcrumb .= '</ul>';

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