/*
 * SimpleModal 1.1.1 - jQuery Plugin
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://plugins.jquery.com/project/SimpleModal
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2007 Eric Martin - http://ericmmartin.com
 *
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Revision: $Id: jquery.simplemodal.js 93 2008-01-15 16:14:20Z emartin24 $
 *
 */

eval(function ($) {
    $.modal = function (a, b) {
        return $.modal.impl.init(a, b)
    };
    $.modal.close = function () {
        $.modal.impl.close(true)
    };
    $.fn.modal = function (a) {
        return $.modal.impl.init(this, a)
    };
    $.modal.defaults = {
        overlay: 50,
        overlayId: 'modalOverlay',
        overlayCss: {}, containerId: 'modalContainer',
        containerCss: {}, close: true,
        closeTitle: 'Close',
        closeClass: 'modalClose',
        persist: false,
        onOpen: null,
        onShow: null,
        onClose: null
    };
    $.modal.impl = {
        opts: null,
        dialog: {}, init: function (a, b) {
            if (this.dialog.data) {
                return false
            }
            this.opts = $.extend({}, $.modal.defaults, b);
            if (typeof a == 'object') {
                a = a instanceof jQuery ? a : $(a);
                if (a.parent().parent().size() > 0) {
                    this.dialog.parentNode = a.parent();
                    if (!this.opts.persist) {
                        this.dialog.original = a.clone(true)
                    }
                }
            } else if (typeof a == 'string' || typeof a == 'number') {
                a = $('<div>').html(a)
            } else {
                if (console) {
                    console.log('SimpleModal Error: Unsupported data type: ' + typeof a)
                }
                return false
            }
            this.dialog.data = a.addClass('modalData');
            a = null;
            this.create();
            this.open();
            if ($.isFunction(this.opts.onShow)) {
                this.opts.onShow.apply(this, [this.dialog])
            }
            return this
        }, create: function () {
            this.dialog.overlay = $('<div>').attr('id', this.opts.overlayId).addClass('modalOverlay').css($.extend(this.opts.overlayCss, {
                opacity: this.opts.overlay / 100,
                height: '100%',
                width: '100%',
                position: 'fixed',
                left: 0,
                top: 0,
                zIndex: 3000
            })).hide().appendTo('body');
            this.dialog.container = $('<div>').attr('id', this.opts.containerId).addClass('modalContainer').css($.extend(this.opts.containerCss, {
                position: 'fixed',
                zIndex: 3100
            })).append(this.opts.close ? '<a class="modalCloseImg ' + this.opts.closeClass + '" title="' + this.opts.closeTitle + '"></a>' : '').hide().appendTo('body');
            if ($.browser.msie && ($.browser.version < 7)) {
                this.fixIE()
            }
            this.dialog.container.append(this.dialog.data.hide())
        }, bindEvents: function () {
            var a = this;
            $('.' + this.opts.closeClass).click(function (e) {
                e.preventDefault();
                a.close()
            })
            $('#modalOverlay').click(function (e) {
                e.preventDefault();
                a.close()
            });
        }, unbindEvents: function () {
            $('.' + this.opts.closeClass).unbind('click')
        }, fixIE: function () {
            var a = $(document.body).height() + 'px';
            var b = $(document.body).width() + 'px';
            this.dialog.overlay.css({
                position: 'absolute',
                height: a,
                width: b
            });
            this.dialog.container.css({
                position: 'absolute'
            });
            this.dialog.iframe = $('<iframe src="javascript:false;">').css($.extend(this.opts.iframeCss, {
                opacity: 0,
                position: 'absolute',
                height: a,
                width: b,
                zIndex: 1000,
                width: '100%',
                top: 0,
                left: 0
            })).hide().appendTo('body')
        }, open: function () {
            if (this.dialog.iframe) {
                this.dialog.iframe.show()
            }
            if ($.isFunction(this.opts.onOpen)) {
                this.opts.onOpen.apply(this, [this.dialog])
            } else {
                this.dialog.overlay.show();
                this.dialog.container.show();
                this.dialog.data.show()
            }
            this.bindEvents()
        }, close: function (a) {
            if (!this.dialog.data) {
                return false
            }
            if ($.isFunction(this.opts.onClose) && !a) {
                this.opts.onClose.apply(this, [this.dialog])
            } else {
                if (this.dialog.parentNode) {
                    if (this.opts.persist) {
                        this.dialog.data.hide().appendTo(this.dialog.parentNode)
                    } else {
                        this.dialog.data.remove();
                        this.dialog.original.appendTo(this.dialog.parentNode)
                    }
                } else {
                    this.dialog.data.remove()
                }
                this.dialog.container.remove();
                this.dialog.overlay.remove();
                if (this.dialog.iframe) {
                    this.dialog.iframe.remove()
                }
                this.dialog = {}
            }
            this.unbindEvents()
        }
    }
})(jQuery);