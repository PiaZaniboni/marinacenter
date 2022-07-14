jQuery(function ($) {
    $.productVariations = function (el) {
        var $widget = $(el),
            $variationList = $widget.find('.the7-vr-options'),
            $form = $widget.find('form'),
            $singleVariation  = $form.find( '.single_variation' );
            methods = {};
        // $widget.vars = {
        //     toogleSpeed: 250,
        //     animationSpeed: 150,
        //     fadeIn: {opacity: 1},
        //     fadeOut: {opacity: 0}
        // };
        // Store a reference to the object
        $.data(el, "productVariations", $widget);
        // Private methods
        methods = {
            init: function () {
                $('li a', $variationList).on('click', function(e) {
                    e.preventDefault();
                    var $this = $(this),
                        $parent = $this.parent();
                    var id = $this.attr('data-id');
                    var atr = $this.parents('ul').attr('data-atr');
                    if($parent.hasClass('active')){
                        $parent.removeClass('active');
                        $singleVariation.slideUp( 200 );

                    }else{
                        $parent.siblings().removeClass('active');
                        $parent.addClass('active');
                        $this.parents().siblings('select#' + atr).val(id);
                        $this.parents().siblings('select#' + atr).trigger('change');
                        $singleVariation.slideDown( 200 );
                        setTimeout(function() {
                            methods.triggerChange();
                        }, 200);
                    }
                });
                $widget.find( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {

                    $widget.find('.woocommerce-variation > div').not(':empty').last().addClass('last');
                } );

                $('.woocommerce div.product form.cart .variations select').each(function() {
                    var $this = $(this),
                        val = $this.val(),
                        atr = $this.attr('id');
                    if (val.length) {
                        if ($('.the7-vr-options[data-atr="' + atr + '"] li a[data-id="' + val + '"]').length) {
                            $('.the7-vr-options[data-atr="' + atr + '"] li a[data-id="' + val + '"]').trigger('click');
                        } else if ($('.the7-vr-options[data-atr="' + atr + '"]').length) {
                            $('.the7-vr-options[data-atr="' + atr + '"]').val(val);
                        }
                    }
                });


            },
            triggerChange: function($el) {
                $variationList.each(function() {
                    var $this = $(this),
                        $box = $this.parents('.variations');

                    if ($box.find('select').length) {
                        var id = $box.find('select').val();
                        var atr = $box.find('select').attr('id');
                    } else {
                        var id = $box.find('li.active a').attr('data-id');
                        var atr = $box.find($variationList).attr('data-atr');
                    }
                    if (!$('.variations select#' + atr).val().length) {
                        $('.variations select#' + atr).val(id);
                        $('.variations select#' + atr).trigger('change');
                    }
                    if ($this.find('li.active.disabled').length) {
                        var dis_atr = $(this).attr('data-atr');
                        $('.woocommerce div.product form.cart .variations select#' + dis_atr).val('');
                    }

                });
            },


        };
        //global functions

        methods.init();
    };

    $.fn.productVariations = function () {
        return this.each(function () {
            if ($(this).data('productVariations') !== undefined) {
                $(this).removeData("productVariations")
            }
            new $.productVariations(this);
        });
    };
});
(function ($) {
    // Make sure you run this code under Elementor.
    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction("frontend/element_ready/the7-woocommerce-product-add-to-cart-v2.default", function ($widget, $) {
            $(document).ready(function () {
                $widget.productVariations();
            })
        });
    });
})(jQuery);
