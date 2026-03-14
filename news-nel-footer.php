<?php
/*
Plugin Name: News nel Footer
Description: Slider offerte automatico categoria offerte
Version: 1.3
Author: Alessandro Isoardi
*/

if (!defined('ABSPATH')) {
    exit;
}

function nnf_shortcode($atts) {
    static $instance = 0;
    $instance++;

    $atts = shortcode_atts([
        'posts' => 6,
        'categoria' => 'offerte',
    ], $atts);

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
        if (is_numeric($categoria_raw)) {
            $query_args['cat'] = (int) $categoria_raw;
        } else {
            $query_args['category_name'] = sanitize_title($categoria_raw);
        }
    }

    $query = new WP_Query($query_args);

    if (!$query->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    $slider_id = 'nnf-slider-' . $instance;

    ob_start();
    ?>

    <div class="nnf-container" id="<?php echo esc_attr($slider_id); ?>">

        <div class="nnf-header">
            <span>ALTRE OFFERTE CONSIGLIATE</span>
        </div>

        <div class="nnf-viewport">
            <div class="nnf-track">

                <?php while ($query->have_posts()) : $query->the_post(); ?>

                    <a class="nnf-card" href="<?php the_permalink(); ?>">

                        <div class="nnf-img">
                            <?php
                            if (has_post_thumbnail()) {
                                the_post_thumbnail('medium');
                            }
                            ?>
                        </div>

                        <div class="nnf-text">

                            <div class="nnf-title">
                                <?php the_title(); ?>
                            </div>

                            <div class="nnf-date">
                                <?php echo esc_html(get_the_date('d/m/Y')); ?>
                            </div>

                        </div>

                    </a>

                <?php endwhile; ?>

            </div>
        </div>

        <div class="nnf-dots"></div>

    </div>

    <?php

    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('news_offerte_footer', 'nnf_shortcode');

/* CSS */

function nnf_style() {
    ?>

    <style>
        .nnf-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px 14px;
            overflow: hidden;
        }

        .nnf-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 14px;
            color: #243a5a;
            text-transform: uppercase;
            line-height: 1;
        }

        .nnf-header span {
            font-size: 14px;
        }

        .nnf-header:before,
        .nnf-header:after {
            content: "";
            flex: 1;
            height: 1px;
            background: #d9d9d9;
            margin: 0 14px;
        }

        .nnf-viewport {
            overflow: hidden;
        }

        .nnf-track {
            display: flex;
            gap: 16px;
            transition: transform 0.3s ease;
            will-change: transform;
        }

        .nnf-card {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 0 0 calc((100% - 32px) / 3);
            min-width: 0;
            text-decoration: none;
            color: #000;
        }

        .nnf-img {
            width: 95px;
            height: 95px;
            flex: 0 0 95px;
            border-radius: 12px;
            overflow: hidden;
            background: #f3f3f3;
        }

        .nnf-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .nnf-text {
            min-width: 0;
        }

        .nnf-title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.12;
            margin-bottom: 8px;
            color: #11294d;
            word-break: break-word;
        }

        .nnf-date {
            font-size: 12px;
            color: #1a3559;
            line-height: 1.1;
        }

        .nnf-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
        }

        .nnf-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: none;
            background: #e0e2e7;
            padding: 0;
            cursor: pointer;
        }

        .nnf-dot.is-active {
            background: #4a5568;
        }

        @media (max-width: 768px) {
            .nnf-card {
                flex: 0 0 100%;
            }

            .nnf-header {
                font-size: 14px;
            }

            .nnf-header span {
                font-size: 14px;
            }

            .nnf-title {
                font-size: 14px;
            }

            .nnf-date {
                font-size: 12px;
            }
        }
    </style>

    <?php
}

add_action('wp_head', 'nnf_style');

function nnf_script() {
    ?>

    <script>
        (function() {
            function initSlider(container) {
                var track = container.querySelector('.nnf-track');
                var dotsWrap = container.querySelector('.nnf-dots');
                if (!track || !dotsWrap) {
                    return;
                }

                var cards = Array.prototype.slice.call(track.querySelectorAll('.nnf-card'));
                if (!cards.length) {
                    return;
                }

                var currentPage = 0;
                var dots = [];

                function cardsPerPage() {
                    return window.matchMedia('(max-width: 768px)').matches ? 1 : 3;
                }

                function getPageCount() {
                    return Math.ceil(cards.length / cardsPerPage());
                }

                function goToPage(page) {
                    var pageCount = getPageCount();
                    currentPage = Math.max(0, Math.min(page, pageCount - 1));
                    var targetCard = cards[currentPage * cardsPerPage()];
                    var offset = targetCard ? targetCard.offsetLeft : 0;
                    track.style.transform = 'translateX(-' + offset + 'px)';

                    dots.forEach(function(dot, index) {
                        dot.classList.toggle('is-active', index === currentPage);
                    });
                }

                function renderDots() {
                    var pageCount = getPageCount();
                    dotsWrap.innerHTML = '';
                    dots = [];

                    if (pageCount <= 1) {
                        return;
                    }

                    for (var i = 0; i < pageCount; i++) {
                        var dot = document.createElement('button');
                        dot.type = 'button';
                        dot.className = 'nnf-dot' + (i === currentPage ? ' is-active' : '');
                        dot.setAttribute('aria-label', 'Vai alla slide ' + (i + 1));
                        dot.addEventListener('click', (function(pageIndex) {
                            return function() {
                                goToPage(pageIndex);
                            };
                        })(i));
                        dotsWrap.appendChild(dot);
                        dots.push(dot);
                    }
                }

                function rebuild() {
                    currentPage = Math.min(currentPage, Math.max(0, getPageCount() - 1));
                    renderDots();
                    goToPage(currentPage);
                }

                rebuild();
                window.addEventListener('resize', rebuild);
            }

            document.addEventListener('DOMContentLoaded', function() {
                var sliders = document.querySelectorAll('.nnf-container');
                sliders.forEach(initSlider);
            });
        })();
    </script>

    <?php
}

add_action('wp_footer', 'nnf_script');
