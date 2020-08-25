var gulp = require('gulp');
var concat = require('gulp-concat');
var minify = require('gulp-minify');
var cleanCss = require('gulp-clean-css');
var rev = require('gulp-rev');
var del = require('del');

var jsFiles = [
    'assets/js/modules/axios.js',
    'assets/js/modules/sweet-alert.min.js',
    'assets/js/services/request.js',
    'assets/js/lcpolls-global-functions.js',
    'assets/js/lcpolls.js'
];

var cssFiles = [
    'assets/css/modules/sweet-alert.min.css',
    'assets/css/lcpolls.css',
    'assets/css/responsive.css'
];

gulp.task('clean-js', function () {
    return del([
        'build/public/js/*.js'
    ]);
});

gulp.task('clean-css', function () {
    return del([
        'build/public/css/*.css'
    ]);
});

gulp.task('clean-manifest', function () {
    return del([
        'build/public/rev-manifest.json'
    ]);
});

gulp.task('pack-js', function () {
    return gulp.src(jsFiles)
        .pipe(concat('bundle.min.js'))
        .pipe(minify({
            ext:{
                min:'.js'
            },
            noSource: true
        }))
        .pipe(rev())
        .pipe(gulp.dest('build/public/js'))
        .pipe(rev.manifest('build/public/rev-manifest.json', {
            base: 'build/public',
            merge: true
        }))
        .pipe(gulp.dest('build/public'));
});

gulp.task('pack-css', function () {
    return gulp.src(cssFiles)
        .pipe(concat('lcpolls-main.min.css'))
        .pipe(cleanCss())
        .pipe(rev())
        .pipe(gulp.dest('build/public/css'))
        .pipe(rev.manifest('build/public/rev-manifest.json', {
            base: 'build/public',
            merge: true
        }))
        .pipe(gulp.dest('build/public'));
});

gulp.task('pack-dev-js', function () {
    return gulp.src(jsFiles)
        .pipe(concat('bundle.js'))
        .pipe(gulp.dest('build/dev/js'));
});

gulp.task('pack-dev-css', function () {
    return gulp.src(cssFiles)
        .pipe(concat('lcpolls-main.css'))
        .pipe(gulp.dest('build/dev/css'));
});

gulp.task('default', gulp.series('pack-dev-js', 'pack-dev-css', 'clean-manifest', 'clean-css', 'clean-js', 'pack-js', 'pack-css'));

gulp.task('watch', function() {
    gulp.watch('assets/js/**/*.js', gulp.series('pack-dev-js'));
    gulp.watch('assets/css/**/*.css', gulp.series('pack-dev-css'));
});
