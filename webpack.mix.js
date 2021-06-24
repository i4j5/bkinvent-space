const mix = require('laravel-mix');
require('dotenv').config();
let webpack = require('webpack');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

let dotenvplugin = new webpack.DefinePlugin({
    'process.env': {
        APP_URL: JSON.stringify(process.env.APP_URL),
        CALL_TRACKER_CSS_CLASS: JSON.stringify(process.env.CALL_TRACKER_CSS_CLASS),
        CALL_TRACKER_MASK: JSON.stringify(process.env.CALL_TRACKER_MASK),
        CALL_TRACKER_TRACK_TIME: JSON.stringify(process.env.CALL_TRACKER_TRACK_TIME),
        CALL_TRACKER_DEFAULT_NUMBER: JSON.stringify(process.env.CALL_TRACKER_DEFAULT_NUMBER),
    }
});

mix.webpackConfig({
    plugins: [
        dotenvplugin,
    ]
});

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer'),
    ]);

mix.js('resources/js/visit-tracker.js', 'public/js')

if (mix.inProduction()) {
    mix.version();
}

