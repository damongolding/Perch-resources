$(function() {
    $('.iris').each(function() {
        var color = $(this).val();
        $(this).siblings('.colourPreview').css('background-color', color);
    });
    $('.iris').iris({
        width: 292,
        palettes: ['#36685a', '#255346', '#5a3460', '#62897c', '#b4c7c1', '#e7edeb', '#c2d1cd', '#f9fafa', '#e5ebea', '#eef2f1', '#01a52f', '#02992c'],
        change: function(event, ui) {
            // event = standard jQuery event, produced by whichever control was changed.
            // ui = standard jQuery UI object, with a color member containing a Color.js object

            // change the headline color
            $(this).siblings('.colourPreview').css('background-color', ui.color.toString());
        }
    });
});