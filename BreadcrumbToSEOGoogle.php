<?php
/**
 * Plugin Name: Breadcrumb to SEO Google
 * Description: Plugin for Breadcrumbs, with SEO attributes
 * Plugin URI:  https://github.com/vvalikpavlenko/BreadcrumbToSEOGoogle
 * Author:      Valik Pavlenko
 * Author URI:  https://valik.pavlenko.org.ua/
 * Version:     1.3.0
 * License: GPLv2 or later
 * Text Domain: breadcrumbtoseo
 *
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

function name_in_breadcrumb($name, $positionIndex)
{
  $str_link = '<span itemprop="name">%1$s</span><meta itemprop="position" content="%2$s" />';
  return sprintf($str_link, $name, $positionIndex);
}

function link_in_breadcrumb($name, $positionIndex, $link)
{
  $str_link = '<a href="%1$s" itemprop="item" title="%2$s"><span itemprop="name">%2$s</span></a><meta itemprop="position" content="%3$s" />';

  return sprintf($str_link, $link, $name, $positionIndex);
}

function list_in_breadcrumb($name, $positionIndex, $link = false, $class = false)
{
  $list = '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';

  $list .= $class ? 'class="vv-breadcrumb__item ' . $class . '">' : 'class="vv-breadcrumb__item">';

  $list .= $link ? link_in_breadcrumb($name, $positionIndex, $link) : name_in_breadcrumb($name, $positionIndex);

  $list .= '</li>';

  return $list;
}

function vv_breadcrumb()
{

  $positionIndex = 1;
  // Get text domain for translations
  $theme = wp_get_theme();
  $text_domain = $theme->get('TextDomain');

  // Open list
  $breadcrumb = '<ul itemscope itemtype="https://schema.org/BreadcrumbList" id="breadcrumb" class="vv-breadcrumb">';

  // Front page
  if (is_front_page()) {
    $breadcrumb .= list_in_breadcrumb(get_bloginfo('name'), $positionIndex);
  } else {
    $breadcrumb .= list_in_breadcrumb(get_bloginfo('name'), $positionIndex, home_url());
  }
  $positionIndex++;

  // Category, tag, author and date archives
  if (is_archive() && !is_tax() && !is_post_type_archive()) {

    // Title of archive
    if (is_category() or is_tag()) {
      $breadcrumb .= list_in_breadcrumb(single_cat_title('', false), $positionIndex++);
    } else if (is_author()) {
      $breadcrumb .= list_in_breadcrumb(get_the_author(), $positionIndex++);
    } else if (is_date()) {
      if (is_day()) {
        $breadcrumb .= list_in_breadcrumb(get_the_time('F j, Y'), $positionIndex++);
      } else if (is_month()) {
        $breadcrumb .= list_in_breadcrumb(get_the_time('F, Y'), $positionIndex++);
      } else if (is_year()) {
        $breadcrumb .= list_in_breadcrumb(get_the_time('Y'), $positionIndex++);
      }
    }
  } // Posts
  else if (is_singular('post')) {

    // Post categories
    $post_cats = get_the_category();

    if ($post_cats[0]) {
      $breadcrumb .= list_in_breadcrumb($post_cats[0]->name, $positionIndex, get_category_link($post_cats[0]->term_id));
      $positionIndex++;
    }

    // Post title
    $breadcrumb .= list_in_breadcrumb(get_the_title(), $positionIndex, false, 'is-active');
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
        $crumbs[] = list_in_breadcrumb(get_the_title($page->ID), $positionIndex++, get_permalink($page->ID));
        $parent_id = $page->post_parent;
      }

      $crumbs = array_reverse($crumbs);

      foreach ($crumbs as $crumb) {
        $breadcrumb .= $crumb;
      }
    }

    // Page title
    $breadcrumb .= list_in_breadcrumb(get_the_title(), $positionIndex++, false, 'is-active');
  }

  // Attachments
  if (is_attachment()) {
    // Attachment parent
    $post = get_post(get_the_ID());

    if ($post->post_parent) {
      $breadcrumb .= list_in_breadcrumb(get_the_title($post->post_parent), $positionIndex++, get_permalink($post->post_parent));
    }

    // Attachment title
    $breadcrumb .= list_in_breadcrumb(get_the_title(), $positionIndex++, false, 'is-active');
  }

  // Search
  if (is_search()) {
    $breadcrumb .= list_in_breadcrumb(__('Search', $text_domain), $positionIndex++, false, 'is-active');;
  }

  // 404
  if (is_404()) {
    $breadcrumb .= list_in_breadcrumb(__('404', $text_domain), $positionIndex++, false, 'is-active');
  }

  // Custom Post Type Archive
  if (is_post_type_archive()) {
    $breadcrumb .= list_in_breadcrumb(post_type_archive_title('', false), $positionIndex++, false, 'is-active');
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
        $breadcrumb .= list_in_breadcrumb($cpt_obj->labels->name, $positionIndex++, get_post_type_archive_link($cpt_obj->name));
        $output = true;
      }
    }

    // Term title
    $breadcrumb .= list_in_breadcrumb(single_term_title('', false), $positionIndex++, false, 'is-active');
  }

  // Custom Post Types
  if (is_single() && get_post_type() != 'post' && get_post_type() != 'attachment') {
    $cpt_obj = get_post_type_object(get_post_type());

    // Is cpt hierarchical like pages or posts?
    if (is_post_type_hierarchical($cpt_obj->name)) {
      // Like pages

      // Cpt archive
      if (get_post_type_archive_link($cpt_obj->name)) {
        $breadcrumb .= list_in_breadcrumb($cpt_obj->labels->name, $positionIndex++, get_post_type_archive_link($cpt_obj->name));
      }

      // Cpt parents
      $post = get_post(get_the_ID());

      if ($post->post_parent) {
        $parent_id = $post->post_parent;
        $crumbs = [];

        while ($parent_id) {
          $page = get_page($parent_id);
          $crumbs[] = link_in_breadcrumb(get_the_title($page->ID), $positionIndex++, get_permalink($page->ID));
          $parent_id = $page->post_parent;
        }

        $crumbs = array_reverse($crumbs);

        foreach ($crumbs as $crumb) {
          $breadcrumb .= list_in_breadcrumb($crumb, $positionIndex++);
        }
      }
    } else {
      // Like posts

      // Cpt archive
      if (get_post_type_archive_link($cpt_obj->name)) {
        $breadcrumb .= list_in_breadcrumb($cpt_obj->labels->name, $positionIndex, get_post_type_archive_link($cpt_obj->name));
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
              $breadcrumb .= list_in_breadcrumb($cpt_term->name, $positionIndex++, get_term_link($cpt_term->name, $cpt_taxes[0]));
              $output = true;
            }
          }
        }
      }
    }

    // Cpt title
    $breadcrumb .= list_in_breadcrumb(get_the_title(), $positionIndex, false, 'is-active');
  }

  // Close list
  $breadcrumb .= '</ul>';

  // Ouput
  echo $breadcrumb;

}

add_shortcode('vv_breadcrumb', 'vv_breadcrumb');
?>
