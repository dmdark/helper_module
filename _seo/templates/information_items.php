<?php
$html = '<div class="_seo_is_list">';
foreach($information_items['items'] as $item){
   $html .= '<div class="_seo_is_list-item">';
   if(!empty($config['template_item'])){
      $html .= '<div><a href="' . _s_getInformationSystemUrl($information_items['url'], $item['url']) . '">' . $item['title'] . '</a></div>';
   } else{
      $html .= '<div>' . $item['title'] . '</div>';
   }
   $html .= '<div>' . $item['text'] . '</div>';
   $html .= '</div>';
}
$html .= '</div>';
return $html;
