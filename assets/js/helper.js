if (typeof Object.create !== 'function') {
  Object.create = function (o) {
    function F() {}
    F.prototype = o;
    return new F();
  };
}

function $tryCatch() {
    for (var i = 0, l = arguments.length; i < l; i++){
        try {
            return arguments[i]();
        } catch(e){}
    }
    return null;
};

var Browser = {

    Engine: {name: 'unknown', version: 0},

    Platform: {name: (window.orientation != undefined) ? 'ipod' : (navigator.platform.match(/mac|win|linux/i) || ['other'])[0].toLowerCase()},

    Features: {xpath: !!(document.evaluate), air: !!(window.runtime), query: !!(document.querySelector)},

    Plugins: {},

    Engines: {

        presto: function(){
            return (!window.opera) ? false : ((arguments.callee.caller) ? 960 : ((document.getElementsByClassName) ? 950 : 925));
        },

        trident: function(){
            return (!window.ActiveXObject) ? false : ((window.XMLHttpRequest) ? ((document.querySelectorAll) ? 6 : 5) : 4);
        },

        webkit: function(){
            return (navigator.taintEnabled) ? false : ((Browser.Features.xpath) ? ((Browser.Features.query) ? 525 : 420) : 419);
        },

        gecko: function(){
            return (!document.getBoxObjectFor && window.mozInnerScreenX == null) ? false : ((document.getElementsByClassName) ? 19 : 18);
        }

    }

};

Browser.Platform[Browser.Platform.name] = true;

Browser.detect = function(){

    for (var engine in this.Engines){
        var version = this.Engines[engine]();
        if (version){
            this.Engine = {name: engine, version: version};
            this.Engine[engine] = this.Engine[engine + version] = true;
            break;
        }
    }

    return {name: engine, version: version};

};

Browser.detect();

Browser.Request = function(){
    return $tryCatch(function(){
        return new XMLHttpRequest();
    }, function(){
        return new ActiveXObject('MSXML2.XMLHTTP');
    }, function(){
        return new ActiveXObject('Microsoft.XMLHTTP');
    });
};

Browser.Features.xhr = !!(Browser.Request());

Browser.Plugins.Flash = (function(){
    var version = ($tryCatch(function(){
        return navigator.plugins['Shockwave Flash'].description;
    }, function(){
        return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version');
    }) || '0 r0').match(/\d+/g);
    return {version: parseInt(version[0] || 0 + '.' + version[1], 10) || 0, build: parseInt(version[2], 10) || 0};
})();

function $exec(text){
    if (!text) return text;
    if (window.execScript){
        window.execScript(text);
    } else {
        var script = document.createElement('script');
        script.setAttribute('type', 'text/javascript');
        script[(Browser.Engine.webkit && Browser.Engine.version < 420) ? 'innerText' : 'text'] = text;
        document.head.appendChild(script);
        document.head.removeChild(script);
    }
    return text;
};

function getPosition(element) {
    var xPosition = 0;
    var yPosition = 0;
  
    while(element) {
        xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
        yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
        element = element.offsetParent;
    }
    return { x: xPosition, y: yPosition };
}