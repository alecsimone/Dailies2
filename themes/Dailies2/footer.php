<script>
jQuery(window).scroll(function() {
    if (jQuery(this).scrollTop() >= 100) {
        jQuery('#return-to-top').fadeIn(500);
    } else {
        jQuery('#return-to-top').fadeOut(500);
    }
});
jQuery('#return-to-top').click(function() {
    jQuery('body,html').animate({
        scrollTop : 0
    }, 500);
});
</script>

<?php wp_footer(); ?>
</body>
