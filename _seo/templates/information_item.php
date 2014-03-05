<?php
$html = '<div class="_seo_is_item">';
$html .= '<div class="_seo_is_item-title">' . $information_item['title'] . '</div>';
$html .= '<div class="_seo_is_item-text">' . $information_item['text'] . '</div>';
$html .= '<a href="' . $information_items['url'] . '">вернуться к списку</a>';
$html .= '</div>';
return $html;