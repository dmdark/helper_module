<?php
$html = '<div class="_seo_is_list">';
foreach($items as $item){
   $html .= '<div class="_seo_is_list-listitem">';

   $html .= '<div class="_seo_is_list-title">';
   if(!empty($config['template_item'])){
      $html .= '<a href="' . _s_getInformationSystemUrl($item['is_url'], $item['url']) . '">' . $item['title'] . '</a>';
   } else{
      $html .= $item['title'];
   }
   $html .= '</div>';
   $html .= '<div>' . $item['text'] . '</div>';
//   $html .= '<div class="_seo_is_list-date">' . $item['date'] . '</div>';
   $html .= '</div>';
}
$html .= '</div>';

$html .= '<link href="/_seo/frontend/information_systems/styles.css" rel="stylesheet" type="text/css"/>';
return $html;
