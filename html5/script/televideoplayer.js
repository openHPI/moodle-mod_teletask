require.config({
    baseUrl: MEDIA_URL + "html5/script",
    paths : {
        jquery : "vendor/jquery-1.8.3.min"
    },
    shim: {
      'jquery-deparam': {
        deps: ['jquery'],
        exports: 'jquery-deparam'
      }
    }
});

var html5Player;

require(
    [
        "jquery",
        "jquery-deparam",
        "video_player"
    ], function( $ , deparam, VideoPlayer ) {
        // $$ = $.noConflict();

        $(".televideoplayer").each(function() {
            html5Player = new VideoPlayer( $(this) );
        });
    }
)
