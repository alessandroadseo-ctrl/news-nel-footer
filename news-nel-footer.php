<?php
/*
Plugin Name: News nel Footer
Description: Slider offerte automatico categoria offerte
Version: 1.1
Author: Alessandro Isoardi
*/

if (!defined('ABSPATH')) exit;

function nnf_shortcode($atts){

$atts = shortcode_atts([
'posts' => 6,
'categoria' => 'offerte'
], $atts);

$query = new WP_Query([
'post_type' => 'post',
'posts_per_page' => $atts['posts'],
'category_name' => $atts['categoria']
]);

ob_start();

?>

<div class="nnf-container">

<div class="nnf-header">
<span>ALTRE OFFERTE CONSIGLIATE</span>
</div>

<div class="nnf-slider">

<?php while($query->have_posts()) : $query->the_post(); ?>

<a class="nnf-card" href="<?php the_permalink(); ?>">

<div class="nnf-img">
<?php the_post_thumbnail('medium'); ?>
</div>

<div class="nnf-text">

<div class="nnf-title">
<?php the_title(); ?>
</div>

<div class="nnf-author">
Di <?php the_author(); ?>
</div>

</div>

</a>

<?php endwhile; ?>

</div>

<div class="nnf-dots"></div>

</div>

<?php

wp_reset_postdata();

return ob_get_clean();

}

add_shortcode('news_offerte_footer','nnf_shortcode');


/* CSS */

function nnf_style(){

?>

<style>

.nnf-container{
max-width:1200px;
margin:auto;
padding:25px 10px;
}

.nnf-header{
display:flex;
align-items:center;
justify-content:center;
margin-bottom:20px;
font-weight:700;
letter-spacing:1px;
font-size:14px;
color:#2c3e50;
}

.nnf-header:before,
.nnf-header:after{
content:"";
flex:1;
height:1px;
background:#ddd;
margin:0 15px;
}

.nnf-slider{
display:flex;
gap:20px;
overflow:hidden;
}

.nnf-card{
display:flex;
gap:12px;
width:280px;
flex-shrink:0;
text-decoration:none;
color:#000;
}

.nnf-img img{
width:90px;
height:90px;
border-radius:10px;
object-fit:cover;
}

.nnf-title{
font-size:14px;
font-weight:600;
line-height:1.3;
margin-bottom:6px;
}

.nnf-author{
font-size:12px;
color:#777;
}

/* responsive */

@media(max-width:768px){

.nnf-slider{
overflow-x:auto;
}

}

</style>

<?php

}

add_action('wp_head','nnf_style');