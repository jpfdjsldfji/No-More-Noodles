<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <title>Method</title>
    <link rel="stylesheet" type="text/css" href="selected-result.css">
    <link href="https://fonts.googleapis.com/css2?family=Yusei+Magic&display=swap" rel="stylesheet">
  </head>
<body>
  <div class="form">
      <a href="#"><img id="logo" src="../index-page/logo.png"></a>
      <script type="text/javascript">
        function owned(x) {
          document.getElementById(x).style.color = "green";
        }
        function unowned(x) {
          document.getElementById(x).style.color = "blue";
        }
      </script>

<?php

  require('fpdf181/fpdf.php');

  $servername = "localhost";
  $username = "root";
  $password = "root";
  $db = "Y1";

  $conn = mysqli_connect($servername, $username, $password, $db);

  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }
  //echo "Connected successfully" . "<br />";

  //has to be get so it works as a link
  //not necessarily a bad thing as each recipe page has a unique URL
  $recipeID = $_GET['recipeId'];

  //uses recipeID to fetch name, image, ingredients, nutritional info, time, difficulty, method
  //not fetched but could be: user rating, popularity, flags
  $sql = "SELECT recipe_name, image, ingredients, calories, fat, carbs, salt, sugar, time, difficulty, method
  FROM recipes WHERE recipeId = '$recipeID'";
  $records = $conn->query($sql);
  $records = $records->fetch_assoc();

  //--- DISPLAY RECIPE NAME ---
  echo ("<h1>$records[recipe_name]</h1>");

  //--- DISPLAY RECIPE IMAGE ---
  echo "<img class='img' src = ../recipes-page/$records[image]>";

  //--- DISPLAY NUTRITIONAL INFORMATION SECTION ---

  echo "<div class='nutritional-facts-container'>";
  echo "<h2>Nutritional information</h2>";
  $output =  "<table>
  	           <tr>
  	            <th>Calories</th>
  	            <th>Fat</th>
  	            <th>Carbohydrates</th>
  	            <th>Salt</th>
  	            <th>Sugar</th>
  	           </tr>
  	           <tr>
  	            <td>$records[calories]</td>
  	            <td>$records[fat]</td>
  	            <td>$records[carbs]</td>
  	            <td>$records[salt]</td>
  	            <td>$records[sugar]r</td>
  	           </tr>
  	          </table>";

  echo $output;

  //display time (inside nutritional info container)
  echo "<p>Time: $records[time]</p>";
  echo "<p>Difficulty rating: $records[difficulty]</p>";
  echo "<button onclick='create_pdf.php'>Get shopping list</button>  ";
  echo "<button onclick='create_pdf.php'>Get method</button>  ";
  //display difficulty (inside nutritional info container)
  echo "</div>"; //close nutritional info container

  //--- DISPLAY INGREDIENTS SECTION ---
  echo "<div class='ingredients-container'>";
  echo "<h2>Ingredients</h2>";

  //split $records[ingredients] by the regex /~/
  $ingArray = preg_split("/~/", $records['ingredients']);
  $i = 0;
  echo "<ul>";
  foreach ($ingArray as $ing) {
    echo "<li id=ig" . $i . ">" . str_replace("+", " ", $ing) . "</li>";
    $i++;
  }
  echo "</ul>";
  echo "</div>";

  //--- DISPLAY METHOD SECTION ---
  //(much the same as ingredients)
  echo "<div class='steps-container'>";
  echo "<h2>Method</h2>";

  //split $records[method] by the regex /~/
  $metArray = preg_split("/~/", $records['method']);
  echo "<ol>";
  foreach ($metArray as $met) {
    echo "<li>$met</li>";
  }
  echo "</ol>";
  echo "</div>";



  // Selects owned ingredient
  $sql = "SELECT owned_ingredients FROM user WHERE userId=". $_SESSION['user_id'];
  if ($conn->query($sql)) {
    $records = $conn->query($sql);
    while ($row = $records->fetch_assoc()) {
      $current_ingredients = $row['owned_ingredients'];
    }
  }
  else {
    echo "Error: " . $conn->error . "<br />";
  }

  // Uses Luke's code from search-results
  $current_ingredientsArray = preg_split("/~/", $current_ingredients);
  $counter = 0;
  $owned_ingredients = array();
  $unowned_ingredients = array();
  $indexes = array();

  foreach ($ingArray as $ing) {
    $match = 0;
    foreach ($current_ingredientsArray as $ingredient) {
      $ingredient = trim($ingredient);
      $processed_string = trim(strtolower($ing));
      $temp = preg_split("/\+/", $processed_string);
      $processed_string = $temp[1];
      if ($ingredient == $processed_string) {
        $match = 1;
      }
    }
    if ($match == 1) {
      array_push($owned_ingredients, str_replace("+", " ", $ing));
      array_push($indexes, $counter);
    }
    else {
      array_push($unowned_ingredients, str_replace("+", " ", $ing));
    }
    $counter++;
  }

  $_SESSION['unowned_ingredients'] = $unowned_ingredients;
  $_SESSION['owned_ingredients'] = $owned_ingredients;

  for ($x=0;$x<count($ingArray);$x++)
  {
    $owned = 0;
    foreach($indexes as $index) {
      if ($x == $index) {
        $owned = 1;
      }
    }
    if ($owned == 1) {
        echo "<script type='text/javascript'>owned('ig" . $x . "');</script>";
    }
    else {
      echo "<script type='text/javascript'>unowned('ig" . $x . "');</script>";
    }
    $owned = 0;
  }


?>
</div>
</body>
</html>
