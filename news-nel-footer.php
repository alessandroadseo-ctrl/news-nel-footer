<?php
/*
Plugin Name: News nel Footer
Description: Slider offerte automatico categoria offerte
Version: 1.5
Author: Alessandro Isoardi
*/

if (!defined('ABSPATH')) {
    exit;
}

function nnf_should_render() {
    return !is_admin() && !is_feed() && !is_search();
}

function nnf_mark_assets_required() {
    $GLOBALS['nnf_assets_required'] = true;
}

function nnf_assets_required() {
    return !empty($GLOBALS['nnf_assets_required']);
}

function nnf_shortcode($atts) {
    if (!nnf_should_render()) {
        return '';
    }

    static $instance = 0;
    $instance++;

    $atts = shortcode_atts(['posts' => 6, 'categoria' => 'offerte'], $atts);
    $posts_per_page = max(1, (int) $atts['posts']);
    $categoria_raw = trim((string) $atts['categoria']);

    $query_args = [
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ];

    if ($categoria_raw !== '') {
        $query_args[is_numeric($categoria_raw) ? 'cat' : 'category_name'] = is_numeric($categoria_raw) ? (int) $categoria_raw : sanitize_title($categoria_raw);
    }

    $query = new WP_Query($query_args);
    if (!$query->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    nnf_mark_assets_required();

    ob_start(); ?>
    <div class="nnf-container" id="<?php echo esc_attr('nnf-slider-' . $instance); ?>">
        <div class="nnf-header"><span>ALTRE OFFERTE CONSIGLIATE</span></div>
        <div class="nnf-viewport"><div class="nnf-track"><?php while ($query->have_posts()) : $query->the_post(); ?><a class="nnf-card" href="<?php the_permalink(); ?>"><div class="nnf-img"><?php if (has_post_thumbnail()) { the_post_thumbnail('medium'); } ?></div><div class="nnf-text"><div class="nnf-title"><?php the_title(); ?></div><div class="nnf-date"><?php echo esc_html(get_the_date('d/m/Y')); ?></div></div></a><?php endwhile; ?></div></div>
        <div class="nnf-dots"></div>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('news_offerte_footer', 'nnf_shortcode');

function nnf_style() {
    if (!nnf_assets_required()) {
        return;
    }

    echo '<style>.nnf-container{max-width:1200px;margin:auto;padding:20px 14px;overflow:hidden}.nnf-header{display:flex;align-items:center;justify-content:center;margin-bottom:16px;font-weight:700;letter-spacing:.5px;font-size:14px;color:#243a5a;text-transform:uppercase;line-height:1}.nnf-header span{font-size:14px}.nnf-header:before,.nnf-header:after{content:"";flex:1;height:1px;background:#d9d9d9;margin:0 14px}.nnf-viewport{overflow:hidden;touch-action:pan-y;cursor:grab;user-select:none}.nnf-viewport.is-dragging{cursor:grabbing}.nnf-track{display:flex;gap:16px;transition:transform .3s ease;will-change:transform}.nnf-card{display:flex;align-items:center;gap:12px;flex:0 0 calc((100% - 32px)/3);min-width:0;text-decoration:none;color:#000}.nnf-img{width:95px;height:95px;flex:0 0 95px;border-radius:12px;overflow:hidden;background:#f3f3f3}.nnf-img img{width:100%;height:100%;object-fit:cover;display:block}.nnf-text{min-width:0}.nnf-title{font-size:14px;font-weight:700;line-height:1.12;margin-bottom:8px;color:#11294d;word-break:break-word}.nnf-date{font-size:12px;color:#1a3559;line-height:1.1}.nnf-dots{display:flex;justify-content:center;align-items:center;gap:10px;margin-top:18px}.nnf-dot{width:14px;height:14px;border-radius:50%;border:none;background:#e0e2e7;padding:0;cursor:pointer}.nnf-dot.is-active{background:#4a5568}@media (max-width:768px){.nnf-card{flex:0 0 100%}.nnf-header,.nnf-header span{font-size:14px}.nnf-title{font-size:14px}.nnf-date{font-size:12px}}</style>';
}
add_action('wp_head', 'nnf_style');

function nnf_script() {
    if (!nnf_assets_required()) {
        return;
    }

    echo "<script>(function(){function i(c){var t=c.querySelector('.nnf-track'),d=c.querySelector('.nnf-dots'),v=c.querySelector('.nnf-viewport');if(!t||!d||!v)return;var cards=[].slice.call(t.querySelectorAll('.nnf-card'));if(!cards.length)return;var p=0,dots=[],sx=0,dragging=false;function cpp(){return window.matchMedia('(max-width: 768px)').matches?1:3}function pages(){return Math.ceil(cards.length/cpp())}function setPage(n){var m=pages();p=Math.max(0,Math.min(n,m-1));var el=cards[p*cpp()],off=el?el.offsetLeft:0;t.style.transform='translateX(-'+off+'px)';dots.forEach(function(x,j){x.classList.toggle('is-active',j===p)})}function next(){setPage(p+1)}function prev(){setPage(p-1)}function start(x){sx=x;dragging=true;v.classList.add('is-dragging')}function end(x){if(!dragging)return;dragging=false;v.classList.remove('is-dragging');var dx=x-sx;if(Math.abs(dx)<40)return;dx<0?next():prev()}function rd(){var m=pages();d.innerHTML='';dots=[];if(m<=1)return;for(var j=0;j<m;j++){var b=document.createElement('button');b.type='button';b.className='nnf-dot'+(j===p?' is-active':'');b.setAttribute('aria-label','Vai alla slide '+(j+1));(function(k){b.addEventListener('click',function(){setPage(k)})})(j);d.appendChild(b);dots.push(b)}}function rb(){p=Math.min(p,Math.max(0,pages()-1));rd();setPage(p)}v.addEventListener('touchstart',function(e){if(e.touches&&e.touches[0])start(e.touches[0].clientX)},{passive:true});v.addEventListener('touchend',function(e){if(e.changedTouches&&e.changedTouches[0])end(e.changedTouches[0].clientX)});v.addEventListener('mousedown',function(e){start(e.clientX)});window.addEventListener('mouseup',function(e){end(e.clientX)});v.addEventListener('mouseleave',function(e){if(dragging)end(e.clientX)});rb();window.addEventListener('resize',rb)}document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.nnf-container').forEach(i)})})();</script>";
}
add_action('wp_footer', 'nnf_script');
