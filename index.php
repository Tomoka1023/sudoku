<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="favicon.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>まさ坊数独 - 難易度選択</title>
  <style>
    /* css */
    html {
      background:radial-gradient(ellipse at top,rgb(143, 70, 104),rgb(143, 108, 164));
      min-height: 100vh;
    }

    .title {
      background: linear-gradient(180deg,rgb(245, 210, 253) 40%, #ff3bef 80%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .fontColor{
      color: rgb(232, 190, 234);
      /* text-shadow: 0 0 5px rgb(228, 209, 253); */
    }

    span {
      color: rgb(230, 155, 234);
      /* text-shadow: 0 0 5px rgb(228, 209, 253); */
    }

    select[name="level"] {
      color: rgb(230, 155, 234);
      /* background-color:rgb(220, 179, 247); */
      background: none;
      /* border: none; */
      border-color:rgb(202, 131, 206);
      padding: 5px 10px;
      border-radius: 25px;
      font-size: 14px;
      box-shadow: 0 0 5px rgb(228, 209, 253);
    }

    input[type="submit"] {
      margin-top: 10px;
      padding: 10px 20px;
      font-size: 16px;
      background: linear-gradient(135deg,rgb(187, 35, 164),rgb(234, 70, 174),rgb(254, 120, 250));
      color:rgb(214, 214, 214);
      border: none;
      border-radius: 30px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      box-shadow: 0 0 15px #4cc9f0;
    }

    @media screen and (max-width: 600px) {
      h1 {
        font-size: 16px;
      }
    }

  </style>
</head>

<body style="text-align: center; margin-top: 50px; font-family: sans-serif;">
  <h1 class="title">＼( 'ω')／まさ坊数独へようこそ！＼('ω' )／</h1>
  <p class="fontColor"><span>Levelを選んでね⊂(´ω`⊂ )</span></p>
  <form action="play.php" method="post">
    <select name="level" style="padding: 10px; font-size: 16px;">
      <option value="30">easy</option>
      <option value="40" selected>normal</option>
      <option value="50">hard</option>
    </select>
    <br><br>
    <input type="submit" value="始めるよっ" style="padding: 10px 20px; font-size: 18px;">
  </form>
  <p class="fontColor">数独とは...φ(．． )ﾒﾓﾒﾓ<br>
  縦・横・3×3のブロックに、1〜9の数字を<br>
  重複せずに入れていくパズルゲームです！<br>
  難易度を選んで、自分のペースでチャレンジしてみてね♪<br>
  「答えをチェック」ボタンで結果を確認できます。<br>
  間違っていたらヒントとして色が変わるよ！</p>
</body>

</html>
