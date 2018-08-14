<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit( 'Direct script access denied.' );
}

function web3_fn_site($atts, $content = null){
  global $site_opts;
  $params = array('class'=>'obj-default', 'view'=>'contact', 'title' => '');
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-site-info';
  $classes[] = 'obj-site-'.$view;
  $classes = implode(' ', $classes);
  $infoItems = array();

  $title = apply_filters('web3_replacer', $title);

  $sns = '';
  ob_start();
  echo do_shortcode('[vc_social_icons/]');
  $sns = ob_get_contents();
  ob_end_clean();

  switch($view){
    case 'location':
      $location = get_theme_mod('web3_company_info_address', '');
      $location = nl2br($location);
      $infoItems[] = $location;
      break;
    case 'sns':
      $infoItems[] = $sns;
      break;
    case 'cta':
      $btn_1 = array(
        'url' => '#',
        'title' => __('Select Location')
      );
      $btn_2 = array(
        'url' => '#',
        'title' => __('Contact Us')
      );
      $infoItems[] = sprintf('[web3_vc_btn class_btn="btn-cta" vc_link="url:%s|title:%s"/]', $btn_1['url'], $btn_1['title']);
      $infoItems[] = sprintf('[web3_vc_btn vc_link="url:%s|title:%s" style="2"/]', $btn_2['url'], $btn_2['title']);
      break;
    default:
      if($view === 'location-with'){
        $location = get_theme_mod('web3_company_info_address', '');
        $location = nl2br($location);
        $infoItems[] = '<span class="obj-prefix"><i class="obj-i fa fa-map-marker"></i></span>'.$location;
      }
      $infoItems[] = '<span class="obj-prefix"><i class="obj-i fa fa-phone"></i></span>'.get_theme_mod('web3_company_info_phone', '');
      $email = get_theme_mod('web3_company_info_email', '');
      $email = eae_encode_emails($email);
      $infoItems[] = '<a href="mailto:'.$email.'" target="_blank"><span class="obj-prefix"><i class="obj-i fa fa-envelope"></i></span>'.$email.'</a>';
      if($view === 'sns-with'){
        $infoItems[] = $sns;
      }
      break;
  }
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <?php if(!empty( $title )): ?><h4 class="obj-title"><?= $title; ?></h4><?php endif; ?>
        <ul class="obj-ul">
        <?php $c = 1; foreach ($infoItems as $key => $value) :?>
          <li class="obj-li obj-li-<?= $c; $c++;?>"><div class="obj-text"><?= do_shortcode($value); ?></div></li>
        <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_site', 'web3_fn_site');

function web3_fn_logo($atts, $content = null){
  global $svg;
  $params = array('style'=>'default', 'add'=>'');
  extract(shortcode_atts($params, $atts));

  ob_start(); ?>
  <div class="obj obj-svg-wrap obj-logo">
    <a href="<?= home_url(); ?>" class="obj-link obj-link-logo">
      <?php
      if ( !isset( $svg['logo'] ) ) :
        $custom_logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
      ?>
        <img src="<?= esc_url( $custom_logo[0] ) ?>" alt="<?= esc_attr( get_bloginfo( 'name' ) ); ?>">
      <?php else : ?>
      <?= $svg['logo']; ?>
      <?php endif; ?>
      <?php if($add === 'slogan'): ?>
      <strong class="obj-text obj-text-slogan vc_hidden-xs vc_hidden-sm"><?= get_bloginfo('description') ?: get_theme_mod('web3_company_slogan', 'Moving Global Energy Forward'); ?></strong>
      <?php endif; ?>
    </a>
  </div>
  <?php
  $outputs = ob_get_contents();
  ob_end_clean();
  return $outputs;
}
add_shortcode('web3_logo', 'web3_fn_logo');

function fn_share_elem( $atts, $content = null ) {
  $params = array("class" => 'default', "elem_id" => '');
  extract(shortcode_atts($params, $atts));
  if(empty($elem_id)) return;
  global $styles;

  $ex_page = get_post( $elem_id);
  $ex_page_id = $ex_page->ID;
  $ex_content = $ex_page->post_content;
  $before_elem = '';
  $after_elem = '';

  $classes = array();
  $classes[] = $class;
  if(function_exists('get_field')){
    $classes[] = get_field('class_name',$ex_page_id);
    $before_elem = get_field('before_element',$ex_page_id);
    $after_elem = get_field('after_element',$ex_page_id);
  }
  $styles .= get_post_meta('_wpb_shortcodes_custom_css', $ex_page_id);

  $classes = implode(' ', $classes);
  ob_start(); ?>
  <div class="obj obj-extra-content <?= $classes; ?>">
    <?= $before_elem; ?>
    <?= do_shortcode($ex_content); ?>
    <?= $after_elem; ?>
  </div>
  <?php
  $contents = ob_get_contents();
  ob_end_clean();

  return $contents;
}
add_shortcode('share_elem', 'fn_share_elem');


function web3_fn_btn($atts, $content = null){
  $params = array(
    'title'=>'',
    'vc_link'=>'',
    'style'=>'0',
    'class'=>'obj-default',
    'class_btn'=>'',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-btn-element';
  $classes[] = 'obj-btn-element-'.$style;

  $vc_link = vc_build_link( $vc_link );

  $btn_text = '_quote_text_en_';
  $btn_link = '';
  $target = '';

  $has_btn = false;

  if(isset($vc_link['url'])){
    $has_btn = true;
    $btn_link = $vc_link['url'] ?: $btn_link;
  }
  if(isset($vc_link['title'])){
    $btn_text = $vc_link['title'] ?: $btn_text;
  }

  $btn_clases = explode(' ', $class_btn);

  $btn_clases[] = 'obj-btn';
  $btn_clases[] = 'obj-btn-jumbo';
  $btn_clases[] = 'obj-btn-'.$style;

  if($style === '2'){
    $has_btn = true;
    $btn_post_id = get_theme_mod('web3_page_contact', '');
    $btn_post_url = get_the_permalink($btn_post_id);

    $btn_link = $btn_post_url ?: '#';
  }

  $btn_link = apply_filters('web3_replacer', $btn_link);
  $btn_text = apply_filters('web3_replacer', $btn_text);

  if( ( isset($vc_link['target']) ) && ( !empty($vc_link['target']) ) ){
    $target = ' target="'.$vc_link['target'].'"';
  } else {
    if(!empty( $btn_link )){
      if(!strpos( $btn_link, esc_url( get_site_url() ) ) ){
        $target = ' target="_blank"';
      }
    }
  }

  switch ( $style ) {
    case 0:
      $btn_clases[] = 'outline';
      break;
    case 4:
      $btn_clases[] = 'obj-btn-secondary';
      break;
    default:
      $btn_clases[] = 'primary-color';
      $btn_clases[] = 'obj-btn-primary';
      break;
  }

  $classes = implode(' ', $classes);
  $btn_clases = implode(' ', $btn_clases);

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <?php if($title): ?><h3 class="obj-title"><?= $title; ?></h3><?php endif; ?>
      <?php if($has_btn): ?>
        <div class="obj-btn-wrap">
          <a href="<?= $btn_link; ?>" class="<?= $btn_clases; ?>"<?= $target; ?>><?= $btn_text; ?></a>
        </div>
      <?php endif; ?>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_btn', 'web3_fn_btn');

function web3_fn_svg_image($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = rawurldecode( base64_decode( strip_tags( $content ) ) );
  $content = apply_filters('web3_replacer', $content);
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-svg-image';
  $classes = implode(' ', $classes);
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <?= $content; ?>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_svg_image', 'web3_fn_svg_image');

function fn_get_sns_links(){
  $sns_links = array();

  if(strlen( get_theme_mod( 'vct_footer_area_social_link_facebook', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_facebook', '' ) ),
      "class" => "fa fa-facebook",
      "link-class" => "obj-link-share",
      "sns" => "facebook"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_twitter', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_twitter', '' ) ),
      "class" => "fa fa-twitter",
      "link-class" => "obj-link-share",
      "sns" => "twitter"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_linkedin', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_linkedin', '' ) ),
      "class" => "fa fa-linkedin",
      "link-class" => "obj-link-share",
      "sns" => "linkedIn"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_instagram', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_instagram', '' ) ),
      "class" => "vct-icon-instagram-with-circle",
      "link-class" => "obj-link-share",
      "sns" => "instagram"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_pinterest', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_pinterest', '' ) ),
      "class" => "vct-icon-pinterest-with-circle",
      "link-class" => "obj-link-share",
      "sns" => "pin"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_youtube', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_youtube', '' ) ),
      "class" => "vct-icon-youtube-with-circle",
      "link-class" => "obj-link-share",
      "sns" => "youtube"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_vimeo', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_vimeo', '' ) ),
      "class" => "vct-icon-vimeo-with-circle",
      "link-class" => "obj-link-share",
      "sns" => "vimeo"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_flickr', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_flickr', '' ) ),
      "class" => "vct-icon-flickr-with-circle",
      "link-class" => "obj-link-share",
      "sns" => "flickr"
    );
  }
  if(strlen( get_theme_mod( 'vct_footer_area_social_link_github', '' ) )){
    $sns_links[] = array(
      "url" => esc_url( get_theme_mod( 'vct_footer_area_social_link_github', '' ) ),
      "class" => "vct-icon-github-with-circle",
      "link-class" => "obj-link-share",
      "sns" => "github"
    );
  }

  return $sns_links;
}
function fn_social_icons($atts, $content = null){
  $params = array(
    'is_share'=> false,
    'style'=>'1',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));

  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-sns';
  $classes[] = 'obj-holder-share-'.$is_share;
  if($is_share){
    $style = '2';
  }
  $classes[] = 'obj-holder-sns-style-'.$style;
  $classes = implode(' ', $classes);

  $sns_links = fn_get_sns_links();

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <ul class="obj-ul list-inline">
          <?php foreach($sns_links as $sns):
            $href = $sns['url'];
            $target = ' target="_blank"';
            $link_class = array('obj-link', 'obj-link-sns');
            $attr = array();
            if($is_share){
              $target = '';
              $href = '#';
              $link_class[] = $sns['link-class'];
              $attr[] = 'data-sns="'.$sns['sns'].'"';
            } else {
              $attr[] = $target;
            }
            $link_class = implode(' ', $link_class);
            $attr[] = 'href="'.$href.'"';
            $attr[] = 'class="'.$link_class.'"';
            $attr = implode(' ', $attr);
          ?>
            <li class="obj-li">
              <a <?= $attr; ?>><span class="obj-i-wrap"><i class="obj-i <?= $sns['class']; ?>"></i></span></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('vc_social_icons', 'fn_social_icons');

