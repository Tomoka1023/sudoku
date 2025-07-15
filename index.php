<?php
session_start();

// 盤面サイズ
define('SIZE', 9);

$message = '';

// 数独の盤面を作成する関数
function generateSudokuBoard() {
    $board = array_fill(0, SIZE, array_fill(0, SIZE, 0));
    fillBoard($board);
    return $board;
}

// 再帰的に盤面を埋める関数（バックトラッキング）
function fillBoard(&$board) {
    for ($row = 0; $row < SIZE; $row++) {
        for ($col = 0; $col < SIZE; $col++) {
            if ($board[$row][$col] == 0) {
                $numbers = range(1, 9);
                shuffle($numbers);
                foreach ($numbers as $num) {
                    if (isValid($board, $row, $col, $num)) {
                        $board[$row][$col] = $num;
                        if (fillBoard($board)) {
                            return true;
                        }
                        $board[$row][$col] = 0; // 戻す
                    }
                }
                return false;
            }
        }
    }
    return true;
}

// 値が配置可能かチェック
function isValid($board, $row, $col, $num) {
    // 行と列チェック
    for ($i = 0; $i < SIZE; $i++) {
        if ($board[$row][$i] == $num || $board[$i][$col] == $num) {
            return false;
        }
    }

    // 3x3ブロックチェック
    $startRow = $row - $row % 3;
    $startCol = $col - $col % 3;
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            if ($board[$startRow + $i][$startCol + $j] == $num) {
                return false;
            }
        }
    }

    return true;
}

// 表示用（テスト）
function printBoard($board, $isForm = false, $userInput = [], $wrongCells = []) {
    if ($isForm) echo "<form method = 'post'>";
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    for ($row = 0; $row < SIZE; $row++) {
        echo "<tr>";
        for ($col = 0; $col < SIZE; $col++) {
          $value = $board[$row][$col];
          $classes = [];

          if ($row % 3 == 0) $classes[] = 'top-border';
          if ($col % 3 == 0) $classes[] = 'left-border';
          if ($row == SIZE - 1) $classes[] = 'bottom-border';
          if ($col == SIZE - 1) $classes[] = 'right-border';


          // 3×3 ブロックの色分け
          $blockId = (int)($row / 3) * 3 + (int)($col / 3);
          $blockClass = $blockId % 2 == 0 ? 'block-a' : 'block-b';
          $classes[] = $blockClass;

          $isWrong = isset($wrongCells[$row][$col]) && $wrongCells[$row][$col];
          if ($isWrong) $classes[] = 'wrong';
          $classAttr = implode(' ', $classes);
          echo "<td class='$classAttr'>";

          if ($isForm && $value == 0) {
            $valueInput = $userInput[$row][$col] ?? '';
            $class = $isWrong ? 'wrong-input' : '';
            echo "<input type='number' class='$class' name='cell[$row][$col]' min='1' max='9' value='$valueInput'>";
          } else {
            echo $value != 0 ? "<strong>$value</strong>" : "";
          }
          echo "</td>";
        }
        echo "</tr>";
      }
      echo "</table>";
      if ($isForm) echo "<br><input type='submit' value='答えをチェック'></form>";
}

if (isset($_POST['new_game'])) {
  session_unset();
  header("Location: index.php");
  exit;
}

if (isset($_POST['reset_input'])) {
  $_SESSION['userInput'] = [];
  header("Location: index.php");
  exit;
}

if (isset($_POST['new_game_with_level'])) {
  session_unset();
  $level = (int)($_POST['level'] ?? 40);
  $generatedBoard = generateSudokuBoard();
  $puzzleBoard = makePuzzle($generatedBoard, $level);
  $_SESSION['solution'] = $generatedBoard;
  $_SESSION['puzzle'] = $puzzleBoard;
  header("Location: index.php");
  exit;
}

