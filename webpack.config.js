var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/contaorateit')
    .setManifestKeyPrefix('bundles/contaorateit')
    .addEntry('contao-rate-it-bundle', './assets/js/main.js')
    .addEntry('contao-rate-it-bundle-backend', './assets/js/backend.entry.js')
    .disableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(false)
    .configureBabel(function (babelConfig) {
    }, {
        // include the bundle sources in babel processing
        includeNodeModules: ['contao-rate-it-bundle']
    })
;

module.exports = Encore.getWebpackConfig();