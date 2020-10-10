export default class PictureMap extends HTMLElement {

    static get observedAttributes() { return ['long', 'lat']; }

    constructor() {
        super()
        this.long = parseFloat(this.getAttribute('long'))
        this.lat = parseFloat(this.getAttribute('lat'))

        this.div = $("<div>", {
            id: 'map'
        })
    }

    connectedCallback() {
        $(this).append(this.div)
        if(this.long && this.lat) {
            this.generateMap(this.long, this.lat)
        }
    }

    disconnectedCallback() {

    }

    attributeChangedCallback(name, oldValue, newValue) {
        $("picture-map").html("")

        let $lat;
        let $long;

        if(name === "lat") {
            $lat = parseFloat(newValue)
            $long = this.long;
        } else {
            $long = parseFloat(newValue)
            $lat = this.lat;
        }

        this.generateMap($long, $lat)
    }

    generateMap(long, lat) {
        this.map = new ol.Map({
            target: 'map',
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM()
                })
            ],
            view: new ol.View({
                center: ol.proj.fromLonLat([long, lat]),
                zoom: 10
            })
        });

        this.newlayer = new ol.layer.Vector({
            source: new ol.source.Vector({
                features: [
                    new ol.Feature({
                        geometry: new ol.geom.Point(ol.proj.fromLonLat([long, lat]))
                    })
                ]
            })
        });

        this.map.addLayer(this.newlayer)
    }
}
