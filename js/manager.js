function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

$(document).ready(function(){
    var page = getParameterByName('page');
    
    if(!page){
        page = 'index';
    }

    $.ajax({
        url: 'pages/' + page + '.html',
        success: function(data){
            $("#container").empty().html(data);
        },
        error: function(){
            $.ajax({
                url: 'pages/index.html',
                success: function(data){
                    $("#container").empty().html(data);
                }
            });
        }
    });

});
