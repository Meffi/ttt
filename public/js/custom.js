var Game = new Object();
Game.timeout = 5000;
Game.square_clicker_enabled = false;
var gameId;



function init() {
    // add click handler to new game box
  //  $("#newGame").click(new_game_clicker);

    // get current board

    var arg = new Object();
    arg.get_board = gameId;

    $.ajax({url: '/api/game/id/'+gameId,
        type: 'GET',
        dataType: 'json',
        timeout: Game.timeout,
        error: bomb,
        success: function(a) { paint_board(a);}
    });
}

function square_clicker(e) {
    if (Game.square_clicker_enabled) {
        Game.square_clicker_enabled = false;

        $.ajax({url: '/api/game/id/'+gameId+'/pos/'+this.id.charAt(1),
            type: 'GET',
            dataType: 'json',
            timeout: Game.timeout,
            error: bomb,
            success: function(a) { paint_board(a);}
        });
    } else {

        console.log("Thinking!")
    }
}

function new_game_clicker() {
    if (confirm("Really start a new game?")) {
        var arg = new Object();
        arg["new"] = 1;
        $.ajax({url: '/api/game/',
            type: 'POST',
            data: arg,
            dataType: 'json',
            timeout: Game.timeout,
            error: bomb,
            success: function(a) {
                window.location.href = "/game/play/id/"+a.id;
           // paint_board(a);
            }
        })

    }
}

function paint_board (a) {

    if (a.board == null) {
        $("#status").text("No game in progress");
        Game.square_clicker_enabled = false;

    } else {
        Game.square_clicker_enabled = true;

        // Populate the board

        for (var i=0; i < a.board.length; i++) {
            $("#s" + i).empty();
            if (a.board.charAt(i) == '0') {
                var events = $("#s"+i).data("events");
                if (events == null) {

                    $("#s"+i).click(square_clicker);
                }
            } else {
                $("#s"+i).unbind('click',square_clicker);
                var e = $("<span></span>").text(a.board.charAt(i).toUpperCase()).addClass("p");
                $("#s"+i).append(e);
            }
        }

        if (a.msg == null) {
            a.msg = " ";
        }
        if (a.msg == "Computer has won" || a.msg == "You won") {

            Game.square_clicker_enabled = false;
        }
        $("#status").text(a.msg + " ");
    }
}

function bomb () {
    //alert("Oh Lord, that didn't work.");
    console.log("Oh Lord, that didn't work.")
}