var baseicon,geo_marker_mgr=null;
function onLoad(b,c,d,e){baseicon=new GIcon;baseicon.iconSize=new GSize(20,25);baseicon.iconAnchor=new GPoint(10,25);baseicon.infoWindowAnchor=new GPoint(10,10);if(geo_basic_load(b||18,c||15,d||2))geo_map.addControl(new GLargeMapControl),geo_marker_mgr=new GMarkerManager(geo_map),geo_load_xml("post","",0,base_url+"img/geo/nueva-notita.png"),GEvent.addListener(geo_map,"click",function(a,b){a&&0<a.myId&&GDownloadUrl(base_url+"geo/"+a.myType+".php?id="+a.myId,function(b,c){a.openInfoWindowHtml(b)})})}
;
