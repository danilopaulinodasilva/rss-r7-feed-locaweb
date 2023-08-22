<?php

/*
Plugin Name: RSS Feed Portal R7 Locaweb
Description: Plugin para criar um feed RSS para o Portal R7 da Rede Record personalizado na hospedagem da Locaweb
Version: 1.0.0
Author: Danilo P. da Silva
Author URI: https://dps.tec.br
Plug-in URI: https://github.com/danilopaulinodasilva/rss-r7-feed-locaweb
License: GPL-3.0+
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

function get_featured_image_url($post_id)
{
  $image_url = '';
  $thumbnail_id = get_post_thumbnail_id($post_id);
  if ($thumbnail_id) {
    $image_url = wp_get_attachment_url($thumbnail_id);
  }
  return $image_url;
}


function custom_feed_template()
{
  // Define o tipo de conteúdo para o feed
  header('Content-Type: application/rss+xml; charset=UTF-8');

  // Cria o cabeçalho do feed
  echo '<?xml version="1.0" encoding="UTF-8"?>';
  echo '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">';

  // Adiciona a tag <atom:link>
  echo '<channel>';
  echo '<atom:link href="' . esc_url(get_feed_link('rssr7')) . '" rel="self" type="application/rss+xml" />';
  echo '<title>Meu Feed Personalizado</title>';
  echo '<link>' . get_bloginfo('url') . '</link>';
  echo '<description>Meu Feed Personalizado</description>';


  // Query personalizada para obter os posts desejados
  $args = array(
    'post_type' => 'post',
    'posts_per_page' => 30,
  );
  $query = new WP_Query($args);

  // Loop através dos posts
if ($query->have_posts()) {
  while ($query->have_posts()) {
    $query->the_post();

    // Obtém os dados do post
    $post_title = get_the_title();
    $post_link = get_permalink();

        // Obter a data formatada do post
        $post_date = get_the_time('Y-m-d H:i:s');
    $post_date_pubdate = date(DATE_RFC2822, strtotime($post_date));

    $post_author = get_the_author();
    $post_category = get_the_category();
    $post_description = get_the_excerpt();
    $post_featured_image = get_featured_image_url(get_the_ID()); // Obtém a URL da imagem em destaque

    // Obtém o conteúdo formatado do post
    $post_content = get_the_content();
    $post_content_encoded = '<![CDATA[' . htmlspecialchars($post_content) . ']]>';

    // Inicia a exibição do item no feed
    echo '<item>';
    echo '<title>' . html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8') . '</title>';
    echo '<link>' . htmlspecialchars($post_link, ENT_XML1) . '</link>';
    echo '<guid isPermaLink="false">' . htmlspecialchars($post_link, ENT_XML1) . '</guid>';
    echo '<pubDate>' . $post_date_pubdate . '</pubDate>';
    echo '<dc:creator>' . htmlspecialchars($post_author, ENT_XML1) . '</dc:creator>';

    foreach ($post_category as $category) {
      echo '<category>' . htmlspecialchars($category->name, ENT_XML1) . '</category>';
    }

    echo '<description>' . htmlspecialchars($post_description, ENT_XML1) . '</description>';
      echo '<content:encoded><![CDATA[';
      $post_featured_image = get_featured_image_url(get_the_ID());
      echo '<img src="' . esc_url($post_featured_image) . '" alt="" />';
      echo '' . htmlspecialchars(get_the_content(), ENT_XML1) . '';
      echo ']]></content:encoded>';
      echo '</item>';

  }
}


  // Finaliza o feed
  echo '</channel>';
  echo '</rss>';

  // Restaura a consulta original do WordPress
  wp_reset_query();

  // Encerra a execução do script
  exit;
}

add_action('do_feed_custom', 'custom_feed_template');

// Registra o feed personalizado
function register_custom_feed()
{
  add_feed('rssr7', 'custom_feed_template');
}

add_action('init', 'register_custom_feed');