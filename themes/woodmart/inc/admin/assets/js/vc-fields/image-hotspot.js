(function ($) {

    $('#vc_ui-panel-edit-element').on('vcPanel.shown', function () {
        var shortcode = $(this).data('vc-shortcode');

        if (shortcode != 'woodmart_image_hotspot' && shortcode != 'woodmart_hotspot') return;

        var params = vc.shortcodes.findWhere({ id: vc.active_panel.model.attributes.parent_id }).attributes.params;
        var _background_id = vc.shortcodes.findWhere({ id: vc.active_panel.model.attributes.parent_id }).attributes.params.img;

        if ( 'undefined' !== typeof params.source_type && 'video' === params.source_type ) {
            _background_id = vc.shortcodes.findWhere({ id: vc.active_panel.model.attributes.parent_id }).attributes.params.video;
        }

        $('.xts-image-hotspot-preview').each(function () {
            var $preview = $(this);
            var $overlay = $preview.find('.xts-image-hotspot-overlay');
            var $positionField = $preview.siblings('.xts-image-hotspot-position');
            var isDragging = false;
            var timer;

            $preview.addClass('xts-loading');

            $.ajax({
                url: woodmartConfig.ajaxUrl,
                dataType: 'json',
                data: {
                    image_id: _background_id,
					action: 'woodmart_get_hotspot_image',
					security: woodmartConfig.get_hotspot_image_nonce,
                },
                success: function (response) {
                    $preview.removeClass('xts-loading');

                    if (response.status == 'success') {
                        $preview.find('.xts-image-hotspot-image').append(response.html).fadeIn(500);
                        $preview.css('min-width', $preview.find('.woodmart-hotspot-img').outerWidth());
                    } else if (response.status == 'warning') {
                        $preview.remove();
                        $positionField.after(response.html);
                    }
                },
                error: function (response) {
                    console.log('ajax error');
                },
            });

            $overlay.on('mousedown', function (event) {
                isDragging = true;
                event.preventDefault();
            }).on('mouseup', function () {
                isDragging = false;
            }).on('mouseleave', function () {
                timer = setTimeout(function () {
                    $overlay.trigger('mouseup');
                }, 500);
            }).on('mouseenter', function () {
                clearTimeout(timer);
            }).on('mousemove', function (event) {
                if (!isDragging) return;
                setPosition(event);
            }).on('click', function (event) {
                setPosition(event);
            }).on('dragstart', function (event) {
                event.preventDefault();
            });

            function setPosition(event) {
                var position = {
                    x: (event.offsetX / $preview.width() * 100).toFixed(3),
                    y: (event.offsetY / $preview.height() * 100).toFixed(3)
                };

                $preview.find('.xts-image-hotspot').css({
                    left: position.x + '%',
                    top: position.y + '%'
                });

                $positionField.attr('value', position.x + '||' + position.y).trigger('change');
            }
        });

    });

})(jQuery);
