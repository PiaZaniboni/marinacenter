jQuery(function ($) {
    $.menuCart = function (el) {
        let $widget = $(el),
            $cartBtn = $widget.find('.dt-menu-cart__toggle');
            methods = {};
        // Store a reference to the object
        $.data(el, "menuCart", $widget);
        // Private methods
        methods = {
            init: function () {
                $(document.body).on( 'wc_fragments_loaded wc_fragments_refreshed', function() {
                    const $cartCount = $widget.find('.dt-cart-subtotal').attr('data-product-count');
                    $widget.find('[data-counter]').attr('data-counter',  $cartCount);
                    $widget.find('.dt-cart-indicator').text('(' + $cartCount + ')');
                    if($cartCount > 0){
                        $cartBtn.removeClass('dt-empty-cart');
                    }else{
                        $cartBtn.addClass('dt-empty-cart');
                    }
              } );
            },
        };
        //global functions

        methods.init();
    };

    $.fn.menuCart = function () {
        return this.each(function () {
            if ($(this).data('menuCart') !== undefined) {
                $(this).removeData("menuCart")
            }
            new $.menuCart(this);
        });
    };
});
(function ($) {
    // Make sure you run this code under Elementor.
    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction("frontend/element_ready/the7-woocommerce-menu-cart.default", function ($widget, $) {
            $(document).ready(function () {
                $widget.menuCart();
            })
        });
    });
})(jQuery);
