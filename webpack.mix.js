const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.scripts([
    'resources/js/jquery-3.4.1.js',
    'resources/bootstrap/js/bootstrap.js',
    'resources/js/app.js'
], 'public/js/scripts.js')
    .sourceMaps(false, 'source-map')
    .sass('resources/sass/app.scss', 'public/css/_app.css')
    .styles([
        'resources/bootstrap/css/bootstrap.css',
        'public/css/_app.css'
    ], 'public/css/style.css')
    .browserSync('rest.wsr')
    .disableNotifications();
