/*
Copyright (c) 2006, Webframeworks LLC. All rights reserved.

Code licensed under the BSD License:

http://dev.webframeworks.com/dist/JSOC-license.txt

version: 0.11.0
*/

/*/  
 *  JSOC - An object Cache framework for JavaScript
 *  version 0.11.0 [beta]
/*/


JSOC = function(){
    var Cache = {};
    return {
        "get":function(n){
            var obj = {}, val = Cache[n];
            obj[n] = val;
            if(val) return obj;
        },
        "getMulti":function(l){
            var a = [];
            for (var k in l) a.push(this.get(l[k]));
            return a;
        },
        "getType":function(t){
            var a = [];
            for (var o in Cache) if(typeof(Cache[o])==t.toLowerCase()){a.push(this.get(o))}
            return a;
        },
        "set":function(n,v){
            if(Cache[n]) delete(Cache[n]);
            Cache[n]=v;
            if (arguments[2]){
                var ttl = arguments[2].ttl || null;
                if(ttl) var self = this, to = setTimeout(function(){self.remove(n)}, ttl);
            }
            return (Cache[n])?1:0;
        },
        "add":function(n,v){
            if(!Cache[n]){
                Cache[n]=v;
                if (arguments[2]){
                    var ttl = arguments[2].ttl || null;
                    if(ttl) var self = this, to = setTimeout(function(){self.remove(n)}, ttl);
                }
                return (Cache[n])?1:0;
            }
        },
        "replace":function(n,v){
            if(Cache[n]){
                delete(Cache[n]);
                Cache[n]=v;
                if (arguments[2]){
                    var ttl = arguments[2].ttl || null;
                    if(ttl) var self = this, to = setTimeout(function(){self.remove(n)}, ttl);
                }
                return (Cache[n])?1:0;
            }
        },
        "remove":function(n){
            delete(Cache[n]);
            return (!Cache[n])?1:0;
        },
        "flush_all":function(){
            for(k in Cache) delete(Cache[k]);
            return 1;
        }
    }
}

