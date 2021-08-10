<?php
/**
 * Regular controller
 **/
class Api_GameController extends REST_Controller
{
    protected $ip;


    public function init()
    {
        $this->session = new Zend_Session_Namespace('game');
       // $this->setConfig("responseTypes","json");
    }
    public function indexAction()
    {

       /* $this->ip = $_SERVER["REMOTE_ADDR"];
        $board  = $this->newGame();
        $this->view->message = $board ;
        $this->_response->ok();*/


    }
    public function headAction()
    {

        $this->view->message = 'headAction has been called';
        $this->_response->ok();
    }
    public function getAction()
    {
        $pos = $this->_getParam('pos', '');
        //echo ">>".is_integer($pos);
        //$this->view->test = is_integer($pos);
        if ($pos!= '' && pos >= 0 && pos < 9 ) {
            $this->view->pos = $pos;
            if($this->moveClick('x',$pos) == false) {

                $this->view->msg = "Illegal move";
                $this->view->board = $this->session->board;
                return;
            }

            if ($w = $this->getWinner()) {
                // Human appears to have won
                $this->view->msg = ($w == 'x' ? "You" : "Computer has") . " won";
                $this->view->board = $this->session->board;
                return;
            }

            # Computer move
            $this->moveClick();
            if ($w = $this->getWinner()) {
                // Computer appears to have won
                $this->view->msg = ($w == 'x' ? "You" : "Computer has") . " won";
                $this->view->board = $this->session->board;
                return;
            }

            $this->view->msg = $this->wittyReparte();
            $this->view->board = $this->session->board;

        }

      //  echo ">>>".$pos;
        $gameId = $this->_getParam('id', '');
        if ($gameId) {
            $this->gameId = $gameId;
            $this->view->gameId = $pos;
        }


        $this->view->board = $this->session->board;
        $this->view->message =  $gameId;//sprintf('Resource #%s', $id);


        $this->_response->ok();
    }
    public function postAction()
    {

       // $this->view->params = $this->_request->getParams();

        $new  = $this->_getParam('new', 0);
        if ($new) {
            $ret = $this->newGame();
            if ($ret) {
                $this->view->message = 'Resource Created';
                $this->view->id = $this->session->gameId;
                $this->_response->created();
            } else {
                $this->view->message = 'Unknown request';
                $this->getResponse()->setHttpResponseCode(404);
            }

        }
    }
    public function newGame()
    {
        # New board, new SID tied to IP
       // $ip = $_SERVER["REMOTE_ADDR"];
        $this->session->gameId =  rand(1,9999);
        $board = "000000000";
        $this->session->board =  $board;

       // $_SESSION[$this->gameId]['board'] = $board;

        return $this->session->gameId; //$_SESSION[$this->game]['board'];

    }


    public function moveClick($player='o', $pos=false) {
        # Make move, if legal
        # If player is two, move for computer
        # Return new board
        // Begin move() for '$player'
        $board = $this->session->board;
        if ($w = $this->getWinner($board)) {
            // Move attempted, but there's already a winner
            return false; # already a winner
        }

        if ($player == 'x') {
            # Is this a legal move
            if ($board[$pos] == "0") {
                $this->session->board = $this->applyMove($board, $player, $pos);
                return true;
            } else {
                // Player attempted an illegal move
                return false;
            }

        } else {
            $outcomes = $this->searchWiner($board, $player);
            if (count($outcomes) == 0) {
                // Computer had no more moves
                return false;
            }
            arsort($outcomes, SORT_NUMERIC);
            // Computer outcomes:\n".print_r($outcomes,1));
            foreach ($outcomes as $pos => $val) {
                if (!isset($pos)) {
                    // Computer's move isn't set, but the value is '$val'
                    continue;
                }
                $this->session->board = $this->applyMove($board, $player, $pos);
                return true;
            }
            // Computer has outcomes, but no legal moves?!
            return false;
        }
    }

    public function getWinner($board="") {
        # Return x if player wins
        #        o if computer wins
        #        false if no winner
        # // Begin getWinner()
        if (strlen($board) == 0) {
            $board = $this->session->board;
        }

        if (strlen($board) != 9) {
            // No board to interrogate for winner
            return false;
        }

        $winners = array("111000000",
            "000111000",
            "000000111",
            "100100100",
            "010010010",
            "001001001",
            "100010001",
            "001010100",
        );

        foreach (array("x","o") as $curr) {
            $won = false;
            foreach ($winners as $w) {
                for ($i=0; $i < strlen($board); $i++) {
                    if ($w[$i] == 1) {
                        # This position is required for winning
                        if (($won = ($board[$i] == $curr)) == false) {
                            continue 2; // Bounce out to the next pattern
                        }
                    }
                }

                if ($won) {
                    # // Looks like the " . ($curr == "x" ? "Human" : "Computer") . " won with this pattern '$w'
                    return $curr;
                }
            }
        }
        return false;
    }

    public function searchWiner ($board, $player, $level=0) {
        $outcomes = array();

        # Unfettered, this is a O(n!) runtime!
        if ($level > 4) {
            return $outcomes;
        }

        $moves = $this->getAvailableMoves($board);
        foreach ($moves as $m) {
            $outcomes[$m] = 0;
            $newboard = $this->applyMove($board, $player, $m);
            $w = $this->getWinner($newboard);
            if ($w) {
                if ($w != $player) {
                    // searchWiner: the other player won?!
                }

                # The only possible win is for the current player
                $outcomes[$m] = 100;
            } else {
                $future = $this->searchWiner($newboard, ($player == 'x' ? 'o' : 'x'), $level+1);
                foreach ($future as $k=>$v) {
                    # victories for the other player are losses for us
                    $outcomes[$m] -= $v/2;
                }
            }
        }
        return $outcomes;
    }

    public function getAvailableMoves ($board) {
        $moves = array();
        for ($i=0; $i < strlen($board); $i++) {
            if ($board[$i] == "0") {
                array_push($moves, $i);
            }
        }
        return $moves;
    }

    public function applyMove($board, $player, $move) {
        if (!isset($move)) {
            // Got an unset move!
            return false;
        }

        if ($board[$move] == "0") {
            $board[$move] = $player;
        } else {
            return false;
        }
        return $board;
    }

    public function wittyReparte() {
        $wit = array("Mmm.  How about here?",
            "You humans are dumb",
            "I wish I understood alpha-beta pruning better",
            "My other avatar is 70th level dwarf",
            "Scared of my advanced heuristics?",
            "Did I mention that I know the HAL9000?",
            "My new BogoMIPS are unstoppable",
            "Desperation is a stinky cologne",
        );

        return $wit[rand(0,count($wit)-1)];
    }

}