function web3_stock($atts, $content = null){
  $params = array(
    'symbol'=>'TSX:MCB',
    'title'=> __('Last:'),
    'class'=>'obj-default',
    'holder'=>'last',
  );
  extract(shortcode_atts($params, $atts));

  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-stock';
  $classes = implode(' ', $classes);
  $web3 = !1;
  ob_start();
  ?>
    <div class="<?= $classes; ?>" data-symbol="<?= $symbol; ?>">
      <div class="obj-inner">
        <?php if(!empty($symbol)) :?><span class="obj-text obj-text-primary" aria-hidden="true"><?= $symbol; ?></span><?php endif; ?>
        <span class="obj-text obj-text-second"><?= $title; ?></span>
        <span class="obj-text obj-text-second obj-text-m" data-holder="<?= $holder; ?>"><b><i class="fa fa-spin fa-refresh"></i></b></span>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_stock', 'web3_stock');

function web3_fn_holder_stock_info($atts, $content = null){
  global $stock_params;

  $params = array(
    'symbol'=>'TSX:MCB',
    'display'=>'',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-stock-wrap';
  $classes = implode(' ', $classes);
  $display_array = explode(',',$display);
  ob_start();
  ?>
  <div class="<?= $classes; ?>">
    <?php foreach($display_array as $key => $value): ?>
    [web3_stock symbol="<?= $symbol; ?>" title="<?= $stock_params[$value]; ?>" holder="<?= $value; ?>" /]
    <?php endforeach; ?>
  </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return do_shortcode($output);
}
add_shortcode('web3_vc_holder_stock_info', 'web3_fn_holder_stock_info');

function web3_fn_blog_post($atts, $content = null){
  $params = array(
    'title' => '',
    'posts_per_page' => 3,
    'class'=>'obj-default',
    'vc_link'=>'',
    'vc_link_2'=>'',
    'has_view_all' => '1',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-blog';
  $classes = implode(' ', $classes);
  $query_args = array('posts_per_page' => $posts_per_page);
  $query_args['ignore_sticky_posts'] = !0;

  $title = $title ?: get_theme_mod('web3_tbar_title', 'News');

  $title = apply_filters('web3_replacer', $title);
  $vc_link = vc_build_link( $vc_link );
  $vc_link_2 = vc_build_link( $vc_link_2 );
  $blog_link = get_permalink( get_option( 'page_for_posts' ) );
  $link_url = $vc_link['url'];

  $blog_link = w3_url($blog_link);
  $link_url = w3_url($link_url);

  $wp_posts = new WP_Query($query_args);
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-section-header">
          <h2 class="obj-title"><?= $title; ?></h2>
        </div>
        <div class="obj-section-body">
          <div class="archive">
            <div class="vc_row vc_row-flex vc_row-o-content-top vc_row-o-equal-height">
            <?php 
            // Start the loop.
            while ( $wp_posts->have_posts() ) : $wp_posts->the_post();
              echo '<div class="vc_column_container vc_col-sm-6 vc_col-md-4"><div class="vc_column-inner">';
              /*
               * Include the Post-Format-specific template for the content.
               * If you want to override this in a child theme, then include a file
               * called content-___.php (where ___ is the Post Format name) and that will be used instead.
               */
              get_template_part( 'template-parts/content', get_post_format() );

              echo '</div></div>';
              // End the loop.
            endwhile;
            wp_reset_query();
            ?>
            </div>
          </div>
        </div>
        <div class="obj-section-footer">
          <div class="obj-btns">
          <?php if($has_view_all === '1'): ?>
            <?= do_shortcode('[web3_vc_btn vc_link="url:'.$blog_link.'|title:'.__('View All').'|"]'); ?>
          <?php endif; ?>
          <?php if(isset($vc_link['url'])): ?>
            <?= do_shortcode('[web3_vc_btn vc_link="url:'.$link_url.'|title:'.$vc_link['title'].'|"]'); ?>
          <?php endif; ?>
          <?php if(isset($vc_link_2['url'])): $link_url2 = w3_url($vc_link_2['url']); ?>
            <?= do_shortcode('[web3_vc_btn btn_class="w" vc_link="url:'.$link_url2.'|title:'.$vc_link_2['title'].'|"]'); ?>
          <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_blog_post', 'web3_fn_blog_post');

function fn_get_parents($id = 0, $obj, $array = array()){
  if(!is_object($obj)) return $val;

  if($id !== 0){
    $array[] = $obj->name;
    $parent_id = $obj->parent;
    $taxo = $obj->taxonomy;
    $parent_obj = get_term($parent_id, $taxo);
    $array = fn_get_parents($parent_id, $parent_obj, $array);
  }

  return $array;
}

// Career Holder
function fn_career_item_param($array){
  $para_value = $array['value'];
  if(!$para_value) return;

  static $li_count;
  if(!$li_count){
    $li_count = 0;
  }
  $li_count++;

  $para_label = $array['label'];
  $para_i = $array['icon'];
  $classes_value = array('obj-text', 'obj-text-value');
  if(isset($array['class'])){
    $classes_value[] = $array['class'];
  }

  $classes_value = implode(' ', $classes_value);

  ?>
    <span class="obj-li obj-li-<?= $li_count; ?>">
      <span class="obj-text-holder">
        <strong class="<?= $classes_value; ?>"><?= $para_value; ?></strong>
      </span>
    </span>
  <?php

}
function fn_career_item($item){
  $output = null;
  static $carrer_count;
  if(!$carrer_count){
    $carrer_count = 1;
  }

  $taxo_location = 'location';

  $item_id = $item->ID;
  $item_title = $item->post_title;
  $item_content = $item->post_content;
  $item_short_description = get_field('short_description', $item_id);
  $item_job_link = get_field('job_link', $item_id);
  $item_opening_date = get_the_date('', $item_id);
  $item_closing_date = get_field('career_close', $item_id);
  $item_closing_date_obj = '-';
  $item_closing = '-';
  if((!empty($item_closing_date))){
    $item_closing_date_obj = new DateTime($item_closing_date);
    $item_closing = $item_closing_date_obj->format(get_option('date_format'));
  }
  $item_company_name = get_field('company_name', $item_id);
  $item_types = get_field('career_type', $item_id);
  $item_is_featured = get_field('featured_position', $item_id);

  $item_id_raw = sanitize_title($item_title);

  $item_type = array();

  if(!empty($item_types)){
    if( count( $item_types ) > 1 ){
      foreach($item_types as $type){
        $item_type[] = $item_types;
      }
    } else {
      $item_type[] = $item_types;
    }
  }
  $item_type_raw = implode(',', $item_type);
  $item_type = implode(', ', $item_type);
  $item_locations = get_the_terms($item_id, $taxo_location);
  $item_location = array();

  foreach ( $item_locations as $key => $value ) {
    $location_id = $value->term_id;
    $item_location = fn_get_parents($location_id, $value, $item_location);
  }

  $item_location_implode_raw = implode(',', $item_location);
  $item_location_implode = implode(', ', $item_location);

  $val_null = ' - ';

  $item_params = array();

  $item_params[] = array(
    'label' => __('Location'),
    'value' => $item_location_implode ?: $val_null,
    'icon' => 'map-o'
  );
  $item_params[] = array(
    'label' => __('Job Type'),
    'value' => $item_type ?: $val_null,
    'icon' => 'handshake-o'
  );
  $item_params[] = array(
    'label' => __('Posted'),
    'value' => $item_opening_date ?: $val_null,
    'icon' => 'bullhorn',
    'class' => 'obj-text-uppercase'
  );
  $item_params[] = array(
    'label' => __('Until'),
    'value' => $item_closing ?: $val_null,
    'icon' => 'remove',
    'class' => 'obj-text-uppercase'
  );
  $item_params[] = array(
    'label' => __('Company'),
    'value' => $item_company_name ?: $val_null,
    'icon' => 'building-o'
  );

  $item_job_link_raw = w3_url($item_job_link); 
  $item_link_btn = '[web3_vc_btn class_btn="obj-scroll-to" vc_link="url:#apply-now|title:'.__('Apply Now').'|target:_self"]';

  ob_start(); ?>
  <div id="<?= $item_id_raw; ?>" class="obj-item obj-item-<?= $carrer_count; ?> obj-item-career obj-toggle-item " data-location="<?= $item_location_implode_raw; ?>" data-type="<?= $item_type_raw; ?>">
    <div class="obj-item-inner obj-toggle-target">
      <?php if($item_is_featured): ?><span class="obj-text-primary obj-text-uppercase obj-text-extra"><?= __('Featured Position'); ?></span><?php endif; ?>
      <div class="obj-item-inner-section obj-item-inner-header">
        <a href="#<?= $item_id_raw; ?>" class="obj-link obj-toggle-trigger">
        <span class="vc_row">
          <span class="vc_col-xs-11 vc_col-sm-8 vc_col-md-3">
            <span class="obj-text obj-title-career"><strong class="obj-text obj-text-value"><?= $item_title; ?></strong></span>
          </span>
          <span class="vc_col-xs-11 vc_col-sm-3 vc_col-md-2">
            <?= fn_career_item_param($item_params[0]); ?>
          </span>
          <span class="vc_hidden-xs vc_hidden-sm vc_col-md-2">
            <?= fn_career_item_param($item_params[1]); ?>
          </span>
          <span class="vc_hidden-xs vc_hidden-sm vc_col-md-2">
            <?= fn_career_item_param($item_params[2]); ?>
          </span>
          <span class="vc_hidden-xs vc_hidden-sm vc_col-md-2">
            <?= fn_career_item_param($item_params[3]); ?>
          </span>
          <span class="obj-i-wrap obj-i-wrap-indicator"><i class="obj-i fa fa-angle-down"></i></span>
        </span>
        </a>
      </div>
      <div class="obj-item-inner-section obj-item-inner-body">
        <div class="obj-item-role-content-long">
          <div class="obj-item-role-inner-section obj-item-role-inner-body">
            <?= wpautop( do_shortcode( $item_content ) ); ?>
          </div>
          <div class="obj-item-role-inner-section obj-item-role-inner-footer">
            <div class="obj-flex-lg obj-flex-lg-right">
              <?= do_shortcode($item_link_btn); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php

  $output = ob_get_contents();
  ob_end_clean();

  $carrer_count++;
  return $output;
}
function fn_career_item_filter($filter_taxo_name){
  $terms = wp_dropdown_categories(array(
    'show_option_none' => __('All Locations'),
    'option_none_value' => __( 'All Locations' ),
    'taxonomy'=>$filter_taxo_name, 
    'echo' => !1,
    'name' => 'location',
    'class' => 'obj-form-field obj-form-field-input obj-form-field-select obj-input',
    'value_field' => 'name',
    'hierarchical' => !0
  ));
  $filter = null;
  $types = array();
  $acf_objects = fn_acf_fields(203);
  foreach($acf_objects as $obj){
    if($obj['name'] !== 'type') continue;
    $choices = $obj['choices'];
    foreach($choices as $choice){
      $types[] = $choice;
    }
  }
  ob_start();
  ?>
  <div class="obj-filter">
    <div class="obj-filter-inner">
      <form action="?" method="get" accept-charset="utf-8" class="obj-form">
        <div class="obj-form-inner">
          <div class="obj-input-fields">
            <div class="form-section">
              <div class="vc_row">
                <div class="vc_col-sm-6 vc_col-md-4">
                  <div class="obj-form-field obj-form-field-input obj-field-select">
                    <label class="obj-label"><i class="obj-i fa fa-fw fa-map-o"></i><span class="obj-text"><?= __('Location'); ?></span></label>
                    <?= $terms; ?>
                  </div>
                </div>
                <div class="vc_col-sm-6 vc_col-md-4">
                  <div class="obj-form-field obj-form-field-input obj-field-select">
                    <label class="obj-label"><i class="obj-i fa fa-fw fa-handshake-o"></i><span class="obj-text"><?= __('Type of Position'); ?></span></label>
                    <select name="type" class="obj-form-field obj-form-field-input obj-form-field-select obj-input">
                      <?php $i = 0; foreach($types as $type): $i++; ?>
                        <option value="<?= $type; ?>"><?= $type; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="vc_col-sm-12 vc_col-md-4">
                  <div class="obj-form-field obj-field-reset">
                    <div class="obj-flex obj-flex-lg-right">
                      <button type="reset" class="wpcf7-form-control wpcf7-submit obj-btn obj-btn-jumbo obj-btn-fixed"><span class="obj-i-wrap"><i class="obj-i obj-i-dot"></i></span><?= __('Reset'); ?></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php
  $filter = ob_get_contents();
  ob_end_clean();
  return $filter;
}
function web3_fn_holder_career($atts, $content = null){
  $params = array(
    'is_filter' => 'false',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));

  $is_filter = filter_var($is_filter, FILTER_VALIDATE_BOOLEAN);

  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-career';
  $classes[] = 'obj-holder-filter';
  $classes = implode(' ', $classes);

  $term_name = 'location';
  $terms_arg = array(
    'taxonomy' => $term_name,
    'hide_empty' => true,
    'parent' => 0
  );
  $terms = get_terms($terms_arg);

  $post_args = array(
    'post_type' => 'career',
    'posts_per_page' => -1
  );
  $post_items = get_posts($post_args);

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-inner-section obj-inner-section-header">
          <?php if( $is_filter ): ?>
          <?= fn_career_item_filter($term_name); ?>
          <?php endif; ?>
          <div class="vc_row">
            <div class="vc_col-xs-11 vc_col-sm-8 vc_col-md-3">
              <span class="obj-text"><?= __('Position Title'); ?></span>
            </div>
            <div class="vc_col-xs-11 vc_col-sm-4 vc_col-md-2">
              <span class="obj-text"><?= __('Location'); ?></span>
            </div>
            <div class="vc_hidden-xs vc_hidden-sm vc_col-md-2">
              <span class="obj-text"><?= __('Type'); ?></span>
            </div>
            <div class="vc_hidden-xs vc_hidden-sm vc_col-md-2">
              <span class="obj-text"><?= __('Post Date'); ?></span>
            </div>
            <div class="vc_hidden-xs vc_hidden-sm vc_col-md-3">
              <span class="obj-text"><?= __('Closing Date'); ?></span>
            </div>
          </div>
        </div>
        <div class="obj-inner-section obj-inner-section-body">
        <?php foreach($post_items as $item): echo fn_career_item($item); endforeach; ?>
        </div>
        <div class="obj-inner-section obj-inner-section-footer obj-holder-results-no">
          <p><?= __('It looks like nothing was found at this location.'); ?></p>
          <?= do_shortcode('[web3_vc_btn class_btn="obj-scroll-to" vc_link="title:'.__('Apply Now').'|url:#apply-now|target:"]'); ?>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_career', 'web3_fn_holder_career');

/**
 * Extend get terms with post type parameter.
 *
 * @global $wpdb
 * @param string $clauses
 * @param string $taxonomy
 * @param array $args
 * @return string
 */
function df_terms_clauses( $clauses, $taxonomy, $args ) {
  if ( isset( $args['post_type'] ) && ! empty( $args['post_type'] ) && $args['fields'] !== 'count' ) {
    global $wpdb;

    $post_types = array();

    if ( is_array( $args['post_type'] ) ) {
      foreach ( $args['post_type'] as $cpt ) {
        $post_types[] = "'" . $cpt . "'";
      }
    } else {
      $post_types[] = "'" . $args['post_type'] . "'";
    }

    if ( ! empty( $post_types ) ) {
      $clauses['fields'] = 'DISTINCT ' . str_replace( 'tt.*', 'tt.term_taxonomy_id, tt.taxonomy, tt.description, tt.parent', $clauses['fields'] ) . ', COUNT(p.post_type) AS count';
      $clauses['join'] .= ' LEFT JOIN ' . $wpdb->term_relationships . ' AS r ON r.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN ' . $wpdb->posts . ' AS p ON p.ID = r.object_id';
      $clauses['where'] .= ' AND (p.post_type IN (' . implode( ',', $post_types ) . ') OR p.post_type IS NULL)';
      $clauses['orderby'] = 'GROUP BY t.term_id ' . $clauses['orderby'];
    }
  }
  return $clauses;
}

add_filter( 'terms_clauses', 'df_terms_clauses', 10, 3 );

function fnTriggerItem($item){

  $tab_icon = (isset($item['tab_icon'])) ? $item['tab_icon'] : null;
  extract($item);
  $item_classes = array(
    'obj-li',
    'obj-wrap-box-tab-item',
    'obj-li-'.$c,
    $item_class
  );
  $data_attr = array(
    'data-id="'.$tab_id.'"'
  );
  $a = null;
  if(isset($item['tab_url'])){
    $a = '<a href="'.$tab_url.'" class="obj-a obj-a-cover"></a>';
    $data_attr[] = 'data-url="'.$tab_url.'"';
  }
  if(isset($item['tab_title_raw'])){
    $tab_title_raw = htmlspecialchars($tab_title_raw);
    $data_attr[] = 'data-title="'.$tab_title_raw.'"';
  }

  $data_attr = implode(' ', $data_attr);

  $item_classes = implode(' ', $item_classes);
  ?>
  <div class="<?= $item_classes; ?> " <?= $data_attr; ?>>
    <div class="obj-li-inner">
      <div class="obj-box-tab-item obj-item-header obj-trigger-init" <?= $style; ?>>
        <?= $a; ?>
        <span class="obj-bg obj-bg-title animate" aria-hidden="true"></span>
        <h4 class="obj-title"><?= $tab_icon; ?><?= $tab_title; ?></h4>
        <span class="obj-indicator animate" aria-hidden="true"><i class="obj-i fa fa-arrow-circle-o-down"></i></span>
      </div>
      <?php if($tab_content): ?>
      <div class="obj-box-tab-item obj-item-body bg-dashed">
        <?= do_shortcode($tab_content); ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php
}
function web3_fn_holder_member($atts, $content = null){

  $params = array(
    'class'=>'obj-default',
    'posttype' => 'member',
    'term_id_group' => '',
    'term_id_location' => ''
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-member';
  $classes = implode(' ', $classes);

  $img_placeholder_src = get_stylesheet_directory_uri().'/images/img-370-310.png';

  $item_args = array(
    'post_type' => $posttype,
    'posts_per_page' => -1
  );

  if( (!empty($term_id_group)) || (!empty($term_id_location)) ){
    $item_args['tax_query'] = array();
    if( (!empty($term_id_group)) ){
      $item_args['tax_query'][] = array(
        'relation' => 'AND',
        array(
          'taxonomy' => 'group',
          'field' => 'term_id',
          'terms' => array(
            $term_id_group
          )
        )
      );
    }
    if( (!empty($term_id_location)) ){
      $item_args['tax_query'][] = array(
        'relation' => 'AND',
        array(
          'taxonomy' => 'location',
          'field' => 'term_id',
          'terms' => array(
            $term_id_location
          )
        )
      );
    }
  }

  $items = get_posts( $item_args );

  if( (empty( $items )) ) return;

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="container pt-20 pb-lg-40">
        <div class="row">
        <?php $c = 1; foreach($items as $item): 
          $item_id = $item->ID;
          $item_title = $item->post_title;
          $item_sub_title = get_field( 'title_position', $item_id );
          $img_id = get_post_thumbnail_id( $item_id );
          $item_img_array = wp_get_attachment_image_src( $img_id, 'large' );
          $item_img_src = '';
          if( (!empty( $item_img_array )) ){
            $item_img_src = ' style="background-image:url('.$item_img_array[0].');"';
          }
        ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="obj-item mb-60">
              <div class="obj-item-section obj-item-section-header mb-30">
                <div class="obj-item-img"<?= $item_img_src; ?>>
                  <img src="<?= $img_placeholder_src; ?>" alt="<?= __('placeholder'); ?>" height="310" width="370">
                </div>
              </div>
              <div class="obj-item-section obj-item-section-body">
                <h4 class="obj-item-title mb-10"><strong><?= $item_title; ?></strong></h4>
                <h4 class="obj-item-title-sub"><?= $item_sub_title; ?></h4>
              </div>
            </div>
          </div>
        <?php endforeach;?>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_member', 'web3_fn_holder_member');

//returns BOOLEAN false or a term id (int)
$GLOBALS['taxonomies_for_acf_term_meta_filter'] = [];
function my_acf_get_term_id_from_acf_post_id( $acf_post_id ){
  if( is_numeric($acf_post_id) ) return false; //not a special ID...

  if( empty($GLOBALS['taxonomies_for_acf_filter']) )
    $GLOBALS['taxonomies_for_acf_filter'] = get_taxonomies();
  
  $taxonomies = &$GLOBALS['taxonomies_for_acf_filter']; //shorthand
  $term_id = 0;
  foreach( $taxonomies as $taxonomy ){
    //special id is in format somestring_ID
    $term_id = str_replace( $taxonomy.'_', '', $acf_post_id );

    //If not numeric at all, or something is removed while cast type to int (must use "!=" !!!!!)
    //=> not a proper ID... => nothing to do here...
    if(  ! is_numeric($term_id) || ( ((int)$term_id) != $term_id )  ) continue;
      
    $term_id = (int)$term_id;
    break;     
  }
  
  if( $term_id < 1 ) return false; //not a proper ID...
  
  return $term_id;
}
function my_acf_update_term_meta($value, $post_id, $field) {
  //FILTER! ===> MUST ALWAYS RETURN $value !

  $term_id = my_acf_get_term_id_from_acf_post_id( $post_id );
  if( $term_id === false ) return $value; //not a proper ID... MUST USE "===" !!!!!
  
  update_term_meta($term_id, $field['name'], $value);
  
  return $value;
}
add_filter('acf/update_value', 'my_acf_update_term_meta', 10, 3);
add_filter('acf/update_value', 'my_acf_update_term_meta', 10, 3);

function my_acf_load_term_meta($value, $post_id, $field) {
  //FILTER! ===> MUST ALWAYS RETURN $value !

  $term_id = my_acf_get_term_id_from_acf_post_id( $post_id );
  if( $term_id === false ) return $value; //not a proper ID... MUST USE "===" !!!!!
  
  $value = get_term_meta($term_id, $field['name'], true);
  
  return $value;
}
add_filter('acf/load_value', 'my_acf_load_term_meta', 10, 3);
add_filter('acf/load_value', 'my_acf_load_term_meta', 10, 3);

function web3_fn_content_member_name($m){
  ?>
  <span class="obj-text-wrap obj-text-member"><b class="obj-text obj-text-b"><?= $m['member_name']; ?>,</b><?= $m['member_name_position']; ?></span>
  <?php
}
function web3_fn_content_member_details($m_details){
  foreach($m_details as $m_detail): ?>
  <div class="obj-text-wrap obj-text-wrap-<?= $m_detail['i']; ?>">
    <?= isset($m_detail['i'])? sprintf('<span class="obj-i-wrap"><i class="obj-i fa fa-%s"></i></span>', $m_detail['i']) : null; ?>
    <?= $m_detail['data']; ?>
  </div>
  <?php endforeach;
}
function web3_fn_content_member_accordion($atts, $content = null){
  $params = array(
    'term_id' => '',
    'group_by' => 'location',
    'compare' => 'LIKE',
    'class'=>'obj-default',
    'style' => 'grid'
  );
  extract(shortcode_atts($params, $atts));

  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-accordion';
  $classes[] = 'obj-style-accordion';
  $classes = implode(' ', $classes);

  $accordion_groups = $group_by;
  $accordion_arg = array(
    'taxonomy' => $accordion_groups,
  );
  if($term_id){
    $accordion_arg['term_taxonomy_id'] = $term_id;
  }
  $accordion_sections = get_terms($accordion_arg);
  $accordion_items = 'member';
  $accordion_items_arg = array(
    'post_type' => $accordion_items,
    'posts_per_page' => -1,
    'order' => 'ASC'
  );

  ob_start(); ?>
  <div class="<?= $classes; ?>">
    <div class="obj-inner">
      <?php foreach($accordion_sections as $section):
        $section_id = $section->term_id;
        $section_title = $section->name;
        $accordion_items_arg['tax_query'] = array(
          'relation' => 'AND',
          array(
            'taxonomy' => $accordion_groups,
            'field' => 'term_id',
            'terms' => array($section_id)
          )
        );
        $accordion_obj = get_posts($accordion_items_arg);
      ?>
      <div class="obj-inner-section obj-inner-section-header">
        <h3 class="obj-title"><?= $section_title; ?></h3>
      </div>
        <?php foreach($accordion_obj as $obj): 
          $obj_id = $obj->ID;
          $obj_title = $obj->post_title;
          $obj_title_sub = get_field('post_title_sub', $obj_id);
          $obj_body = get_field('additional_information', $obj_id);
        ?>
      <div class="obj-inner-section obj-inner-section-body obj-accordion-section">
        <div class="obj-style-accordion-header obj-accordion-trigger">
          <div class="obj-style-inner-body">
            <h4 class="obj-title-accordion animate"><strong class="obj-text obj-text-strong"><?= $obj_title; ?></strong><?php if(!empty($obj_title_sub)): ?><i class="obj-text obj-text-i"><?= $obj_title_sub; ?></i><?php endif; ?></h4>
            <span class="obj-i-wrap" aria-hidden="true"><i class="obj-i fa fa-angle-down"></i></span>
          </div>
        </div>
        <div class="obj-style-accordion-body obj-accordion-body">
          <div class="obj-style-inner-body">
            <?= do_shortcode($obj_body); ?>
          </div>
        </div>
      </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();

  return $output;
}
function web3_fn_content_member($atts, $content = null){
  $params = array(
    'term_id' => '',
    'group_by' => 'location',
    'compare' => 'LIKE',
    'class'=>'obj-default',
    'class_column' => '',
    'style' => 'grid'
  );
  extract(shortcode_atts($params, $atts));

  $output = null;

  // Select Output Style
  switch($style){
    case 'accordion':
      $output = web3_fn_content_member_accordion($atts);
      return $output;
      break;
  }

  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-members';
  $classes = implode(' ', $classes);

  $box_taxo = 'group';
  $box_taxo_location = 'location';
  $box_name = 'member';

  $arg = array(
    'post_type' => $box_name,
    'posts_per_page' => -1,
    'status' => 'publish',
    'order' => 'ASC',
  );
  $arg_location = array(
    'taxonomy' => $box_taxo_location,
    'field' => 'term_id',
    'terms' => array($term_id),
    'include_children' => false,
  );

  $term_args = array(
    'taxonomy' => $box_taxo,
    'post_type' => array($box_name),
    'hide_empty' => false,
    'parent' => 0
  );
  if($group_by === 'location'){
    $term_args['meta_query'] = array(
      'relation' => 'AND',
      array(
        'key' => 'location',
        'value' => $term_id,
        'compare' => $compare, 
      )
    );
  } else {
    $term_args['term_taxonomy_id'] = $term_id;
  }

  $groups = get_terms($term_args);
  $row_used = 12;

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="vc_row vc_row-flex">
        <?php foreach($groups as $group): 

          $group_id = $group->term_id;
          $group_name = $group->name;
          $group_name = get_field('term_name_custom', $group) ?: $group_name;
          $group_phone = get_field('phone_number', $group);
          $group_phone_cell = get_field('phone_number_cell', $group);
          $group_email = get_field('email', $group);

          if($group_by === 'location'){
            $arg['tax_query'] = array(
              'relation' => 'AND',
              $arg_location,
              array(
                'taxonomy' => $box_taxo,
                'field' => 'term_id',
                'terms' => array($group_id),
                'include_children' => !1,
              )
            );
          } else {
            $arg['tax_query'] = array(
              'relation' => 'AND',
              array(
                'taxonomy' => $box_taxo,
                'field' => 'term_id',
                'terms' => array($group_id),
                'include_children' => !1,
              )
            );
          }

          $members = get_posts($arg);

          $members_row = '';
          ob_start();

          $col_used = 0;

          if(!$members){
            $members = array();
            $group_obj = new stdClass();
            $group_obj->post_title = null;
            $group_obj->post_title_sub = null;
            $group_obj->additional_information = null;
            $group_obj->phone_number = $group_phone ?: null;
            $group_obj->phone_number_cell = $group_phone_cell ?: null;
            $group_obj->email = $group_email ?: null;
            $members[] = $group_obj;
          }

          foreach($members as $member):
            $col_used++;
            $member_name = $member->post_title;
            $member_name_position = get_field('post_title_sub', $member) ?: $member->post_title_sub;
            $member_phone = get_field('phone_number', $member) ?: $member->phone_number;
            $member_phone_cell = get_field('phone_number_cell', $member) ?: $member->phone_number_cell;
            $member_email = get_field('email', $member) ?: $member->email;
            $member_additional_information = get_field('additional_information', $member) ?: $member->additional_information;

            $member_email = eae_encode_emails($member_email);
            $m = array(
              'member_name' => $member_name,
              'member_name_position' => $member_name_position,
            );
            $m_details = array();
            if(!empty($member_phone_cell)){
              $m_details[] = array(
                'i' => 'mobile',
                'data' => $member_phone_cell
              );
            }
            if(!empty($member_phone)){
              $m_details[] = array(
                'i' => 'phone',
                'data' => $member_phone
              );
            }
            if(!empty($member_email)){
              $m_details[] = array(
                'i' => 'envelope',
                'data' => $member_email
              );
            }
            if(!empty($member_additional_information)){
              $m_details[] = array(
                'i' => 'commenting-o',
                'data' => $member_additional_information
              );
            }
        ?>
        <div class="vc_col-sm-{sm} vc_col-md-{md} vc_col-lg-{lg}">
          <div class="obj-holder-texts">
            <?= (!empty($member_name)) ? web3_fn_content_member_name($m) : null; ?>
            <?= web3_fn_content_member_details($m_details); ?>
          </div>
        </div>
      <?php endforeach; 
        $members_row = ob_get_contents();
        ob_end_clean();
        $members_row = apply_filters('web3_replacer', $members_row);
        $col_length_sm = 12;
        $col_length_md = 12;
        $col_sm = 6;
        $col_md = $col_lg = 4;

        if($col_used >= 3){
          $col_length_md = 12;
          $col_length_sm = 12;
          $col_sm = 6;
          $col_md = $col_lg = 4;
        } elseif($col_used === 2){
          $col_length_md = 8;
          $col_length_sm = 12;
          $col_sm = 6;
          $col_md = $col_lg = 6;
        } elseif(1 >= $col_used){
          $col_length_md = 4;
          $col_length_sm = 6;
          if($group_by !== 'location'){
            $col_length_md = 12;
            $col_length_sm = 12;
          }
          $col_sm = 12;
          $col_md = $col_lg = 12;
        }

        $classes_column = array(
          'vc_col-sm-'.$col_length_sm,
          'vc_col-md-'.$col_length_md,
        );
        
        $classes_column = implode(' ', $classes_column);

        if(empty($class_column)){
          $members_row = str_replace('{sm}', $col_sm, $members_row);
          $members_row = str_replace('{md}', $col_md, $members_row);
          $members_row = str_replace('{lg}', $col_lg, $members_row);
        } else {
          $col_sizes = explode(',', $class_column);
          foreach($col_sizes as $col_text){
            $col_size = explode('-', $col_text);
            if(!isset($col_size[1])) continue;

            $members_row = str_replace('{'.$col_size[0].'}', $col_size[1], $members_row);
          }
        }

      ?>
          <div class="obj-col-holder-group <?= $classes_column; ?>">
            <h4 class="obj-title-group"><?= $group_name; ?></h4>
            <div class="vc_row vc_row-flex">
              <?= $members_row; ?>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_content_member', 'web3_fn_content_member');


function web3_fn_holder_download($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
    'is_date' => '1',
    'file_category' => '0',
    'max_tab' => !1
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-files';
  $classes[] = 'obj-is-date-'.$is_date;
  $classes = implode(' ', $classes);
  $args = array('post_type' => 'wpdmpro', 'posts_per_page' => -1);
  if($file_category){
    $args['tax_query'] = array(
      'relation' => 'AND',
      array(
        'taxonomy' => 'wpdmcategory',
        'field' => 'term_id',
        'terms' => array($file_category)
      )
    );
  }
  $cat = get_term($file_category);
  $featured_items = get_field('list_of_reports', $cat) ?: array();
  if(!empty($featured_items)){
    $featured_items = explode(PHP_EOL, $featured_items);
  }
  $cat_name = $cat->name;
  $cat_name_sanitize = sanitize_title($cat->name);

  $files_by_years = array();
  $files = get_posts($args);
  foreach ($files as $key => $file) {
    $file_id = $file->ID;
    $file_Y = get_the_date('Y', $file_id);
    $file_md = get_the_date('md', $file_id);
    if(!isset($files_by_years[$file_Y])){
      if($max_tab){
        if(count($files_by_years) > $max_tab - 1){
          continue;
        }
      }
      $files_by_years[$file_Y] = array();
    }
    if(!isset($files_by_years[$file_Y][$file_md])){
      $files_by_years[$file_Y][$file_md] = array();
    }
    $files_by_years[$file_Y][$file_md][] = $file;
  }
  $date_format = get_option('date_format');

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-inner-section-header vc_hidden-sm vc_hidden-md vc_hidden-lg">
          <h3 class="obj-title obj-trigger"><?= $cat_name; ?></h3>
        </div>
        <div class="obj-inner-section-body">
          [vc_tta_tour style="flat" shape="square" color="white" active_section="1" no_fill_content_area="true"]
            <?php  $i = 0; foreach($files_by_years as $year => $files_added): ?>
            [vc_tta_section title="<?= $year; ?>" tab_id="<?= $year.'-'.$cat_name_sanitize; ?>"]
              <?php $c = 1; foreach($files_added as $added_md):
                $this_file_date_raw = $added_md[0]->post_date;
                $this_file_date_raw = strtotime($this_file_date_raw);
                $this_file_date = date($date_format, $this_file_date_raw);
                $featured_item = (isset($featured_items[$i])) ? $featured_items[$i] : null;
              ?>
              <div class="obj-holder-download-file">
                <?php if($is_date === '1'): ?><h4 class="obj-title-file"><?= $this_file_date; ?></h4><?php endif; ?>
                <?php 
                  if((!empty($featured_item)) && ($c === 1)){
                    $featured_item_obj = get_post($featured_item);
                    $featured_item_id = $featured_item_obj->ID;
                    $featured_item_title = $featured_item_obj->post_title;
                    $featured_item_url = get_the_permalink($featured_item_id);
                    echo '<p><a href="'.$featured_item_url.'" target="_blank"><strong>'.$featured_item_title.'</stong></a></p>';
                    $c++;
                  }
                ?>
              <?php foreach($added_md as $added): 
                $added_id = $added->ID;
                ?>
                <?= do_shortcode('[web3_download id="'.$added_id.'" /]'); ?>
              <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
            [/vc_tta_section]
            <?php $i++; endforeach; ?>
          [/vc_tta_tour]
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  $output = do_shortcode($output);
  return $output;
}
add_shortcode('web3_vc_holder_download', 'web3_fn_holder_download');

function fn_web3_download($atts, $content = null){
  $params = array(
    'id'=>'',
    'style'=>'list',
    'style_icon' => '0',
    'url_title' => '',
    'btn_class' => ''
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $added = get_post($id);
  $permalink = get_permalink($id);

  $pdf_icon = get_theme_mod('web3_product_icon_pdf', '');

  $url = $permalink.'?wpdmdl='.$id;
  $url_title = (empty($url_title)) ? $added->post_title : $url_title;

  ob_start();
  switch($style):
    case 'list':
  ?>
  <p>
    <a href="<?= $url; ?>" target="_blank" class="obj-link">
    <?php if(($style_icon === '0')): ?>
    <img src="<?= $pdf_icon; ?>" alt="pdf icon" width="39" height="36"/>
    <?php else: ?>
    <span class="obj-i-wrap"><i class="obj-i fa fa-file-pdf-o"></i></span>
    <?php endif; ?>
    <strong><?= $url_title; ?></strong></a></p>
  <?php
    break;
    default:
    $url = w3_url($url);
  ?>
    <?= do_shortcode(sprintf('[web3_vc_btn class_btn ="%s" vc_link="url:%s|title:%s|target:_blank"/]', $btn_class, $url, $url_title) ); ?>
  <?php
      break;
  endswitch;
  $output = ob_get_contents();
  ob_end_clean();

  return $output;
}
remove_shortcode('wpdm_package');
add_shortcode('wpdm_package', 'fn_web3_download');
add_shortcode('web3_download', 'fn_web3_download');

function web3_fn_holder_download_WP_DownloadManager($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
    'is_date' => '1',
    'file_category' => '0',
    'max_tab' => !1
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-files';
  $classes[] = 'obj-is-date-'.$is_date;
  $classes = implode(' ', $classes);
  $category_sql = ($file_category == '0')? null : 'file_category ='.$file_category.' AND';
  $args = array('post_type' => 'downloads');
  $cat_names = get_option('download_categories');
  $cat_name = $cat_names[$file_category];
  $cat_name_sanitize = sanitize_title($cat_name);
  
  global $wpdb;
  $files = $wpdb->get_results("SELECT * FROM $wpdb->downloads WHERE $category_sql file_permission = -1 ORDER BY file_date DESC");
  $files_by_years = array();
  foreach ($files as $key => $file) {
    $file_Y = date('Y', $file->file_date);
    $file_md = date('md', $file->file_date);
    if(!isset($files_by_years[$file_Y])){
      if($max_tab){
        if(count($files_by_years) > $max_tab - 1){
          continue;
        }
      }
      $files_by_years[$file_Y] = array();
    }
    if(!isset($files_by_years[$file_Y][$file_md])){
      $files_by_years[$file_Y][$file_md] = array();
    }
    $files_by_years[$file_Y][$file_md][] = $file;
  }
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-inner-section-header vc_hidden-sm vc_hidden-md vc_hidden-lg">
          <h3 class="obj-title obj-trigger"><?= $cat_name; ?></h3>
        </div>
        <div class="obj-inner-section-body">
          [vc_tta_tour style="flat" shape="square" color="white" active_section="1" no_fill_content_area="true"]
            <?php foreach($files_by_years as $year => $files_added): ?>
            [vc_tta_section title="<?= $year; ?>" tab_id="<?= $year.'-'.$cat_name_sanitize; ?>"]
              <?php foreach($files_added as $added_md): 
                $this_file_date_raw = $added_md[0]->file_date;
                $this_file_date = date(get_option('date_format'), $this_file_date_raw);
              ?>
              <div class="obj-holder-download-file">
                <?php if($is_date === '1'): ?><h4 class="obj-title-file"><?= $this_file_date; ?></h4><?php endif; ?>
              <?php foreach($added_md as $added): ?>
                [download id="<?= $added->file_id; ?>"]
              <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
            [/vc_tta_section]
            <?php endforeach; ?>
          [/vc_tta_tour]
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  $output = do_shortcode($output);
  return $output;
}
add_shortcode('web3_vc_holder_download_WP_DownloadManager', 'web3_fn_holder_download_WP_DownloadManager');

function web3_fn_holder_boxtab($atts, $content = null){
  $params = array(
    'term_id' => '',
    'title' => '',
    'is_toggle' => '0',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;

  $title = apply_filters('web3_replacer',$title);
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $is_toggle = ($is_toggle === '1');

  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-box-tab';
  $classes[] = 'obj-holder-box-tab';

  if($is_toggle){
    $classes[] = 'obj-holder-toggle';
    $title .= '<span class="obj-i-wrap"><i class="obj-i fa fa-angle-down"></i></span>';
  }
  $classes = implode(' ', $classes);

  $post_type = 'box-tab';
  $post_arg = array('posts_per_page'=>-1,'post_type'=>$post_type, 'order'=>'ASC');

  if(!empty($term_id)){
    $post_arg['tax_query'] = array(
      'relation' => 'AND',
      array(
        'taxonomy' => 'category-box',
        'field' => 'term_id',
        'terms' => $term_id
      )
    );
  }

  $boxtabs = get_posts($post_arg);

  $svg_pre = '<span class="obj-svg-holder"><span class="obj-svg-wrap">';
  $svg_suf = '</span></span>';

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-inner-section obj-inner-section-header">
          <h2 class="obj-title"><?= $title; ?></h2>
        </div>
        <div class="obj-inner-section obj-inner-section-body">
          <div class="obj-ul">
            <?php $c = 1; foreach($boxtabs as $box):
              $tab_id = $box->ID;
              $tab_title = $box->post_title;
              $tab_title = get_field('custom_title',$tab_id) ?: $tab_title;
              $tab_title = apply_filters('web3_replacer', $tab_title);
              $tab_content = $box->post_content;
              $tab_icon = get_field('icon', $tab_id);
              if(strpos($tab_icon, '_svg_') > -1){
                $tab_icon = apply_filters('web3_replacer', $tab_icon);
              } elseif(!empty($tab_icon)) {
                $tab_icon = $svg_pre.$tab_icon.$svg_suf;
              }

              $style = '';

              $item_param = array(
                'c' => $c,
                'tab_id' => $tab_id,
                'style' => $style,
                'tab_title' => $tab_title,
                'tab_content' => $tab_content,
                'tab_icon' => $tab_icon,
                'item_class' => 'obj-trigger-item',
              );
              fnTriggerItem($item_param);
              $c++;
              endforeach;
            ?>
          </div>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_boxtab', 'web3_fn_holder_boxtab');

function web3_fn_slogan($atts, $content = null){
  $params = array(
    'title' => '',
    'h' => '2',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-content align-center transform-default';
  $classes[] = 'obj-slogan';
  $classes = implode(' ', $classes);
  $title = apply_filters('web3_replacer', $title);
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <h<?= $h; ?> class="obj-title"><?= $title; ?></h<?= $h; ?>>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_slogan', 'web3_fn_slogan');

// Blog Element
function fn_blog_item(){

  $blog_arg = array(
    'posts_per_page' => 2,
    'post__in' => get_option( 'sticky_posts' )
  );

  $blog_posts = get_posts($blog_arg) ?: array();
  
  if(2 > count($blog_posts)){
    unset($blog_arg['post__in']);
    $blog_arg['posts_per_page'] = 2 - count($blog_posts);
    $blog_arg['ignore_sticky_posts'] = !0;
    $blog_posts_new = get_posts($blog_arg);
    $blog_posts = array_merge($blog_posts, $blog_posts_new);
  }

  $output = null;
  ob_start();
  if($blog_posts){
 ?>
    <div class="obj obj-holder-blog-items">
    <?php foreach($blog_posts as $blog_item):
      $item_id = $blog_item->ID;
      $item_text = $blog_item->post_excerpt;
      $item_text = wp_trim_words($item_text, 8);
      $item_link = get_the_permalink($item_id);
      $date_m = get_the_date('M', $item_id);
      $date_d = get_the_date('d', $item_id);
    ?>
      <div class="obj-blog-item">
        <a href="<?= $item_link; ?>" class="obj-link-post">
          <span class="obj-text obj-text-date-wrap">
            <span class="obj-text-date obj-text-date-m"><?= $date_m; ?></span>
            <span class="obj-text-date obj-text-date-d"><?= $date_d; ?></span>
          </span>
          <span class="obj-text obj-text-excerpt"><?= $item_text; ?></span>
        </a>
      </div>
    <?php endforeach; ?>
    </div>
    <?php
  }

  $output = ob_get_contents();
  ob_end_clean();

  $output = apply_filters('web3_replacer', $output);
  return $output;
}

// Banner Element
function fn_banner_item_column($col){

  static $col_count;
  if(!$col_count){
    $col_count = 1;
  }
  $output = null;
  ob_start();

  ?>
  <div class="vc_col-sm-12 vc_col-md-4 column_inner-wrap column_inner-wrap-<?= $col_count; ?>">
    <div class="column_inner">
      <div class="obj-col-piece-wrap">
        <h4 class="obj-col-piece obj-col-piece-title"><?= $col['title']; ?></h4>
        <div class="obj-col-piece obj-col-piece-body">
          <?= do_shortcode($col['content']); ?>
        </div>
      </div>
      <div class="obj-col-piece obj-col-piece-footer">
        <a href="<?= $col['btn_link']; ?>" class="obj-link">
          <span class="obj-text"><?= $col['btn_title']; ?></span>
          <span class="obj-i-wrap"><i class="obj-i fa fa-angle-right"></i></span>
        </a>
      </div>
    </div>
  </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();

  $col_count ++;
  $output = apply_filters('web3_replacer', $output);
  return $output;
}
function fn_banner_item($item){
  static $count;
  if(!$count){
    $count = 1;
  }

  $item_columns = 4;
  $item_columns_obj = array();
  $item_classes = array();
  $item_classes[] = 'obj-banner-item-inner';

  $banner_title = get_field('banner_text', $item);
  for ($i=1; $i < $item_columns; $i++) {
    $col = 'column_'.$i;
    $column_title = get_field($col.'_title', $item);
    if(empty($column_title)) continue;

    $item_is = get_field($col.'_is', $item); 
    $content = get_field($col.'_content', $item);

    switch ($item_is) {
      case 'event':
        $content = fn_blog_item();
        break;
      default:
        $content = wpautop($content); 
        break;
    }
    $item_columns_obj[] = array(
      'title' => $column_title,
      'is' => $item_is,
      'content' => $content,
      'btn_title' => get_field($col.'_btn_title', $item),
      'btn_link' => get_field($col.'_btn_link', $item),
    );
  }

  // BG SRCs
  $bg_src_fields = array(
    'mobile' => 'xs',
    'tablet' => 'md',
    'desktop' => 'lg',
  );
  $bg_src = array();

  foreach($bg_src_fields as $key => $size) {
    $img_src_obj = get_field('bg_'.$key, $item);
    $img_src = (isset($img_src_obj['url'])) ? $img_src_obj['url'] : !1;
    if(!$img_src) continue;

    $bg_src[$size] = array('src'=>$img_src);
  }

  $bg_attr = array();
  if(!empty($bg_src)) {
    $bg_src = json_encode($bg_src);
    $bg_attr[] = "data-bg-src='$bg_src'";
  }
  $bg_attr = implode(' ', $bg_attr);

  $item_classes = implode(' ', $item_classes);
  $wrapper_attributes[] = 'class="'.$item_classes.'"';

  $wrapper_attributes = implode(' ', $wrapper_attributes);

  $banner_title = apply_filters('web3_replacer', $banner_title);
  ob_start();

  ?>
  <div class="obj-banner-item">
    <div <?= $wrapper_attributes; ?>>
      <span class="obj-banner-bg obj-bg-src" <?= $bg_attr; ?>></span>
      <div class="<?= esc_attr( vct_get_header_container_class() ); ?>">
        <div class="obj-banner-item-row obj-banner-item-header">
          <h1 class="obj-title"><?= $banner_title; ?></h1>
        </div>
        <div class="obj-banner-item-row obj-banner-item-body">
          <div class="vc_row">
            <?php foreach($item_columns_obj as $col): echo fn_banner_item_column($col); endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();

  $count++;
  return $output;
}
function web3_fn_banner($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-banner';
  $classes[] = 'obj-holder-slider';
  $classes = implode(' ', $classes);

  $post_name = 'banner';
  $post_args = array(
    'posts_per_page' => -1,
    'post_type' => $post_name
  );

  $banners = get_posts($post_args);

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-banner-section">
        <?php foreach($banners as $banner): ?>
          <?= fn_banner_item($banner); ?>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_banner', 'web3_fn_banner');

// Product Holder
function fn_CategoryItems($cat){

  if($cat === 'br'){
    echo '<div class="obj-separation" aria-hidden="true"></div>';
    return;
  }

  $is_exclude = get_field('exclude', $cat);
  if($is_exclude){
    return;
  }

  $tab_id = $cat->term_id ?: $cat->ID;
  $tab_title_raw = $cat->name ?: $cat->post_title;
  $tab_title = get_field('custom_title', $cat) ?: $tab_title_raw;
  $tab_title = apply_filters('web3_replacer', $tab_title);
  $has_separation_after = get_field('has_separation_after', $cat);
  $has_separation_after_title = get_field('separation_title', $cat);
  $url = get_field('custom_url', $cat);
  $item_class = 'obj-item-trigger-ajax';

  static $item_c;
  if(!$item_c){
    $item_c = 0;
  }
  $item_c++;

  $tab_icon_type = get_field('icon_type', $cat) ?: 'img';
  $tab_icon = get_field('icon_'.$tab_icon_type, $cat);

  if(is_array($tab_icon)){
    $tab_icon_src = $tab_icon['sizes']['medium'];
    $tab_icon = sprintf('<span class="obj-img-holder"><span class="obj-img" style="background-image:url(%s)"></span></span>', $tab_icon_src);
  }
  if(strpos($tab_icon, '_svg_') > -1){
    $tab_icon = apply_filters('web3_replacer', $tab_icon);
  }
  $tab_bg = get_field('box_bg', $cat);

  $style = null;
  if($tab_bg){
    $style = 'style="background-image:url('.$tab_bg['url'].')"';
  }

  if(empty($url)){
    if(isset($cat->ID)){
      $url = get_the_permalink($cat);
    } else {
      $url = get_term_link($cat);
    }
  } else {
    $item_class .= ' obj-item-custom-url';
  }

  $item_param = array(
    'c' => $item_c,
    'tab_id' => $tab_id,
    'style' => $style,
    'tab_title' => $tab_title,
    'tab_title_raw' => $tab_title_raw,
    'tab_icon' => $tab_icon,
    'tab_content' => null,
    'item_class' => $item_class,
    'tab_url' => $url,
  );

  fnTriggerItem($item_param);

  if($has_separation_after){
    if(!empty($has_separation_after_title)){
      $has_separation_after_title = '<h3 class="obj-title-separation">'.$has_separation_after_title.'</h3>';
    }
    echo '<div class="obj-separation" aria-hidden="true">'.$has_separation_after_title.'</div>';
  }
}
function fn_CompareItem($item_obj, $fields){
  if($item_obj === 'br'){
    return;
  }
  $item = null;
  $item_id = 0;
  $title = null;
  $apply = '0';
  $icon_img_compare = !1;

  if(isset($item_obj->term_id)){
    $item_id = $item_obj->term_id;
    $title = __('{{Compare}}||All ').$item_obj->name;
    $icon_img_compare = get_field('compare_img', $item_obj);
  } else {
    $apply = '1';
    $item_id = $item_obj->ID;
  }

  $item_title = $item_obj->post_title ?: null;
  $product_title = get_field('product_title', $item_id) ?: null;
  $product_title_sub = get_field('product_title_sub', $item_id);
  $part_number = get_field('part_number', $item_id);
  $icon_img_compare = (!$icon_img_compare) ? get_field('icon_img_compare', $item_id) : $icon_img_compare;

  ob_start(); ?>
  <div class="obj-compare-item obj-holder-highlighter">
    <div class="obj-compare-item-section obj-compare-item-header obj-highlighted-off">
      <div class="obj-compare-item-inner">
        <?php if(!empty($title)): ?>
          <?php if(isset($icon_img_compare['url'])): ?><img src="<?= $icon_img_compare['url']; ?>" alt="<?= $item_title; ?>" class="obj-img hidden"><span class="obj-figure-image" style="background-image:url(<?= $icon_img_compare['url']; ?>);"></span><?php endif; ?>
        <?= do_shortcode('[web3_vc_content title="'.$title.'"][/web3_vc_content]'); ?>
        <?php else: ?>
        <figure class="obj-figure">
          <?php if(isset($icon_img_compare['url'])): ?><img src="<?= $icon_img_compare['url']; ?>" alt="<?= $item_title; ?>" class="obj-img hidden"><span class="obj-figure-image" style="background-image:url(<?= $icon_img_compare['url']; ?>);"></span><?php endif; ?>
          <figcaption class="obj-figcaption obj-trigger obj-trigger-highlight"><span class="obj-i-wrap obj-i-wrap-off"><i class="obj-i fa fa-square-o"></i></span><span class="obj-i-wrap obj-i-wrap-on"><i class="obj-i fa fa-check-square-o"></i></span> <span class="obj-text"><?= $product_title; ?></span></figcaption>
        </figure>
        <?php if((!empty($product_title_sub)) && (!empty($part_number)) && ($part_number !== 'N/A')): ?>
        <dl class="obj-dl">
          <dt class="hide"><?= __('Part Option'); ?></dt>
          <dd class="obj-dd obj-dd-title"><?= $product_title_sub; ?></dd>
          <dt class="hide"><?= __('Part Number'); ?></dt>
          <dd class="obj-dd obj-dd-list"><?= $part_number; ?></dd>
        </dl>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="obj-compare-item-section obj-compare-item-body">
      <div class="obj-compare-item-inner">
        <ul class="obj-ul">
          <?php $i = 1; foreach($fields as $field):
            $field_title = ($apply === '0')? $field['title']:get_field($field['key'], $item_obj); 
            $field_title = (empty($field_title)) ? __('N/A') : $field_title ;
          ?>
          <li class="obj-li obj-li-<?= $i; $i++; ?>"><span class="obj-text obj-highlighted-off"><?= $field_title; ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php if($apply === '1'): 
      $item_url = get_the_permalink($item_id);
      $item_url = w3_url($item_url);
    ?>
    <div class="obj-compare-item-section obj-compare-item-footer obj-highlighted-off">
      <div class="obj-btns">
        <?= do_shortcode('[web3_vc_btn class_btn="obj-btn-sm" vc_link="title:'.__('View Product').'|url:'.$item_url.'|target:|_self"/]'); ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php
  $item = ob_get_contents();
  ob_end_clean();

  return $item;
}
function fn_CompareItems($items, $q){
  $compare_section = null;
  $compared_section_id = 1095;
  $compare_items = fn_acf_fields($compared_section_id);
  $fields = array();

  $compare_by = get_field('compare_by', $q);
  $is_compare_section = false;


  foreach($compare_items as $compare_item){
    if($compare_item['type'] === 'tab'){
      $is_compare_section = ($compare_by === $compare_item['label']);
      continue;
    }
    if(!$is_compare_section) continue;

    $fields[] = array(
      'title' => $compare_item['label'],
      'key' => $compare_item['name']
    );
  }

  ob_start(); ?>
  <div class="obj obj-holder-compare">
    <div class="obj-inner">
      <div class="obj-inner-section obj-inner-section-header">
        <?= fn_CompareItem($q, $fields); ?>
      </div>
      <div class="obj-inner-section obj-inner-section-body">
        <div class="obj-body-scroll">
          <div class="obj-compare-items">
            <?php foreach($items as $item): ?>
              <?= fn_CompareItem($item, $fields); ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
  $compare_section = ob_get_contents();
  ob_end_clean();

  return $compare_section;
}

function web3_fn_holder_product_category($title_category, $description, $q = false){
  $product_category = null;
  $q = $q ?: get_queried_object();
  $custom_body_page = get_field('custom_body_page', $q);
  ob_start();
  if((empty($custom_body_page))):
    if(((!empty($title_category)) || !empty($description))): ?>
      <?= do_shortcode('[web3_vc_content title="'.$title_category.'"]'.$description.'[/web3_vc_content]'); ?>
  <?php endif; else: $custom_style = get_post_meta( $custom_body_page->ID, '_wpb_shortcodes_custom_css', true );?>
    <?= do_shortcode($custom_body_page->post_content); ?>
    <?php if(!empty($custom_style)): ?>
      <style type="text/css" media="screen">
        <?= $custom_style; ?>
      </style>
    <?php endif; ?>
  <?php
  endif;
  $product_category = ob_get_contents();
  ob_end_clean();
  return $product_category;
}
function web3_fn_holder_product($atts, $content = null){
  $params = array(
    'title' => get_theme_mod('web3_product_breadcrumb_text', ''),
    'class'=>'obj-default',
    'parent_term_id' => '0',
    'is_single' => '0'
  );
  extract(shortcode_atts($params, $atts));

  $is_single = (is_singular('product'));

  $id_selected = 0;

  $archive_page_id = get_theme_mod('web3_product_page');
  $archive_page_title = get_the_title($archive_page_id);
  $arc_url = get_the_permalink($archive_page_id);
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-product';
  $classes[] = 'obj-holder-box-tab';
  $parent_term_id = intval($parent_term_id);

  $crumbles = array(
    array(
      'url' => $arc_url,
      'title' => $title,
      'id' => 0
    )
  );

  $q = get_queried_object();
  $title_category = null;
  $description = null;
  $has_compare = !1;
  $hide_category_sub = !1;
  $box_order = '1';
  $product_category_name = 'category-product';
  $marge_boxes = !1;
  if(isset($q->taxonomy)){
    $product_category_name = $q->taxonomy;
    $parent_term_id = $q->term_id;
    $title_category = get_field('custom_title_page', $q) ?: $q->name;
    $description = $q->description;
    $has_compare = get_field('has_compare', $q);
    $hide_category_sub = get_field('hide_category_sub', $q); 
    $box_order = get_field('box_order', $q) ?: $box_order; 
    $marge_boxes = get_field('marge_boxes', $q) ?: $marge_boxes; 
  }
  // Products Category
  $product_category = (!$hide_category_sub) ? get_terms(
    array(
      'taxonomy'=>$product_category_name,
      'hide_empty' => !1,
      'parent' => $parent_term_id, 
      'orderby' => 'term_order',
    )
  ) : array();

  $items = null;

  // Products Single
  $post_name = 'product';
  $post_arg = array(
    'post_type' => $post_name,
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'tax_query' => array(
      'relation' => 'AND',
      array(
        'taxonomy' => $product_category_name,
        'field' => 'term_id',
        'terms' => array($parent_term_id),
        'include_children'  => false
      )
    )
  );
  $classes[] = 'has-items';
  $items = get_posts($post_arg);
  if($box_order === '1'){
    if(!$marge_boxes){
      if(!empty($items)){
        $items[] = 'br';
      }
    }
    $items = array_merge($items, $product_category);
  } else {
    if(!$marge_boxes){
      if(!empty($product_category)){
          $product_category[] = 'br';
      }
    }
    $items = array_merge($product_category, $items);
  }

  // Bread Crumble
  if((isset($q->parent))){
    if((!$q->parent)){
      $id_selected = $q->term_id;
      $arc_url = get_term_link($id_selected);
      $crumbles[] = array(
        'url' => $arc_url,
        'title' => $q->name,
        'id' => $id_selected
      );
    } else {
      $arc_url = get_term_link($q->parent);
      $crumbles_taxo = web3_fn_hierarchy_check($parent_term_id, $q);
      $crumbles_taxo = array_reverse($crumbles_taxo);
      $crumbles = array_merge($crumbles, $crumbles_taxo);
    }
  }
  // Single
  if($is_single){
    global $post;

    $single_id = $post->ID;
    $single_title = $post->post_title;
    $single_url = get_the_permalink($single_id);
    $s_terms = wp_get_post_terms($single_id, $product_category_name);
    $crumbles_taxo = array();
    foreach($s_terms as $s_term){
      $s_term_parent = $s_term->parent;
      $crumbles_taxo = web3_fn_hierarchy_check($s_term_parent, $s_term);
    }
    $crumbles_taxo = array_reverse($crumbles_taxo);
    $crumbles = array_merge($crumbles, $crumbles_taxo);

    if(count($crumbles) === 1){
      $single_terms = wp_get_post_terms($single_id, $product_category_name);
      foreach($single_terms as $single_term){
        $crumbles[] = array(
          'id' => $single_term->term_id,
          'url' => get_term_link($single_term->term_id),
          'title' => $single_term->name
        );
      }
    }

    $crumbles[] = array(
      'url' => $single_url,
      'title' => $single_title,
      'id' => $single_id
    );
  }

  // Dropdown
  $product_category_dd = wp_dropdown_categories(array(
    'show_option_none' => $archive_page_title,
    'option_none_value' => 0,
    'selected'  => $id_selected,
    'taxonomy'=> $product_category_name, 
    'echo' => !1,
    'hide_empty' => !1,
    'name' => 'category',
    'class' => 'obj-form-field obj-form-field-input obj-form-field-select obj-input',
    'value_field' => 'term_id',
    'hierarchical' => !0
  ));
  
  $classes = implode(' ', $classes);

  $wp_nonce = wp_create_nonce('product_none');

  ob_start();
  ?>
    <div class="<?= $classes; ?>" data-nonce="<?= $wp_nonce; ?>">
      <div class="obj-inner">
        <div class="obj-inner-section obj-inner-section-header">
          <div class="vc_hidden-xs vc_hidden-sm">
            <div class="vc_row">
              <div class="vc_col-lg-10">
                <ul class="obj-ul-breadcrumb obj-holder-breadcrumb">
                  <?php foreach($crumbles as $crumble): ?>
                  <li class="obj-li obj-li-breadcrumb" data-action="link" data-id="<?= $crumble['id']; ?>"><span class="obj-i-wrap"><i class="obj-i fa fa-angle-double-right"></i></span><a href="<?= $crumble['url']; ?>" class="obj-link"><?= $crumble['title']; ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <div class="vc_col-lg-2">
                <ul class="obj-ul-breadcrumb">
                  <li class="obj-li obj-li-breadcrumb obj-li-back" data-action="back"><span class="obj-i-wrap"><i class="obj-i fa fa-angle-double-left"></i></span><a href="<?= $arc_url; ?>" class="obj-link"><?= __('Back'); ?></a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="vc_hidden-md vc_hidden-lg">
            <form action="?" method="post" accept-charset="utf-8" class="obj-form">
              <div class="obj-inner-form">
                <div class="obj-form-section">
                  <div class="obj-form-field">
                    <?= $product_category_dd; ?>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="obj-inner-section obj-inner-section-body">
          <div class="obj-holder-html">
            <?php if(!$is_single): ?>
              <?= web3_fn_holder_product_category($title_category, $description); ?>
              <?php if(!empty($items)): ?>
              <div class="obj-ul obj-ul-holder-box">
              <?php foreach($items as $item): fn_CategoryItems($item); endforeach; ?>
              </div>
              <?php endif; ?>
              <?php if($has_compare): ?>
              <?= fn_CompareItems($items, $q); ?>
              <?php endif; ?>
            <?php else : ?>
            <?= get_template_part( 'template-parts/content', 'product' ); ?>
            <?php endif; ?>
          </div>
          <span class="obj-bg animate" aria-hidden="true">
            <span class="obj-i-wrap">
              <i class="obj-i fa fa-spin fa-refresh"></i>
            </span>
          </span>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();

  return $output;
}
add_shortcode('web3_vc_holder_product', 'web3_fn_holder_product');

function fn_get_product_items(){
  $nonce = $_REQUEST['_wpnonce'];
  $error = __('Error has been occurred. Please refresh the page.');
  if ( ! wp_verify_nonce( $nonce, 'product_none' ) ){
    echo $error;
    exit;
  }

  $is_error = !0;
  $requests = array(
    array(
      'name' => 'term_id',
      'check'  => 'id'
    ),
    array(
      'name' => 'taxonomy',
      'check'  => 'taxonomy'
    ),
  );
  $passed = array();

  foreach($requests as $request){
    $is_set = (isset($_REQUEST[$request['check']]));
    $val = htmlspecialchars($_REQUEST[$request['check']]);
    if((!$is_set) || (empty($val))){
      $is_error = !0;
    }
    $passed[$request['name']] = $val;
  }

  $is_loop = !1;
  $loop_objs = null;
  
  $items = array();

  $term_obj = get_term($passed['term_id'], $passed['taxonomy']);
  $box_order = get_field('box_order', $term_obj) ?: '1'; 
  $marge_boxes = get_field('marge_boxes', $term_obj) ?: !1; 
  $hide_category_sub = get_field('hide_category_sub', $term_obj) ?: !1;

  $loop_objs = (!$hide_category_sub) ? get_terms(array('taxonomy'=>$passed['taxonomy'],'hide_empty' => !1, 'parent' => $passed['term_id'])) : array();

  $product_arg = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'tax_query' => array(
      'relation' => 'AND',
      array(
        'taxonomy' => $passed['taxonomy'],
        'field' => 'term_id',
        'terms' => $passed['term_id'],
        'include_children' => false
      )
    )
  );
  $items = get_posts($product_arg);

  if($box_order === '1'){
    if(!$marge_boxes){
      if(!empty($items)){
        $items[] = 'br';
      }
    }
    $items = array_merge($items, $loop_objs);
  } else {
    if(!$marge_boxes){
      if(!empty($loop_objs)){
        $loop_objs[] = 'br';
      }
    }
    $items = array_merge($loop_objs, $items);
  }
  
  if(class_exists('WPBMap') && method_exists('WPBMap', 'addAllMappedShortcodes')) {
    WPBMap::addAllMappedShortcodes();
  }

  if((isset($term_obj->term_id)) || ($passed['term_id'] === '0')){
    ob_start();
    if(!empty($items)){
      foreach($items as $obj){
        fn_CategoryItems($obj);
      }
    }
    $loop_obj = ob_get_contents();
    ob_end_clean();
    $title_category = get_field('custom_title_page', $term_obj) ?: $term_obj->name;
    $description_category = $term_obj->description;
    $has_compare = get_field('has_compare', $term_obj);

    global $q;

    $q = $term_obj;

    echo web3_fn_holder_product_category($title_category, $description_category, $term_obj);
    if(!empty($items)){
      echo '<div class="obj-ul">'.$loop_obj.'</div>';
    }

    if($has_compare): ?>
      <?= fn_CompareItems($items, $term_obj); ?>
    <?php endif;

  } else {

    global $post;
    $post = get_post($passed['term_id']);
    $post_id = $post->ID;
    $post_metas = get_post_meta($post_id, '_wpb_shortcodes_custom_css', !0);
    echo get_template_part( 'template-parts/content', 'product' );
    if(!empty($post_metas)){
      echo '<style type="text/css" media="screen">'.$post_metas.'</style>';
    }

  }
  exit;
}
add_action('wp_ajax_get_product_items', 'fn_get_product_items');
add_action('wp_ajax_nopriv_get_product_items', 'fn_get_product_items');

// Play Video
function web3_fn_holder_play_video($atts, $content = null){
  $params = array(
    'title' => '',
    'vc_link' => '',
    'class'=>'obj-default',
  );

  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-play-video';
  $classes = implode(' ', $classes);

  $vc_link = vc_build_link( $vc_link );

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <a href="<?= $vc_link['url']; ?>" class="obj-bg-cover trigger-video"></a>
        <span class="obj-holder-svg">_svg_play_btn_</span>
        <h3 class="obj-title"><?= $title; ?></h3>
        [ultimate_modal modal_contain="ult-youtube" modal_on="custom-selector" modal_on_selector=".trigger-video" modal_size="medium" modal_style="overlay-fade" overlay_bg_color="#000000" overlay_bg_opacity="80" img_size="80"]<iframe width="560" height="315" src="<?= $vc_link['url']; ?>" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>[/ultimate_modal]
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  $output = apply_filters('web3_replacer', $output);
  $output = do_shortcode($output);
  return $output;
}
add_shortcode('web3_vc_holder_play_video', 'web3_fn_holder_play_video');

// Popup on Load
function web3_fn_holder_popup_onload($atts, $content = null){

  if(isset($_SESSION['dislcaimer_agree'])) return;

  $params = array(
    'title' => '',
    'btn_download_id' => '0',
    'btn_download_title' => __('Print/Save'),
    'vc_link_disagree' => '',
    'vc_link_agree' => '',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-box';
  $classes[] = 'obj-holder-box-popup';
  $classes[] = 'obj-holder-disclaimer';
  $classes = implode(' ', $classes);

  $is_dl = !1;

  $download_obj = get_post($btn_download_id);
  if(isset($download_obj->post_title)){
    $is_dl = !0;
    $title = (!empty($title)) ? $title : $download_obj->post_title;
    $content = $content ?: $download_obj->post_content;
  }

  $vc_link_disagree = vc_build_link($vc_link_disagree);
  $vc_link_agree = vc_build_link($vc_link_agree);
  $c = 1;

  $data_arg = array();
  $data_arg[] = 'data-modal="!0"';

  if(isset($vc_link_disagree['url'])){
    $this_url = ($vc_link_disagree['url'] === '#') ? site_url() : $vc_link_disagree['url'] ;
    $data_arg[] = 'data-redirect-url="'.$this_url.'"';
  }

  $nonce = wp_create_nonce('dislcaimer_agree');
  $data_arg[] = 'data-nonce="'.$nonce.'"';

  $data_arg = implode(' ', $data_arg);

  ob_start();
  ?>
  [ultimate_modal modal_on="onload" onload_delay="0" modal_size="medium" modal_style="overlay-fade" overlay_bg_color="#000000" overlay_bg_opacity="80" img_size="80" el_class="modal-onload"]
    <div class="<?= $classes; ?>"<?= $data_arg; ?>>
      <div class="obj-inner">
        <div class="obj-inner-section obj-inner-section-header">
          <h3 class="obj-title"><?= $title; ?></h3>
        </div>
        <div class="obj-inner-section obj-inner-section-body">
          <div class="obj-body-scroll">
            <div class="obj-body-scroll-inner">
              <?= wpautop(do_shortcode($content)); ?>
            </div>
          </div>
        </div>
        <div class="obj-inner-section obj-inner-section-footer">
          <div class="obj-btns obj-btns-hover-1">
            <div class="obj-flex obj-flex-lg-sides">
            <?php if($is_dl): ?>
              <div class="obj-flex-side obj-flex-side-<?= $c; $c++; ?>">
              <?= sprintf('[web3_download id="%s" url_title="%s" style="btn" btn_class="obj-btn-sm" /]', $btn_download_id, $btn_download_title); ?>
              </div>
            <?php endif; ?>
            <div class="obj-flex-side obj-flex-side-<?= $c; $c++; ?>">
              <div class="obj-flex obj-flex-lg-right">
                <?php if(isset($vc_link_disagree['url'])): 
                  $this_url = w3_url($this_url);
                  $this_title = $vc_link_disagree['title'];
                ?>
                <?= sprintf('[web3_vc_btn style="4" class_btn="obj-btn-sm obj-btn-disagree" vc_link="url:%s|title:%s"/]', $this_url, $this_title); ?>
                <?php endif; ?>
                <?php if(isset($vc_link_agree['url'])): 
                  $this_url = ($vc_link_agree['url'] === '#') ? site_url() : $vc_link_agree['url'] ;
                  $this_url = w3_url($this_url);
                  $this_title = $vc_link_agree['title'];
                ?>
                <?= sprintf('[web3_vc_btn style="3" class_btn="obj-btn-sm obj-btn-agree" vc_link="url:%s|title:%s"/]', $this_url, $this_title); ?>
                <?php endif; ?>
              </div>
            </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  [/ultimate_modal]
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  $output = do_shortcode($output);
  return $output;
}
add_shortcode('web3_vc_holder_popup_onload', 'web3_fn_holder_popup_onload');

function fn_session_user_agreed(){

  if(!isset($_REQUEST['nonce'])){
    echo 'error';
    exit;
  }

  $nonce = $_REQUEST['nonce'];
  if(!wp_verify_nonce($nonce, 'dislcaimer_agree')){
    echo 'error';
    exit;
  }

  fn_sest();
  $_SESSION["dislcaimer_agree"] = $nonce;

  exit;
}
add_action('wp_ajax_session_user_agreed', 'fn_session_user_agreed');
add_action('wp_ajax_nopriv_session_user_agreed', 'fn_session_user_agreed');


function web3_fn_holder_hot_spots($atts, $content = null){
  global $q;
  $params = array(
    'has_list' => '1',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-hot-spots';
  $classes = implode(' ', $classes);
  $current_q = $q ?: get_queried_object();

  if(!isset($current_q->term_id)) return;

  $q_id = $current_q->term_id;
  $q_name = $current_q->name;
  $q_description = $current_q->description;
  $q_base_img = get_field('base_img', $current_q);
  $q_base_img_h = $q_base_img['height'];
  $q_base_img_w = $q_base_img['width'];

  $hotspots = get_terms(array('taxonomy'=>'category-product','parent'=>$current_q->term_id, 'hide_empty' => !1));

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="vc_row">
          <div class="vc_col-sm-6 vc_col-lg-7">
            <div class="obj-hot-spot-frame obj-hot-spot-frame-body">
              <div class="obj-item-wrap">
                <div class="obj-item obj-item-base active">
                  <div class="obj-item-panel">
                    <div class="obj-item-panel-section obj-item-panel-section-header">
                      <h4 class="obj-title-panel"><?= $q_name; ?></h4>
                    </div>
                    <div class="obj-item-panel-section obj-item-panel-section-body">
                      <?= wpautop(do_shortcode($q_description)); ?>
                    </div>
                    <?php if($has_list === '1'): ?>
                    <div class="obj-item-panel-section obj-item-panel-section-footer">
                      <ul class="obj-ul">
                        <?php $i = 1; foreach($hotspots as $hotspot): 
                          $hotspot_id = $hotspot->term_id;
                          $hotspot_name = get_field('custom_title', $hotspot) ?: $hotspot->name;
                          $hotspot_name = str_replace('||','', $hotspot_name);
                          $hotspot_name = apply_filters('web3_replacer', $hotspot_name);
                        ?>
                        <li class="obj-li obj-li-<?= $i;?>">
                          <span class="obj-trigger obj-trigger-panel" data-target="<?= $hotspot_id; ?>">
                            <span class="obj-i-wrap"><i class="obj-i"><?= $i; ?></i></span>
                            <span class="obj-text"><?= $hotspot_name; ?></span>
                          </span>
                        </li>
                        <?php $i++; endforeach; ?>
                      </ul>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="obj-item obj-item-panels">
                  <?php foreach($hotspots as $hotspot): 
                    $panel_id = $hotspot->term_id;
                    $panel_name = get_field('custom_title', $hotspot) ?: $hotspot->name;
                    $panel_name_raw = $panel_name;
                    $panel_name = str_replace('||','', $panel_name);
                    $panel_name = apply_filters('web3_replacer', $panel_name);
                    $panel_description = $hotspot->description;
                    $panel_url = get_term_link($panel_id);
                    $panel_url = w3_url($panel_url);
                  ?>
                  <div class="obj-item-panel" data-panel-id="<?= $panel_id; ?>">
                    <div class="obj-item-panel-section obj-item-panel-section-header">
                      <h4 class="obj-title-panel"><?= $panel_name; ?></h4>
                    </div>
                    <div class="obj-item-panel-section obj-item-panel-section-body">
                      <?= wpautop(do_shortcode($panel_description)); ?>
                    </div>
                    <?php ob_start(); ?>
                    <div class="obj-item-panel-section obj-item-panel-section-footer">
                      <div class="obj-btns">
                      <?= sprintf('[web3_vc_btn class_btn="obj-btn-sm obj-btn-back" vc_link="url:#|title:%s"/]', __('Back')); ?>
                      <?= sprintf('[web3_vc_btn class_btn="obj-btn-fluid" vc_link="url:%s|title:%s"/]', $panel_url, $panel_name_raw); ?>
                      </div>
                    </div>
                    <?php $btns = ob_get_contents(); ob_end_clean(); echo do_shortcode($btns); ?>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="vc_col-sm-6 vc_col-lg-5">
            <div class="obj-hot-spot-frame obj-hot-spot-frame-view">
              <div class="obj-flex obj-flex-center">
                <div class="obj-holder-figure obj-holder-tooltips">
                  <img src="<?= $q_base_img['url']; ?>" alt="<?= $q_base_img['alt']; ?>" height="<?= $q_base_img_h; ?>" width="<?= $q_base_img_w; ?>" class="obj-img">
                  <?php $i = 1; global $term_obj; foreach($hotspots as $hotspot): 
                    $panel_id = $hotspot->term_id;
                    $term_obj = $hotspot;
                    echo do_shortcode('[web3_vc_tooltip term_id="'.$panel_id.'" w="'.$q_base_img_w.'" h="'.$q_base_img_h.'" i="'.$i.'" /]');
                  ?>
                  <?php $i++; endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_hot_spots', 'web3_fn_holder_hot_spots');

function web3_fn_tooltip($atts, $content = null){
  $params = array(
    'term_id' => '',
    'panel_name' => '',
    'h' => 'false',
    'w' => 'false',
    'i' => '1',
    'direction' => 'left',
    'class' => 'obj-default',
    'icon_url' => 'false',
    'class' => ''
  );
  extract(shortcode_atts($params, $atts));

  global $term_obj;
  $hotspot = $term_obj ?: get_term($term_id);
  $term_id = $term_id ?: $hotspot->term_id;
  $tooltip_info = $content ?: get_field('information', $hotspot);
  $panel_name = $panel_name ?: get_field('custom_title', $hotspot) ?: $hotspot->name;
  $panel_name = apply_filters('web3_replacer', $panel_name);
  $tooltip_top = (int) get_field('position_top', $hotspot) ?: 0;
  $tooltip_left = (int) get_field('position_left', $hotspot) ?: 0;

  $style = array();
  $sufix = '%;';
  if($h === 'false'){
    $sufix = 'px;';
  } else {
    if($h > 0){
      $tooltip_top = $tooltip_top * 100 / $h;
    } else {
      $tooltip_top = 0;
    }
  }
  $style[] = 'top:'.$tooltip_top.$sufix;

  $sufix = '%;';
  if($w === 'false'){
    $sufix = 'px;';
  } else {
    if($w > 0){
      $tooltip_left = $tooltip_left * 100 / $w;
    } else {
      $w = 0;
    }
  }
  $style[] = 'left:'.$tooltip_left.$sufix;
  
  $style = implode(' ', $style);
  $classes = array('obj-tooltip', 'obj-trigger-panel');
  $classes[] = $class;
  $classes = implode(' ', $classes);
  ob_start();
  ?>
  <div class="<?= $classes; ?>" style="<?= $style; ?>" data-target="<?= $term_id; ?>" data-panel-id="<?= $term_id; ?>">
    <?php if($icon_url === 'false'): ?>
    <span class="obj-i-wrap"><i class="obj-i"><?= $i; ?></i></span>
    <?php else: ?>
    <span class="obj-i-wrap-img"><img src="<?= $icon_url; ?>" alt="marker" width="46" height="60" class="obj-img"></span>
    <?php endif; if(!empty($tooltip_info)): ?>
    <div class="obj-tooltip-info  obj-tooltip-info-<?= $direction;?>">
      <div class="obj-tooltip-info-inner">
        <h5 class="obj-title-tooltip"><?= $panel_name; ?></h5>
        <?= wpautop(do_shortcode($tooltip_info)); ?>
      </div>
      <span class="obj-i-wrap-arrow"><i class="obj-i obj-i-1"></i><i class="obj-i obj-i-2"></i></span>
    </div><?php endif; ?>
  </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;

}
add_shortcode('web3_vc_tooltip', 'web3_fn_tooltip');

function web3_fn_map_markers($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-tooltips';
  $classes = implode(' ', $classes);

  $map_taxo = 'location';
  $map_arg = array('taxonomy'=>$map_taxo, 'hide_empty'=>!1);
  $map_arg['meta_query'] = array(
    'relation' => 'AND',
    array(
      'key' => 'featured_location',
      'value' => '1',
      'compare' => '=', 
    )
  );
  $markers = get_terms($map_arg);
  $marker_img = get_theme_mod('vct_overall_site_map_marker', '');

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <?php global $term_obj; foreach($markers as $marker):
          $term_obj = $marker;
          $marker_address = get_field('marker_address', $marker);
          ob_start(); ?>
          [web3_vc_tooltip icon_url="<?= $marker_img; ?>" direction="right"]
            <ul class="obj-ul">
              <li class="obj-li"><i class="fa fa-map-marker"></i> <?= $marker_address; ?></li>
            </ul>
          [/web3_vc_tooltip]
          <?php
          $tooltip = ob_get_contents();
          ob_end_clean();
          echo do_shortcode($tooltip); 
        endforeach; ?>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_map_markers', 'web3_fn_map_markers');

// Catalog
function fn_CatCat($catalog_cat_items, $catalog_item_arg, $level = 0){ 

  $level++;
  ?>
  <ul class="obj-ul obj-cc obj-cc-level-<?= $level; ?>">
  <?php foreach($catalog_cat_items as $cat_cat_item):
    $cat_item_id = $cat_cat_item->term_id;
    $cat_item_name = $cat_cat_item->name;
    $catalog_item_arg['tax_query'] = array(
      'relation' => 'AND',
      array(
        'taxonomy' => $cat_cat_item->taxonomy,
        'field' => 'term_id',
        'terms' => array($cat_item_id),
        'include_children' => !1,
      )
    );

    $catalog_items = get_posts($catalog_item_arg); 
    $catalog_cat_children = get_terms(array('taxonomy'=>$cat_cat_item->taxonomy, 'parent'=>$cat_item_id,'hide_empty'=>!0));

    ?>

    <li class="obj-li obj-cc-items">
      <span class="obj-i-wrap" aria-hidden="true"><i class="obj-i fa fa-angle-double-right"></i></span>
      <h4 class="obj-title-cc"><?= $cat_item_name; ?></h4>
      <?php foreach($catalog_items as $catalog_item): $cc_id = $catalog_item->ID; $cc_title = $catalog_item->post_title; ?>
      <div class="obj-cc-item obj-search-item" data-title="<?= $cc_title; ?>" data-category="<?= $cat_item_name; ?>">
        <?= do_shortcode('[web3_download id="'.$cc_id.'"/]'); ?>
        <p class="obj-p-category"><small class="obj-text-small"><?= $cat_item_name; ?></small></p>
      </div>
      <?php endforeach; ?>
      <?php if(!empty($catalog_cat_children)): fn_CatCat($catalog_cat_children, $catalog_item_arg, $level); endif; ?>
    </li>
  <?php endforeach; ?>
  </ul>
<?php
}
function web3_fn_holder_catalog($atts, $content = null){
  $params = array(
    'catalog_id' => '0',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-catalog';
  $classes[] = 'obj-search-form';
  $classes = implode(' ', $classes);
  $catalog_cat = 'wpdmcategory';
  $catalog_cat_arg = array('taxonomy'=>$catalog_cat, 'hide_empty'=>!1);
  $catalog_id = (int) $catalog_id;
  if($catalog_id !== 0){
    $catalog_cat_arg['include'] = array($catalog_id); 
  } else {
    $catalog_cat_arg['parent'] = 0;
  }
  $catalog_cat_items = get_terms($catalog_cat_arg);
  $catalog_item_arg = array(
    'post_type' => 'wpdmpro',
    'posts_per_page' => -1,
  );
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-inner-section obj-inner-form">
          <div class="obj obj-search">
            <form action="?" method="get" accept-charset="utf-8" class="obj-form">
              <div class="obj-form-inner">
                <div class="form-section">
                  <div class="vc_row">
                    <div class="vc_col vc_col-sm-12">
                      <div class="obj-form-field obj-form-field-input obj-field-text">
                        <input type="text" name="s-glossary" placeholder="Search..." value="" size="40" class="obj-input obj-input-search" autocomplete="off">
                        <button type="submit" class="obj-submit obj-submit-icon"><i class="obj-i fa fa-search"></i></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
            <div class="obj-search-results"></div>
          </div>
        </div>
        <div class="obj-inner-section obj-inner-section-body">
          <?= fn_CatCat($catalog_cat_items, $catalog_item_arg); ?>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_catalog', 'web3_fn_holder_catalog');


function web3_fn_holder_tab_simple($atts, $content = null){
  $params = array(
    'title'=>'',
    'section_id' => '',
    'btn_title' => __('DOWNLOAD PDF'),
    'pdf_id' => '',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj-entry-section';
  $classes[] = 'obj-entry-section-tab';
  $classes = implode(' ', $classes);
  ob_start();
  ?>
  <div class="entry-content">
    <div class="<?= $classes; ?>">
      <?php if(!empty($title)): ?>
      <div class="obj-js-navlinks obj-tab-count-1">
        <div class="vc_wp_custommenu wpb_content_element vc_hidden-xs">
          <ul class="menu">
            <li class="menu-item"><a href="#<?= $section_id; ?>"><?= $title; ?></a></li>
          </ul>
        </div>
        <div class="obj obj-extra-content">
          <div class="obj-holder-files">
            <div class="obj-inner">
              <div class="obj-inner-section-header obj-content vc_hidden-sm vc_hidden-md vc_hidden-lg vc_hidden-xl">
                <h3 class="obj-title"><?= $title; ?></h3>
              </div>
              <div class="obj-inner-section-body obj-content">
                <?php if(!empty($content)): ?>
                <div class="obj-content-inner">
                  <?= do_shortcode($content); ?>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <?php if(!empty($pdf_id)): ?>
      <div class="obj-entry-section obj-entry-section-files">
        <div class="obj-flex">
          <?= do_shortcode('[web3_download id="'.$pdf_id.'" url_title="'.$btn_title.'" style_icon="1"]'); ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_tab_simple', 'web3_fn_holder_tab_simple');


function web3_fn_holder_contact($atts, $content = null){
  $params = array(
    'career_title' => '',
    'career_page_id' => '',
    'career_body_text' => '',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));


  $items = array();

  $location = get_theme_mod('web3_company_info_address', '');
  $location = nl2br($location);

  if(!empty( $location )){
    $items[] = array(
      'icon' => 'fa-map-marker',
      'title' => __('Address'),
      'body' => $location
    );
  }

  $phone = get_theme_mod('web3_company_info_phone', '');
  if(!empty( $phone )){
    $items[] = array(
      'icon' => 'fa-phone',
      'title' => __('Phone'),
      'body' => $phone
    );
  }

  $email = get_theme_mod('web3_company_info_email', '');
  if(function_exists('eae_encode_emails')){
    $email = eae_encode_emails($email);
  }
  if(!empty( $email )){
    $items[] = array(
      'icon' => 'fa-envelope',
      'title' => __('Email'),
      'body' => $email,
      'url' => 'mailto:'.$email
    );
  }

  if(!empty( $career_title )){
    $link = get_the_permalink($career_page_id);
    $item = array(
      'icon' => 'fa-briefcase',
      'title' => __($career_title),
      'body' => $career_body_text,
    );
    if(!empty( $link )){
      $item['url'] = $link;
    }
    $items[] = $item;
  }

  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-contact';
  $classes = implode(' ', $classes);
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="vc_row">
        <?php foreach ($items as $item) : 
          $item_url = (isset($item['url'])) ? $item['url'] : false;
          $item_title =  (isset($item['title'])) ? $item['title'] : null;
          $item_icon =  (isset($item['icon'])) ? $item['icon'] : null;
          $item_body =  (isset($item['body'])) ? $item['body'] : null;
        ?>
          <div class="obj-item vc_col-xs-12 vc_col-sm-6 vc_col-lg-3">
            <div class="obj-item-inner">
              <div class="obj-item-inner-section obj-item-inner-section-header">
                <span class="obj-i-wrap"><i class="obj-i fa <?= $item_icon; ?>"></i></span>
                <span class="obj-title-text"><?= $item_title; ?></span>
              </div>
              <div class="obj-item-inner-section obj-item-inner-section-body">
                <?php if($item_url): ?><a href="<?= $item_url; ?>" class="obj-link"><?php endif; ?>
                <span class="obj-text"><?= $item_body; ?></span>
                <?php if($item_url): ?></a><?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_contact', 'web3_fn_holder_contact');

function web3_fn_holder_partner($atts, $content = null){
  $params = array(
    'posts_per_page' => 6,
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-partner';
  $classes = implode(' ', $classes);

  $item_arg = array( 'post_type'=>'partner', 'posts_per_page' => $posts_per_page, 'order'=>'ASC' );
  $items = get_posts($item_arg);

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <div class="obj-scroller vc_row">
          <?php foreach($items as $item): 
            $item_id = $item->ID;
            $item_img_id = get_post_thumbnail_id($item_id);
            $item_img_array = wp_get_attachment_image_src($item_img_id, 'full', true);
            $item_img_src = $item_img_array[0];
            $item_style = 'background-image:url('.$item_img_src.');';
            $item_link = get_field('data_page_link', $item_id);
          ?>
          <div class="obj-scroller-item vc_col-sm-6 vc_col-md-4">
            <div class="obj-scroller-item-inner">
              <a href="<?= $item_link; ?>" class="obj-link">
                <span class="obj-item-bg" style="<?= $item_style; ?>"></span>
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_partner', 'web3_fn_holder_partner');


function web3_fn_holder_partner_full($atts, $content = null){
  $params = array(
    'post_type' => 'partner',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-partner-full';
  $classes = implode(' ', $classes);

  $item_arg = array('post_type' => $post_type,'posts_per_page'=>-1, 'order'=>'ASC');
  $items = get_posts($item_arg);

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
      <?php foreach($items as $item): 
        $item_id = $item->ID;
        $item_img_id = get_post_thumbnail_id($item_id);
        $item_img_array = wp_get_attachment_image_src($item_img_id, 'full', true);
        $item_img_src = $item_img_array[0];
        $item_style = 'background-image:url('.$item_img_src.');';
        $item_link = get_field('data_website', $item_id) ?: '#';
        $item_title = $item->post_title;
        $item_id_raw = sanitize_title($item_title);
        $item_title = get_field('data_title_custom', $item_id) ?: $item_title;
        $item_comment = get_field('data_comment', $item_id);
        $item_info = get_field('data_info', $item_id);
        $item_link_website = get_field('data_website', $item_id);
        $item_link_website = apply_filters('w3_url', $item_link_website);
        $item_link_page = get_field('data_page_link', $item_id);
        $is_content = ( !empty( $item_info ) ) || ( !empty( $item_link_website ) );

      ?>
        <div id="<?= $item_id_raw; ?>" class="obj-item<?php if( $is_content ): ?> obj-toggle-item<?php endif; ?>">
          <div class="obj-item-inner obj-toggle-target">
            <div class="obj-item-inner-section obj-item-inner-section-header obj-toggle-trigger" data-href="#<?= $item_id_raw; ?>">
              <div class="vc_row">
                <div class="vc_col-md-4">
                  <div class="obj-item-col obj-item-col-img">
                    <span class="obj-item-thumb" style="<?= $item_style; ?>"></span>
                  </div>
                </div>
                <div class="vc_col-md-8">
                  <div class="obj-item-col obj-item-col-last obj-item-col-header">
                    <?php if( $is_content ): ?>
                    <span class="obj-i-wrap obj-i-wrap-indicator" aria-hidden="true"><i class="obj-i fa fa-angle-down"></i></span>
                    <?php endif; ?>
                    <h3 class="obj-partner-title"><?= $item_title; ?></h3>
                    <?= wpautop( $item_comment ); ?>
                  </div>
                </div>
              </div>
            </div>
            <?php if( $is_content ): ?>
            <div class="obj-item-inner-section obj-item-inner-section-body">
              <div class="vc_row">
                <div class="vc_col-sm-8">
                  <?php if( !empty( $item_info ) ): ?>
                  <div class="obj-item-col obj-item-col-body">
                    <?= wpautop( do_shortcode( $item_info ) ); ?>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="vc_col-sm-4">
                  <div class="obj-item-col obj-item-col-last obj-item-col-footer">
                    <?= do_shortcode('[web3_vc_btn vc_link="url:'.$item_link_website.'|title:'.__('View Website').'"]'); ?>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_partner_full', 'web3_fn_holder_partner_full');

function web3_fn_holder_carousel($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
  );
  $item_count = 3;
  $bps = array(
    'box_attach_image_',
    'box_title_',
    'box_text_',
    'box_vc_link_',
    'box_links_',
  );
  for ($i=1; $i <= $item_count; $i++) { 
    foreach($bps as $bp){
      $params[$bp.$i] = '';
    }
  }
  extract(shortcode_atts($params, $atts));

  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-carousel';
  $classes = implode(' ', $classes);
  
  $i = 1;

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner owl-carousel owl-theme">
        <?php for ($i=1; $i <= $item_count; $i++):
          $box_img_id = ${$bps[0].$i};
          $box_title = ${$bps[1].$i};
          $box_text = ${$bps[2].$i};
          $box_vc_link = ${$bps[3].$i};
          $box_vc_link_buld = vc_build_link($box_vc_link);
          $box_links = ${$bps[4].$i};

          $box_img_array = wp_get_attachment_image_src($box_img_id, 'large');
          $box_img_src = $box_img_array[0];
        ?>
        <div class="obj-carousel-item">
          <div class="obj-box-link">
            <div class="obj-box-link-section obj-box-link-header animate">
              <span class="obj-bg-img animate" style="background-image: url(<?= $box_img_src; ?>);"></span>
            </div>
            <div class="obj-box-link-section obj-box-link-body animate">
              <div class="obj-box-link-body-inner animate">
                <h3 class="obj-title"><?= $box_title; ?></h3>
                <div class="obj-box-link-on animate">
                  <?php if( ( !empty( $box_text ) ) || ( !empty( $box_links ) )): ?>
                  <div class="obj-box-link-text">
                    <?= wpautop($box_text); ?>
                    <?php if( !empty( $box_links ) ): $box_links = explode( ',', $box_links ); ?>
                    <ul class="obj-ul">
                      <?php foreach( $box_links as $id ): 
                        $link = get_the_permalink( $id );
                        $link_title = get_the_title( $id );
                      ?>
                      <li class="obj-li"><a href="<?= $link; ?>" class="obj-link"><?= $link_title; ?></a></li>
                      <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                  </div>
                  <?php endif; ?>
                  <?php if(!empty( $box_vc_link_buld['url'] )): ?>
                  <?= do_shortcode('[web3_vc_btn vc_link="'.$box_vc_link.'"]'); ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_carousel', 'web3_fn_holder_carousel');


function web3_fn_holder_page_links_children($atts, $content = null){
  $params = array(
    'title'=> '',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));

  global $post;
  $post_id = $post->ID;
  $post_id_parent = $post->post_parent;

  $post_children = get_pages( array( 'parent'=> $post_id_parent ) );

  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-page-links';
  $classes = implode(' ', $classes);
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-inner">
        <?php if(!empty( $title )): ?>
        <div class="obj-inner-section obj-inner-section-header">
          <h3 class="obj-title"><?= apply_filters('web3_replacer', $title); ?></h3>
        </div><?php endif; ?>
        <div class="obj-inner-section obj-inner-section-body">
          <div class="nav-links archive-navigation">
            <div class="navigation pagination"  role="navigation">
            <?php $c = 0; foreach($post_children as $child): $c++; 
              $child_id = $child->ID;
              $child_link = get_the_permalink($child_id);
              $child_icon = get_field('page_icon', $child_id);
              $child_title = $child->post_title;
            ?>
              <div class="obj-tooltip<?= ($child_id === $post_id) ? ' current':''; ?>">
              <?php if($child_id === $post_id): ?>
                <span aria-current="page" class="page-numbers current"><?= apply_filters( 'web3_replacer', $child_icon ); ?></span>
              <?php else: ?>
                <a href="<?= $child_link; ?>" class="obj-link page-numbers"><?= apply_filters( 'web3_replacer', $child_icon ); ?></a>
              <?php endif; ?>
                <span class="obj-tooltip-title"><?= $child_title; ?></span>
              </div>
            <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_page_links_children', 'web3_fn_holder_page_links_children');


function web3_fn_search($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
    'title' => __( 'Search Results for' )
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-snippet';
  $classes = implode(' ', $classes);
  if( !empty( $title ) ){
    $title = sprintf(esc_html__( '%s "%s"', 'visual-composer-starter' ), $title, '<strong><span>' . esc_html( get_search_query() ) . '</span></strong>' );
  }
  ob_start();
  ?>
    <div class="search-results-header">
      <?php if( !empty( $title ) ): ?><h3 class="entry-title"><?= $title; ?></h3><?php endif; ?>
      <?php get_search_form(); ?>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_search', 'web3_fn_search');

function web3_fn_toggle($atts, $content = null){
  $params = array(
    'title' => __('General Inquiries'),
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $title = apply_filters( 'web3_replacer', $title );
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-toggle-element';
  $classes = implode(' ', $classes);
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="obj-toggle-section obj-toggle-section-header text-center container-fluid obj-trigger-row" data-target-id="contact-us">
        <div class="row">
          <div class="col-12 p-40">
            <h2 class="obj-title"><?= $title; ?><span class="obj-i-wrap animate ph-20"><i class="obj-i fa fa-angle-down"></i></span></h2>
          </div>
        </div>
      </div>
      <div id="contact-us" class="obj-toggle-section obj-toggle-section-body container obj-target-row">
        <?= do_shortcode( $content ); ?>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_toggle', 'web3_fn_toggle');

function web3_fn_downloadable_items($atts, $content = null){
  $params = array(
    'class'=>'obj-default',
    'category_field'=>'',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-holder-download-list';
  $classes = implode(' ', $classes);

  $item_taxo = 'wpdmcategory';

  $item_args = array(
    'post_type' => 'wpdmpro',
    'posts_per_page' => -1,
  );

  if(!empty( $category_field )){
    $category_field = explode(',', $category_field);
  } else {
    $category_field = array('0');
  }

  $home_url = home_url();

  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="container">
      
      <?php foreach ($category_field as $key => $value):
        $term = '';
        if( $value !== '0' ){
          $item_args['tax_query'] = array(
            'relation' => 'AND',
            array(
              'taxonomy' => $item_taxo,
              'terms' => $value
            )
          );
          $term = get_term( $value, $item_taxo );
        }
        $items = get_posts( $item_args );
      ?>
      <?php if(($term)): ?>
      <h3 class="obj-title mb-30"><?= $term->name; ?></h3>
      <?php endif; ?>
      <?php foreach( $items as $item ): 
        $item_id = $item->ID;
        $item_title = $item->post_title;
        $item_slug = $item->post_name; 
        $item_url = $home_url.'/download/'.$item_slug.'/?wpdmdl='.$item_id;
        $item_url = apply_filters('w3_url', $item_url);
      ?>
      <div class="row obj-dl-list pb-10">
        <div class="col-12 col-lg-8">
          <div class="obj-dl-list-wrap p-20 pb-0 mb-20">
            <div class="obj-dl-list-wrap-col obj-dl-list-wrap-icon">
              <span class="obj-i-wrap"><i class="far fa-file-pdf"></i></span>
            </div>
            <div class="obj-dl-list-wrap-col obj-dl-list-wrap-text">
              <h4 class="obj-dl-list-title pb-20 pl-100"><strong><?= $item_title; ?></strong></h4>
            </div>
          </div>
        </div>
        <div class="col-12 push-lg-1 col-lg-3 push-xl-0 col-xl-4 d-flex justify-content-lg-end pb-20 pb-lg-0">
          <?= do_shortcode('[web3_vc_btn vc_link="url:'.$item_url.'|title:'.__('View PDF').'|target:_blank" style="3"]'); ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endforeach; ?>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_downloadable_items', 'web3_fn_downloadable_items');

// Contact form 7 
function web3_fn_contact_form_7( $atts, $contact = null ){

  $keys = array_keys($atts);

  $res = array_reduce($keys, function($carry, $key) use ($atts) {
     return $carry . " {$key}=\"{$atts[$key]}\"";
  });
  remove_shortcode( 'contact-form-7' );
  add_shortcode('contact-form-7', 'wpcf7_contact_form_tag_func');

  ob_start(); ?>
  <div class="obj obj-contact-form">
    <div class="container p-60 p-lg-100">
      [contact-form-7<?= $res; ?>]
    </div>
  </div>
  <?php
  $sc = ob_get_contents();
  ob_end_clean();
  return do_shortcode( $sc );
}
remove_shortcode( 'contact-form-7' );
add_shortcode('contact-form-7', 'web3_fn_contact_form_7');

// Contact Information
function web3_fn_holder_contact_info($atts, $content = null){
  $params = array(
    'title' => '',
    'class'=>'obj-default',
  );
  extract(shortcode_atts($params, $atts));
  $output = null;
  $content = wpb_js_remove_wpautop( $content, true );
  $content = wpautop( $content );
  $classes = explode(' ', $class);
  $classes[] = 'obj';
  $classes[] = 'obj-contact-information';
  $classes = implode(' ', $classes);

  $phone_number = get_theme_mod('web3_company_info_phone', '780.760.3333');
  $fax_number = get_theme_mod('web3_company_info_fax', '780.760.3333');
  $company_address = get_theme_mod('web3_company_info_address', '780.760.3333');
  $company_hours = get_theme_mod('web3_company_hours', '');

  $company_address = explode(PHP_EOL, $company_address);
  if( (!empty( $company_address )) ){
    $new_address = '<p class="mb-15">'.$company_address[0].'</p>';
    if( count($company_address) > 1 ){
      $new_address .= '<p class="mb-15">';
    }
    $company_address[0] = '';
    foreach( $company_address as $line ){
      if( (!empty($line) ) ){
        $new_address .= ' '.$line;
      }
    }
    if( count($company_address) > 1 ){
      $new_address .= '</p>';
    }
    $company_address = $new_address;
  }

  $company_hours = explode(PHP_EOL, $company_hours);
  if( (!empty( $company_hours )) ){
    $new_company_hours = '<p class="mb-15">'.$company_hours[0].'</p>';
    if( count($company_hours) > 1 ){
      $new_company_hours .= '<p class="mb-15">';
    }
    $company_hours[0] = '';
    foreach( $company_hours as $line ){
      $line = trim( $line );
      if( (!empty($line) ) ){
        $new_company_hours .= ' '.$line;
      }
    }
    if( count($company_hours) > 1 ){
      $new_company_hours .= '</p>';
    }
    $company_hours = $new_company_hours;
  }

  $has_extra_item = ( (!empty($title)) || (!empty($content)) );
  $col_class = array(
    'mb-40',
    'col-auto',
  );
  $col_class = implode(' ', $col_class);
  ob_start();
  ?>
    <div class="<?= $classes; ?>">
      <div class="container pt-40">
        <div class="row d-flex justify-content-between">
          <?php if( $has_extra_item ): ?>
          <div class="col-auto mb-40">
            <?php if( ( (!empty($title))  )): ?>
            <h3 class="mb-10"><?= $title; ?></h3>
            <?php endif; ?>
            <?= $content; ?>
          </div>
          <?php endif; ?>
          <div class="<?= $col_class; ?>">
            <h3 class="mb-15"><?= __('Contact Info'); ?></h3>
            <p class="mb-15">P. <?= $phone_number; ?></p>
            <p class="mb-15">F. <?= $fax_number; ?></p>
          </div>
          <div class="col-auto mb-40">
            <h3 class="mb-15"><?= __('Address'); ?></h3>
            <?= $company_address; ?>
          </div>
          <div class="col-auto mb-40">
            <h3 class="mb-15"><?= __('Office Hours'); ?></h3>
            <?= $company_hours; ?>
          </div>
        </div>
      </div>
    </div>
  <?php
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
add_shortcode('web3_vc_holder_contact_info', 'web3_fn_holder_contact_info');


require get_stylesheet_directory().'/web3/short-codes/_content.php';
require get_stylesheet_directory().'/web3/short-codes/_testimonial.php';
require get_stylesheet_directory().'/web3/short-codes/_features.php';