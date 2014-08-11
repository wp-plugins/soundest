var product_parameters = JSON.parse(product_params);
var soundestInShop = {};
soundestInShop.productID = product_parameters.productID;
soundestInShop.baseURL = 'https://soundest.net/';
soundestInShop.version = Math.floor(new Date().getTime() / 3600000);
(function () {
    var se = document.createElement('script');
    se.type = 'text/javascript';
    se.async = true;
    se.src = soundestInShop.baseURL + 'inShop/launcher.js?v=' + soundestInShop.version;
    var ss = document.getElementsByTagName('script')[0];
    ss.parentNode.insertBefore(se, ss);
})();
