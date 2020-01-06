// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and the Jonéame Development Team (admin@joneame.net)
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// A copy of the AFFERO GENERAL PUBLIC LICENSE is included in the file "COPYING".

var baseicon;
var geo_marker_mgr = null;

function onLoad(lat, lng, zoom, icon) {
    baseicon = new GIcon();
    baseicon.iconSize = new GSize(20, 25);
    baseicon.iconAnchor = new GPoint(10, 25);
    baseicon.infoWindowAnchor = new GPoint(10, 10);
    if (geo_basic_load(lat||18, lng||15, zoom||2)) {
        geo_map.addControl(new GLargeMapControl());
        geo_marker_mgr = new GMarkerManager(geo_map);
        geo_load_xml('post', '', 0, base_url+"img/geo/nueva-notita.png");
        GEvent.addListener(geo_map, 'click', function (overlay, point) {
            if (overlay && overlay.myId > 0) {
                GDownloadUrl(base_url+"geo/"+overlay.myType+".php?id="+overlay.myId, function(data, responseCode) {
                overlay.openInfoWindowHtml(data);
                });
            }
        });
    }
}
