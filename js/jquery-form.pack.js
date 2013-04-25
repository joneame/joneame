(function(b){b.fn.ajaxSubmit=function(a){function f(){function c(){if(!s++){m.detachEvent?m.detachEvent("onload",c):m.removeEventListener("load",c,!1);var a=!0;try{if(r)throw"timeout";var e,g;g=m.contentWindow?m.contentWindow.document:m.contentDocument?m.contentDocument:m.document;l.responseText=g.body?g.body.innerHTML:null;l.responseXML=g.XMLDocument?g.XMLDocument:g;l.getResponseHeader=function(a){return{"content-type":d.dataType}[a]};if("json"==d.dataType||"script"==d.dataType){var h=g.getElementsByTagName("textarea")[0];
l.responseText=h?h.value:l.responseText}else"xml"==d.dataType&&(!l.responseXML&&null!=l.responseText)&&(l.responseXML=f(l.responseText));e=b.httpData(l,d.dataType)}catch(t){a=!1,b.handleError(d,l,"error",t)}a&&(d.success(e,"success"),n&&b.event.trigger("ajaxSuccess",[l,d]));n&&b.event.trigger("ajaxComplete",[l,d]);n&&!--b.active&&b.event.trigger("ajaxStop");d.complete&&d.complete(l,a?"success":"error");setTimeout(function(){k.remove();l.responseXML=null},100)}}function f(a,b){window.ActiveXObject?
(b=new ActiveXObject("Microsoft.XMLDOM"),b.async="false",b.loadXML(a)):b=(new DOMParser).parseFromString(a,"text/xml");return b&&b.documentElement&&"parsererror"!=b.documentElement.tagName?b:null}var e=h[0],d=b.extend({},b.ajaxSettings,a),g="jqFormIO"+(new Date).getTime(),k=b('<iframe id="'+g+'" name="'+g+'" />'),m=k[0],u=b.browser.opera&&9>window.opera.version();if(b.browser.msie||u)m.src='javascript:false;document.write("");';k.css({position:"absolute",top:"-1000px",left:"-1000px"});var l={responseText:null,
responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){}},n=d.global;n&&!b.active++&&b.event.trigger("ajaxStart");n&&b.event.trigger("ajaxSend",[l,d]);var s=0,r=0;setTimeout(function(){var f=h.attr("target"),l=h.attr("action");h.attr({target:g,encoding:"multipart/form-data",enctype:"multipart/form-data",method:"POST",action:d.url});d.timeout&&setTimeout(function(){r=!0;c()},d.timeout);var n=[];try{if(a.extraData)for(var p in a.extraData)n.push(b('<input type="hidden" name="'+
p+'" value="'+a.extraData[p]+'" />').appendTo(e)[0]);k.appendTo("body");m.attachEvent?m.attachEvent("onload",c):m.addEventListener("load",c,!1);e.submit()}finally{h.attr("action",l),f?h.attr("target",f):h.removeAttr("target"),b(n).remove()}},10)}"function"==typeof a&&(a={success:a});a=b.extend({url:this.attr("action")||window.location.toString(),type:this.attr("method")||"GET"},a||{});var c={};this.trigger("form-pre-serialize",[this,a,c]);if(c.veto)return this;var e=this.formToArray(a.semantic);if(a.data){a.extraData=
a.data;for(var d in a.data)e.push({name:d,value:a.data[d]})}if(a.beforeSubmit&&!1===a.beforeSubmit(e,this,a))return this;this.trigger("form-submit-validate",[e,this,a,c]);if(c.veto)return this;c=b.param(e);"GET"==a.type.toUpperCase()?(a.url+=(0<=a.url.indexOf("?")?"&":"?")+c,a.data=null):a.data=c;var h=this,g=[];a.resetForm&&g.push(function(){h.resetForm()});a.clearForm&&g.push(function(){h.clearForm()});if(!a.dataType&&a.target){var k=a.success||function(){};g.push(function(c){b(a.target).html(c).each(k,
arguments)})}else a.success&&g.push(a.success);a.success=function(a,b){for(var c=0,d=g.length;c<d;c++)g[c](a,b,h)};c=b("input:file",this).fieldValue();e=!1;for(d=0;d<c.length;d++)c[d]&&(e=!0);a.iframe||e?b.browser.safari&&a.closeKeepAlive?b.get(a.closeKeepAlive,f):f():b.ajax(a);this.trigger("form-submit-notify",[this,a]);return this};b.fn.ajaxForm=function(a){return this.ajaxFormUnbind().bind("submit.form-plugin",function(){b(this).ajaxSubmit(a);return!1}).each(function(){b(":submit,input:image",
this).bind("click.form-plugin",function(a){var c=this.form;c.clk=this;if("image"==this.type)if(void 0!=a.offsetX)c.clk_x=a.offsetX,c.clk_y=a.offsetY;else if("function"==typeof b.fn.offset){var e=b(this).offset();c.clk_x=a.pageX-e.left;c.clk_y=a.pageY-e.top}else c.clk_x=a.pageX-this.offsetLeft,c.clk_y=a.pageY-this.offsetTop;setTimeout(function(){c.clk=c.clk_x=c.clk_y=null},10)})})};b.fn.ajaxFormUnbind=function(){this.unbind("submit.form-plugin");return this.each(function(){b(":submit,input:image",
this).unbind("click.form-plugin")})};b.fn.formToArray=function(a){var f=[];if(0==this.length)return f;var c=this[0],e=a?c.getElementsByTagName("*"):c.elements;if(!e)return f;for(var d=0,h=e.length;d<h;d++){var g=e[d],k=g.name;if(k)if(a&&c.clk&&"image"==g.type)!g.disabled&&c.clk==g&&f.push({name:k+".x",value:c.clk_x},{name:k+".y",value:c.clk_y});else if((g=b.fieldValue(g,!0))&&g.constructor==Array)for(var q=0,p=g.length;q<p;q++)f.push({name:k,value:g[q]});else null!==g&&"undefined"!=typeof g&&f.push({name:k,
value:g})}if(!a&&c.clk){a=c.getElementsByTagName("input");d=0;for(h=a.length;d<h;d++)e=a[d],(k=e.name)&&(!e.disabled&&"image"==e.type&&c.clk==e)&&f.push({name:k+".x",value:c.clk_x},{name:k+".y",value:c.clk_y})}return f};b.fn.formSerialize=function(a){return b.param(this.formToArray(a))};b.fn.fieldSerialize=function(a){var f=[];this.each(function(){var c=this.name;if(c){var e=b.fieldValue(this,a);if(e&&e.constructor==Array)for(var d=0,h=e.length;d<h;d++)f.push({name:c,value:e[d]});else null!==e&&"undefined"!=
typeof e&&f.push({name:this.name,value:e})}});return b.param(f)};b.fn.fieldValue=function(a){for(var f=[],c=0,e=this.length;c<e;c++){var d=b.fieldValue(this[c],a);null===d||("undefined"==typeof d||d.constructor==Array&&!d.length)||(d.constructor==Array?b.merge(f,d):f.push(d))}return f};b.fieldValue=function(a,f){var c=a.name,e=a.type,d=a.tagName.toLowerCase();"undefined"==typeof f&&(f=!0);if(f&&(!c||a.disabled||"reset"==e||"button"==e||("checkbox"==e||"radio"==e)&&!a.checked||("submit"==e||"image"==
e)&&a.form&&a.form.clk!=a||"select"==d&&-1==a.selectedIndex))return null;if("select"==d){var h=a.selectedIndex;if(0>h)return null;for(var c=[],d=a.options,g=(e="select-one"==e)?h+1:d.length,h=e?h:0;h<g;h++){var k=d[h];if(k.selected){k=b.browser.msie&&!k.attributes.value.specified?k.text:k.value;if(e)return k;c.push(k)}}return c}return a.value};b.fn.clearForm=function(){return this.each(function(){b("input,select,textarea",this).clearFields()})};b.fn.clearFields=b.fn.clearInputs=function(){return this.each(function(){var a=
this.type,b=this.tagName.toLowerCase();"text"==a||"password"==a||"textarea"==b?this.value="":"checkbox"==a||"radio"==a?this.checked=!1:"select"==b&&(this.selectedIndex=-1)})};b.fn.resetForm=function(){return this.each(function(){("function"==typeof this.reset||"object"==typeof this.reset&&!this.reset.nodeType)&&this.reset()})};b.fn.enable=function(a){void 0==a&&(a=!0);return this.each(function(){this.disabled=!a})};b.fn.select=function(a){void 0==a&&(a=!0);return this.each(function(){var f=this.type;
"checkbox"==f||"radio"==f?this.checked=a:"option"==this.tagName.toLowerCase()&&(f=b(this).parent("select"),a&&(f[0]&&"select-one"==f[0].type)&&f.find("option").select(!1),this.selected=a)})}})(jQuery);