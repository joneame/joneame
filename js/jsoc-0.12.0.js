JSOC=function(){var b={};return{get:function(a){var c={},d=b[a];if(c[a]=d)return c},getMulti:function(a){var c=[],b;for(b in a)c.push(this.get(a[b]));return c},getType:function(a){var c=[],d;for(d in b)typeof b[d]==a.toLowerCase()&&c.push(this.get(d));return c},set:function(a,c,d){b[a]&&delete b[a];b[a]=c;if(d&&(c=d.ttl||null)){var e=this;setTimeout(function(){e.remove(a)},c)}return b[a]?1:0},add:function(a,c,d){if(!b[a]){b[a]=c;if(d&&(c=d.ttl||null)){var e=this;setTimeout(function(){e.remove(a)},
c)}return b[a]?1:0}},replace:function(a,c,d){if(b[a]){delete b[a];b[a]=c;if(d&&(c=d.ttl||null)){var e=this;setTimeout(function(){e.remove(a)},c)}return b[a]?1:0}},remove:function(a){delete b[a];return!b[a]?1:0},flush_all:function(){for(var a in b)delete b[a];return 1}}};