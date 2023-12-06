<!DOCTYPE html>
<html lang="en">
<head>
    <title>Display Movie Information</title>
    <meta charset="utf-8"/>
    <style>
        #output {
            display: flex;
            justify-content: space-between;
        }

        #movieList, #movieDetails {
            flex: 1;
            padding: 10px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
<form method="get">
    <label>Movie title: <input type="text" name="form-input" id="form-input" value="<?php echo isset($_GET['form-input']) ? $_GET['form-input'] : ''; ?>"/></label>
    <input type="submit" value="Display Info"/>
</form>
<div id="output">
    <div id="movieList">
        <?php
        if (isset($_GET['form-input'])) {
            $query = urlencode($_GET['form-input']);
            $movieData = getMovieData($query);
            $movieData = json_decode($movieData);
            displayClickableItems($movieData);
        }
        ?>
    </div>
    <div id="movieDetails">
        <?php
        if (isset($_GET['movieId'])) {
            $movieId = $_GET['movieId'];
            $movieDetails = getMovieDetails($movieId);
            $movieDetails = json_decode($movieDetails);
            showDetails($movieDetails);
        }
        ?>
    </div>
</div>
</body>
</html>

<?php
function getMovieData($query)
{
    $url = "https://api.themoviedb.org/3/search/movie?query=" . $query;
    $apiKey = "api-key";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url . "&api_key=" . $apiKey);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function displayClickableItems($jsonData)
{
    echo "<ul>";

    foreach ($jsonData->results as $movie) {
        $title = $movie->title;
        $movieId = $movie->id;
        $releaseYear = substr($movie->release_date, 0, 4);

        echo "<li><a href='?movieId=$movieId&form-input=" . urlencode($_GET['form-input']) . "'>" . $title . " (" . $releaseYear . ")</a></li>";
    }

    echo "</ul>";
}

function getMovieDetails($movieId)
{
    $url = "https://api.themoviedb.org/3/movie/" . $movieId;
    $apiKey = "api-key";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url . "?api_key=" . $apiKey);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function showDetails($movieId)
{
    $title = $movieId->title;
    $summary = $movieId->overview;
    $image = $movieId->poster_path;
    $genres = $movieId->genres;
    $genreList = "";

    if (count($genres) > 0) {
        $genreNames = array_map(function ($gen) {
            return $gen->name;
        }, $genres);

        $genreList = implode(', ', $genreNames);
    } else {
        $genreList = "None";
    }

    $cast = getMovieCast($movieId->id);

    echo "<h2> Title : $title</h2>";
    echo "<img src='http://image.tmdb.org/t/p/w185/$image' alt='alternatetext'>";
    echo "<h3> Overview : $summary</h3>";
    echo "<h3> Genre : $genreList</h3>";
    echo "<h3> Cast:</h3>";
    echo "<ul>";
    $top5Cast = array_slice($cast->cast, 0, 5); // Get the top 5 cast members
    foreach ($top5Cast as $actor) {
        echo "<li>" . $actor->name . "</li>";
    }
    echo "</ul>";
}

function getMovieCast($movieId)
{
    $url = "https://api.themoviedb.org/3/movie/" . $movieId . "/credits";
    $apiKey = "api-key";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url . "?api_key=" . $apiKey);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result);
}
?>