if (!isset($_SESSION['solution'])) {
  $generatedBoard = generateSudokuBoard();
  $puzzleBoard = makePuzzle($generatedBoard, 40);
  $_SESSION['solution'] = $generatedBoard;
  $_SESSION['puzzle'] = $puzzleBoard;
} else {
  $generatedBoard = $_SESSION['solution'];
  $puzzleBoard = $_SESSION['puzzle'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cell'])) {
  $user = $_POST['cell'];
  $solution = $_SESSION['solution'];
  $puzzleBoard = $_SESSION['puzzle'];

  $isCorrect = true;
  $hasEmpty = false;

  $wrongCells = [];

  for ($row = 0; $row < SIZE; $row++) {
      for ($col = 0; $col < SIZE; $col++) {
          if ($puzzleBoard[$row][$col] == 0) {
              $input = $user[$row][$col] ?? '';

               // 入力が空なら間違い
            if ($input === '' || !is_numeric($input)) {
              $hasEmpty = true;
              continue;
            }
            if ((int)$input !== $solution[$row][$col]) {
              $isCorrect = false;
              $wrongCells[$row][$col] = true;
            }
          }
      }
  }
  $_SESSION['userInput'] = $user;
  $_SESSION['wrongCells'] = $wrongCells;
  if ($hasEmpty) {
    $_SESSION['message'] = "<p class='message' style='color:#ff51ff; font-weight:bold; text-shadow:1px 1px 3px rgb(80, 106, 255);'>がんばれがんばれ〜(＾ω＾≡＾ω＾)</p>";
  } else {
    $_SESSION['message'] = $isCorrect
      ? "<p class='message' style='color:rgb(109, 58, 237); font-weight:bold; text-shadow:1px 1px 3px rgb(80, 106, 255);'>お見事〜v(｡･ω･｡)ｨｪｨ♪</p>"
      : "<p class='message' style='color:rgb(173, 52, 54); font-weight:bold; text-shadow:1px 1px 3px rgb(80, 106, 255);'>どっか違うっぽいよ〜(´・∀・｀ )ｱﾗﾏｧ</p>";
  }
  header("Location: index.php");
  exit;
}

$userInput = $_SESSION['userInput'] ?? [];
unset($_SESSION['userInput']);

// 表示前に取り出し
$wrongCells = $_SESSION['wrongCells'] ?? [];
unset($_SESSION['wrongCells']);



// 問題用に盤面を削る関数（$removeCountマスを空欄に）
function makePuzzle($board, $removeCount = 40) {
  $puzzle = $board;

  $removed = 0;
  while ($removed < $removeCount) {
      $row = rand(0, 8);
      $col = rand(0, 8);
      if ($puzzle[$row][$col] != 0) {
          $puzzle[$row][$col] = 0;
          $removed++;
      }
  }

  return $puzzle;
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
  html, body {
    overflow-x: hidden;
  }

  body {
    font-family: 'Segoe UI', sans-serif;
    background:radial-gradient(ellipse at bottom,rgb(100, 170, 202),rgb(112, 74, 124));
    text-align: center;
    margin-top: 10px;
    min-height: 100vh;
  }

  table {
    margin: 0 auto;
    border-collapse: collapse;
    box-shadow: 0 0 15px rgb(158, 76, 240);
    width: 100%;
    max-width: 500px;
    table-layout: fixed;
  }

  td {
    color:rgb(189, 67, 255);
    width: 40px;
    height: 40px;
    text-align: center;
    vertical-align: middle;
    font-size: 22px;
    background-color: none;
    border: 1px dotted #c184ff;
  }

  td.top-border {
    border-top: 3px solid rgb(158, 76, 240);
  }

  td.left-border {
    border-left: 3px solid rgb(158, 76, 240);
  }

  td.bottom-border {
    border-bottom: 3px solid rgb(158, 76, 240);
  }
  td.right-border {
    border-right: 3px solid rgb(158, 76, 240);
  }


  .block-a {
    /* background:radial-gradient(ellipse at top,rgb(112, 74, 124),rgb(100, 170, 202)); */
    background-color:rgb(215, 215, 255);
    opacity: 0.5;
  }
  .block-b {
    /* background:radial-gradient(ellipse at bottom,rgb(100, 170, 202),rgb(112, 74, 124)); */
    background-color:rgb(223, 238, 255);
    opacity: 0.5;
  }


  input[type="number"] {
    width: 38px;
    height: 38px;
    border: none;
    font-size: 20px;
    text-align: center;
    background-color: transparent;
  }

  input[type="number"]:focus {
    outline: 2px solid #4cc9f0;
    box-shadow: 0 0 5px #4cc9f0;
  }

  input[type="submit"] {
    margin-top: 10px;
    padding: 10px 20px;
    font-size: 16px;
    background: linear-gradient(135deg, #3a0ca3, #7209b7, #4361ee);
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
  }

  input[type="submit"]:hover {
    box-shadow: 0 0 15px #4cc9f0;
  }

  .message {
    font-size: 18px;
    margin-top: 15px;
  }

  .button-group {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
  }

  .header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 45px;
  background: rgba(80, 40, 120, 0.8);
  padding: 10px 0;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  overflow-x: hidden;
}

.header > p {
  color: white;
}

.header-buttons {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  max-width: 100%;
  justify-content: center;
  padding: 0 6px;
  gap: 4px;
  flex-grow: 1;
  flex-shrink: 1;
  box-sizing: border-box;
}

.header-buttons input[type="submit"],
.header-buttons select {
  margin-top: 0;
  margin-bottom: 0;
  padding: 6px 10px;
  font-size: 13px;
  height: auto;
  border-radius: 20px;
  border: none;
  max-width: 100%;
  flex-shrink: 1;
  min-width: 0;          /* ← デフォルトの最小幅を無効に */
  flex-shrink: 1;        /* ← 幅が足りない時に縮める */
  width: auto;           /* ← shrinkを活かすため */
  box-sizing: border-box;
}

.header-buttons select {
  color: white;
  background-color:rgb(170, 73, 235);
}


body {
  padding-top: 63px; /* ヘッダーの高さぶん余白を作る */
}

.difficulty-label {
  color: white;
  font-weight: bold;
  font-size: 12px;
}

select[name="level"] {
  color: white;
  background-color:rgb(168, 66, 236);
  border: none;
  padding: 5px 10px;
  border-radius: 8px;
  font-size: 14px;
}

td.wrong input[type="number"] {
  background-color: rgba(255, 100, 100, 0.5);
  border-radius: 6px;
  color: red;
  font-weight: bold;
}

td.wrong input.wrong-input {
  background-color: rgba(255, 100, 100, 0.5);
  border-radius: 6px;
  color: red;
  font-weight: bold;
}


@media screen and (max-width: 600px) {
  table {
    width: calc(100vw - 35px);          /* ← 横幅画面ぴったりに */
    max-width: 100vw;
    margin: 0 auto;
    table-layout: fixed;   /* ← これ重要！ */
  }

  td {
    width: calc((100vw - 35px) / 9);   /* ← 横幅を9等分 */
    height: calc((100vw - 35px) / 9);  /* ← 正方形になるように */
    font-size: 5vw;
    padding: 0;
  }

  input[type="number"] {
    width: 100%;
    height: 100%;
    font-size: 5vw;
  }

  input[type="submit"] {
    font-size: 14px;
    padding: 8px 16px;
  }

  .header {
    flex-direction: column;
    gap: 6px;
    padding: 6px 4px;
    height: 58px;
  }

  .header-buttons {
    flex-wrap: wrap;
    max-width: 100vw;
  }

  .header-buttons input[type="submit"],
  .header-buttons select {
    font-size: 12px;
    padding: 4px 8px;
  }

  .difficulty-label {
    font-size: 11px;
  }
}

</style>
</head>
<body>
<div class="header">
  <form method="post" class="header-buttons">
    <input type="submit" name="new_game" value="新しい問題にする">
    <input type="submit" name="reset_input" value="リセット">
  <label class="difficulty-label">
    難易度：
    <select name="level">
      <option value="30">easy</option>
      <option value="40">normal</option>
      <option value="50">hard</option>
    </select>
  </label>
    <input type="submit" name="new_game_with_level" value="この難易度で新しく始める">
  </form>
</div>

<?php
printBoard($puzzleBoard, true, $userInput, $wrongCells);
if (isset($_SESSION['message'])) {
  echo $_SESSION['message'];
  unset($_SESSION['message']);
}
?>
</body>
</html>