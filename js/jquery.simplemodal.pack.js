eval(function(a){a.modal=function(d,c){return a.modal.impl.init(d,c)};a.modal.close=function(){a.modal.impl.close(true)};a.fn.modal=function(b){return a.modal.impl.init(this,b)};a.modal.defaults={overlay:50,overlayId:"modalOverlay",overlayCss:{},containerId:"modalContainer",containerCss:{},close:true,closeTitle:"Close",closeClass:"modalClose",persist:false,onOpen:null,onShow:null,onClose:null};a.modal.impl={opts:null,dialog:{},init:function(d,c){if(this.dialog.data){return false}this.opts=a.extend({},a.modal.defaults,c);if(typeof d=="object"){d=d instanceof jQuery?d:a(d);if(d.parent().parent().size()>0){this.dialog.parentNode=d.parent();if(!this.opts.persist){this.dialog.original=d.clone(true)}}}else{if(typeof d=="string"||typeof d=="number"){d=a("<div>").html(d)}else{if(console){console.log("SimpleModal Error: Unsupported data type: "+typeof d)}return false}}this.dialog.data=d.addClass("modalData");d=null;this.create();this.open();if(a.isFunction(this.opts.onShow)){this.opts.onShow.apply(this,[this.dialog])}return this},create:function(){this.dialog.overlay=a("<div>").attr("id",this.opts.overlayId).addClass("modalOverlay").css(a.extend(this.opts.overlayCss,{opacity:this.opts.overlay/100,height:"100%",width:"100%",position:"fixed",left:0,top:0,zIndex:3000})).hide().appendTo("body");this.dialog.container=a("<div>").attr("id",this.opts.containerId).addClass("modalContainer").css(a.extend(this.opts.containerCss,{position:"fixed",zIndex:3100})).append(this.opts.close?'<a class="modalCloseImg '+this.opts.closeClass+'" title="'+this.opts.closeTitle+'"></a>':"").hide().appendTo("body");if(a.browser.msie&&(a.browser.version<7)){this.fixIE()}this.dialog.container.append(this.dialog.data.hide())},bindEvents:function(){var b=this;a("."+this.opts.closeClass).click(function(c){c.preventDefault();b.close()});a("#modalOverlay").click(function(c){c.preventDefault();b.close()})},unbindEvents:function(){a("."+this.opts.closeClass).unbind("click")},fixIE:function(){var d=a(document.body).height()+"px";var c=a(document.body).width()+"px";this.dialog.overlay.css({position:"absolute",height:d,width:c});this.dialog.container.css({position:"absolute"});this.dialog.iframe=a('<iframe src="javascript:false;">').css(a.extend(this.opts.iframeCss,{opacity:0,position:"absolute",height:d,width:c,zIndex:1000,width:"100%",top:0,left:0})).hide().appendTo("body")},open:function(){if(this.dialog.iframe){this.dialog.iframe.show()}if(a.isFunction(this.opts.onOpen)){this.opts.onOpen.apply(this,[this.dialog])}else{this.dialog.overlay.show();this.dialog.container.show();this.dialog.data.show()}this.bindEvents()},close:function(b){if(!this.dialog.data){return false}if(a.isFunction(this.opts.onClose)&&!b){this.opts.onClose.apply(this,[this.dialog])}else{if(this.dialog.parentNode){if(this.opts.persist){this.dialog.data.hide().appendTo(this.dialog.parentNode)}else{this.dialog.data.remove();this.dialog.original.appendTo(this.dialog.parentNode)}}else{this.dialog.data.remove()}this.dialog.container.remove();this.dialog.overlay.remove();if(this.dialog.iframe){this.dialog.iframe.remove()}this.dialog={}}this.unbindEvents()}}})(jQuery);