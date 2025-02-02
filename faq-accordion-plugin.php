<?php
/*
Plugin Name: FAQ Accordion Plugin
Description: Adds an FAQ section with a modern accordion layout to posts and products.
Version: 1.8
Author: Pooya
*/

if (!defined('ABSPATH')) {
    exit; // امنیت افزونه
}

// اضافه کردن متاباکس به ویرایشگر نوشته‌ها و محصولات
function faq_add_meta_box() {
    $post_types = ['post', 'product']; // اضافه کردن نوع نوشته محصول
    foreach ($post_types as $post_type) {
        add_meta_box(
            'faq_meta_box', 'FAQ Section', 'faq_meta_box_html', $post_type, 'normal', 'high'
        );
    }
}
add_action('add_meta_boxes', 'faq_add_meta_box');

// HTML مربوط به متاباکس
function faq_meta_box_html($post) {
    $faq_data = get_post_meta($post->ID, '_faq_data', true);
    ?>
    <div id="faq-accordion">
        <div id="faq-items">
            <?php if (!empty($faq_data)) {
                foreach ($faq_data as $faq) { ?>
                    <div class="faq-item">
                        <input type="text" name="faq_question[]" placeholder="Question" value="<?php echo esc_attr($faq['question']); ?>" />
                        <textarea name="faq_answer[]" placeholder="Answer"><?php echo esc_textarea($faq['answer']); ?></textarea>
                        <button type="button" class="remove-faq">Remove</button>
                    </div>
                <?php }
            } ?>
        </div>
        <button type="button" id="add-faq">Add FAQ</button>
    </div>
    <p>از این شرت کد استفاده کن</p>
    <code>[faq_accordion]</code> <!-- نمایش شرت‌کد -->
    <?php
}

// ذخیره اطلاعات متاباکس
function faq_save_postdata($post_id) {
    if (array_key_exists('faq_question', $_POST)) {
        $faq_data = [];
        foreach ($_POST['faq_question'] as $index => $question) {
            $faq_data[] = [
                'question' => sanitize_text_field($question),
                'answer' => sanitize_textarea_field($_POST['faq_answer'][$index]),
            ];
        }
        update_post_meta($post_id, '_faq_data', $faq_data);
    }
}
add_action('save_post', 'faq_save_postdata');

// شرت‌کد برای نمایش سوالات به صورت آکاردئون
function faq_accordion_shortcode() {
    global $post;
    $faq_data = get_post_meta($post->ID, '_faq_data', true);

    if (empty($faq_data)) return '';

    ob_start();
    ?>
    <div class="faq-accordion">
        <?php foreach ($faq_data as $faq) { ?>
            <div class="faq-item">
                <h3 class="faq-question"><?php echo esc_html($faq['question']); ?></h3>
                <div class="faq-answer" style="display: none;"><?php echo wpautop($faq['answer']); ?></div>
            </div>
        <?php } ?>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('.faq-question').on('click', function() {
                $(this).next('.faq-answer').slideToggle();
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('faq_accordion', 'faq_accordion_shortcode');

// اضافه کردن CSS به صورت inline
function faq_accordion_inline_styles() {
    echo '<style>
    .faq-accordion { 
        margin-top: 20px;
        border-radius: 14px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background-color: #f9f9f9;
        padding: 10px;
    }

    .faq-item {
        margin-bottom: 10px;
        border-radius: 8px;
        overflow: hidden;
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .faq-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .faq-question {
        background-color: #ffffff;
        color: #333;
        padding: 15px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        border: none;
        text-align: right;
        transition: background-color 0.3s ease;
        border-radius: 13px;
        margin-bottom: 5px;
    }

    .faq-question:hover {
        background-color: #f6f6f6;
    }

    .faq-answer {
        padding: 15px;
        background-color: #fff;
        border-top: 1px solid #e0e0e0;
        display: none;
        font-size: 14px;
        line-height: 1.6;
        color: #666;
    }
    </style>';
}
add_action('wp_head', 'faq_accordion_inline_styles');

// جاوااسکریپت برای مدیریت بخش افزودن سوالات متداول در ویرایشگر
function faq_admin_scripts() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            // اضافه کردن سوال جدید
            $('#add-faq').on('click', function() {
                $('#faq-items').append(`
                <div class="faq-item">
                    <input type="text" name="faq_question[]" placeholder="Question" />
                    <textarea name="faq_answer[]" placeholder="Answer"></textarea>
                    <button type="button" class="remove-faq">Remove</button>
                </div>
            `);
            });

            // حذف سوال
            $(document).on('click', '.remove-faq', function() {
                $(this).closest('.faq-item').remove();
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'faq_admin_scripts');