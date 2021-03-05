<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>STG Hall of Records - MediaWiki Image Scraper</title>
    <style>
      body {
          background-color: #EEEEEE;
          margin: 20px;
      }

      output stats {
          display: block;
      }
      output stat {
          display: block;
      }
      output stat-title::after {
          content: ": ";
      }
      output stats show-scores {
          font-size: small;
      }

      output messages {
          display: block;
      }
      output message {
          display: block;
          font-size: small;
      }
      output stats + messages {
          margin-top: 15px;
      }

      output game {
          display: block;
      }
      output game-name {
          display: block;
          font-size: large;
          font-weight: bold;
      }
      output game-message {
          display: block;
          font-size: small;
          padding: 5px;
      }
      output game + game,
      output message + game,
      output game + message {
          margin-top: 15px;
      }

      output score {
          display: block;
          #border: solid 1px #a5a5a5;
          padding: 5px;
      }
      output score:nth-child(2n+1) {
          background-color: #ddd;
      }
      output score:nth-child(2n) {
          background-color: #ccc;
      }
      output score-name {
          display: block;
          font-style: italic;
      }
      output score-entry {
          display: block;
          font-size: small;
      }
      output score-message::before {
          content: "- ";
      }
      output score-context {
          font-style: italic;
      }
      output context-name:first-child::before {
          content: "{ ";
      }
      output context-value:last-child::after {
          content: " }";
      }
      output context-name::after {
          content: ": ";
      }
      output context-value + context-name::before {
          content: " | ";
      }

      .error {
          color: #c81717;
      }
      .success {
          color: #17c817;
      }

      #runtimeLimit {
          width: 75px;
      }
    </style>
  </head>

  <body>
    <h1>MediaWiki Image Scraper</h1>

    <div class="error">{{ error }}</div>

    <p>This tool scrapes the images from the STG Hall of Records database and saves them on the seb server.</p>
    <p>Every image is only scraped once, so only images that are not already saved will be downloaded.</p>
    <p>Already saved images can be browsed <a href="{{ saveUrl }}">here</a>.</p>

    <form method="post">
    <div>
      <label for="runtimeLimit">Runtime limit (in seconds)</label>
      <input type="number"
             id="runtimeLimit"
             name="runtimeLimit"
             value="{{ runtimeLimit.value }}"
             min="{{ runtimeLimit.min }}"
             max="{{ runtimeLimit.max }}"
             step="0.1"
             />
    </div>
    <div>
        <input type="submit" name="scrap" value="Scrap images" />
    </div>
    </form>

{% if messages %}
    <br/>
    <hr/>

    <h2>Output</h2>

    {% set messages = include('media-wiki-image-scraper--messages-output.tpl') %}
    {% set numScores = [
        {
            'name': 'all',
            'title': 'Total',
            'value': messages|split('<score-name class=')|length - 1,
        },
        {
            'name': 'success',
            'title': 'Success',
            'value': messages|split('<score-name class="success"')|length - 1,
        },
        {
            'name': 'error',
            'title': 'Error',
            'value': messages|split('<score-name class="error"')|length - 1,
        },
        {
            'name': 'info',
            'title': 'Skipped',
            'value': messages|split('<score-name class="info"')|length - 1,
        },
    ] %}

    <output>
        <stats>
        {% for stat in numScores %}
            <stat class="{{ stat.name }}">
                <stat-title>{{ stat.title }}</stat-title>
                <stat-value>{{ stat.value }}</stat-value>
                <show-scores>
                    <a href="#" onclick="return hor_filterScores('{{ stat.name }}')">Show</a>
                </show-scores>
            </stat>
        {% endfor %}
            <stat>
                <stat-title>Elapsed time</stat-title>
                <stat-value>{{ elapsedTime|round(5) }}s</stat-value>
            </stat>
        </stats>

        {{ messages|raw }}

    </output>

    <script>
      function hor_filterScores(type) {
          // Hide all games.
          document.querySelectorAll('game').forEach(function(game) {
              game.style.display = 'none';
          });

          // Display scores.
          document.querySelectorAll('score-name').forEach(function(scoreName) {
              if (scoreName.className === type || type === 'all') {
                  scoreName.parentNode.style.display = 'block';
              } else {
                  scoreName.parentNode.style.display = 'none';
              }
          });

          let gameSelector = 'game score-name';
          if (type !== 'all') {
              gameSelector += '.' + type;
          }

          // Display games which have at least one score displayed.
          let gamesSeen = {};
          document.querySelectorAll(gameSelector).forEach(function(scoreName) {
              let game = scoreName.parentNode.parentNode;
              if (gamesSeen[game.id] !== true) {
                  game.style.display = 'block';
                  gamesSeen[game.id] = true;
              }
          });

          return false;
      }
    </script>
{% endif %}

</body>
</html>
